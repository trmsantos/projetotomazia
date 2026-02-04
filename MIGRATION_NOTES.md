# 🏗️ Migration Notes - Clean Architecture Refactoring

## Overview

This document explains the changes made to refactor the Bar da Tomazia PHP application to follow modern clean architecture and best practices while maintaining all existing functionality.

---

## 📁 New Directory Structure

```
projetotomazia/
├── app/                          # Application code (MVC)
│   ├── Controllers/              # Handle HTTP requests
│   │   ├── AdminController.php   # Admin dashboard operations
│   │   ├── AuthController.php    # Login/logout handling
│   │   ├── CustomerController.php # Customer-facing pages
│   │   └── HomeController.php    # Home page and registration
│   │
│   ├── Core/                     # Core framework classes
│   │   ├── BaseController.php    # Base class for all controllers
│   │   ├── Config.php            # Configuration loader
│   │   ├── Database.php          # Database abstraction layer
│   │   ├── ExceptionHandler.php  # Centralized error handling
│   │   └── Router.php            # URL routing system
│   │
│   ├── Helpers/                  # Utility classes
│   │   ├── Logger.php            # Structured logging system
│   │   ├── SecurityHelper.php    # Security functions (CSRF, etc.)
│   │   └── ValidationHelper.php  # Input validation
│   │
│   ├── Middleware/               # Request middleware
│   │   ├── AdminMiddleware.php   # Admin authentication check
│   │   ├── AuthMiddleware.php    # General authentication
│   │   ├── CsrfMiddleware.php    # CSRF token verification
│   │   └── Middleware.php        # Middleware interface
│   │
│   ├── Models/                   # Database models
│   │   ├── AdminUser.php         # Admin user model
│   │   ├── BaseModel.php         # Base class for all models
│   │   ├── Customer.php          # Customer model
│   │   ├── Event.php             # Event model
│   │   └── Product.php           # Product model
│   │
│   ├── Services/                 # Business logic (NEW)
│   │   ├── CustomerService.php   # Customer business logic
│   │   └── SmsService.php        # SMS marketing logic
│   │
│   ├── Views/                    # View templates (NEW)
│   │   ├── layouts/              # Layout templates
│   │   │   └── base.php          # Base HTML layout
│   │   └── partials/             # Reusable view components
│   │       ├── navigation.php    # Navigation menu
│   │       └── footer.php        # Footer section
│   │
│   └── .htaccess                 # Deny direct access
│
├── config/                       # Configuration files (NEW)
│   ├── app.php                   # Application configuration
│   ├── routes.php                # Route definitions
│   └── .htaccess                 # Deny direct access
│
├── public/                       # Web root (front controller)
│   └── index.php                 # Single entry point
│
├── storage/                      # Storage directory (NEW)
│   ├── logs/                     # Log files (future)
│   ├── database/                 # Database files (future)
│   └── .htaccess                 # Deny direct access
│
├── bd/                           # Database (current location)
│   └── bd_teste.db               # SQLite database
│
├── logs/                         # Log files (current location)
│   └── .htaccess                 # Deny direct access
│
├── css/                          # Stylesheets
├── js/                           # JavaScript files
├── img/                          # Images and media
│
├── composer.json                 # Composer configuration
├── .env.example                  # Environment template
├── .htaccess                     # Root URL routing
└── config.php                    # Legacy config (backward compat)
```

---

## 🔄 Key Changes

### 1. Front Controller Pattern

All HTTP requests now flow through `public/index.php`:

```
Request → .htaccess → public/index.php → Router → Controller → View
```

**Benefits:**
- Single entry point for all requests
- Centralized error handling
- Consistent security checks
- Clean URL support

### 2. Router System

The custom `App\Core\Router` class provides:

```php
// Route registration
$router->get('/bemvindo', 'CustomerController@welcome', 'welcome');
$router->post('/login', 'AuthController@login', 'login.post');

// Route parameters
$router->get('/product/{id}', 'ProductController@show');
```

**Features:**
- Clean URLs without `.php` extension
- Named routes for URL generation
- GET and POST method support
- Dynamic route parameters
- 404 handling with legacy fallback

### 3. MVC Architecture

**Controllers** handle HTTP requests:
```php
class CustomerController extends BaseController {
    public function welcome(array $params = []): void {
        $this->view('bemvindo.php');
    }
}
```

**Models** handle database operations:
```php
class Customer extends BaseModel {
    protected string $table = 'tomazia_clientes';
    
    public function findByUserId(string $userId): ?array {
        return $this->db->queryOne($sql, ['user_id' => $userId]);
    }
}
```

**Services** encapsulate business logic:
```php
class CustomerService {
    public function register(array $data, string $userId): array {
        // Validation, sanitization, database operations
    }
}
```

### 4. Database Abstraction

The `App\Core\Database` class provides:

