<?php

namespace App\Middleware;

/**
 * Middleware interface
 */
interface Middleware
{
    /**
     * Handle the request
     * @return bool True to continue, false to stop
     */
    public function handle(): bool;
}
