<?php

namespace App\Core;

use SQLite3;
use Exception;

/**
 * Database - Database abstraction layer with prepared statements
 */
class Database
{
    private static ?Database $instance = null;
    private ?SQLite3 $connection = null;
    private string $dbPath;

    private function __construct(string $dbPath)
    {
        $this->dbPath = $dbPath;
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            $dbPath = $_ENV['DB_PATH'] ?? __DIR__ . '/../../bd/bd_teste.db';
            self::$instance = new self($dbPath);
        }
        return self::$instance;
    }

    /**
     * Get database connection
     */
    public function getConnection(): SQLite3
    {
        if ($this->connection === null) {
            try {
                $this->connection = new SQLite3($this->dbPath);
                $this->connection->enableExceptions(true);
            } catch (Exception $e) {
                error_log("Database connection error: " . $e->getMessage());
                throw new Exception("Erro ao conectar à base de dados.");
            }
        }
        return $this->connection;
    }

    /**
     * Execute a query with parameters (SELECT)
     */
    public function query(string $sql, array $params = []): array
    {
        $db = $this->getConnection();
        $stmt = $db->prepare($sql);

        if ($stmt === false) {
            throw new Exception("Failed to prepare statement");
        }

        $this->bindParams($stmt, $params);
        $result = $stmt->execute();

        $rows = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Execute a query and return first row
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        $results = $this->query($sql, $params);
        return $results[0] ?? null;
    }

    /**
     * Execute a statement (INSERT, UPDATE, DELETE)
     */
    public function execute(string $sql, array $params = []): bool
    {
        $db = $this->getConnection();
        $stmt = $db->prepare($sql);

        if ($stmt === false) {
            throw new Exception("Failed to prepare statement");
        }

        $this->bindParams($stmt, $params);
        return $stmt->execute() !== false;
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId(): int
    {
        return $this->getConnection()->lastInsertRowID();
    }

    /**
     * Bind parameters to prepared statement
     */
    private function bindParams($stmt, array $params): void
    {
        foreach ($params as $key => $value) {
            $param = is_numeric($key) ? $key + 1 : ':' . $key;
            
            $type = SQLITE3_TEXT;
            if (is_int($value)) {
                $type = SQLITE3_INTEGER;
            } elseif (is_float($value)) {
                $type = SQLITE3_FLOAT;
            } elseif (is_null($value)) {
                $type = SQLITE3_NULL;
            }

            $stmt->bindValue($param, $value, $type);
        }
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): bool
    {
        return $this->getConnection()->exec('BEGIN TRANSACTION');
    }

    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        return $this->getConnection()->exec('COMMIT');
    }

    /**
     * Rollback transaction
     */
    public function rollback(): bool
    {
        return $this->getConnection()->exec('ROLLBACK');
    }

    /**
     * Close connection
     */
    public function close(): void
    {
        if ($this->connection !== null) {
            $this->connection->close();
            $this->connection = null;
        }
    }
}
