<?php
namespace App\Core;
use mysqli;

final class Database {
    private mysqli $connection;
    public function __construct() {
        $connected = false;

        // 1. Attempt connection using .env variables first
        $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
        $user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
        $pass = isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : (getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
        $name = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: '';
        $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: 3306;

        if ($name) {
            try {
                $this->connection = new mysqli($host, $user, $pass, $name, (int)$port);
                if (!$this->connection->connect_error) {
                    $connected = true;
                }
            } catch (\mysqli_sql_exception $e) {
                // Let it fallback
            }
        }

        // 2. Fallbacks for standard local configurations if env connection failed or DB was empty
        if (!$connected) {
            $fallbacks = [
                ['localhost', 'root', '', 'mrt_library'],
                ['localhost', 'root', '', 'cbmdl'],
                ['localhost', 'root', '', 'svpsm27m_cbmdl']
            ];

            foreach ($fallbacks as $fb) {
                try {
                    $this->connection = new mysqli($fb[0], $fb[1], $fb[2], $fb[3]);
                    if (!$this->connection->connect_error) {
                        $connected = true;
                        break;
                    }
                } catch (\mysqli_sql_exception $e) {
                    continue;
                }
            }
        }

        if (!$connected || $this->connection->connect_error) {
            die('Database connection failed. Please create the database (e.g., mrt_library) in phpMyAdmin, import schema.sql, or configure .env.');
        }
        $this->connection->set_charset('utf8mb4');
        $this->connection->query("SET time_zone = '+05:30'");
    }
    public function connection(): mysqli { return $this->connection; }
}
