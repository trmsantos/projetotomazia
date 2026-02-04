# 📖 Guia de Instalação - Bar da Tomazia

## 📋 Índice
1. [Requisitos](#requisitos)
2. [Instalação Local](#instalação-local)
3. [Instalação no cPanel](#instalação-no-cpanel)
4. [Estrutura de Rotas](#estrutura-de-rotas)
5. [Configuração](#configuração)
6. [Segurança](#segurança)
7. [Resolução de Problemas](#resolução-de-problemas)

---

## 🔧 Requisitos

### Requisitos Mínimos
- **PHP**: 7.4 ou superior
- **Extensões PHP**: 
  - `sqlite3` (para base de dados)
  - `mbstring` (para strings multibyte)
  - `curl` (para integrações futuras)
- **Servidor Web**: Apache ou Nginx
- **Espaço em Disco**: Mínimo 50MB

### Requisitos Recomendados
- **PHP**: 8.0 ou superior
- **HTTPS**: Certificado SSL (gratuito via Let's Encrypt)
- **Backup**: Sistema de backup automático

---

## 💻 Instalação Local

### Opção 1: Usando o Servidor PHP Integrado (Desenvolvimento)

1. **Clone ou extraia o repositório:**
```bash
cd /caminho/para/projetos
git clone https://github.com/trmsantos/projetotomazia.git
cd projetotomazia
```

2. **Verifique se o PHP está instalado:**
```bash
php --version
php -m | grep sqlite3
```

3. **Configure as credenciais WiFi (opcional):**
Edite o ficheiro `config.php` e altere:
```php
define('WIFI_REDE', 'Nome-Da-Sua-Rede');
define('WIFI_PASSWORD', 'Sua-Password-WiFi');
```

4. **Inicie o servidor de desenvolvimento:**
```bash
php -S localhost:8000
```

5. **Acesse a aplicação:**
- Frontend: http://localhost:8000/index.php
- Admin: http://localhost:8000/login.php
- Credenciais padrão: (consulte o administrador)

### Opção 2: Usando XAMPP/WAMP (Windows)

1. **Instale o XAMPP:**
   - Download: https://www.apachefriends.org/
   - Instale e inicie Apache

2. **Copie os ficheiros:**
```
C:\xampp\htdocs\projetotomazia\
```

3. **Acesse:**
   - http://localhost/projetotomazia/index.php

### Opção 3: Usando MAMP (macOS)

1. **Instale o MAMP:**
   - Download: https://www.mamp.info/
   
2. **Copie os ficheiros para:**
```
/Applications/MAMP/htdocs/projetotomazia/
```

3. **Acesse:**
   - http://localhost:8888/projetotomazia/index.php

---

## 🌐 Instalação no cPanel

### Passo 1: Preparação

1. **Comprima os ficheiros localmente:**
```bash
zip -r projetotomazia.zip * -x "*.git*" -x "vendor/*" -x "node_modules/*"
```

### Passo 2: Upload via cPanel

1. **Acesse o cPanel:**
   - URL: https://seudominio.com:2083 ou https://seudominio.com/cpanel
   - Login com suas credenciais

2. **Navegue até "Gestor de Ficheiros" (File Manager):**
   - Clique em "File Manager"
   - Navegue até `public_html` (ou pasta do seu domínio)

3. **Faça upload do ficheiro ZIP:**
   - Clique em "Upload"
   - Selecione o ficheiro `projetotomazia.zip`
   - Aguarde o upload completar

4. **Extraia os ficheiros:**
   - Volte ao File Manager
   - Clique com botão direito no `projetotomazia.zip`
   - Selecione "Extract"
   - Confirme a extração

### Passo 3: Configurar Permissões

1. **Defina permissões corretas:**
   - Pasta `bd/`: 755
   - Ficheiro `bd/bd_teste.db`: 644
   - Pasta `logs/`: 755
   - Pasta `img/`: 755 (se permitir upload de imagens)

2. **Via File Manager:**
   - Clique com botão direito na pasta/ficheiro
   - Selecione "Change Permissions"
   - Defina as permissões apropriadas

### Passo 4: Configurar Domínio

**Opção A: Domínio Principal**
- Os ficheiros devem estar diretamente em `public_html/`

**Opção B: Subdomínio**
1. Crie um subdomínio em "Domains" → "Subdomains"
2. Coloque os ficheiros na pasta do subdomínio

**Opção C: Subpasta**
- Acesse via: `https://seudominio.com/projetotomazia/`

### Passo 5: Configurar PHP

1. **Selecione a versão do PHP:**
   - Vá em "Select PHP Version"
   - Escolha PHP 7.4 ou superior
   - Ative as extensões necessárias:
     - `sqlite3`
     - `mbstring`
     - `curl`

### Passo 6: Configurar SSL (HTTPS)

1. **Via cPanel:**
   - Vá em "SSL/TLS Status"
   - Clique em "Run AutoSSL" (Let's Encrypt gratuito)
   - Aguarde alguns minutos

2. **Force HTTPS:**
   - Descomente no `.htaccess`:
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### Passo 7: Testar a Instalação

1. **Acesse seu domínio:**
   - Frontend: https://seudominio.com/
   - Admin: https://seudominio.com/login.php

2. **Verifique funcionalidades:**
   - Registo de clientes
   - Visualização do menu
   - Acesso WiFi
   - Painel administrativo

---

## 🗺️ Estrutura de Rotas

### Rotas Públicas (Frontend)

| Rota | Ficheiro | Descrição |
|------|----------|-----------|
| `/` ou `/index.php` | index.php | Página inicial com formulário de registo |
| `/bemvindo.php` | bemvindo.php | Página de boas-vindas (após registo) |
| `/cardapio.php` | cardapio.php | Cardápio digital completo |
| `/fotos.php` | fotos.php | Galeria de fotos |
| `/termos.php` | termos.php | Termos e condições |
| `/erro.php` | erro.php | Página de erro (acesso negado) |

### Rotas Administrativas (Backend)

| Rota | Ficheiro | Descrição |
|------|----------|-----------|
| `/login.php` | login.php | Login do administrador |
| `/admin.php` | admin.php | Painel de administração |
| `/criaradmin.php` | criaradmin.php | Criação de conta admin (usar apenas uma vez) |

### Rotas de Processamento

| Rota | Ficheiro | Descrição |
|------|----------|-----------|
| `/form.php` | form.php | Processa registo de clientes |
| `/register.php` | register.php | Processa criação de admin |

### Componentes de Configuração

| Ficheiro | Descrição |
|----------|-----------|
| `config.php` | Configurações centralizadas |
| `.htaccess` | Configurações do Apache e segurança |
| `.env.example` | Exemplo de variáveis de ambiente |

---

## ⚙️ Configuração

### Configurar Credenciais WiFi

Edite `config.php`:
```php
define('WIFI_REDE', 'Nome-Da-Rede');
define('WIFI_PASSWORD', 'Password-Da-Rede');
```

### Configurar Base de Dados

A base de dados SQLite está em `bd/bd_teste.db` e é criada automaticamente.

**Backup da Base de Dados:**
```bash
# Local
cp bd/bd_teste.db bd/bd_teste_backup_$(date +%Y%m%d).db

# No servidor via cPanel
# Use File Manager para copiar o ficheiro
```

### Criar Conta de Administrador

1. **Acesse (apenas uma vez):**
```
https://seudominio.com/criaradmin.php
```

2. **Preencha os dados:**
   - Username
   - Password (mínimo 8 caracteres)

3. **IMPORTANTE:** Remova o ficheiro após criar o admin:
```bash
# Via SSH
rm criaradmin.php

# Via cPanel File Manager
# Delete o ficheiro criaradmin.php
```

---

## 🔒 Segurança

### Checklist de Segurança

- [ ] **HTTPS Ativado**: Force HTTPS no `.htaccess`
- [ ] **Passwords Fortes**: Use passwords complexas para admin
- [ ] **Remova ficheiros de teste**: Delete `criaradmin.php` após uso
- [ ] **Permissões corretas**: 
  - Ficheiros: 644
  - Pastas: 755
  - Base de dados: 644
- [ ] **Backups regulares**: Configure backup automático no cPanel
- [ ] **Atualizações**: Mantenha PHP atualizado
- [ ] **Logs**: Monitore `logs/` para atividades suspeitas

### Proteções Implementadas

✅ **Proteção CSRF**: Todos os formulários protegidos
✅ **Prevenção XSS**: Inputs sanitizados com `htmlspecialchars()`
✅ **Prevenção SQL Injection**: Queries parametrizadas
✅ **Cookies Seguros**: HTTPOnly, Secure, SameSite
✅ **Password Hashing**: BCrypt para passwords de admin
✅ **Headers de Segurança**: Configurados no `.htaccess`

### Headers de Segurança Implementados

```apache
X-Frame-Options: SAMEORIGIN
X-XSS-Protection: 1; mode=block
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), microphone=(), camera=()
```

### Recomendações Adicionais

1. **Firewall**: Ative o firewall do cPanel (ModSecurity)
2. **Monitorização**: Configure alertas para tentativas de login falhadas
3. **Backup**: Automatize backups diários
4. **Atualizações**: Revise e atualize a aplicação regularmente

---

## 🔧 Resolução de Problemas

### Erro: "Database connection error"

**Solução:**
1. Verifique permissões da pasta `bd/` (755)
2. Verifique permissões do ficheiro `bd/bd_teste.db` (644)
3. Confirme que a extensão SQLite3 está ativa:
```bash
php -m | grep sqlite3
```

### Erro: "CSRF token validation failed"

**Solução:**
1. Limpe cookies do navegador
2. Verifique se sessões PHP estão funcionando:
```bash
# No cPanel, verifique session.save_path
```

### Erro 500 (Internal Server Error)

**Solução:**
1. Verifique logs de erro:
   - cPanel: "Errors" → "Error Log"
   - Local: `logs/error.log`
2. Verifique sintaxe do `.htaccess`
3. Confirme versão do PHP (mínimo 7.4)

### Imagens não carregam

**Solução:**
1. Verifique permissões da pasta `img/` (755)
2. Confirme que os caminhos estão corretos
3. Verifique se os ficheiros existem

### Slideshow não funciona

**Solução:**
1. Confirme que jQuery e Bootstrap JS estão carregando
2. Abra o console do navegador (F12) para verificar erros
3. Verifique se há fotos aprovadas na base de dados

### CSS/JavaScript não carrega

**Solução:**
1. Limpe cache do navegador (Ctrl+F5)
2. Verifique caminhos dos ficheiros
3. Confirme permissões dos ficheiros CSS/JS (644)

---

## 📞 Suporte

Para questões adicionais:
- **Email**: [seu-email@dominio.com]
- **GitHub**: https://github.com/trmsantos/projetotomazia

---

## 📝 Notas Importantes

- **Backup Regular**: Sempre faça backup antes de atualizações
- **Testes**: Teste todas as funcionalidades após instalação
- **Documentação**: Mantenha este guia atualizado
- **Segurança**: Revise configurações de segurança periodicamente

---

**Última atualização**: Fevereiro 2026
**Versão**: 1.0
