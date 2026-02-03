<?php

namespace App\Models;

/**
 * Customer Model
 */
class Customer extends BaseModel
{
    protected string $table = 'tomazia_clientes';
    protected string $primaryKey = 'id';

    /**
     * Find customer by user_id (cookie)
     */
    public function findByUserId(string $userId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id LIMIT 1";
        return $this->db->queryOne($sql, ['user_id' => $userId]);
    }

    /**
     * Find customer by email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findOne(['email' => $email]);
    }

    /**
     * Find customer by phone
     */
    public function findByPhone(string $phone): ?array
    {
        return $this->findOne(['telemovel' => $phone]);
    }

    /**
     * Create or update customer
     */
    public function createOrUpdate(string $userId, array $data): int
    {
        // Try to find existing by email or phone
        $existing = $this->findByEmail($data['email']);
        if (!$existing) {
            $existing = $this->findByPhone($data['telemovel']);
        }

        $data['user_id'] = $userId;
        $data['data_registro'] = date('Y-m-d H:i:s');

        if ($existing) {
            $this->update($existing['id'], $data);
            return $existing['id'];
        } else {
            return $this->insert($data);
        }
    }

    /**
     * Get all customers with phone numbers
     */
    public function getAllWithPhones(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE telemovel IS NOT NULL AND telemovel != ''";
        return $this->db->query($sql);
    }
}
