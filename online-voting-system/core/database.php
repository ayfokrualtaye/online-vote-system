<?php
require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            die(json_encode(['error' => 'Database connection failed.']));
        }
        $this->conn->set_charset('utf8mb4');
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): mysqli {
        return $this->conn;
    }

    // Prepared query helper
    public function query(string $sql, string $types = '', array $params = []): mysqli_stmt|false {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }

    // Fetch all rows
    public function fetchAll(string $sql, string $types = '', array $params = []): array {
        $stmt = $this->query($sql, $types, $params);
        if (!$stmt) return [];
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Fetch single row
    public function fetchOne(string $sql, string $types = '', array $params = []): array|null {
        $stmt = $this->query($sql, $types, $params);
        if (!$stmt) return null;
        $result = $stmt->get_result()->fetch_assoc();
        return $result ?: null;
    }

    // Insert and return last ID
    public function insert(string $sql, string $types = '', array $params = []): int|false {
        $stmt = $this->query($sql, $types, $params);
        if (!$stmt) return false;
        return $this->conn->insert_id;
    }
}
