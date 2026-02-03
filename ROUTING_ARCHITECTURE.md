# Routing System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                         User's Browser                                │
│                    http://example.com/produto/123                     │
└───────────────────────────────┬─────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        Apache Web Server                              │
│                                                                       │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  Root .htaccess                                             │   │
│  │  - Redirect to /public/                                     │   │
│  │  - RewriteRule ^(.*)$ public/$1                             │   │
│  └──────────────────────┬──────────────────────────────────────┘   │
│                         │                                             │
│                         ▼                                             │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  public/.htaccess                                           │   │
│  │  - Allow direct access to files (CSS, JS, images)          │   │
│  │  - Route everything else to index.php                       │   │
│  │  - RewriteRule ^(.*)$ index.php [QSA,L]                    │   │
│  └──────────────────────┬──────────────────────────────────────┘   │
└─────────────────────────┼─────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    public/index.php (Front Controller)                │
│                                                                       │
│  1. session_start()                                                  │
│  2. require 'config.php'                                             │
│  3. require 'Router.php'                                             │
│  4. $router = new Router()                                           │
│  5. require 'routes.php'                                             │
│  6. $router->dispatch()                                              │
└───────────────────────────────┬─────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      Router.php (Router Class)                        │
│                                                                       │
│  getCurrentUri() → /produto/123                                      │
│  matchRoute()    → Find matching pattern                             │
│                                                                       │
│  Routes Table:                                                       │
│  ┌──────────────────────────┬──────────────────────────────────┐   │
│  │ Pattern                  │ Handler                          │   │
│  ├──────────────────────────┼──────────────────────────────────┤   │
│  │ /                        │ function() { require index.php } │   │
│  │ /login                   │ function() { require login.php } │   │
│  │ /produto/{id}            │ [$controller, 'show']            │   │
│  │ /admin/dashboard         │ function() { echo "Admin" }      │   │
│  └──────────────────────────┴──────────────────────────────────┘   │
│                                                                       │
│  Match found: /produto/{id}                                          │
│  Extracted params: ['id' => '123']                                   │
└───────────────────────────────┬─────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────────┐
│              controllers/ProductController.php                        │
│                                                                       │
│  show($params) {                                                     │
│    $productId = $params['id']; // 123                               │
│    $product = $this->getProductById($productId);                    │
│                                                                       │
│    ┌────────────────────────────────────────┐                       │
│    │  SQLite Database (bd/bd_teste.db)      │                       │
│    │  SELECT * FROM produtos WHERE id = 123 │                       │
│    │  Returns: {id: 123, nome: "Café", ...} │                       │
│    └────────────────────────────────────────┘                       │
│                                                                       │
│    $this->renderProductPage($product);                              │
│  }                                                                   │
└───────────────────────────────┬─────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────────┐
│                         HTML Response                                 │
│                                                                       │
│  <!DOCTYPE html>                                                     │
│  <html>                                                              │
│    <head>                                                            │
│      <title>Café - Bar da Tomazia</title>                           │
│      <link href="/css/style.css">                                   │
│    </head>                                                           │
│    <body>                                                            │
│      <h1>Café</h1>                                                   │
│      <p>Preço: €0.85</p>                                            │
│    </body>                                                           │
│  </html>                                                             │
└───────────────────────────────┬─────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────────┐
│                         User's Browser                                │
│                  Displays: Product page for Café                      │
└─────────────────────────────────────────────────────────────────────┘
```

## Request Flow Explanation

### 1. User Request
User visits `http://example.com/produto/123`

### 2. Apache Processing
- Root `.htaccess` checks if request is for `/public/`
- If not, redirects to `/public/produto/123`
- Public `.htaccess` checks if file exists
- File doesn't exist, so rewrites to `/public/index.php`

### 3. Front Controller
- `public/index.php` starts session
- Loads configuration
- Creates Router instance
- Loads route definitions from `routes.php`
- Calls `$router->dispatch()`

