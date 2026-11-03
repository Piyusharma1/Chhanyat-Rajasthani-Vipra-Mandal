<?php
require_once __DIR__ . '/require_auth.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

$pageTarget = isset($_POST['page_target']) ? trim($_POST['page_target']) : '';
if (!$pageTarget) {
    echo json_encode(['success' => false, 'error' => 'कृपया पृष्ठ का चयन करें (मुखपृष्ठ या माह 1-12)।']);
    exit;
}

if (!isset($_FILES['page_image']) || $_FILES['page_image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'कृपया एक वैध इमेज फ़ाइल अपलोड करें।']);
    exit;
}

$file = $_FILES['page_image'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowedTypes)) {
    echo json_encode(['success' => false, 'error' => 'केवल JPG, PNG या WEBP इमेज ही मान्य हैं।']);
    exit;
}

$baseDir = __DIR__ . '/../uploads/calendar/';
if (!file_exists($baseDir)) {
    mkdir($baseDir, 0755, true);
}
$pagesDir = $baseDir . 'pages/';
if (!file_exists($pagesDir)) {
    mkdir($pagesDir, 0755, true);
}

// Helper to convert any image into optimized WebP
function convertToWebp($sourcePath, $destWebpPath, $mime) {
    if ($mime === 'image/webp') {
        return copy($sourcePath, $destWebpPath);
    }

    if (function_exists('imagewebp')) {
        $image = null;
        if ($mime === 'image/jpeg') {
            $image = @imagecreatefromjpeg($sourcePath);
        } elseif ($mime === 'image/png') {
            $image = @imagecreatefrompng($sourcePath);
            if ($image) {
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
            }
        }

        if ($image) {
            $result = imagewebp($image, $destWebpPath, 88);
            imagedestroy($image);
            if ($result && file_exists($destWebpPath)) {
                return true;
            }
        }
    }

    // Fallback using Python Pillow if available and exec enabled
    if (function_exists('exec')) {
        $pythonCmd = 'python3 -c "from PIL import Image; im = Image.open(r\'' . addslashes($sourcePath) . '\'); im.save(r\'' . addslashes($destWebpPath) . '\', \'WEBP\', quality=88)" 2>&1';
        @exec($pythonCmd);
        if (file_exists($destWebpPath)) {
            return true;
        }
        $pythonCmd2 = 'python -c "from PIL import Image; im = Image.open(r\'' . addslashes($sourcePath) . '\'); im.save(r\'' . addslashes($destWebpPath) . '\', \'WEBP\', quality=88)" 2>&1';
        @exec($pythonCmd2);
        if (file_exists($destWebpPath)) {
            return true;
        }
    }

    // Direct copy fallback
    if (@copy($sourcePath, $destWebpPath)) {
        return true;
    }

    return false;
}

// Helper to create resized WebP thumbnail
function createCalendarThumbnail($sourceWebpPath, $destThumbPath, $maxW = 600, $maxH = 800) {
    $dir = dirname($destThumbPath);
    if (!file_exists($dir)) {
        @mkdir($dir, 0755, true);
    }
    if (function_exists('imagecreatefromwebp') && function_exists('imagewebp')) {
        $src = @imagecreatefromwebp($sourceWebpPath);
        if ($src) {
            $w = imagesx($src);
            $h = imagesy($src);
            $ratio = min($maxW / $w, $maxH / $h, 1.0);
            $newW = (int)round($w * $ratio);
            $newH = (int)round($h * $ratio);
            $thumb = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($thumb, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
            imagewebp($thumb, $destThumbPath, 82);
            imagedestroy($thumb);
            imagedestroy($src);
            return file_exists($destThumbPath);
        }
    }
    return @copy($sourceWebpPath, $destThumbPath);
}

$thumbDir = $baseDir . 'thumbs/';
if (!file_exists($thumbDir)) {
    @mkdir($thumbDir, 0755, true);
}

if ($pageTarget === 'cover') {
    $targetWebp = $baseDir . 'cover.webp';
    $targetPng = $baseDir . 'cover.png';
    $targetThumb = $thumbDir . 'cover.webp';

    if (convertToWebp($file['tmp_name'], $targetWebp, $mime)) {
        // Also keep PNG in sync
        @copy($file['tmp_name'], $targetPng);
        createCalendarThumbnail($targetWebp, $targetThumb, 600, 900);

        echo json_encode([
            'success' => true,
            'message' => 'मुखपृष्ठ (Cover Page) WebP प्रारूप में सफलतापूर्वक अपडेट हो गया!',
            'url' => 'uploads/calendar/cover.webp?v=' . time()
        ]);
        exit;
    }
} else {
    $monthNum = (int)$pageTarget;
    if ($monthNum < 1 || $monthNum > 12) {
        echo json_encode(['success' => false, 'error' => 'अमान्य माह नंबर।']);
        exit;
    }

    $targetWebp = $pagesDir . $monthNum . '.webp';
    $targetThumb = $thumbDir . $monthNum . '.webp';

    if (convertToWebp($file['tmp_name'], $targetWebp, $mime)) {
        if ($mime === 'image/jpeg') {
            @copy($file['tmp_name'], $pagesDir . $monthNum . '.jpg');
        } elseif ($mime === 'image/png') {
            @copy($file['tmp_name'], $pagesDir . $monthNum . '.png');
        }

        createCalendarThumbnail($targetWebp, $targetThumb, 600, 750);

        echo json_encode([
            'success' => true,
            'message' => "माह {$monthNum} का पृष्ठ WebP प्रारूप में सफलतापूर्वक अपडेट हो गया!",
            'url' => "uploads/calendar/pages/{$monthNum}.webp?v=" . time()
        ]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'फ़ाइल को WebP प्रारूप में सहेजने में त्रुटि हुई।']);
?>
