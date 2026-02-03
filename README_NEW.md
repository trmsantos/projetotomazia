# Bar da Tomazia - Modern PHP Web Application

A refactored, modernized, and secured web application for Bar da Tomazia featuring MVC architecture, clean URLs, comprehensive security features, and backward compatibility.

## 🎯 Features

### Customer Features
- **Digital Menu**: Browse cocktails, snacks, and beverages organized by category
- **WiFi Access**: Easy access to venue WiFi credentials
- **Events Calendar**: View upcoming events at the bar
- **Location Map**: Find Bar da Tomazia with integrated Google Maps
- **Photo Sharing**: Share moments from the bar

### Admin Features
- **Product Management**: Full CRUD operations for menu items
- **Event Management**: Create, edit, and manage bar events
- **SMS Marketing**: Send promotional messages to customers
- **Analytics Dashboard**: Track customer engagement and adherence
- **Secure Authentication**: Password-protected admin panel with rate limiting

## 🏗️ Architecture

### Modern MVC Structure

```
projetotomazia/
├── app/
│   ├── Core/                 # Core framework components
│   │   ├── Router.php        # URL routing with dynamic parameters
│   │   ├── Database.php      # Database abstraction layer
│   │   ├── BaseController.php # Base controller class
│   │   └── ExceptionHandler.php # Centralized exception handling
│   ├── Controllers/          # Request handlers
│   │   ├── HomeController.php
│   │   ├── AuthController.php
│   │   ├── CustomerController.php
│   │   └── AdminController.php
│   ├── Models/               # Data layer
│   │   ├── BaseModel.php
│   │   ├── Customer.php
│   │   ├── Product.php
│   │   ├── Event.php
│   │   └── AdminUser.php
│   ├── Services/             # Business logic layer
│   ├── Middleware/           # Request interceptors
│   │   ├── AuthMiddleware.php
│   │   ├── AdminMiddleware.php
│   │   └── CsrfMiddleware.php
│   └── Helpers/              # Utility classes
│       ├── SecurityHelper.php
│       ├── ValidationHelper.php
│       └── Logger.php
├── public/                   # Public web root
│   ├── index.php            # Front controller
│   └── assets/              # Static assets
├── vendor/                   # Composer dependencies
├── logs/                     # Application logs
├── bd/                       # SQLite database
├── .env                      # Environment configuration (not in repo)
├── .env.example              # Environment template
├── .htaccess                 # Apache URL rewriting
└── composer.json             # PHP dependencies

# Legacy files (maintained for backward compatibility)
├── index.php, login.php, admin.php, etc.
```

## 🔒 Security Features

### Implemented Security Measures

1. **CSRF Protection**
   - Session-based CSRF tokens on all forms
   - Automatic token validation on POST/PUT/DELETE requests
   - Token regeneration after login

2. **SQL Injection Prevention**
   - All database queries use prepared statements
   - No dynamic SQL concatenation
   - Type-safe parameter binding

3. **XSS Prevention**
   - All user input sanitized with `htmlspecialchars()`
   - Output escaping helper: `SecurityHelper::escape()`
   - Content Security Policy headers (configurable)

4. **Authentication Security**
   - Password hashing with `password_hash()` (BCrypt)
   - Session regeneration after login
   - Login rate limiting (5 attempts per 5 minutes)
   - Secure session configuration

5. **Session Hardening**
   - `httponly` flag enabled
   - `secure` flag for HTTPS
   - `samesite=Strict` attribute
   - Strict session mode

6. **File Upload Security**
   - MIME type validation
   - Extension restrictions
   - Random filename generation
   - Sanitization helpers

7. **Access Control**
   - Authentication middleware for protected routes
   - Admin authorization middleware
   - Direct file access protection via .htaccess

8. **Environment Security**
   - Credentials in environment variables
   - `.env` file excluded from repository
   - Protected directories (app, vendor, logs)

## 📋 Requirements

