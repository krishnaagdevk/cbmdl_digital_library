<?php
namespace App\Core;
use mysqli;

final class Database {
    private mysqli $connection;

    public function __construct() {
        $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
        $user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
        $pass = isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : (getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
        $name = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'cbmdl';
        $port = (int)($_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: 3306);

        $connected = $this->tryConnect($host, $user, $pass, $name, $port);

        // If host was localhost and failed, try 127.0.0.1 explicitly (resolves IPv6 ::1 vs IPv4 binding issue on Windows)
        if (!$connected && ($host === 'localhost' || $host === '127.0.0.1')) {
            $altHost = ($host === 'localhost') ? '127.0.0.1' : 'localhost';
            $connected = $this->tryConnect($altHost, $user, $pass, $name, $port);
        }

        // 1. If connection to database failed, try auto-creating database and importing schema
        if (!$connected) {
            $connected = $this->autoProvisionDatabase($host, $user, $pass, $name, $port);
        }

        // 2. If MySQL server process seems down on local Windows XAMPP, attempt self-healing restart
        if (!$connected && (strpos($host, 'localhost') !== false || $host === '127.0.0.1')) {
            $this->attemptXamppSelfHealing();
            sleep(1);
            $connected = $this->tryConnect($host, $user, $pass, $name, $port);
            if (!$connected) {
                $connected = $this->autoProvisionDatabase($host, $user, $pass, $name, $port);
            }
        }

        // 3. Fallbacks for standard local configurations if env connection failed
        if (!$connected) {
            $fallbacks = [
                ['localhost', 'root', '', 'cbmdl'],
                ['localhost', 'root', '', 'mrt_library'],
                ['localhost', 'root', '', 'svpsm27m_cbmdl']
            ];

            foreach ($fallbacks as $fb) {
                if ($this->tryConnect($fb[0], $fb[1], $fb[2], $fb[3], 3306)) {
                    $connected = true;
                    break;
                }
            }
        }

        if (!$connected || $this->connection->connect_error) {
            $this->renderFriendlyErrorPage();
            exit;
        }

        $this->connection->set_charset('utf8mb4');
    }

    private function tryConnect(string $host, string $user, string $pass, string $name, int $port): bool {
        try {
            mysqli_report(MYSQLI_REPORT_OFF);
            $conn = @new mysqli($host, $user, $pass, $name, $port);
            if (!$conn->connect_error) {
                $this->connection = $conn;
                return true;
            }
        } catch (\Throwable $e) {
            return false;
        }
        return false;
    }

    private function autoProvisionDatabase(string $host, string $user, string $pass, string $name, int $port): bool {
        try {
            mysqli_report(MYSQLI_REPORT_OFF);
            // Connect to server without selecting database
            $serverConn = @new mysqli($host, $user, $pass, null, $port);
            if ($serverConn->connect_error) {
                return false;
            }

            // Create Database if missing
            $dbnameSanitized = preg_replace('/[^a-zA-Z0-9_]/', '', $name ?: 'cbmdl');
            if (empty($dbnameSanitized)) $dbnameSanitized = 'cbmdl';

            $serverConn->query("CREATE DATABASE IF NOT EXISTS `$dbnameSanitized` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            if (!$serverConn->select_db($dbnameSanitized)) {
                $serverConn->close();
                return false;
            }

            $this->connection = $serverConn;

            // Check if core tables exist
            $res = $this->connection->query("SHOW TABLES LIKE 'members'");
            if ($res && $res->num_rows === 0) {
                // Import schema.sql automatically
                $schemaFile = dirname(__DIR__, 2) . '/schema.sql';
                if (is_file($schemaFile)) {
                    $sql = file_get_contents($schemaFile);
                    if ($sql) {
                        $this->importSql($sql);
                    }
                }
            }

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function importSql(string $sql): void {
        $lines = explode("\n", $sql);
        $cleanSql = '';
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || strpos($trimmed, '--') === 0 || strpos($trimmed, '/*') === 0 || strpos($trimmed, 'START TRANSACTION') === 0 || strpos($trimmed, 'COMMIT') === 0) {
                continue;
            }
            $cleanSql .= $line . "\n";
        }

        $statements = array_filter(array_map('trim', explode(';', $cleanSql)));
        foreach ($statements as $stmt) {
            if (!empty($stmt)) {
                @$this->connection->query($stmt);
            }
        }
    }

    private function attemptXamppSelfHealing(): void {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $dataDir = 'C:\\xampp\\mysql\\data';
            if (is_dir($dataDir)) {
                @unlink($dataDir . '\\aria_log.00000001');
                @unlink($dataDir . '\\aria_log_control');

                $cmd = 'start /B C:\\xampp\\mysql\\bin\\mysqld.exe --defaults-file=C:\\xampp\\mysql\\bin\\my.ini';
                if (function_exists('pclose') && function_exists('popen')) {
                    @pclose(@popen($cmd, "r"));
                }
            }
        }
    }

    private function renderFriendlyErrorPage(): void {
        http_response_code(503);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Cantonment Digital Library - Service Status</title>
            <style>
                body { font-family: system-ui, -apple-system, sans-serif; background: #f8fafc; color: #1e293b; margin: 0; padding: 40px 20px; display: flex; justify-content: center; align-items: center; min-height: 80vh; }
                .card { background: #ffffff; max-width: 520px; width: 100%; border-radius: 16px; border: 1px solid #e2e8f0; padding: 32px; box-shadow: 0 10px 30px rgba(15,23,42,0.06); text-align: center; }
                .icon-badge { width: 64px; height: 64px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 16px; }
                h2 { font-size: 20px; color: #0f172a; margin: 0 0 8px 0; }
                p { font-size: 13.5px; color: #64748b; line-height: 1.5; margin: 0 0 20px 0; }
                .btn { display: inline-block; background: #2563eb; color: #ffffff; font-weight: 600; font-size: 14px; padding: 12px 24px; border-radius: 10px; text-decoration: none; border: none; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(37,99,235,0.2); }
                .btn:hover { background: #1d4ed8; }
                .steps { text-align: left; background: #f1f5f9; border-radius: 10px; padding: 14px 18px; margin-top: 24px; font-size: 12.5px; color: #334155; }
                .steps ol { margin: 6px 0 0 0; padding-left: 20px; }
                .steps li { margin-bottom: 4px; }
            </style>
            <script>
                setTimeout(function() { window.location.reload(); }, 5000);
            </script>
        </head>
        <body>
            <div class="card">
                <div class="icon-badge">⚡</div>
                <h2>Digital Library Database Connecting...</h2>
                <p>The system is initializing or connecting to the database server. If this is a fresh setup or local server restart, it will connect automatically in a few seconds.</p>
                
                <button class="btn" onclick="window.location.reload();">🔄 Auto-Repair & Reconnect Now</button>

                <div class="steps">
                    <strong>For Local XAMPP Administrators:</strong>
                    <ol>
                        <li>Open <strong>XAMPP Control Panel</strong> on your desktop.</li>
                        <li>Click <strong>"Start"</strong> next to <strong>MySQL</strong>.</li>
                        <li>This page will automatically refresh and open the portal!</li>
                    </ol>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    public function connection(): mysqli { return $this->connection; }
}
