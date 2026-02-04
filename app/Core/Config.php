<?php

namespace App\Core;

/**
 * Config - Centralized configuration loader
 * 
 * This class provides a simple way to access configuration values
 * from the config files using dot notation.
 * 
 * Usage:
 *   Config::get('app.name');                // Returns 'Bar da Tomazia'
 *   Config::get('database.path');           // Returns database path
 *   Config::get('security.csrf_token_name'); // Returns 'csrf_token'
 *   Config::get('nonexistent', 'default');  // Returns 'default'
 * 
 * @package App\Core
 */
class Config
{
    /**
     * Cached configuration values
     */
    private static array $config = [];

    /**
     * Whether config has been loaded
     */
    private static bool $loaded = false;

    /**
     * Config directory path
     */
    private static string $configPath = __DIR__ . '/../../config';

    /**
     * Get a configuration value using dot notation
     * 
     * @param string $key Configuration key (e.g., 'app.name', 'database.path')
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value
     */
    public static function get(string $key, $default = null)
    {
        self::loadIfNeeded();

        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Set a configuration value at runtime
     * 
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     */
    public static function set(string $key, $value): void
    {
        self::loadIfNeeded();

        $keys = explode('.', $key);
        $config = &self::$config;

        foreach ($keys as $i => $segment) {
            if ($i === count($keys) - 1) {
                $config[$segment] = $value;
            } else {
                if (!isset($config[$segment]) || !is_array($config[$segment])) {
                    $config[$segment] = [];
                }
                $config = &$config[$segment];
            }
        }
    }

    /**
     * Check if a configuration key exists
     * 
     * @param string $key Configuration key
     * @return bool True if key exists
     */
    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    /**
     * Get all configuration values
     * 
     * @return array All configuration
     */
    public static function all(): array
    {
        self::loadIfNeeded();
        return self::$config;
    }

    /**
     * Load configuration if not already loaded
     */
    private static function loadIfNeeded(): void
    {
        if (!self::$loaded) {
            self::load();
        }
    }

    /**
     * Load all configuration files
     */
    public static function load(): void
    {
        $configFiles = glob(self::$configPath . '/*.php');

        foreach ($configFiles as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            self::$config[$name] = require $file;
        }

        self::$loaded = true;
    }

    /**
     * Reload configuration (useful for testing)
     */
    public static function reload(): void
    {
        self::$config = [];
        self::$loaded = false;
        self::load();
    }

    /**
     * Set the configuration directory path
     * 
     * @param string $path Path to config directory
     */
    public static function setPath(string $path): void
    {
        self::$configPath = $path;
        self::$loaded = false;
    }
}