- PHP 7.4 or higher
- SQLite3 extension
- Apache with mod_rewrite (or equivalent)
- Composer for dependency management
- HTTPS (recommended for production)

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/trmsantos/projetotomazia.git
cd projetotomazia
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

Copy the example environment file and configure:

```bash
cp .env.example .env
```

Edit `.env` with your settings:

```env
# Database
DB_PATH=bd/bd_teste.db

# WiFi Credentials
WIFI_REDE=Your-Network-Name
WIFI_PASSWORD=Your-Password

# Application Settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Security
SECURE_COOKIES=true  # Set to true for HTTPS

# SMS API (optional)
SMS_API_ENABLED=false
```

### 4. Set Up Web Server

#### Apache Configuration

The included `.htaccess` handles URL rewriting. Ensure `mod_rewrite` is enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Set the document root to the project directory (not `public/`), or configure a virtual host.

#### Development Server

For development, use PHP's built-in server:

```bash
php -S localhost:8000
```

**Note**: The built-in server doesn't support .htaccess. Routes will work, but legacy URLs won't redirect automatically.

### 5. Database Setup

The SQLite database is included. For production, ensure proper permissions:

```bash
chmod 664 bd/bd_teste.db
chmod 775 bd/
```

### 6. Create Admin User (if needed)

```bash
php criaradmin.php
```

## 🔗 Routing System

### Clean URLs

The application supports clean URLs without `.php` extensions:

```
Old URL                  New URL              Status
/index.php              /                    301 Redirect
/login.php              /login               301 Redirect
/admin.php              /admin               301 Redirect
/bemvindo.php           /bemvindo            301 Redirect
/cardapio.php           /cardapio            301 Redirect
```

### Dynamic Routes

Routes support dynamic parameters:

```php
// In routes configuration
$router->get('/product/{id}', 'ProductController@show');

// Controller receives parameters
public function show(array $params): void
{
    $productId = $params['id'];
    // ...
}
```

### Named Routes

Generate URLs using named routes:

```php
// Define named route
$router->get('/admin/product/{id}', 'ProductController@edit', 'admin.product.edit');

// Generate URL
$url = $router->url('admin.product.edit', ['id' => 123]);
// Result: /admin/product/123
```

## 🛠️ Usage

### For Customers

1. Visit homepage: `/` or `/index.php`
2. Register with name, email, and phone
3. Access WiFi credentials
4. Browse the digital menu at `/cardapio`
5. View upcoming events at `/bemvindo`
6. Share photos at `/fotos`

### For Administrators

1. Login at `/login`
2. Access admin panel at `/admin`
3. Manage products, events, and customers
4. Send SMS marketing campaigns
5. View analytics and reports

## 🔧 Configuration

### Environment Variables

All configuration is in `.env`:

```env
# Core Settings
DB_PATH=bd/bd_teste.db
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Security
CSRF_TOKEN_NAME=csrf_token
SECURE_COOKIES=true
SESSION_NAME=bar_tomazia_session
SESSION_LIFETIME=86400

# WiFi
WIFI_REDE=Your-Network
WIFI_PASSWORD=Your-Password

# SMS API
SMS_API_ENABLED=false
SMS_API_KEY=your_api_key
SMS_API_SECRET=your_api_secret
SMS_API_FROM=+351912345678
SMS_API_ENDPOINT=https://api.example.com/sms
SMS_API_COUNTRY_CODE=+351
SMS_API_TIMEOUT=30
```

### Security Headers

Configure in `.htaccess`:

```apache
# X-Frame-Options
Header always set X-Frame-Options "SAMEORIGIN"

# XSS Protection
Header always set X-XSS-Protection "1; mode=block"

# Content Type Options
Header always set X-Content-Type-Options "nosniff"
```

## 🧪 Development

### Adding New Routes

Edit `public/index.php`:

```php
// Add new route
$router->get('/myroute', 'MyController@method', 'route.name');
$router->post('/myroute', 'MyController@save');
```

### Creating Controllers

