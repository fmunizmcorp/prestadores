# 🏗️ ARQUITETURA VPS HOSTINGER - ISOLAMENTO MULTI-TENANT

## 📋 DOCUMENTO DE REFERÊNCIA OBRIGATÓRIA

**Este documento deve ser consultado em TODAS as manutenções do sistema Clinfec Prestadores.**

Data: 16/11/2025  
Versão: 1.0  
Status: ✅ IMPLEMENTADO E VALIDADO

---

## 🎯 OBJETIVO DA ARQUITETURA

Garantir que múltiplos sites hospedados no mesmo servidor VPS sejam **completamente isolados**, de forma que:

- ❌ Site A **não pode** ler arquivos do Site B
- ❌ Site A **não pode** acessar banco de dados do Site B
- ❌ Site A **não pode** afetar performance do Site B
- ❌ Invasão no Site A **não compromete** Site B
- ✅ Cada site opera como se estivesse em servidor dedicado

---

## 🛡️ CAMADAS DE ISOLAMENTO

### 1️⃣ ISOLAMENTO DE PROCESSO (PHP-FPM Pools)

Cada site possui seu **próprio processo PHP** independente.

**Configuração:**
```ini
# /etc/php/8.3/fpm/pool.d/prestadores.conf
[prestadores]
user = prestadores
group = www-data
listen = /var/run/php/php8.3-fpm-prestadores.sock
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 10
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3

php_admin_value[memory_limit] = 256M
php_admin_value[upload_max_filesize] = 25M
php_admin_value[post_max_size] = 25M
php_admin_value[max_execution_time] = 60
php_admin_value[open_basedir] = /opt/webserver/sites/prestadores:/tmp
```

**Benefícios:**
- ✅ Processo separado por site
- ✅ Crash isolado (loop infinito não afeta outros sites)
- ✅ CPU/RAM isolados
- ✅ Restart independente

---

### 2️⃣ ISOLAMENTO DE USUÁRIO (Linux System Users)

Cada site pertence a um **usuário Linux diferente**.

**Implementação:**
```bash
# Usuário criado automaticamente pelo create-site.sh:
useradd -r -s /bin/false -d /opt/webserver/sites/prestadores prestadores

# Permissões:
drwxr-xr-x prestadores www-data /opt/webserver/sites/prestadores/
```

**Benefícios:**
- ✅ Leitura bloqueada entre sites
- ✅ Escrita bloqueada entre sites
- ✅ Proteção no nível do kernel Linux
- ✅ Auditoria de ações por usuário

---

### 3️⃣ ISOLAMENTO DE FILESYSTEM (open_basedir)

PHP só pode acessar diretórios **explicitamente permitidos**.

**Configuração:**
```ini
php_admin_value[open_basedir] = /opt/webserver/sites/prestadores:/tmp
```

**Benefícios:**
- ✅ Bloqueio de include/require fora do escopo
- ✅ Bloqueio de file_get_contents em outros diretórios
- ✅ Proteção contra path traversal
- ✅ Proteção adicional mesmo com falha de permissões

---

### 4️⃣ ISOLAMENTO DE BANCO DE DADOS

Cada site possui **banco de dados e credenciais exclusivas**.

**Implementação:**
```sql
CREATE DATABASE db_prestadores CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'user_prestadores'@'localhost' IDENTIFIED BY 'SENHA_UNICA';
GRANT ALL PRIVILEGES ON db_prestadores.* TO 'user_prestadores'@'localhost';
FLUSH PRIVILEGES;
```

**Benefícios:**
- ✅ Acesso negado entre BDs
- ✅ Senhas únicas por site
- ✅ Backup individual
- ✅ Quota individual por BD

---

## 📁 ESTRUTURA DE DIRETÓRIOS PADRÃO

### ⚠️ ESTRUTURA OBRIGATÓRIA

**ATENÇÃO:** Esta estrutura NÃO PODE SER MODIFICADA. Está alinhada com a arquitetura multi-tenant do servidor.

