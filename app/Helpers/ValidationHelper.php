<?php

namespace App\Helpers;

/**
 * ValidationHelper - Input validation and sanitization
 */
class ValidationHelper
{
    private array $errors = [];
    private array $data = [];

    /**
     * Validate required field
     */
    public function required(string $field, $value, string $message = null): self
    {
        if (empty($value) && $value !== '0') {
            $this->errors[$field][] = $message ?? "O campo $field é obrigatório.";
        }
        return $this;
    }

    /**
     * Validate email
     */
    public function email(string $field, $value, string $message = null): self
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = $message ?? "O campo $field deve ser um email válido.";
        }
        return $this;
    }

    /**
     * Validate minimum length
     */
    public function minLength(string $field, $value, int $min, string $message = null): self
    {
        if (!empty($value) && strlen($value) < $min) {
            $this->errors[$field][] = $message ?? "O campo $field deve ter pelo menos $min caracteres.";
        }
        return $this;
    }

    /**
     * Validate maximum length
     */
    public function maxLength(string $field, $value, int $max, string $message = null): self
    {
        if (!empty($value) && strlen($value) > $max) {
            $this->errors[$field][] = $message ?? "O campo $field deve ter no máximo $max caracteres.";
        }
        return $this;
    }

    /**
     * Validate pattern (regex)
     */
    public function pattern(string $field, $value, string $pattern, string $message = null): self
    {
        if (!empty($value) && !preg_match($pattern, $value)) {
            $this->errors[$field][] = $message ?? "O campo $field está em formato inválido.";
        }
        return $this;
    }

    /**
     * Validate phone number (Portuguese format)
     */
    public function phone(string $field, $value, string $message = null): self
    {
        if (!empty($value) && !preg_match('/^9\d{8}$/', $value)) {
            $this->errors[$field][] = $message ?? "O campo $field deve ser um número de telemóvel português válido (9 dígitos, começando por 9).";
        }
        return $this;
    }

    /**
     * Validate numeric
     */
    public function numeric(string $field, $value, string $message = null): self
    {
        if (!empty($value) && !is_numeric($value)) {
            $this->errors[$field][] = $message ?? "O campo $field deve ser numérico.";
        }
        return $this;
    }

    /**
     * Validate integer
     */
    public function integer(string $field, $value, string $message = null): self
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->errors[$field][] = $message ?? "O campo $field deve ser um número inteiro.";
        }
        return $this;
    }

    /**
     * Custom validation
     */
    public function custom(string $field, $value, callable $callback, string $message = null): self
    {
        if (!$callback($value)) {
            $this->errors[$field][] = $message ?? "O campo $field é inválido.";
        }
        return $this;
    }

    /**
     * Check if validation passed
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Check if validation failed
     */
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Get all errors
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get errors for specific field
     */
    public function getErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * Get first error for field
     */
    public function getFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Sanitize string
     */
    public static function sanitize(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize for SQL (trim only, use prepared statements!)
     */
    public static function sanitizeForDb(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return trim($value);
    }

    /**
     * Clean string (remove special characters)
     */
    public static function clean(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $value);
    }

    /**
     * Validate multiple fields at once
     */
    public static function validate(array $data, array $rules): ValidationHelper
    {
        $validator = new self();
        
        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            
            foreach ($ruleSet as $rule) {
                if (is_string($rule)) {
                    $parts = explode(':', $rule);
                    $method = $parts[0];
                    $params = isset($parts[1]) ? explode(',', $parts[1]) : [];
                    
                    if (method_exists($validator, $method)) {
                        call_user_func_array([$validator, $method], array_merge([$field, $value], $params));
                    }
                } elseif (is_callable($rule)) {
                    $rule($validator, $field, $value);
                }
            }
        }
        
        return $validator;
    }
}
