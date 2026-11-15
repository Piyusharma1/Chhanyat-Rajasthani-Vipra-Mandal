<?php
// api/ip_helper.php
// Safely resolves the real client IP address.
// Trusts X-Forwarded-For ONLY when the connection comes from a known
// reverse-proxy / load-balancer IP. On shared hosting (Hostinger) the
// server talks directly to the client, so REMOTE_ADDR is always the
// canonical source; we fall back to X-Forwarded-For only when
// REMOTE_ADDR is itself a known private/loopback range.

if (!function_exists('get_client_ip')) {
    /**
     * Returns a validated, safe client IP string (max 45 chars for IPv6).
     */
    function get_client_ip(): string {
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        // Trust X-Forwarded-For only when REMOTE_ADDR is a private/loopback
        // address (i.e. the request is coming through a local proxy).
        // On public shared hosting REMOTE_ADDR is the real client IP.
        if (_is_private_ip($remoteAddr) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // X-Forwarded-For can be a comma-separated list; take the first
            // non-private entry that looks like a valid IP.
            $candidates = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($candidates as $candidate) {
                $candidate = trim($candidate);
                if (filter_var($candidate, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return substr($candidate, 0, 45);
                }
            }
        }

        // Always fall back to the TCP-level REMOTE_ADDR
        return substr($remoteAddr, 0, 45);
    }

    /**
     * Returns true when the IP is a private / loopback / link-local address.
     */
    function _is_private_ip(string $ip): bool {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
?>

