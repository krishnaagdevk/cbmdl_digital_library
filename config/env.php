<?php
// Load environment configuration
$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            // Strip matching quotes if any
            if (preg_match('/^"(.*)"$/', $val, $matches) || preg_match('/^\'(.*)\'$/', $val, $matches)) {
                $val = $matches[1];
            }
            $_ENV[$key] = $val;
            putenv("$key=$val");
        }
    }
}

if (!defined('BASE_URL')) {
    define('BASE_URL', $_ENV['BASE_URL'] ?? '/cbmdl/');
}

if (!function_exists('get_admin_master_pin')) {
    function get_admin_master_pin(): string {
        return $_ENV['ADMIN_MASTER_PIN'] ?? getenv('ADMIN_MASTER_PIN') ?: '1953';
    }
}
