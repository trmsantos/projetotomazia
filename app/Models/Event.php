<?php

namespace App\Models;

/**
 * Event Model
 */
class Event extends BaseModel
{
    protected string $table = 'eventos';
    protected string $primaryKey = 'id';

    /**
     * Get visible events
     */
    public function getVisible(): array
    {
        return $this->findAll(['visivel' => 1], 'data_evento DESC');
    }

    /**
     * Toggle event visibility
     */
    public function toggleVisibility(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET visivel = NOT visivel WHERE {$this->primaryKey} = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    /**
     * Get upcoming events
     */
    public function getUpcoming(int $limit = 10): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE visivel = 1 AND (data_evento >= date('now') OR data_evento IS NULL OR data_evento = '')
                ORDER BY data_evento ASC LIMIT :limit";
        return $this->db->query($sql, ['limit' => $limit]);
    }
}
