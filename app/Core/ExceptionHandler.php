<?php

namespace App\Core;

/**
 * ExceptionHandler - Centralized exception handling
 */
class ExceptionHandler
{
    private bool $debug;

    public function __construct(bool $debug = false)
    {
        $this->debug = $debug;
    }

    /**
     * Register exception and error handlers
     */
    public function register(): void
    {
        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * Handle exceptions
     */
    public function handleException(\Throwable $exception): void
    {
        $this->logException($exception);

        http_response_code(500);

        if ($this->debug) {
            $this->renderDebugError($exception);
        } else {
            $this->renderProductionError();
        }

        exit;
    }

    /**
     * Handle errors
     */
    public function handleError(int $level, string $message, string $file = '', int $line = 0): bool
    {
        if (error_reporting() & $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }

        return false;
    }

    /**
     * Handle fatal errors on shutdown
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->handleException(
                new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line'])
            );
        }
    }

    /**
     * Log exception
     */
    private function logException(\Throwable $exception): void
    {
        $message = sprintf(
            "[%s] %s in %s:%d\nStack trace:\n%s\n",
            date('Y-m-d H:i:s'),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        error_log($message);
    }

    /**
     * Render debug error page
     */
    private function renderDebugError(\Throwable $exception): void
    {
        echo '<!DOCTYPE html><html><head><title>Error</title>';
        echo '<style>body{font-family:sans-serif;padding:20px;background:#f5f5f5;}';
        echo '.error{background:#fff;padding:20px;border-left:4px solid #e74c3c;margin:20px 0;}';
        echo '.error h1{color:#e74c3c;margin:0 0 10px 0;}';
        echo '.error pre{background:#f8f8f8;padding:10px;overflow:auto;}</style></head><body>';
        echo '<div class="error">';
        echo '<h1>' . get_class($exception) . '</h1>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($exception->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . htmlspecialchars($exception->getFile()) . ':' . $exception->getLine() . '</p>';
        echo '<pre>' . htmlspecialchars($exception->getTraceAsString()) . '</pre>';
        echo '</div></body></html>';
    }

    /**
     * Render production error page
     */
    private function renderProductionError(): void
    {
        echo '<!DOCTYPE html><html><head><title>Erro</title>';
        echo '<style>body{font-family:sans-serif;padding:40px;text-align:center;background:#5D1F3A;color:#fff;}';
        echo 'h1{color:#D4AF37;}</style></head><body>';
        echo '<h1>Ocorreu um erro</h1>';
        echo '<p>Pedimos desculpa pelo inconveniente. Por favor, tente novamente mais tarde.</p>';
        echo '</body></html>';
    }
}