```
/opt/webserver/sites/prestadores/
├── public_html/           ⚠️ OBRIGATÓRIO: Document root (não pode ser "public")
│   ├── index.php         # Front controller
│   ├── .htaccess         # Regras Apache (se houver)
│   ├── css/              # Estilos
│   ├── js/               # JavaScript
│   └── images/           # Imagens
├── src/
│   ├── Controllers/      # PascalCase (não lowercase!)
│   ├── Models/
│   ├── Views/
│   ├── Helpers/
│   └── Database.php
├── config/
│   ├── database.php      # Credenciais do banco
│   ├── config.php        # Configurações gerais
│   └── version.php
├── database/
│   ├── install.sql       # Schema completo
│   └── migrations/       # Migrations SQL
├── logs/                 # Logs NGINX/PHP (permissão 775)
├── cache/                # Cache da aplicação (permissão 775)
├── temp/                 # Arquivos temporários (permissão 775)
├── uploads/              # Uploads de usuários (permissão 775)
└── CREDENTIALS.txt       # Credenciais geradas pelo script
```

### 🚫 ERROS COMUNS A EVITAR

1. ❌ Usar `/public/` como document root → Deve ser `/public_html/`
2. ❌ Usar `controllers/` (lowercase) → Deve ser `Controllers/` (PascalCase)
3. ❌ Usar caminhos relativos → Sempre usar constants (ROOT_PATH, SRC_PATH)
4. ❌ Usar redirecionamentos absolutos (`/dashboard`) → Deve usar query strings (`/?page=dashboard`)

---

## ⚙️ CONFIGURAÇÃO NGINX

### Template Padrão

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name prestadores.clinfec.com.br www.prestadores.clinfec.com.br 72.61.53.222;
    
    # ⚠️ IMPORTANTE: Deve apontar para public_html/
    root /opt/webserver/sites/prestadores/public_html;
    index index.php index.html;
    
    access_log /opt/webserver/sites/prestadores/logs/access.log;
    error_log /opt/webserver/sites/prestadores/logs/error.log;
    
    # Upload limits
    client_max_body_size 50M;
    client_body_timeout 120s;
    
    # Front controller pattern
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP processing via socket dedicado
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm-prestadores.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }
    
    # Security
    location ~ /\.(ht|git|env) {
        deny all;
    }
    
    # Static file caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

---

## 🔧 CONFIGURAÇÃO PHP (index.php)

### Template de index.php Correto

```php
<?php
/**
 * Clinfec Prestadores - Front Controller
 * Alinhado com arquitetura VPS Hostinger
 */

session_start();
date_default_timezone_set('America/Sao_Paulo');

// ⚠️ PATHS OBRIGATÓRIOS (não modificar)
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', ROOT_PATH . '/config');

// ⚠️ BASE_URL DINÂMICO (detecta HTTP/HTTPS automaticamente)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? '72.61.53.222';
define('BASE_URL', $protocol . '://' . $host);

// CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Autoloader PSR-4 (mantém PascalCase!)
spl_autoload_register(function($class) {
    $class = str_replace('App\\', '', $class);
    $file = SRC_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});

// Carregar configurações
$config = require CONFIG_PATH . '/config.php';
$dbConfig = require CONFIG_PATH . '/database.php';

// Verificar instalação
try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4";
    $testPdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $stmt = $testPdo->query("SHOW TABLES LIKE 'database_version'");
    if ($stmt->rowCount() == 0) {
        if (basename($_SERVER['PHP_SELF']) !== 'install.php') {
            header('Location: /install.php');
            exit;
        }
    }
} catch (PDOException $e) {
    die('ERRO: Não foi possível conectar ao banco de dados.');
}

require_once SRC_PATH . '/Database.php';

// ⚠️ ROTEAMENTO VIA QUERY STRINGS (não modificar!)
$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

// Mapa de rotas
$routes = [
    'dashboard' => 'DashboardController',
    'auth' => 'AuthController',
    'login' => 'AuthController@showLoginForm',
    'logout' => 'AuthController@logout',
    // ... outras rotas
];

// Processar rotas
if (isset($routes[$page]) && strpos($routes[$page], '@') !== false) {
    list($controllerName, $methodName) = explode('@', $routes[$page]);
    $controllerName = 'App\\Controllers\\' . $controllerName;
    $action = $methodName;
} else {
    if (!isset($routes[$page])) {
        $page = 'dashboard';
    }
    $controllerName = 'App\\Controllers\\' . $routes[$page];
}

// Instanciar controller
if (!class_exists($controllerName)) {
    die("ERRO: Controller $controllerName não encontrado!");
}

$controller = new $controllerName();

if (!method_exists($controller, $action)) {
    die("ERRO: Ação $action não encontrada em $controllerName!");
}

$controller->$action($id);
```

