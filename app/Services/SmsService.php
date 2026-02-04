<?php

namespace App\Services;

use App\Helpers\Logger;

/**
 * SmsService - Business logic for SMS marketing
 * 
 * This service handles all SMS-related functionality including:
 * - Sending bulk SMS to customers
 * - Message validation
 * - API integration
 * 
 * SMS Configuration is read from environment variables:
 * - SMS_API_ENABLED: Enable/disable SMS sending
 * - SMS_API_KEY: API authentication key
 * - SMS_API_ENDPOINT: API endpoint URL
 * - SMS_API_COUNTRY_CODE: Default country code for phone numbers
 * 
 * @package App\Services
 */
class SmsService
{
    /**
     * Minimum message length
     */
    private const MIN_LENGTH = 10;

    /**
     * Maximum message length (SMS standard)
     */
    private const MAX_LENGTH = 160;

    /**
     * Send SMS to multiple phone numbers
     * 
     * @param array $phoneNumbers Array of phone numbers
     * @param string $message Message content
     * @return array Result with 'success', 'sent_count', 'failed_count', 'errors'
     */
    public function send(array $phoneNumbers, string $message): array
    {
        // Validate message
        $validation = $this->validateMessage($message);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'sent_count' => 0,
                'failed_count' => 0,
                'errors' => [$validation['error']]
            ];
        }

        // Check if API is enabled
        $enabled = ($_ENV['SMS_API_ENABLED'] ?? 'false') === 'true';
        
        if (!$enabled) {
            Logger::info("SMS API in simulation mode", [
                'recipients' => count($phoneNumbers)
            ]);

            return [
                'success' => true,
                'sent_count' => count($phoneNumbers),
                'failed_count' => 0,
                'errors' => [],
                'simulation' => true
            ];
        }

        // Validate API configuration
        $apiKey = $_ENV['SMS_API_KEY'] ?? '';
        $endpoint = $_ENV['SMS_API_ENDPOINT'] ?? '';

        if (empty($apiKey) || empty($endpoint)) {
            Logger::error("SMS API configuration incomplete");
            return [
                'success' => false,
                'sent_count' => 0,
                'failed_count' => 0,
                'errors' => ['Configuração da API de SMS incompleta.']
            ];
        }

        // Send SMS to each number
        return $this->sendBulk($phoneNumbers, $message);
    }

    /**
     * Validate SMS message
     * 
     * @param string $message Message to validate
     * @return array Validation result with 'valid' and optional 'error'
     */
    public function validateMessage(string $message): array
    {
        $length = strlen(trim($message));

        if ($length < self::MIN_LENGTH) {
            return [
                'valid' => false,
                'error' => sprintf('A mensagem deve ter pelo menos %d caracteres.', self::MIN_LENGTH)
            ];
        }

        if ($length > self::MAX_LENGTH) {
            return [
                'valid' => false,
                'error' => sprintf('A mensagem deve ter no máximo %d caracteres.', self::MAX_LENGTH)
            ];
        }

        return ['valid' => true];
    }

    /**
     * Send SMS to multiple recipients via API
     * 
     * @param array $phoneNumbers Phone numbers
     * @param string $message Message content
     * @return array Result array
     */
    private function sendBulk(array $phoneNumbers, string $message): array
    {
        $result = [
            'success' => true,
            'sent_count' => 0,
            'failed_count' => 0,
            'errors' => []
        ];

        $countryCode = $_ENV['SMS_API_COUNTRY_CODE'] ?? '+351';
        $from = $_ENV['SMS_API_FROM'] ?? '';
        $endpoint = $_ENV['SMS_API_ENDPOINT'] ?? '';
        $apiKey = $_ENV['SMS_API_KEY'] ?? '';
        $timeout = (int)($_ENV['SMS_API_TIMEOUT'] ?? 30);

        foreach ($phoneNumbers as $phone) {
            try {
                $fullNumber = $countryCode . $phone;
                $success = $this->sendOne($endpoint, $apiKey, $fullNumber, $from, $message, $timeout);

                if ($success) {
                    $result['sent_count']++;
                    Logger::info("SMS sent successfully", ['phone' => $phone]);
                } else {
                    $result['failed_count']++;
                    $result['errors'][] = "Falha ao enviar para {$phone}";
                }
            } catch (\Exception $e) {
                $result['failed_count']++;
                $result['errors'][] = "Erro ao enviar para {$phone}: " . $e->getMessage();
                Logger::error("SMS send error", ['phone' => $phone, 'error' => $e->getMessage()]);
            }
        }

        // Mark as failed if all failed
        if ($result['failed_count'] > 0 && $result['sent_count'] === 0) {
            $result['success'] = false;
        }

        return $result;
    }

    /**
     * Send a single SMS via API
     * 
     * @param string $endpoint API endpoint
     * @param string $apiKey API key
     * @param string $to Recipient phone number
     * @param string $from Sender ID
     * @param string $message Message content
     * @param int $timeout Request timeout
     * @return bool True if sent successfully
     */
    private function sendOne(string $endpoint, string $apiKey, string $to, string $from, string $message, int $timeout): bool
    {
        $postData = [
            'to' => $to,
            'from' => $from,
            'message' => $message
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception($error);
        }

        return $httpCode >= 200 && $httpCode < 300;
    }

    /**
     * Get the maximum message length
     * 
     * @return int Maximum message length
     */
    public function getMaxLength(): int
    {
        return self::MAX_LENGTH;
    }

    /**
     * Get the minimum message length
     * 
     * @return int Minimum message length
     */
    public function getMinLength(): int
    {
        return self::MIN_LENGTH;
    }
}
