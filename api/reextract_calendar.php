<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');
@set_time_limit(180);

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'अनधिकृत पहुंच। कृपया एडमिन लॉगिन करें।']);
    exit;
}

$pdfPath = realpath(__DIR__ . '/../uploads/calendar/calendar.pdf');
if (!$pdfPath || !file_exists($pdfPath)) {
    echo json_encode(['success' => false, 'error' => 'कैलेंडर PDF फ़ाइल uploads/calendar/calendar.pdf नहीं मिली। पहले PDF अपलोड करें।']);
    exit;
}

$extractScript = realpath(__DIR__ . '/../scripts/extract_calendar_pages.py');
if (!$extractScript || !file_exists($extractScript)) {
    echo json_encode(['success' => false, 'error' => 'एक्सट्रैक्शन स्क्रिप्ट नहीं मिली।']);
    exit;
}

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

$pythonCmd = findPythonWithPyMuPDF();

if (!$pythonCmd) {
    echo json_encode([
        'success' => false,
        'error' => 'सर्वर पर Python या PyMuPDF उपलब्ध नहीं है। कृपया प्रत्येक माह के कार्ड पर "फ़ोटो बदलें" बटन का उपयोग करके सीधे WebP/JPG चित्र अपलोड करें।'
    ]);
    exit;
}

$cmd = escapeshellarg($pythonCmd) . ' ' . escapeshellarg($extractScript) . ' 2>&1';
$output = [];
$returnVar = 1;
exec($cmd, $output, $returnVar);

$outputStr = implode("\n", $output);

// Check if pages were generated
$pagesDir = __DIR__ . '/../uploads/calendar/pages/';
$generatedCount = 0;
for ($m = 1; $m <= 12; $m++) {
    if (file_exists($pagesDir . $m . '.webp') || file_exists($pagesDir . $m . '.jpg')) {
        $generatedCount++;
    }
}

if ($returnVar !== 0 && $generatedCount === 0) {
    echo json_encode([
        'success' => false,
        'error' => 'PDF से पृष्ठ निकालने में समस्या आई: ' . ($outputStr ?: 'पायथन लाइब्रेरी (PyMuPDF) उपलब्ध नहीं है। कृपया "फ़ोटो बदलें" बटन से सीधे WebP चित्र अपलोड करें।')
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => "कैलेंडर पृष्ठ सफलतापूर्वक निकाले गए! कुल {$generatedCount}/12 माह पृष्ठ तैयार हैं।",
    'generated_count' => $generatedCount,
    'details' => $outputStr
]);
?>
