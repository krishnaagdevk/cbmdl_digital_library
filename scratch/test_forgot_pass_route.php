<?php
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost/cbmdl/admin-forgot-password',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HEADER => true
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP CODE: " . $httpCode . "\n";
if ($httpCode === 200) {
    echo "SUCCESS: Navigated to admin-forgot-password view without redirect!\n";
    if (strpos($response, 'Librarian Password Recovery') !== false) {
        echo "FOUND VIEW CONTENT: 'Librarian Password Recovery' is present in output!\n";
    } else {
        echo "WARNING: View title missing.\n";
    }
} else {
    echo "FAILED with status code: " . $httpCode . "\n";
    echo "RESPONSE HEADERS:\n" . substr($response, 0, 500) . "\n";
}
