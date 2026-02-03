# Migration Guide - Legacy to Modern PHP

This guide explains the architecture changes and how to work with the modernized codebase.

## Overview of Changes

### 1. Project Structure

**Before:**
```
projetotomazia/
├── index.php
├── login.php
├── admin.php
├── config.php
├── form.php
└── ...
```

**After:**
```
projetotomazia/
├── app/
│   ├── Core/          # Framework components
│   ├── Controllers/   # Request handlers
│   ├── Models/        # Database layer
│   ├── Middleware/    # Request filters
│   └── Helpers/       # Utilities
├── public/
│   └── index.php      # Front controller
├── vendor/            # Dependencies
├── .env               # Configuration
└── [legacy files]     # Backward compatible
```

### 2. Routing System

#### Old Way (Direct File Access)
```
URL: /admin.php
File: admin.php directly executed
```

#### New Way (Front Controller)
```
URL: /admin
Route: GET /admin -> AdminController@dashboard
File: app/Controllers/AdminController.php
```

**Backward Compatibility:** Old URLs like `/admin.php` redirect to `/admin` (301).

### 3. Database Access

#### Old Way (Direct SQLite)
```php
$db = new SQLite3(DB_PATH);
$query = $db->prepare('SELECT * FROM produtos WHERE id = :id');
$query->bindValue(':id', $id, SQLITE3_INTEGER);
$result = $query->execute()->fetchArray(SQLITE3_ASSOC);
```

#### New Way (Model Layer)
```php
$product = new Product();
$result = $product->find($id);
```

**Benefits:**
- Cleaner code
- Reusable queries
- Consistent error handling
- Type safety

### 4. Security Enhancements

#### CSRF Protection

**Old Way:**
```php
// config.php
function generateCsrfToken() { ... }

// In form
<input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

// In handler
if (!verifyCsrfToken($_POST['csrf_token'])) { die(); }
```

**New Way (Same, but with helpers):**
```php
use App\Helpers\SecurityHelper;

// In form
<input type="hidden" name="<?php echo $_ENV['CSRF_TOKEN_NAME']; ?>" 
       value="<?php echo SecurityHelper::generateCsrfToken(); ?>">

// In controller (automatic via middleware)
// Or manually:
if (!SecurityHelper::verifyCsrfToken($token)) { die(); }
```

#### Output Escaping

**Old Way:**
```php
echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
```

**New Way:**
```php
use App\Helpers\SecurityHelper;

echo SecurityHelper::escape($value);
// or shorthand
echo SecurityHelper::e($value);
```

#### Input Validation

**Old Way:**
```php
$nome = trim($_POST['nome']);
if (strlen($nome) < 3) {
    $errors[] = "Nome muito curto";
}
if (!preg_match('/^[a-zA-Z]+$/', $nome)) {
    $errors[] = "Nome inválido";
}
```

**New Way:**
```php
use App\Helpers\ValidationHelper;

$validator = ValidationHelper::validate($_POST, [
    'nome' => ['required', 'minLength:3', 'pattern:/^[a-zA-Z]+$/']
]);

if ($validator->fails()) {
    $errors = $validator->errors();
}
```

### 5. Authentication

#### Login Process

**Old Way:**
```php
// login.php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = // ... check credentials
    if ($user) {
        $_SESSION['loggedin'] = true;
        header('Location: admin.php');
    }
}
```

**New Way:**
```php
// app/Controllers/AuthController.php
public function login(array $params = []): void
{
    // Verify CSRF
    // Check rate limiting
    // Validate credentials
    // Regenerate session
    SecurityHelper::regenerateSession();
    $_SESSION['loggedin'] = true;
    $this->redirect('/admin');
}
```

**Improvements:**
- Rate limiting (5 attempts per 5 minutes)
- Session regeneration
- Logging
- Centralized error handling

### 6. Configuration Management

#### Old Way (Hardcoded)
```php
// config.php
define('WIFI_REDE', 'NOS-2B6E-5');
define('WIFI_PASSWORD', '5YV4UJC4');
define('DB_PATH', __DIR__ . '/bd/bd_teste.db');
```

#### New Way (Environment Variables)
```php
// .env
WIFI_REDE=NOS-2B6E-5
WIFI_PASSWORD=5YV4UJC4
DB_PATH=bd/bd_teste.db

// In code
$wifiNetwork = $_ENV['WIFI_REDE'];
```

**Benefits:**
- No secrets in repository
- Easy deployment configuration
- Environment-specific settings

## Migration Checklist

### For Developers

- [ ] **Read Documentation**
  - Review README_NEW.md
  - Understand routing system
  - Learn validation helpers

- [ ] **Set Up Environment**
  - Copy `.env.example` to `.env`
  - Configure settings
  - Install dependencies: `composer install`

- [ ] **Test Existing Features**
  - All URLs work or redirect
  - Authentication works
  - Database operations work
  - Forms submit correctly

- [ ] **Learn New Patterns**
  - Controllers extend BaseController
  - Models extend BaseModel
  - Use helpers for security

### For Deployment

- [ ] **Pre-Deployment**
  - Backup database
  - Test in staging environment
  - Review `.env` configuration
  - Check PHP version >= 7.4