---

## 🔄 SISTEMA DE REDIRECIONAMENTO

### ⚠️ REGRA CRÍTICA: SEMPRE USAR QUERY STRINGS

**ERRADO ❌:**
```php
header('Location: /dashboard');
header('Location: /login');
header('Location: ' . BASE_URL . '/contratos');
```

**CORRETO ✅:**
```php
header('Location: /?page=dashboard');
header('Location: /?page=auth&action=showLoginForm');
header('Location: /?page=contratos&action=index');
```

### BaseController::redirect()

```php
protected function redirect($route)
{
    $baseUrl = defined('BASE_URL') ? BASE_URL : '';
    
    // Processar rotas especiais
    if ($route === 'login') {
        $url = $baseUrl . '/?page=auth&action=showLoginForm';
    } elseif ($route === 'logout') {
        $url = $baseUrl . '/?page=auth&action=logout';
    } elseif ($route === 'dashboard') {
        $url = $baseUrl . '/?page=dashboard';
    } elseif (strpos($route, '@') !== false) {
        list($page, $action) = explode('@', $route);
        $url = $baseUrl . '/?page=' . urlencode($page) . '&action=' . urlencode($action);
    } else {
        $url = $baseUrl . '/?page=' . urlencode($route);
    }
    
    header('Location: ' . $url);
    exit;
}
```

---

## 🔐 PERMISSÕES DE ARQUIVOS

### Regras Obrigatórias

```bash
# Dono: Usuário do site
# Grupo: www-data (para NGINX acessar)

# Diretórios:
find /opt/webserver/sites/prestadores -type d -exec chmod 755 {} \;

# Arquivos:
find /opt/webserver/sites/prestadores -type f -exec chmod 644 {} \;

# Diretórios com escrita:
chmod 775 /opt/webserver/sites/prestadores/logs
chmod 775 /opt/webserver/sites/prestadores/cache
chmod 775 /opt/webserver/sites/prestadores/temp
chmod 775 /opt/webserver/sites/prestadores/uploads

# Ownership:
chown -R prestadores:www-data /opt/webserver/sites/prestadores
```

### ⚠️ NUNCA fazer:
- ❌ `chmod 777` (exposição de segurança!)
- ❌ `chown www-data:www-data` (quebra isolamento!)
- ❌ Dar permissões 777 para "resolver" erros de escrita

---

## 🧪 TESTES DE VALIDAÇÃO

### Checklist de Testes Após Qualquer Alteração

```bash
# 1. Testar página raiz (deve redirecionar para login)
curl -I http://72.61.53.222/
# Esperado: HTTP 302

# 2. Testar página de login
curl -I "http://72.61.53.222/?page=auth&action=showLoginForm"
# Esperado: HTTP 200

# 3. Testar PHP
curl -s http://72.61.53.222/info.php | grep "PHP Version"
# Esperado: PHP Version 8.3.6

# 4. Verificar permissões
ls -la /opt/webserver/sites/prestadores/ | head -5
# Esperado: drwxr-xr-x prestadores www-data

# 5. Verificar PHP-FPM pool
ps aux | grep php-fpm | grep prestadores
# Esperado: Processos rodando como usuário "prestadores"

# 6. Verificar NGINX config
nginx -t
# Esperado: syntax is ok

# 7. Verificar logs
tail -20 /opt/webserver/sites/prestadores/logs/error.log
# Esperado: Sem erros críticos recentes
```

