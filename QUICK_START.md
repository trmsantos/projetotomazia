# Quick Start Guide - Bar da Tomazia Modern PHP

Quick reference for getting started with the modernized codebase.

## 🚀 5-Minute Setup

### 1. Clone and Install
```bash
git clone https://github.com/trmsantos/projetotomazia.git
cd projetotomazia
composer install
```

### 2. Configure Environment
```bash
cp .env.example .env
# Edit .env with your settings (optional for development)
```

### 3. Start Development Server
```bash
php -S localhost:8000
```

### 4. Access Application
- **Homepage:** http://localhost:8000
- **Admin Login:** http://localhost:8000/login
- **Admin Panel:** http://localhost:8000/admin (after login)

**Default Admin Credentials:** Run `php criaradmin.php` to create admin user

---

## 📁 Project Structure

```
app/
├── Core/          # Framework (Router, Database, BaseController)
├── Controllers/   # Request handlers
├── Models/        # Database operations
├── Helpers/       # Utilities (Security, Validation, Logger)
└── Middleware/    # Auth & security filters

public/
└── index.php      # Front controller (entry point)

Legacy files (backward compatible):
index.php, login.php, admin.php, etc.
```

---

## 🔗 Routes Reference

### Public Routes
```
GET  /              Home page (registration)
GET  /bemvindo      Welcome page
GET  /cardapio      Digital menu
GET  /fotos         Photo gallery
GET  /termos        Terms and conditions
POST /register      Customer registration
```

### Auth Routes
```
GET  /login         Login form
POST /login         Process login
POST /logout        Logout
```

### Admin Routes (Protected)
```
GET  /admin                 Admin dashboard
POST /admin/product         Save product
POST /admin/product/delete  Delete product
POST /admin/event           Save event
POST /admin/event/delete    Delete event
POST /admin/event/toggle    Toggle event visibility
POST /admin/sms             Send SMS campaign
```

---

## 💻 Common Tasks

### Add New Route
**File:** `public/index.php`
```php
$router->get('/myroute', 'MyController@index', 'myroute.name');
```

### Create Controller
**File:** `app/Controllers/MyController.php`
```php
<?php
namespace App\Controllers;
use App\Core\BaseController;

class MyController extends BaseController
{
    public function index(array $params = []): void
    {
        $this->view('mypage.php', ['title' => 'My Page']);
    }
}
```

### Create Model
**File:** `app/Models/MyModel.php`
```php
<?php
namespace App\Models;

class MyModel extends BaseModel
{
    protected string $table = 'my_table';
    
    public function customMethod(): array
    {
        return $this->findAll(['status' => 'active']);
    }
}
```

### Validate Form Input
```php
use App\Helpers\ValidationHelper;

$validator = ValidationHelper::validate($_POST, [
    'email' => ['required', 'email'],
    'name' => ['required', 'minLength:3'],
    'phone' => ['required', 'phone']
]);

if ($validator->fails()) {
    $errors = $validator->errors();
}
```

### Database Operations
```php
use App\Models\Product;

$product = new Product();

// Find
$item = $product->find($id);

// Find all
$all = $product->findAll();

// Insert
$id = $product->insert(['nome_prod' => 'New', 'preco' => 10.50]);

// Update
$product->update($id, ['preco' => 12.00]);

// Delete
$product->delete($id);
```

### Add CSRF to Form
```php
use App\Helpers\SecurityHelper;

<form method="POST" action="/myaction">
    <input type="hidden" name="<?php echo $_ENV['CSRF_TOKEN_NAME']; ?>" 
           value="<?php echo SecurityHelper::generateCsrfToken(); ?>">
    <!-- form fields -->
</form>
```

### Verify CSRF in Controller
```php
use App\Helpers\SecurityHelper;

$tokenName = $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token';
if (!SecurityHelper::verifyCsrfToken($_POST[$tokenName])) {
    die("CSRF token invalid");
}
```

### Escape Output
```php
use App\Helpers\SecurityHelper;

echo SecurityHelper::escape($userInput);
// or shorthand
echo SecurityHelper::e($userInput);
```

### Log Messages
```php
use App\Helpers\Logger;

Logger::info("User action", ['user_id' => $id]);
Logger::error("Error", ['error' => $e->getMessage()]);
Logger::debug("Debug info", ['data' => $array]);
```

---

## 🔒 Security Checklist

### Every Form Needs:
- [ ] CSRF token input field
- [ ] Input validation
- [ ] Sanitization for display

### Every Controller Needs:
- [ ] CSRF verification for POST
- [ ] Input validation
- [ ] Error handling

### Every Output Needs:
- [ ] Escape with `SecurityHelper::e()` or `htmlspecialchars()`

### Every DB Query Needs:
- [ ] Prepared statements (automatic with Models)
- [ ] Type-safe parameters

---

## 🐛 Debugging

### Enable Debug Mode
**File:** `.env`
```env
APP_DEBUG=true
```

### Check Logs
```bash
tail -f logs/app.log
```

### View PHP Errors
Add to top of file temporarily:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### Test Route
```bash
curl http://localhost:8000/myroute
```

### Check Database
```bash
sqlite3 bd/bd_teste.db
> .tables
> SELECT * FROM produtos;
> .quit
```

---

## 📦 Dependencies

### Install
```bash
composer install
```

### Update
```bash
composer update
```

### Autoload (after adding new class)
```bash
composer dump-autoload
```

---

## 🔧 Configuration

### Key Settings (.env)
```env
# Debug
APP_DEBUG=false          # true for development

# Security
SECURE_COOKIES=false     # true for HTTPS

# Database
DB_PATH=bd/bd_teste.db   # relative to project root

# Session
SESSION_NAME=bar_tomazia_session
SESSION_LIFETIME=86400   # 24 hours
```

---

## 📖 Documentation

- **Full Documentation:** [README_NEW.md](README_NEW.md)
- **Migration Guide:** [MIGRATION_GUIDE.md](MIGRATION_GUIDE.md)
- **Security Summary:** [SECURITY_SUMMARY.md](SECURITY_SUMMARY.md)
- **PR Description:** [PR_DESCRIPTION.md](PR_DESCRIPTION.md)

---

## 🚨 Common Issues

### Routes Not Working
- Check `.htaccess` is being read by Apache
- Verify `mod_rewrite` is enabled
- Try: `php -S localhost:8000` for development

### Database Errors
- Check permissions: `chmod 664 bd/bd_teste.db`
- Verify path in `.env`

### Session Issues
- Check `SECURE_COOKIES` setting
- Clear browser cookies
- Verify session directory is writable

### Composer Not Found
```bash
# Install Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
```

---

## ✅ Before Pushing Code

- [ ] Run syntax check: `find app/ -name "*.php" -exec php -l {} \;`
- [ ] Test routes work
- [ ] Test forms submit
- [ ] Check logs for errors
- [ ] Verify CSRF tokens work
- [ ] Test authentication

---

## 🆘 Getting Help

1. Check documentation files
2. Review code examples in `app/Controllers/`
3. Check logs: `logs/app.log`
4. Contact development team

---

## 🎯 Next Steps

1. ✅ Read [README_NEW.md](README_NEW.md) for full documentation
2. ✅ Review [MIGRATION_GUIDE.md](MIGRATION_GUIDE.md) for patterns
3. ✅ Check [SECURITY_SUMMARY.md](SECURITY_SUMMARY.md) for security
4. ✅ Explore existing controllers for examples
5. ✅ Start building!

---

**Happy Coding!** 🚀