Extend `BaseController`:

```php
<?php

namespace App\Controllers;

use App\Core\BaseController;

class MyController extends BaseController
{
    public function method(array $params = []): void
    {
        // Your logic
        $this->view('myview.php', ['data' => $value]);
    }
}
```

### Creating Models

Extend `BaseModel`:

```php
<?php

namespace App\Models;

class MyModel extends BaseModel
{
    protected string $table = 'my_table';
    protected string $primaryKey = 'id';
    
    // Custom methods
    public function findByCustomField(string $value): ?array
    {
        return $this->findOne(['custom_field' => $value]);
    }
}
```

### Input Validation

Use `ValidationHelper`:

```php
use App\Helpers\ValidationHelper;

$validator = ValidationHelper::validate($_POST, [
    'email' => ['required', 'email'],
    'name' => ['required', 'minLength:3'],
    'phone' => ['required', 'phone']
]);

if ($validator->fails()) {
    $errors = $validator->errors();
    // Handle errors
}
```

### Logging

Use the `Logger` helper:

```php
use App\Helpers\Logger;

Logger::info("User logged in", ['user_id' => $userId]);
Logger::error("Database error", ['error' => $e->getMessage()]);
Logger::debug("Debug info", ['data' => $debugData]);
```

## 🔐 Security Best Practices

### For Production

1. **Enable HTTPS**
   - Set `SECURE_COOKIES=true` in `.env`
   - Uncomment HTTPS redirect in `.htaccess`

2. **Disable Debug Mode**
   - Set `APP_DEBUG=false` in `.env`

3. **Protect Sensitive Files**
   - Ensure `.env` is not in repository
   - Verify `.htaccess` protections are active

4. **Regular Updates**
   - Keep PHP updated
   - Run `composer update` regularly
   - Monitor security advisories

5. **Database Backups**
   - Regular automated backups of `bd/bd_teste.db`
   - Store backups securely off-site

6. **Monitor Logs**
   - Check `logs/app.log` regularly
   - Set up alerts for errors

## 🧪 Testing

### Manual Testing

Test all routes:

```bash
# Home page
curl http://localhost:8000/

# Login
curl http://localhost:8000/login

# Admin (requires auth)
curl -c cookies.txt -d "username=admin&password=pass" http://localhost:8000/login
curl -b cookies.txt http://localhost:8000/admin
```

### Syntax Validation

```bash
# Check all PHP files
find . -name "*.php" -not -path "./vendor/*" -exec php -l {} \;
```

### Database Connection

```bash
php -r "require 'config.php'; getDbConnection(); echo 'OK';"
```

## 📝 Migration Notes

### Backward Compatibility

- All legacy URLs redirect to new clean URLs (301)
- Legacy functions in `config.php` still work
- Existing database structure unchanged
- Session data preserved

### Breaking Changes

None. The refactoring maintains full backward compatibility.

### Recommended Migration Path

1. Deploy new code alongside existing
2. Test all routes and functionality
3. Monitor logs for any issues
4. Gradually refactor remaining legacy code
5. Update internal links to use clean URLs

## 🐛 Troubleshooting

### Routes Not Working

- Check `.htaccess` is being read
- Verify `mod_rewrite` is enabled
- Check Apache configuration allows `.htaccess` overrides

### Database Errors

- Check file permissions on `bd/` and `bd/bd_teste.db`
- Verify `DB_PATH` in `.env`
- Check SQLite3 extension is installed

### Session Issues

- Verify session directory is writable
- Check `SECURE_COOKIES` setting matches HTTPS availability
- Clear browser cookies

### 500 Errors

- Enable debug mode: `APP_DEBUG=true`
- Check `logs/app.log`
- Verify PHP error log

## 📄 License

This project is proprietary software for Bar da Tomazia.

## 👥 Authors

- Development Team
- Bar da Tomazia

## 📞 Support

For issues or questions, please contact the development team.

---

**Bar da Tomazia** - Where every moment is special! 🍸✨
