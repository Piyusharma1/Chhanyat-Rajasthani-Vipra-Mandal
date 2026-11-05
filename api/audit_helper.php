<?php
// api/audit_helper.php
// Centralized Activity Logging / Audit Trail Helper
// Records all admin actions (add, update, delete, login, etc.)

require_once __DIR__ . '/session_helper.php';
require_once __DIR__ . '/db.php';

if (!function_exists('ensure_audit_logs_table')) {
    function ensure_audit_logs_table(PDO $pdo): void {
        static $ensured = false;
        if ($ensured) return;

        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS `audit_logs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `admin_id` INT NULL,
                `admin_username` VARCHAR(100) NOT NULL,
                `admin_name` VARCHAR(150) NULL,
                `action` VARCHAR(50) NOT NULL,
                `category` VARCHAR(50) NOT NULL,
                `target_id` VARCHAR(100) NULL,
                `target_name` VARCHAR(255) NULL,
                `details` TEXT NULL,
                `ip_address` VARCHAR(45) NOT NULL,
                `user_agent` VARCHAR(255) NULL,
                `created_at` DATETIME NOT NULL,
                INDEX `idx_category` (`category`),
                INDEX `idx_action` (`action`),
                INDEX `idx_admin` (`admin_username`),
                INDEX `idx_created` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            $ensured = true;
        } catch (Exception $e) {
            error_log('Audit table check error: ' . $e->getMessage());
        }
    }
}

if (!function_exists('log_activity')) {
    /**
     * Record an administrative activity to audit_logs
     *
     * @param string $action    Action code: 'CREATE', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT', 'PASSWORD_CHANGE', 'STATUS_CHANGE', etc.
     * @param string $category  Module: 'family', 'committee', 'announcement', 'gallery', 'ad', 'calendar', 'admin', 'auth'
     * @param string|null $targetId   Target record ID (e.g. 'FAM-123456', admin ID, image filename)
     * @param string|null $targetName Target display name (e.g. 'राकेश शर्मा', 'दीपावली मिलन')
     * @param array|string|null $details Additional context (array will be JSON-encoded, string used as-is)
     * @return bool
     */
    function log_activity(
        string $action,
        string $category,
        ?string $targetId = null,
        ?string $targetName = null,
        $details = null
    ): bool {
        global $pdo;
        if (!$pdo) return false;

        try {
            ensure_audit_logs_table($pdo);

            if (session_status() === PHP_SESSION_NONE) {
                @session_start();
            }

            $adminId       = $_SESSION['admin_id'] ?? null;
            $adminUsername = !empty($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'admin';
            $adminName     = !empty($_SESSION['admin_full_name']) ? $_SESSION['admin_full_name'] : $adminUsername;

            $clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $clientIp = trim($parts[0]);
            }

            $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

            if (is_array($details) || is_object($details)) {
                $detailsStr = json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                $detailsStr = (string)$details;
            }

            $stmt = $pdo->prepare("
                INSERT INTO audit_logs (
                    admin_id, admin_username, admin_name,
                    action, category, target_id, target_name,
                    details, ip_address, user_agent, created_at
                ) VALUES (
                    :admin_id, :admin_username, :admin_name,
                    :action, :category, :target_id, :target_name,
                    :details, :ip_address, :user_agent, NOW()
                )
            ");

            return $stmt->execute([
                ':admin_id'       => $adminId,
                ':admin_username' => $adminUsername,
                ':admin_name'     => $adminName,
                ':action'         => strtoupper(trim($action)),
                ':category'       => strtolower(trim($category)),
                ':target_id'      => $targetId !== null ? substr((string)$targetId, 0, 100) : null,
                ':target_name'    => $targetName !== null ? substr((string)$targetName, 0, 255) : null,
                ':details'        => $detailsStr,
                ':ip_address'     => substr($clientIp, 0, 45),
                ':user_agent'     => $userAgent
            ]);
        } catch (Exception $e) {
            error_log('Audit log recording failed: ' . $e->getMessage());
            return false;
        }
    }
}
?>
