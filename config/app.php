<?php

/**
 * Application Configuration
 * 
 * This file contains centralized configuration settings for the application.
 * All values can be overridden by environment variables in .env file.
 * 
 * @package App\Config
 */

/**
 * Get environment variable with fallback
 * 
 * Checks $_ENV first, then getenv(), then returns default
 */
function env(string $key, $default = null)
{
    $value = $_ENV[$key] ?? getenv($key);
    
    if ($value === false || $value === null) {
        return $default;
    }
    
    // Handle boolean values
    if (is_string($value)) {
        $lower = strtolower($value);
        if ($lower === 'true') return true;
        if ($lower === 'false') return false;
    }
    
    return $value;
}

return [
    /**
     * Application Settings
     * 
     * Basic application configuration including environment and debugging options.
     */
    'app' => [
        'name' => env('APP_NAME', 'Bar da Tomazia'),
        'env' => env('APP_ENV', 'production'),
        'debug' => env('APP_DEBUG', false),
        'url' => env('APP_URL', 'http://localhost:8000'),
        'timezone' => env('APP_TIMEZONE', 'Europe/Lisbon'),
    ],

    /**
     * Database Configuration
     * 
     * SQLite database settings. The path should be relative to the project root.
     * Future migrations to MySQL/PostgreSQL can be configured here.
     */
    'database' => [
        'driver' => env('DB_DRIVER', 'sqlite'),
        'path' => env('DB_PATH', __DIR__ . '/../bd/bd_teste.db'),
        // MySQL/PostgreSQL settings for future migration
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'bar_tomazia'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
    ],

    /**
     * Security Configuration
     * 
     * CSRF protection, session handling, and cookie security settings.
     */
    'security' => [
        'csrf_token_name' => env('CSRF_TOKEN_NAME', 'csrf_token'),
        'secure_cookies' => env('SECURE_COOKIES', false),
        'session_name' => env('SESSION_NAME', 'bar_tomazia_session'),
        'session_lifetime' => (int) env('SESSION_LIFETIME', 86400),
        'rate_limit_max_attempts' => (int) env('RATE_LIMIT_ATTEMPTS', 5),
        'rate_limit_window' => (int) env('RATE_LIMIT_WINDOW', 300),
    ],

    /**
     * WiFi Configuration
     * 
     * WiFi credentials displayed to customers.
     */
    'wifi' => [
        'network' => env('WIFI_REDE', 'NOS-2B6E-5'),
        'password' => env('WIFI_PASSWORD', '5YV4UJC4'),
    ],

    /**
     * SMS API Configuration
     * 
     * Settings for SMS marketing functionality.
     * Enable by setting SMS_API_ENABLED=true in .env
     */
    'sms' => [
        'enabled' => env('SMS_API_ENABLED', false),
        'api_key' => env('SMS_API_KEY', ''),
        'api_secret' => env('SMS_API_SECRET', ''),
        'from' => env('SMS_API_FROM', ''),
        'endpoint' => env('SMS_API_ENDPOINT', 'https://api.example.com/sms'),
        'country_code' => env('SMS_API_COUNTRY_CODE', '+351'),
        'timeout' => (int) env('SMS_API_TIMEOUT', 30),
    ],

    /**
     * Logging Configuration
     * 
     * Configure log files and levels for different purposes.
     */
    'logging' => [
        'default' => 'app',
        'path' => env('LOG_PATH', __DIR__ . '/../logs'),
        'channels' => [
            'app' => 'app.log',
            'error' => 'error.log',
            'security' => 'security.log',
            'access' => 'access.log',
        ],
        'level' => env('LOG_LEVEL', 'info'),
    ],

    /**
     * Paths Configuration
     * 
     * Directory paths used throughout the application.
     */
    'paths' => [
        'app' => __DIR__ . '/../app',
        'config' => __DIR__,
        'public' => __DIR__ . '/../public',
        'storage' => __DIR__ . '/../storage',
        'views' => __DIR__ . '/../app/Views',
        'logs' => __DIR__ . '/../logs',
        'database' => __DIR__ . '/../bd',
    ],
];
