# Bar da Tomazia - Web Application

A modern, secure web application for Bar da Tomazia featuring digital menu, event management, and customer engagement.

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
- **Analytics Dashboard**: Track customer engagement and adherence
- **Secure Authentication**: Password-protected admin panel

## 🔒 Security Features

- **CSRF Protection**: All forms protected with CSRF tokens
- **XSS Prevention**: All user inputs sanitized with htmlspecialchars()
- **SQL Injection Prevention**: Parameterized queries throughout
- **Secure Cookies**: HTTPOnly, Secure, and SameSite attributes
- **Password Hashing**: BCrypt hashing for admin passwords
- **Session Management**: Secure session handling

## 📋 Requirements

- PHP 7.4 or higher
- SQLite3 extension
- Web server (Apache, Nginx, or PHP built-in server)
- HTTPS (recommended for production)

## 🚀 Installation

1. Clone the repository:
```bash
git clone https://github.com/trmsantos/projetotomazia.git
cd projetotomazia
```

2. Verify PHP and SQLite are installed:
```bash
php --version
php -m | grep sqlite3
```

3. Configure the database:
   - The database is already included in `bd/bd_teste.db`
   - Update WiFi credentials in `config.php` if needed

4. Start the development server:
```bash
php -S localhost:8000
```

5. Access the application:
   - Customer Interface: http://localhost:8000/index.php
   - Admin Panel: http://localhost:8000/login.php

## 📁 Project Structure

```
projetotomazia/
├── admin.php           # Admin dashboard with CRUD operations
├── bemvindo.php        # Welcome page with navigation
├── cardapio.php        # Digital menu
├── config.php          # Centralized configuration
├── form.php            # Customer registration handler
├── fotos.php           # Photo sharing page
├── index.php           # Landing page
├── login.php           # Admin authentication
├── bd/
│   └── bd_teste.db     # SQLite database
├── css/
│   └── style.css       # Global styles
└── img/                # Images and assets
```

## 🗄️ Database Schema

### Tables

**produtos** (Menu Items)
- id_produto (INTEGER, PRIMARY KEY)
- nome_prod (TEXT)
- preco (NUMERIC)
- tipo (VARCHAR)

**eventos** (Events)
- id (INTEGER, PRIMARY KEY)
- nome_evento (TEXT, NOT NULL)
- data_evento (DATE, NOT NULL)
- descricao (TEXT)
- imagem_url (TEXT)

**tomazia_clientes** (Customers)
- id (INTEGER, PRIMARY KEY)
- user_id (INTEGER)
- nome (TEXT)
- email (TEXT)
- telemovel (TEXT)
- data_registro (DATETIME)

**admin_users** (Administrators)
- id (INTEGER, PRIMARY KEY)
- username (TEXT, NOT NULL)
- psw (TEXT, NOT NULL)

## 🎨 Design Features

- **Responsive Design**: Mobile-first approach, works on all devices
- **Modern UI**: Clean, card-based interface
- **Color Palette**: 
  - Primary: #A52A2A (Brown)
  - Accent: #8B0000 (Dark Red)
  - Background: #f8f9fa
- **Smooth Animations**: Fade-in effects and transitions
- **Accessibility**: Semantic HTML and ARIA labels

## 🔧 Configuration

Edit `config.php` to customize:

```php
// Database path
define('DB_PATH', __DIR__ . '/bd/bd_teste.db');

// WiFi credentials
define('WIFI_REDE', 'Your-Network-Name');
define('WIFI_PASSWORD', 'Your-Password');
```

## 🛡️ Security Best Practices

1. **HTTPS**: Always use HTTPS in production
2. **Strong Passwords**: Use strong passwords for admin accounts
3. **Regular Updates**: Keep PHP and dependencies updated
4. **Backups**: Regular database backups
5. **Error Logging**: Monitor error logs for suspicious activity

## 📱 Usage

### For Customers
1. Visit the homepage
2. Register with name, email, and phone
3. Access WiFi credentials
4. Browse the digital menu
5. View upcoming events
6. Find the bar location

### For Administrators
1. Login at `/login.php`
2. Manage products in the admin panel
3. Create and edit events
4. View customer analytics
5. Monitor adherence statistics

## 🧪 Testing

Run the included tests:

```bash
# Test all PHP syntax
for file in *.php; do php -l "$file"; done

# Test database connection
php -r "require 'config.php'; getDbConnection();"

# See TESTING_REPORT.md for detailed test results
```

## 📝 Development Notes

- All database queries use prepared statements
- CSRF tokens are session-based
- Cookies require HTTPS to work properly in production
- The database path is relative for portability

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is proprietary software for Bar da Tomazia.

## 👥 Authors

- Development Team
- Bar da Tomazia

## 📞 Support

For issues or questions, please contact the development team.

---

**Bar da Tomazia** - Where every moment is special! 🍸✨