- [ ] **Deployment**
  - Deploy all files
  - Run `composer install --no-dev`
  - Copy `.env.example` to `.env`
  - Configure `.env` for production
  - Set file permissions
  - Enable `.htaccess` processing

- [ ] **Post-Deployment**
  - Test all routes
  - Verify authentication
  - Check logs for errors
  - Monitor performance

- [ ] **Production Settings**
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `SECURE_COOKIES=true` (with HTTPS)
  - Enable HTTPS redirect in `.htaccess`

## Common Patterns

### Creating a New Page

1. **Define Route** (public/index.php)
```php
$router->get('/mypage', 'MyController@index', 'mypage');
```

2. **Create Controller** (app/Controllers/MyController.php)
```php
<?php
namespace App\Controllers;
use App\Core\BaseController;

class MyController extends BaseController
{
    public function index(array $params = []): void
    {
        $data = ['title' => 'My Page'];
        $this->view('mypage.php', $data);
    }
}
```

3. **Create View** (mypage.php)
```php
<?php
require_once 'config.php';
use App\Helpers\SecurityHelper;
SecurityHelper::initSecureSession();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo SecurityHelper::e($title); ?></title>
</head>
<body>
    <!-- Your content -->
</body>
</html>
```

### Adding Form Validation

```php
use App\Helpers\ValidationHelper;
use App\Helpers\SecurityHelper;

// In controller
public function save(array $params = []): void
{
    // Verify CSRF
    $tokenName = $_ENV['CSRF_TOKEN_NAME'];
    if (!SecurityHelper::verifyCsrfToken($_POST[$tokenName])) {
        die("CSRF token invalid");
    }
    
    // Validate
    $validator = ValidationHelper::validate($_POST, [
        'name' => ['required', 'minLength:3'],
        'email' => ['required', 'email'],
        'phone' => ['required', 'phone']
    ]);
    
    if ($validator->fails()) {
        $this->view('form.php', ['errors' => $validator->errors()]);
        return;
    }
    
    // Process...
}
```

### Database Operations

```php
use App\Models\Product;

// Find by ID
$product = new Product();
$data = $product->find($id);

// Find all
$products = $product->findAll();

// Find with conditions
$products = $product->findAll(['tipo' => 'Bebidas']);

// Insert
$id = $product->insert([
    'nome_prod' => 'New Product',
    'preco' => 10.50,
    'tipo' => 'Bebidas'
]);

// Update
$product->update($id, [
    'preco' => 12.00
]);

// Delete
$product->delete($id);
```

### Logging

```php
use App\Helpers\Logger;

Logger::info("User action", ['user_id' => $userId]);
Logger::warning("Unusual activity", ['details' => $data]);
Logger::error("Error occurred", ['error' => $e->getMessage()]);
Logger::debug("Debug info", ['data' => $debugData]);
```

## Key Concepts

### MVC Pattern

- **Model**: Data and database logic
- **View**: HTML presentation
- **Controller**: Request handling and business logic

### Separation of Concerns

- **Controllers**: Handle HTTP requests
- **Models**: Interact with database
- **Services**: Business logic
- **Helpers**: Utility functions
- **Middleware**: Request filtering

### Dependency Injection

Controllers receive dependencies via constructor:

```php
class MyController extends BaseController
{
    private MyService $service;
    
    public function __construct()
    {
        parent::__construct();
        $this->service = new MyService();
    }
}
```

### Error Handling

Centralized via ExceptionHandler:

```php
// All exceptions are caught and logged
throw new Exception("Something went wrong");

// Logs to logs/app.log
// Shows user-friendly error in production
// Shows detailed error in development
```

## FAQ

### Q: Do I need to update all legacy files immediately?

**A:** No. Legacy files continue to work. Refactor gradually as needed.

### Q: Can I use old functions like `generateCsrfToken()`?

**A:** Yes. These functions are maintained for backward compatibility but internally use the new helpers.

### Q: How do I test locally without Apache?

**A:** Use PHP built-in server: `php -S localhost:8000`. Note: .htaccess won't work, so use full URLs like `/login.php`.

### Q: Where are sessions stored?

**A:** PHP default session storage. Configure via `php.ini` if needed.

### Q: How do I debug issues?

**A:** 
1. Set `APP_DEBUG=true` in `.env`
2. Check `logs/app.log`
3. Check PHP error log
4. Use `Logger::debug()` for custom logging

### Q: Can I add my own helpers?

**A:** Yes. Create in `app/Helpers/` and use PSR-4 autoloading:
```php
namespace App\Helpers;
class MyHelper { ... }
```

### Q: How do I protect a route?

**A:** Use middleware in route definition or check in controller:
```php
// Manual check
if (!isset($_SESSION['loggedin'])) {
    $this->redirect('/login');
}
```

## Resources

- **Main README**: README_NEW.md - Full documentation
- **Code Examples**: app/Controllers/ - Reference implementations
- **Security Guide**: See "Security Features" in README_NEW.md
- **PHP Documentation**: https://www.php.net/manual/

## Support

For questions or issues, contact the development team.
