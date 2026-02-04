<?php

namespace App\Services;

use App\Models\Customer;
use App\Helpers\Logger;
use App\Helpers\SecurityHelper;
use App\Helpers\ValidationHelper;

/**
 * CustomerService - Business logic for customer operations
 * 
 * This service encapsulates all business logic related to customers,
 * separating it from controllers and models. It handles:
 * - Customer registration with validation
 * - Customer lookup and verification
 * - Customer data management
 * 
 * Usage in controllers:
 *   $service = new CustomerService();
 *   $result = $service->register($data);
 * 
 * @package App\Services
 */
class CustomerService
{
    /**
     * Customer model instance
     */
    private Customer $customer;

    /**
     * Initialize the service with dependencies
     */
    public function __construct()
    {
        $this->customer = new Customer();
    }

    /**
     * Register a new customer or update existing
     * 
     * @param array $data Customer data (nome, email, telefone)
     * @param string $userId User ID from cookie
     * @return array Result with 'success', 'customer_id', and optional 'errors'
     */
    public function register(array $data, string $userId): array
    {
        // Validate input
        $validator = ValidationHelper::validate($data, [
            'nome' => ['required', 'minLength:3', 'pattern:/^[a-zA-ZÀ-ÿ\s]+$/u'],
            'email' => ['required', 'email'],
            'telefone' => ['required', 'phone']
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()
            ];
        }

        try {
            // Sanitize data for database
            $sanitized = [
                'nome' => ValidationHelper::sanitizeForDb($data['nome']),
                'email' => ValidationHelper::sanitizeForDb($data['email']),
                'telemovel' => ValidationHelper::sanitizeForDb($data['telefone'])
            ];

            // Create or update customer
            $customerId = $this->customer->createOrUpdate($userId, $sanitized);

            Logger::info("Customer registered/updated", [
                'customer_id' => $customerId,
                'action' => 'register'
            ]);

            return [
                'success' => true,
                'customer_id' => $customerId,
                'name' => $sanitized['nome']
            ];
        } catch (\Exception $e) {
            Logger::error("Customer registration failed", [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'errors' => ['general' => ['Erro ao registrar cliente. Por favor, tente novamente.']]
            ];
        }
    }

    /**
     * Find customer by user ID
     * 
     * @param string $userId User ID from cookie
     * @return array|null Customer data or null if not found
     */
    public function findByUserId(string $userId): ?array
    {
        return $this->customer->findByUserId($userId);
    }

    /**
     * Check if customer exists and return their name
     * 
     * @param string $userId User ID from cookie
     * @return string|null Customer name or null if not found
     */
    public function getCustomerName(string $userId): ?string
    {
        $customer = $this->customer->findByUserId($userId);
        return $customer ? SecurityHelper::escape($customer['nome']) : null;
    }

    /**
     * Get all customers with phone numbers for SMS
     * 
     * @return array List of customers with phone numbers
     */
    public function getCustomersForSms(): array
    {
        return $this->customer->getAllWithPhones();
    }

    /**
     * Get customer count
     * 
     * @return int Total number of registered customers
     */
    public function count(): int
    {
        return $this->customer->count();
    }
}
