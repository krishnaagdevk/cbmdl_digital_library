<?php
 
function machine_license_id() {
    return gethostname();
}
 
function generate_license_key($machineId) {
    $salt = "mrt_license_salt_2026";
    return hash("sha256", $machineId . "|" . $salt);
}
$licenseFile = __DIR__ . "/actions.txt";
$currentKey = generate_license_key(machine_license_id());
$savedKey = file_exists($licenseFile) ? trim(file_get_contents($licenseFile)) : '';
 
if (!hash_equals($savedKey, $currentKey)) {
    @file_put_contents($licenseFile, $currentKey);
}