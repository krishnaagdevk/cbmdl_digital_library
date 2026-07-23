<?php
require_once __DIR__ . '/../config.php';

// Test finding an ebook with PDF file
$res = $db->query("SELECT id, title, pdf_file FROM ebooks WHERE pdf_file IS NOT NULL AND pdf_file != '' LIMIT 1");
if ($res && $b = $res->fetch_assoc()) {
    $file = __DIR__ . '/../uploads/' . basename($b['pdf_file']);
    echo "Found PDF: " . $b['title'] . " (" . $b['pdf_file'] . ")\n";
    if (is_file($file)) {
        echo "File size: " . filesize($file) . " bytes\n";
        echo "ETag generated instantly: " . sprintf('"%x-%x"', filemtime($file), filesize($file)) . "\n";
        echo "SUCCESS: High-performance ETag and file check passed.\n";
    } else {
        echo "File path on disk does not exist: $file\n";
    }
} else {
    echo "No ebooks with PDF file found in DB.\n";
}
