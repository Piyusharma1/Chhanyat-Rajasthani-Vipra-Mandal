<?php
require_once 'require_auth.php';
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $photos = [];
    $seen = [];

    $normalizeKey = function($pathOrUrl) {
        $clean = trim((string)$pathOrUrl);
        $path = parse_url($clean, PHP_URL_PATH);
        $basename = basename($path ?: $clean);
        // URL decode in case database stored URL-encoded filename (%E0%A4%AC...)
        $decoded = rawurldecode($basename);
        return mb_strtolower($decoded, 'UTF-8');
    };

    // 1. Fetch from database families table
    if (isset($pdo) && $pdo) {
        try {
            $stmt = $pdo->query("SELECT id, head, branch, photo FROM families WHERE photo IS NOT NULL AND TRIM(photo) != '' ORDER BY id DESC");
            $families = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($families as $f) {
                $rawUrl = trim($f['photo']);
                if (empty($rawUrl)) continue;

                $key = $normalizeKey($rawUrl);
                if (empty($key)) continue;

                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $filename = rawurldecode(basename(parse_url($rawUrl, PHP_URL_PATH) ?: $rawUrl));
                    $photos[] = [
                        'url' => $rawUrl,
                        'head' => $f['head'] ?? '',
                        'branch' => $f['branch'] ?? '',
                        'family_id' => $f['id'] ?? '',
                        'filename' => $filename,
                        'source' => 'db'
                    ];
                }
            }
        } catch (Exception $dbErr) {
            error_log("DB get_family_photos error: " . $dbErr->getMessage());
        }
    }

    // 2. Also scan uploads/families directory to capture any files on disk
    $uploadDir = __DIR__ . '/../uploads/families/';
    if (is_dir($uploadDir)) {
        $files = scandir($uploadDir);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'avif'];
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExtensions)) continue;

            $key = $normalizeKey($file);
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $nameWithoutExt = pathinfo($file, PATHINFO_FILENAME);
                $cleanName = str_replace('_', ' ', $nameWithoutExt);

                $photos[] = [
                    'url' => 'uploads/families/' . $file,
                    'head' => $cleanName,
                    'branch' => '',
                    'filename' => $file,
                    'source' => 'file'
                ];
            }
        }
    }

    echo json_encode([
        'success' => true,
        'count' => count($photos),
        'data' => $photos
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
