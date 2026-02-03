<?php

namespace App\Helpers;

/**
 * SecurityHelper - Centralized security functions
 */
class SecurityHelper
{
    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken(): string
    {
        $tokenName = $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token';
        
        if (!isset($_SESSION[$tokenName])) {
            $_SESSION[$tokenName] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION[$tokenName];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken(string $token): bool
    {
        $tokenName = $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token';
        
        return isset($_SESSION[$tokenName]) && hash_equals($_SESSION[$tokenName], $token);
    }

    /**
     * Escape HTML output
     */
    public static function escape(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Alias for escape
     */
    public static function e(?string $value): string
    {
        return self::escape($value);
    }

    /**
     * Set secure cookie
     */
    public static function setSecureCookie(string $name, string $value, int $expire = 0): void
    {
        $secure = ($_ENV['SECURE_COOKIES'] ?? 'false') === 'true';
        
        $options = [
            'expires' => $expire,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Strict'
        ];
        
        setcookie($name, $value, $options);
    }

    /**
     * Hash password
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify password
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Regenerate session ID (call after login)
     */
    public static function regenerateSession(): void
    {
        session_regenerate_id(true);
    }

    /**
     * Initialize secure session
     */
    public static function initSecureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $sessionName = $_ENV['SESSION_NAME'] ?? 'bar_tomazia_session';
            $secure = ($_ENV['SECURE_COOKIES'] ?? 'false') === 'true';
            
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_samesite', 'Strict');
            
            if ($secure) {
                ini_set('session.cookie_secure', '1');
            }
            
            ini_set('session.use_strict_mode', '1');
            
            session_name($sessionName);
            session_start();
        }
    }

    /**
     * Sanitize filename
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Remove any path components
        $filename = basename($filename);
        
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        
        return $filename;
    }

    /**
     * Generate random filename
     */
    public static function randomFilename(string $extension): string
    {
        return bin2hex(random_bytes(16)) . '.' . ltrim($extension, '.');
    }

    /**
     * Check rate limiting for login attempts
     */
    public static function checkRateLimit(string $key, int $maxAttempts = 5, int $timeWindow = 300): bool
    {
        $rateLimitKey = 'rate_limit_' . $key;
        
        if (!isset($_SESSION[$rateLimitKey])) {
            $_SESSION[$rateLimitKey] = [
                'attempts' => 0,
                'first_attempt' => time()
            ];
        }
        
        $data = $_SESSION[$rateLimitKey];
        
        // Reset if time window passed
        if (time() - $data['first_attempt'] > $timeWindow) {
            $_SESSION[$rateLimitKey] = [
                'attempts' => 1,
                'first_attempt' => time()
            ];
            return true;
        }
        
        // Check if exceeded
        if ($data['attempts'] >= $maxAttempts) {
            return false;
        }
        
        // Increment attempts
        $_SESSION[$rateLimitKey]['attempts']++;
        return true;
    }

    /**
     * Reset rate limit
     */
    public static function resetRateLimit(string $key): void
    {
        $rateLimitKey = 'rate_limit_' . $key;
        unset($_SESSION[$rateLimitKey]);
    }
}
