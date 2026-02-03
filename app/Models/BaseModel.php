<?php

namespace App\Models;

use App\Core\Database;

/**
 * BaseModel - Base class for all models
 */
abstract class BaseModel
{
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find record by ID
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        return $this->db->queryOne($sql, ['id' => $id]);
    }

    /**
     * Find all records
     */
    public function findAll(array $conditions = [], string $orderBy = '', int $limit = 0): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = :$key";
                $params[$key] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        if ($limit > 0) {
            $sql .= " LIMIT $limit";
        }

        return $this->db->query($sql, $params);
    }

    /**
     * Find one record by conditions
     */
    public function findOne(array $conditions): ?array
    {
        $results = $this->findAll($conditions, '', 1);
        return $results[0] ?? null;
    }

    /**
     * Insert record
     */
    public function insert(array $data): int
    {
        $fields = array_keys($data);
        $placeholders = array_map(fn($field) => ":$field", $fields);

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";

        $this->db->execute($sql, $data);
        return $this->db->lastInsertId();
    }

    /**
     * Update record
     */
    public function update(int $id, array $data): bool
    {
        $sets = [];
        foreach (array_keys($data) as $field) {
            $sets[] = "$field = :$field";
        }

        $data[$this->primaryKey] = $id;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . 
               " WHERE {$this->primaryKey} = :{$this->primaryKey}";

        return $this->db->execute($sql, $data);
    }

    /**
     * Delete record
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    /**
     * Count records
     */
    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = :$key";
                $params[$key] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $result = $this->db->queryOne($sql, $params);
        return (int)($result['count'] ?? 0);
    }
}
