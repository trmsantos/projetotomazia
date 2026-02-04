<?php

namespace App\Helpers;

/**
 * Logger - Structured logging utility with multiple channels
 * 
 * This logger supports multiple log channels for different purposes:
 * - app: General application logs
 * - error: Error and exception logs  
 * - security: Security-related events (login attempts, CSRF failures, etc.)
 * - access: HTTP request access logs
 * 
 * Usage:
 *   Logger::info("User logged in", ['user_id' => 123]);
 *   Logger::security("Failed login attempt", ['username' => 'admin', 'ip' => '127.0.0.1']);
 *   Logger::access("GET /admin", ['status' => 200, 'duration' => 0.123]);
 * 
 * @package App\Helpers
 */
class Logger
{
    /**
     * Base directory for log files
     */
    private static string $logPath = __DIR__ . '/../../logs';

    /**
     * Available log channels and their corresponding files
     */
    private static array $channels = [
        'app' => 'app.log',
        'error' => 'error.log',
        'security' => 'security.log',
        'access' => 'access.log',
    ];

    /**
     * Current active channel
     */
    private static string $currentChannel = 'app';

    /**
     * Log an info level message
     * 
     * @param string $message Log message
     * @param array $context Additional context data
     * @param string $channel Log channel (default: 'app')
     */
    public static function info(string $message, array $context = [], string $channel = 'app'): void
    {
        self::log('INFO', $message, $context, $channel);
    }

    /**
     * Log a warning level message
     * 
     * @param string $message Log message
     * @param array $context Additional context data
     * @param string $channel Log channel (default: 'app')
     */
    public static function warning(string $message, array $context = [], string $channel = 'app'): void
    {
        self::log('WARNING', $message, $context, $channel);
    }

    /**
     * Log an error level message
     * 
     * Also writes to the error channel for centralized error tracking.
     * 
     * @param string $message Log message
     * @param array $context Additional context data
     * @param string $channel Log channel (default: 'error')
     */
    public static function error(string $message, array $context = [], string $channel = 'error'): void
    {
        self::log('ERROR', $message, $context, $channel);
    }

    /**
     * Log a critical level message
     * 
     * @param string $message Log message
     * @param array $context Additional context data
     */
    public static function critical(string $message, array $context = []): void
    {
        self::log('CRITICAL', $message, $context, 'error');
    }

    /**
     * Log a debug level message
     * 
     * Only logs when APP_DEBUG is enabled.
     * 
     * @param string $message Log message
     * @param array $context Additional context data
     */
    public static function debug(string $message, array $context = []): void
    {
        if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
            self::log('DEBUG', $message, $context, 'app');
        }
    }

    /**
     * Log a security-related event
     * 
     * Use this for authentication attempts, CSRF failures, rate limiting,
     * and other security-sensitive events.
     * 
     * @param string $message Log message
     * @param array $context Additional context data (IP, user agent, etc.)
     */
    public static function security(string $message, array $context = []): void
    {
        // Automatically add request metadata for security events
        $context = array_merge([
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ], $context);

        self::log('SECURITY', $message, $context, 'security');
    }

    /**
     * Log an HTTP access request
     * 
     * Use this for logging incoming HTTP requests.
     * 
     * @param string $message Request summary (e.g., "GET /admin")
     * @param array $context Additional context (status code, duration, etc.)
     */
    public static function access(string $message, array $context = []): void
    {
        $context = array_merge([
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
            'uri' => $_SERVER['REQUEST_URI'] ?? '/',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ], $context);

        self::log('ACCESS', $message, $context, 'access');
    }

    /**
     * Write log entry to file
     * 
     * @param string $level Log level (INFO, WARNING, ERROR, etc.)
     * @param string $message Log message
     * @param array $context Additional context data
     * @param string $channel Log channel
     */
    private static function log(string $level, string $message, array $context = [], string $channel = 'app'): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logMessage = "[$timestamp] [$level] $message$contextStr\n";

        $logFile = self::getLogFile($channel);

        // Ensure logs directory exists
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        error_log($logMessage, 3, $logFile);
    }

    /**
     * Get the log file path for a channel
     * 
     * @param string $channel Log channel name
     * @return string Full path to log file
     */
    private static function getLogFile(string $channel): string
    {
        $filename = self::$channels[$channel] ?? self::$channels['app'];
        return self::$logPath . '/' . $filename;
    }

    /**
     * Set the base log path
     * 
     * @param string $path Directory path for log files
     */
    public static function setLogPath(string $path): void
    {
        self::$logPath = $path;
    }

    /**
     * Set custom log file for backward compatibility
     * 
     * @param string $path Full path to log file
     * @deprecated Use setLogPath() and channel parameters instead
     */
    public static function setLogFile(string $path): void
    {
        self::$logPath = dirname($path);
        self::$channels['app'] = basename($path);
    }

    /**
     * Add a custom log channel
     * 
     * @param string $name Channel name
     * @param string $filename Log filename
     */
    public static function addChannel(string $name, string $filename): void
    {
        self::$channels[$name] = $filename;
    }
}
