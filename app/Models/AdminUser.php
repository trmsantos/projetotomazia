<?php

namespace App\Models;

/**
 * Admin User Model
 */
class AdminUser extends BaseModel
{
    protected string $table = 'admin_users';
    protected string $primaryKey = 'id';

    /**
     * Find admin by username
     */
    public function findByUsername(string $username): ?array
    {
        return $this->findOne(['username' => $username]);
    }

    /**
     * Verify admin credentials
     */
    public function verifyCredentials(string $username, string $password): ?array
    {
        $admin = $this->findByUsername($username);
        
        if ($admin && password_verify($password, $admin['psw'])) {
            return $admin;
        }
        
        return null;
    }

    /**
     * Create admin with hashed password
     */
    public function createAdmin(string $username, string $password): int
    {
        return $this->insert([
            'username' => $username,
            'psw' => password_hash($password, PASSWORD_DEFAULT)
        ]);
    }
}
