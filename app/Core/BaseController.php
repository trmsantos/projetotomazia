<?php

namespace App\Core;

/**
 * BaseController - Base class for all controllers
 * 
 * This abstract class provides common functionality for all controllers:
 * - View rendering with data passing
 * - HTTP redirects with status codes
 * - JSON response handling
 * - Request data access (GET/POST)
 * - Flash messages for session-based notifications
 * - Database access via dependency injection
 * 
 * Usage:
 *   class MyController extends BaseController {
 *       public function index(): void {
 *           $this->view('my-page.php', ['title' => 'My Page']);
 *       }
 *   }
 * 
 * @package App\Core
 */
class BaseController
{
    /**
     * Database instance for data access
     * 
     * Controllers should use this to interact with the database.
     * For complex queries, consider using a Model or Service instead.
     */
    protected Database $db;

    /**
     * Initialize the controller with dependencies
     * 
     * The database connection is injected automatically.
     * Override this constructor in child classes if you need
     * additional dependencies, but always call parent::__construct().
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Render a view file
     * 
     * Views are loaded from the project root. The data array is extracted
     * into variables that can be used directly in the view.
     * 
     * @param string $viewPath Path to view file relative to project root
     * @param array $data Associative array of data to pass to view
     * @throws \Exception If view file is not found
     * 
     * @example
     *   $this->view('bemvindo.php', ['nome' => 'João']);
     *   // In bemvindo.php: echo $nome; // Outputs 'João'
     */
    protected function view(string $viewPath, array $data = []): void
    {
        extract($data);
        
        $viewFile = __DIR__ . '/../../' . $viewPath;
        
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            throw new \Exception("View file not found: $viewPath");
        }
    }

    /**
     * Redirect to another URL
     * 
     * Performs an HTTP redirect and terminates script execution.
     * Use 301 for permanent redirects, 302 for temporary.
     * 
     * @param string $url Destination URL (absolute or relative)
     * @param int $statusCode HTTP status code (default: 302)
     * 
     * @example
     *   $this->redirect('/login');           // Temporary redirect
     *   $this->redirect('/new-page', 301);   // Permanent redirect
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        header("Location: $url", true, $statusCode);
        exit;
    }

    /**
     * Return a JSON response
     * 
     * Sets the appropriate headers and outputs JSON-encoded data.
     * Terminates script execution after output.
     * 
     * @param mixed $data Data to encode as JSON
     * @param int $statusCode HTTP status code (default: 200)
     * 
     * @example
     *   $this->json(['success' => true, 'message' => 'Created'], 201);
     */
    protected function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Get POST data with optional default
     * 
     * @param string $key POST parameter name
     * @param mixed $default Value to return if key not found
     * @return mixed POST value or default
     */
    protected function post(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET/query data with optional default
     * 
     * @param string $key Query parameter name
     * @param mixed $default Value to return if key not found
     * @return mixed GET value or default
     */
    protected function get(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Check if current request method is POST
     * 
     * @return bool True if POST request
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Check if current request method is GET
     * 
     * @return bool True if GET request
     */
    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Set a flash message in session
     * 
     * Flash messages persist for one request only and are useful
     * for displaying success/error messages after redirects.
     * 
     * @param string $key Message identifier (e.g., 'success', 'error')
     * @param mixed $message Message content
     * 
     * @example
     *   $this->setFlash('success', 'Item saved successfully!');
     *   $this->redirect('/items');
     */
    protected function setFlash(string $key, $message): void
    {
        $_SESSION['flash'][$key] = $message;
    }

    /**
     * Get and clear a flash message
     * 
     * Returns the message and removes it from session.
     * Returns null if message doesn't exist.
     * 
     * @param string $key Message identifier
     * @return mixed Message content or null
     * 
     * @example
     *   $success = $this->getFlash('success');
     *   if ($success) { echo $success; }
     */
    protected function getFlash(string $key)
    {
        $message = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $message;
    }
}
