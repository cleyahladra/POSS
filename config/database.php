<?php
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        if ($this->connection->connect_error) {
            die(json_encode([
                'error' => true,
                'message' => 'Database connection failed: ' . $this->connection->connect_error
            ]));
        }
        $this->connection->set_charset('utf8mb4');
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): mysqli {
        return $this->connection;
    }

    public function query(string $sql): mysqli_result|bool {
        $result = $this->connection->query($sql);
        if ($this->connection->error) {
            error_log('DB Error: ' . $this->connection->error . ' | Query: ' . $sql);
        }
        return $result;
    }

    public function prepare(string $sql): mysqli_stmt|false {
        return $this->connection->prepare($sql);
    }

    public function escape(string $value): string {
        return $this->connection->real_escape_string($value);
    }

    public function lastInsertId(): int {
        return $this->connection->insert_id;
    }

    public function affectedRows(): int {
        return $this->connection->affected_rows;
    }

    public function beginTransaction(): void {
        $this->connection->begin_transaction();
    }

    public function commit(): void {
        $this->connection->commit();
    }

    public function rollback(): void {
        $this->connection->rollback();
    }
}
