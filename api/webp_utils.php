<?php
/**
 * WebP Conversion Utility
 * Automatically converts uploaded images (JPEG, PNG, GIF, WebP) to optimized WebP.
 */

if (!function_exists('convertToWebp')) {
    function convertToWebp($sourcePath, $destinationWebpPath, $quality = 85) {
        if (!file_exists($sourcePath)) {
            return false;
        }

        // Ensure target directory exists
        $dir = dirname($destinationWebpPath);
        if (!file_exists($dir)) {
            @mkdir($dir, 0755, true);
        }

        // Detect MIME type
        $mime = '';
        if (function_exists('mime_content_type')) {
            $mime = mime_content_type($sourcePath);
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $sourcePath);
            finfo_close($finfo);
        }

        // If already WebP
        if ($mime === 'image/webp') {
            if ($sourcePath === $destinationWebpPath) {
                return true;
            }
            if (is_uploaded_file($sourcePath)) {
                return move_uploaded_file($sourcePath, $destinationWebpPath);
            }
            return copy($sourcePath, $destinationWebpPath);
        }

        // Convert using GD imagewebp
        if (function_exists('imagewebp')) {
            $image = null;

            if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
                $image = @imagecreatefromjpeg($sourcePath);
            } elseif ($mime === 'image/png') {
                $image = @imagecreatefrompng($sourcePath);
            } elseif ($mime === 'image/gif') {
                $image = @imagecreatefromgif($sourcePath);
            }

            if (!$image && function_exists('imagecreatefromstring')) {
                $content = @file_get_contents($sourcePath);
                if ($content !== false) {
                    $image = @imagecreatefromstring($content);
                }
            }

            if ($image) {
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);

                if (function_exists('exif_read_data') && ($mime === 'image/jpeg' || $mime === 'image/jpg')) {
                    $exif = @exif_read_data($sourcePath);
                    if (!empty($exif['Orientation'])) {
                        switch ($exif['Orientation']) {
                            case 3: $image = imagerotate($image, 180, 0); break;
                            case 6: $image = imagerotate($image, -90, 0); break;
                            case 8: $image = imagerotate($image, 90, 0); break;
                        }
                    }
                }

                $saved = @imagewebp($image, $destinationWebpPath, $quality);
                imagedestroy($image);

                if ($saved && file_exists($destinationWebpPath) && filesize($destinationWebpPath) > 0) {
                    return true;
                }
            }
        }

        // Fallback: save original
        if (is_uploaded_file($sourcePath)) {
            return move_uploaded_file($sourcePath, $destinationWebpPath);
        }
        return copy($sourcePath, $destinationWebpPath);
    }
}
?>