---

## 🚨 TROUBLESHOOTING

### Erro: ERR_TOO_MANY_REDIRECTS

**Causa:** Redirecionamentos absolutos sem query strings  
**Solução:** Corrigir BaseController e AuthController para usar query strings

### Erro: Permission Denied (13)

**Causa:** Permissões incorretas ou parent directory sem acesso  
**Solução:**
```bash
chmod 751 /opt/webserver/sites
chmod 755 /opt/webserver/sites/prestadores
chmod 755 /opt/webserver/sites/prestadores/public_html
chown -R prestadores:www-data /opt/webserver/sites/prestadores
```

### Erro: Controller não encontrado

**Causa:** Autoloader convertendo PascalCase para lowercase  
**Solução:** Remover conversão no autoloader, manter PascalCase

### Erro: open_basedir restriction

**Causa:** Tentativa de acessar arquivo fora do permitido  
**Solução:** Verificar se está tentando acessar path fora de `/opt/webserver/sites/prestadores`

---

## 📚 DOCUMENTAÇÃO DE REFERÊNCIA

### Arquivos Importantes

1. `/opt/webserver/sites/prestadores/CREDENTIALS.txt` - Credenciais do banco
2. `/opt/webserver/sites/prestadores/config/version.php` - Versão do sistema
3. `/opt/webserver/sites/prestadores/database/install.sql` - Schema completo
4. `/etc/nginx/sites-available/prestadores` - Config NGINX
5. `/etc/php/8.3/fpm/pool.d/prestadores.conf` - Config PHP-FPM

### Comandos Úteis

```bash
# Reiniciar serviços
systemctl restart nginx
systemctl restart php8.3-fpm
systemctl restart mysql

# Ver logs em tempo real
tail -f /opt/webserver/sites/prestadores/logs/error.log
tail -f /var/log/nginx/error.log

# Testar configurações
nginx -t
php-fpm8.3 -t

# Verificar processos
ps aux | grep php-fpm | grep prestadores
ps aux | grep nginx

# Backup do banco
mysqldump -u user_prestadores -p db_prestadores > backup_$(date +%Y%m%d).sql

# Acessar banco
mysql -u user_prestadores -p db_prestadores
```

---

## ✅ CHECKLIST DE IMPLEMENTAÇÃO

Ao fazer qualquer manutenção, verificar:

- [ ] Document root é `/public_html/` (não `/public/`)
- [ ] Diretórios são PascalCase (`Controllers/`, não `controllers/`)
- [ ] Redirecionamentos usam query strings (`/?page=...`)
- [ ] BASE_URL é definido dinamicamente no index.php
- [ ] open_basedir está configurado no PHP-FPM pool
- [ ] Permissões são 755 para diretórios, 644 para arquivos
- [ ] Owner é `prestadores:www-data`
- [ ] Diretórios com escrita tem permissão 775
- [ ] NGINX aponta para `/public_html/`
- [ ] PHP-FPM usa socket Unix dedicado
- [ ] Logs estão sendo gerados corretamente

---

## 🎯 CONCLUSÃO

Esta arquitetura garante **7 camadas de isolamento** entre sites no mesmo servidor VPS:

1. ✅ Processos PHP separados
2. ✅ Usuários Linux separados
3. ✅ Filesystem restrito
4. ✅ Bancos de dados isolados
5. ✅ Cache separado
6. ✅ Logs individuais
7. ✅ Recursos limitados

**IMPORTANTE:** Qualquer desvio desta arquitetura pode comprometer a segurança e estabilidade de TODOS os sites hospedados no servidor.

---

**Última atualização:** 16/11/2025  
**Status:** ✅ VALIDADO EM PRODUÇÃO  
**Responsável:** Sistema de Migração Sprint 63