### 4. Router Processing
- Gets current URI: `/produto/123`
- Iterates through registered routes
- Finds match: `/produto/{id}` pattern
- Extracts parameter: `['id' => '123']`
- Calls registered handler with parameters

### 5. Controller Execution
- ProductController's `show()` method is called
- Receives `['id' => '123']` as parameter
- Queries database for product with ID 123
- Fetches product data: `{id: 123, nome: "Café", preco: 0.85, ...}`
- Renders HTML page with product information

### 6. Response
- HTML is sent back to Apache
- Apache sends to user's browser
- Browser displays the product page

## Static Asset Handling

```
┌─────────────────────────────────────────────────────────────────────┐
│                         User's Browser                                │
│                  http://example.com/css/style.css                     │
└───────────────────────────────┬─────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        Apache Web Server                              │
│                                                                       │
│  Root .htaccess → Redirect to /public/css/style.css                 │
│  Public .htaccess → File exists? YES!                                │
│  → Serve file directly (bypass router)                               │
└───────────────────────────────┬─────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    public/css/style.css                               │
│                    Served directly by Apache                          │
└─────────────────────────────────────────────────────────────────────┘
```

Static files (CSS, JS, images) are served directly by Apache without going through the router for optimal performance.

## 404 Handling Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│                         User's Browser                                │
│                http://example.com/nonexistent                         │
└───────────────────────────────┬─────────────────────────────────────┘
                                │
                                ▼
                      [Apache Processing]
                                │
                                ▼
                    [Front Controller Loads]
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      Router.php                                       │
│                                                                       │
│  getCurrentUri() → /nonexistent                                      │
│  matchRoute()    → No match found!                                   │
│  executeNotFoundHandler()                                            │
└───────────────────────────────┬─────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    404 Handler (from routes.php)                      │
│                                                                       │
│  http_response_code(404)                                             │
│  Display custom 404 page                                             │
└───────────────────────────────┬─────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────────┐
│                         User's Browser                                │
│                   Displays: 404 Error Page                            │
└─────────────────────────────────────────────────────────────────────┘
```

## Component Interactions

```
┌──────────────────┐
│   .htaccess      │
│   (Root)         │──┐
└──────────────────┘  │
                      │
┌──────────────────┐  │    ┌──────────────────┐
│   .htaccess      │  │    │  index.php       │
│   (Public)       │──┼───▶│  (Front          │
└──────────────────┘  │    │   Controller)    │
                      │    └─────────┬────────┘
                      │              │
                      │              ▼
                      │    ┌──────────────────┐
                      │    │  config.php      │
                      │    │  (Configuration) │
                      │    └──────────────────┘
                      │              │
                      │              ▼
                      │    ┌──────────────────┐
                      └───▶│  Router.php      │
                           │  (Router Class)  │
                           └─────────┬────────┘
                                     │
                                     ▼
                           ┌──────────────────┐
                           │  routes.php      │
                           │  (Route Defs)    │
                           └─────────┬────────┘
                                     │
                                     ▼
                           ┌──────────────────┐
                           │  Controllers/    │
                           │  (Controllers)   │
                           └─────────┬────────┘
                                     │
                                     ▼
                           ┌──────────────────┐
                           │  bd/bd_teste.db  │
                           │  (Database)      │
                           └──────────────────┘
```

## Route Resolution Algorithm

```
function dispatch() {
    1. Get REQUEST_METHOD (GET/POST)
    2. Get REQUEST_URI (/produto/123)
    
    3. For each registered route:
        a. Convert pattern to regex
           /produto/{id} → /^\/produto\/(?P<id>[^\/]+)$/
        
        b. Match URI against regex
           preg_match('/^\/produto\/(?P<id>[^\/]+)$/', '/produto/123')
           
        c. If match found:
           - Extract named parameters
           - Call handler with parameters
           - RETURN
    
    4. No match found:
        - Call 404 handler
        - RETURN
}
```

This visual guide demonstrates the complete flow of a request through the routing system, from the initial browser request to the final rendered response.