```php
// Query with prepared statements
$db->query("SELECT * FROM users WHERE id = :id", ['id' => 1]);

// Execute INSERT/UPDATE/DELETE
$db->execute("UPDATE users SET name = :name WHERE id = :id", [...]);

// Transaction support
$db->beginTransaction();
$db->commit();
$db->rollback();
```

**Benefits:**
- SQL injection protection via prepared statements
- Consistent interface for all database operations
- Easy migration to MySQL/PostgreSQL in future

### 5. Configuration Management

Configuration now uses dot notation:

```php
use App\Core\Config;

$appName = Config::get('app.name');           // 'Bar da Tomazia'
$dbPath = Config::get('database.path');       // 'bd/bd_teste.db'
$debug = Config::get('app.debug', false);     // With default value
```

### 6. Structured Logging

The improved `Logger` class supports multiple channels:

```php
use App\Helpers\Logger;

Logger::info("User registered", ['user_id' => 123]);     // → app.log
Logger::error("Database error", ['error' => $msg]);      // → error.log
Logger::security("Failed login", ['ip' => $ip]);         // → security.log
Logger::access("GET /admin", ['status' => 200]);         // → access.log
```

### 7. Security Improvements

**Centralized CSRF Protection:**
```php
use App\Helpers\SecurityHelper;

$token = SecurityHelper::generateCsrfToken();
$valid = SecurityHelper::verifyCsrfToken($token);
```

**Rate Limiting:**
```php
if (!SecurityHelper::checkRateLimit('login_' . $username, 5, 300)) {
    // Too many attempts
}
```

**Secure Sessions:**
```php
SecurityHelper::initSecureSession();
SecurityHelper::regenerateSession(); // After login
```

---

## 🔗 Backward Compatibility

### Legacy URL Support

Old `.php` URLs are automatically redirected:

| Old URL | New URL |
|---------|---------|
| `/index.php` | `/` |
| `/login.php` | `/login` |
| `/bemvindo.php` | `/bemvindo` |
| `/cardapio.php` | `/cardapio` |
| `/admin.php` | `/admin` |

### Legacy Functions

The `config.php` file maintains backward compatible functions:

```php
// These still work for legacy pages
getDbConnection();    // Returns SQLite3 connection
generateCsrfToken();  // Generates CSRF token
verifyCsrfToken($t);  // Verifies CSRF token
setSecureCookie(...); // Sets secure cookie
```

### Legacy Pages

All original `.php` files in the root directory still work:
- Direct access: Works (served by Apache/PHP)
- Via router: Works (fallback in 404 handler)

---

## 📦 Composer & Autoloading

### PSR-4 Configuration

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    }
}
```

### Namespace Structure

| Directory | Namespace |
|-----------|-----------|
| `app/Controllers/` | `App\Controllers\` |
| `app/Core/` | `App\Core\` |
| `app/Helpers/` | `App\Helpers\` |
| `app/Middleware/` | `App\Middleware\` |
| `app/Models/` | `App\Models\` |
| `app/Services/` | `App\Services\` |

### Dependencies

```json
{
    "require": {
        "php": ">=7.4",
        "ext-sqlite3": "*",
        "vlucas/phpdotenv": "^5.5"
    }
}
```

---

## 🔧 Environment Variables

Copy `.env.example` to `.env` and configure:

```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com

# Database
DB_PATH=bd/bd_teste.db

# Security
CSRF_TOKEN_NAME=csrf_token
SECURE_COOKIES=true
SESSION_NAME=bar_tomazia_session

# WiFi
WIFI_REDE=YourNetwork
WIFI_PASSWORD=YourPassword

# SMS (Optional)
SMS_API_ENABLED=false
SMS_API_KEY=your-api-key
```

---

## 🚀 Deployment

### Requirements

- PHP 7.4 or higher
- SQLite3 extension
- mod_rewrite enabled

### Steps

1. Upload all files to server
2. Copy `.env.example` to `.env` and configure
3. Run `composer install --no-dev`
4. Set permissions:
   ```bash
   chmod 755 bd/
   chmod 644 bd/bd_teste.db
   chmod 755 logs/
   ```
5. Enable HTTPS in `.htaccess` (uncomment lines 16-17)

---

## 📝 Future Improvements

1. **Views Migration**: Move HTML from root `.php` files to `app/Views/`
2. **Storage Migration**: Move `bd/` and `logs/` to `storage/`
3. **Middleware Integration**: Apply middleware via router
4. **API Endpoints**: Add RESTful API routes
5. **Unit Tests**: Add PHPUnit test suite

---

## 📚 Resources

- [PSR-4 Autoloading](https://www.php-fig.org/psr/psr-4/)
- [PHP dotenv](https://github.com/vlucas/phpdotenv)
- [MVC Pattern](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller)
- [Front Controller Pattern](https://www.martinfowler.com/eaaCatalog/frontController.html)

---

**Last Updated:** February 2026  
**Version:** 2.0  
**Status:** ✅ Production Ready
