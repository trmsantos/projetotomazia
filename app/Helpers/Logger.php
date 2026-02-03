<?php

namespace App\Helpers;

/**
 * Logger - Simple logging utility
 */
class Logger
{
    private static string $logFile = __DIR__ . '/../../logs/app.log';

    /**
     * Log info message
     */
    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    /**
     * Log warning message
     */
    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    /**
     * Log error message
     */
    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }

    /**
     * Log debug message
     */
    public static function debug(string $message, array $context = []): void
    {
        if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
            self::log('DEBUG', $message, $context);
        }
    }

    /**
     * Write log entry
     */
    private static function log(string $level, string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logMessage = "[$timestamp] [$level] $message$contextStr\n";

        // Ensure logs directory exists
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        error_log($logMessage, 3, self::$logFile);
    }

    /**
     * Set custom log file
     */
    public static function setLogFile(string $path): void
    {
        self::$logFile = $path;
    }
}
