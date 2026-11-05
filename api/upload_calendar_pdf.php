<?php
require_once __DIR__ . '/require_auth.php';
header('Content-Type: application/json; charset=utf-8');
@set_time_limit(180); // Allow ample time for PDF page rendering

if (!isset($_FILES['calendar_pdf']) || $_FILES['calendar_pdf']['error'] !== UPLOAD_ERR_OK) {
    $errorCodes = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds server upload size limit.',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds form size limit.',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No PDF file was selected.',
    ];
    $code = $_FILES['calendar_pdf']['error'] ?? 'unknown';
    echo json_encode(['success' => false, 'error' => $errorCodes[$code] ?? 'Upload failed.']);
    exit;
}

$file = $_FILES['calendar_pdf'];
$maxSize = 50 * 1024 * 1024; // 50MB

if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'error' => 'PDF size cannot exceed 50MB.']);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowedMimes = ['application/pdf', 'application/x-pdf', 'application/acrobat', 'applications/vnd.pdf', 'text/pdf', 'text/x-pdf'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if ($ext !== 'pdf' && !in_array($mime, $allowedMimes)) {
    echo json_encode(['success' => false, 'error' => 'Only PDF files (.pdf) are allowed.']);
    exit;
}

$targetDir = __DIR__ . '/../uploads/calendar/';
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$targetFile = $targetDir . 'calendar.pdf';

if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save uploaded PDF file.']);
    exit;
}

// Helper to detect Python executable with PyMuPDF installed
function findPythonWithPyMuPDF() {
    $candidates = [
        'C:\\Python314\\python.exe',
        'python',
        'py',
        'python3',
        '/usr/bin/python3',
        '/usr/local/bin/python3',
        '/usr/bin/python'
    ];
    if (function_exists('exec')) {
        foreach ($candidates as $cmd) {
            $out = [];
            $code = 1;
            @exec("$cmd -c \"import fitz; print('OK')\" 2>&1", $out, $code);
            if ($code === 0) {
                foreach ($out as $line) {
                    if (trim($line) === 'OK') return $cmd;
                }
            }
        }
    }
    return null;
}

$extractScript = realpath(__DIR__ . '/../scripts/extract_calendar_pages.py');
$pythonCmd = findPythonWithPyMuPDF();
$extracted = false;
$extractDetails = '';
$generatedCount = 0;

if ($extractScript && file_exists($extractScript) && $pythonCmd) {
    $cmd = escapeshellarg($pythonCmd) . ' ' . escapeshellarg($extractScript) . ' 2>&1';
    $out = [];
    $retCode = 1;
    exec($cmd, $out, $retCode);
    $extractDetails = implode("\n", $out);
    
    $pagesDir = __DIR__ . '/../uploads/calendar/pages/';
    for ($m = 1; $m <= 12; $m++) {
        if (file_exists($pagesDir . $m . '.webp') || file_exists($pagesDir . $m . '.jpg')) {
            $generatedCount++;
        }
    }
    if ($retCode === 0) {
        $extracted = true;
    }
}

if ($extracted) {
    $msg = "कैलेंडर PDF अपलोड हो गई और समस्त १२ माह के पृष्ठ (WebP) सफलतापूर्वक तैयार हो गए!";
} else {
    $msg = "कैलेंडर PDF अपलोड हो गई! (यदि पृष्ठ स्वतः अपडेट न हों, तो कृपया 'पुनः निकालें' बटन दबाएं या 'फ़ोटो बदलें' से सीधे WebP अपलोड करें।)";
}

log_activity('UPLOAD', 'calendar', 'calendar.pdf', 'वार्षिक सनातन कैलेंडर PDF', [
    'size' => $file['size'],
    'extracted_pages' => $generatedCount
]);

echo json_encode([
    'success'         => true,
    'message'         => $msg,
    'extracted'       => $extracted,
    'generated_count' => $generatedCount,
    'url'             => 'uploads/calendar/calendar.pdf',
    'details'         => $extractDetails
]);
?>
