# Bar da Tomazia - Aplicação Web

Aplicação web moderna e segura para o Bar da Tomazia com menu digital, gestão de eventos e envolvimento com clientes.

## 🎯 Funcionalidades Principais

### Para Clientes
- **Menu Digital**: Navegue por cocktails, petiscos e bebidas organizados por categoria
- **Acesso WiFi**: Acesso fácil às credenciais WiFi do estabelecimento
- **Calendário de Eventos**: Veja os próximos eventos no bar
- **Mapa de Localização**: Encontre o Bar da Tomazia com Google Maps integrado
- **Galeria de Fotos**: Slideshow automático com fotos do bar

### Para Administradores
- **Gestão de Produtos**: Operações CRUD completas para itens do menu
- **Gestão de Eventos**: Criar, editar e gerir eventos do bar
- **Dashboard de Analytics**: Acompanhe o envolvimento dos clientes
- **Autenticação Segura**: Painel administrativo protegido por password

## 📋 Requisitos

- PHP 7.4 ou superior
- Extensão SQLite3
- Servidor web (Apache, Nginx, ou servidor integrado do PHP)
- HTTPS (recomendado para produção)

## 🚀 Início Rápido

### Instalação Local

```bash
# Clone o repositório
git clone https://github.com/trmsantos/projetotomazia.git
cd projetotomazia

# Inicie o servidor de desenvolvimento
php -S localhost:8000

# Acesse a aplicação
# Frontend: http://localhost:8000/index.php
# Admin: http://localhost:8000/login.php
```

### Instalação no cPanel

Consulte o [Guia de Instalação Completo](GUIA_INSTALACAO.md) para instruções detalhadas sobre:
- Instalação local (XAMPP, WAMP, MAMP)
- Deployment no cPanel
- Configuração de SSL
- Configuração de domínio

## 📁 Ficheiros Essenciais

```
projetotomazia/
├── index.php           # Página inicial com registo
├── bemvindo.php        # Página de boas-vindas (após login)
├── cardapio.php        # Menu digital
├── admin.php           # Painel administrativo
├── login.php           # Login do admin
├── config.php          # Configurações centralizadas
├── form.php            # Processa registo de clientes
├── fotos.php           # Galeria de fotos
├── erro.php            # Página de erro
├── termos.php          # Termos e condições
├── criaradmin.php      # Criação de conta admin (usar uma vez)
├── .htaccess           # Configurações Apache e segurança
├── bd/
│   └── bd_teste.db     # Base de dados SQLite
├── css/
│   └── style.css       # Estilos globais
├── img/                # Imagens e recursos
└── logs/               # Logs de erro
```

## 🗺️ Rotas Principais

| Rota | Descrição |
|------|-----------|
| `/index.php` | Página inicial com formulário de registo |
| `/bemvindo.php` | Boas-vindas com slideshow, WiFi, eventos |
| `/cardapio.php` | Menu completo do bar |
| `/fotos.php` | Galeria de fotos |
| `/login.php` | Login administrativo |
| `/admin.php` | Painel de gestão |

## ⚙️ Configuração

### Credenciais WiFi

Edite `config.php`:

```php
define('WIFI_REDE', 'Nome-Da-Sua-Rede');
define('WIFI_PASSWORD', 'Sua-Password');
```

### Criar Conta Admin

1. Acesse `/criaradmin.php` no navegador
2. Preencha username e password
3. **IMPORTANTE**: Remova o ficheiro após criar a conta

```bash
rm criaradmin.php  # Linux/Mac
del criaradmin.php # Windows
```

## 🔒 Segurança

### Funcionalidades Implementadas

- ✅ Proteção CSRF em todos os formulários
- ✅ Prevenção XSS com `htmlspecialchars()`
- ✅ Prevenção SQL Injection com queries parametrizadas
- ✅ Cookies seguros (HTTPOnly, Secure, SameSite)
- ✅ Password hashing com BCrypt
- ✅ Headers de segurança no `.htaccess`
- ✅ Proteção de ficheiros sensíveis
- ✅ Gestão segura de sessões

### Checklist de Segurança

- [ ] Ativar HTTPS em produção
- [ ] Usar passwords fortes para admin
- [ ] Remover `criaradmin.php` após uso
- [ ] Configurar backups regulares
- [ ] Manter PHP atualizado
- [ ] Monitorizar logs de erro

## 🎨 Melhorias Implementadas

### Slideshow na Galeria (bemvindo.php)
- ✨ Transições suaves com fade
- 🎯 Indicadores personalizados e responsivos
- 📱 Design totalmente responsivo
- 🔢 Contador de fotos
- ⏸️ Pausa ao passar o mouse
- 🎨 Controles estilizados e intuitivos

## 📝 Como Usar

### Para Clientes

1. Visite a homepage
2. Registe-se com nome, email e telefone
3. Aceda às credenciais WiFi
4. Navegue pelo menu digital
5. Veja eventos futuros
6. Encontre a localização do bar

### Para Administradores

1. Faça login em `/login.php`
2. Gira produtos no painel admin
3. Crie e edite eventos
4. Veja estatísticas de clientes
5. Monitorize analytics

## 🧪 Testes

```bash
# Testar sintaxe PHP
for file in *.php; do php -l "$file"; done

# Testar conexão à base de dados
php -r "require 'config.php'; getDbConnection();"
```

## 📖 Documentação Adicional

- [Guia de Instalação Completo](GUIA_INSTALACAO.md) - Instruções detalhadas de instalação
- [.htaccess](.htaccess) - Configurações de segurança e rewrite rules

## 🤝 Contribuir

1. Faça fork do repositório
2. Crie uma branch de funcionalidade
3. Faça suas alterações
4. Teste completamente
5. Submeta um pull request

## 📄 Licença

Este projeto é software proprietário do Bar da Tomazia.

## 👥 Autores

- Equipa de Desenvolvimento
- Bar da Tomazia

---

**Bar da Tomazia** - Onde cada momento é especial! 🍸✨

Para mais informações, consulte o [Guia de Instalação](GUIA_INSTALACAO.md).
