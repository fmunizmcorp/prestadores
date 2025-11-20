# 🚨 SOLUÇÃO ERRO 403 FORBIDDEN - HOSTINGER

## Data: 2025-11-04
## Problema: Access to this resource on the server is denied!

---

## ⚡ SOLUÇÃO RÁPIDA (5 PASSOS)

### PASSO 1: Verificar Estrutura de Diretórios

O Hostinger espera que os arquivos estejam em `public_html`. Verifique:

**❌ ERRADO:**
```
public_html/
├── config/
├── database/
├── public/    ← Arquivos dentro de public/
├── src/
└── .htaccess
```

**✅ CORRETO:**
```
public_html/
├── config/
├── database/
├── src/
├── css/       ← Conteúdo de public/ movido para raiz
├── js/
├── uploads/
├── index.php  ← Arquivo index.php na raiz
└── .htaccess
```

**🔧 COMO CORRIGIR:**

1. **Via File Manager do Hostinger:**
   - Mova TODO o conteúdo de `public_html/public/*` para `public_html/`
   - Delete a pasta `public_html/public/` vazia
   - Certifique-se que `index.php` está em `public_html/index.php`

2. **Via FTP:**
   - Conecte no FTP
   - Mova os arquivos de `public/` para a raiz `public_html/`
   - Delete pasta `public/` vazia

---

### PASSO 2: Corrigir Permissões de Arquivos

**Via File Manager do Hostinger:**

1. Clique com botão direito na pasta `public_html`
2. Selecione "Change Permissions"
3. Configure:

```
Diretórios:  755 (rwxr-xr-x)
Arquivos:    644 (rw-r--r--)
```

**Permissões específicas importantes:**
```
public_html/              → 755
public_html/index.php     → 644
public_html/.htaccess     → 644
public_html/config/       → 755
public_html/uploads/      → 755 (ou 777 se der erro de upload)
public_html/src/          → 755
```

**⚠️ NUNCA use 777 em arquivos .php por segurança!**

---

### PASSO 3: Corrigir .htaccess na RAIZ

Como movemos os arquivos, o `.htaccess` na raiz de `public_html` deve ser **SIMPLES**:

**Arquivo: `public_html/.htaccess`**

```apache
# Clinfec Prestadores - Hostinger Configuration
# IMPORTANTE: Este arquivo está na raiz de public_html

# Ativar RewriteEngine
RewriteEngine On

# Remover www (opcional)
RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]
RewriteRule ^(.*)$ https://%1/$1 [R=301,L]

# Redirecionar para HTTPS (opcional, mas recomendado)
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Impedir acesso a pastas sensíveis
RewriteRule ^(config|database|src|docs|vendor)/ - [F,L]

# Impedir acesso a arquivos .md
RewriteRule \.md$ - [F,L]

# Front Controller - Rotear tudo para index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Desabilitar listagem de diretórios
Options -Indexes

# Proteção de arquivos sensíveis
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

# Headers de segurança
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Habilitar compressão Gzip
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>

# Cache para arquivos estáticos
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

**⚠️ ATENÇÃO:** Se você colocou os arquivos na pasta `public/`, o .htaccess deve redirecionar para lá:

```apache
RewriteEngine On
RewriteRule ^$ public/ [L]
RewriteRule (.*) public/$1 [L]
```

---

### PASSO 4: Atualizar config/config.php

**Arquivo: `public_html/config/config.php`**

Como movemos os arquivos, atualize os caminhos:

```php
<?php
// Clinfec Prestadores - Configurações Gerais
// Hostinger Configuration

return [
    // Informações da Aplicação
    'app_name' => 'Clinfec Prestadores',
    'app_version' => '1.0.0',
    
    // URL Base - IMPORTANTE: Configure seu domínio
    'base_url' => 'https://seudominio.com.br',  // ALTERE AQUI!
    
    // Timezone
    'timezone' => 'America/Sao_Paulo',
    
    // Caminhos (agora sem a pasta public/)
    'upload_path' => __DIR__ . '/../uploads/',  // Caminho absoluto
    'upload_url' => '/uploads/',  // URL relativa
    
    // Upload
    'upload_max_size' => 10485760,  // 10MB em bytes
    'allowed_extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif'],
    
    // Paginação
    'items_per_page' => 25,
    'pagination_options' => [10, 25, 50, 100],
    
    // Sessão
    'session_lifetime' => 7200,  // 2 horas em segundos
    
    // Segurança
    'password_min_length' => 6,
    'csrf_token_name' => 'csrf_token',
    
    // Debug (DESABILITAR EM PRODUÇÃO!)
    'debug' => false,  // IMPORTANTE: false em produção!
    'display_errors' => false,  // IMPORTANTE: false em produção!
];
```

---

### PASSO 5: Atualizar public/index.php → index.php

**Arquivo: `public_html/index.php`**

Como o arquivo agora está na raiz, atualize os caminhos:

```php
<?php
/**
 * Clinfec Prestadores - Front Controller
 * Entry Point da Aplicação
 * Hostinger Configuration
 */

// Iniciar sessão
session_start();

// Definir timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurar error reporting (DESABILITAR EM PRODUÇÃO!)
error_reporting(E_ALL);
ini_set('display_errors', 0);  // 0 em produção, 1 para debug

// Definir constantes de caminho
define('ROOT_PATH', __DIR__);  // Raiz é agora public_html/
define('CONFIG_PATH', ROOT_PATH . '/config');
define('SRC_PATH', ROOT_PATH . '/src');

// Carregar configurações
$config = require CONFIG_PATH . '/config.php';
$dbConfig = require CONFIG_PATH . '/database.php';

// Autoloader PSR-4
spl_autoload_register(function ($class) {
    // Namespace base
    $prefix = 'App\\';
    
    // Diretório base
    $base_dir = SRC_PATH . '/';
    
    // Verificar se a classe usa o namespace
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Nome relativo da classe
    $relative_class = substr($class, $len);
    
    // Substituir namespace por diretório
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // Carregar arquivo se existir
    if (file_exists($file)) {
        require $file;
    }
});

// Importar classes necessárias
use App\Helpers\Database;
use App\Helpers\DatabaseMigration;

// Executar migrations automaticamente
try {
    $migration = new DatabaseMigration();
    $migration->runMigrations();
} catch (Exception $e) {
    // Log error (não mostrar em produção)
    if ($config['debug']) {
        die("Erro ao executar migrations: " . $e->getMessage());
    }
}

// Verificar se usuário está logado (exceto páginas públicas)
$publicPages = ['login', 'logout'];
$currentPage = $_GET['page'] ?? 'dashboard';

if (!isset($_SESSION['user_id']) && !in_array($currentPage, $publicPages)) {
    header('Location: ?page=login');
    exit;
}

// Roteamento simples
$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

// Importar controllers
use App\Controllers\AuthController;
use App\Controllers\EmpresaTomadoraController;
use App\Controllers\EmpresaPrestadoraController;
use App\Controllers\ServicoController;
use App\Controllers\ContratoController;

// Roteamento
try {
    switch ($page) {
        case 'login':
            $controller = new AuthController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->login();
            } else {
                $controller->showLoginForm();
            }
            break;
            
        case 'logout':
            $controller = new AuthController();
            $controller->logout();
            break;
            
        case 'dashboard':
            require SRC_PATH . '/views/dashboard/index.php';
            break;
            
        // Empresas Tomadoras
        case 'empresas-tomadoras':
            $controller = new EmpresaTomadoraController();
            
            switch ($action) {
                case 'index':
                    $controller->index();
                    break;
                case 'create':
                    $controller->create();
                    break;
                case 'store':
                    $controller->store();
                    break;
                case 'show':
                    $controller->show($id);
                    break;
                case 'edit':
                    $controller->edit($id);
                    break;
                case 'update':
                    $controller->update($id);
                    break;
                case 'destroy':
                    $controller->destroy($id);
                    break;
                default:
                    $controller->index();
            }
            break;
            
        // Empresas Prestadoras
        case 'empresas-prestadoras':
            $controller = new EmpresaPrestadoraController();
            
            switch ($action) {
                case 'index':
                    $controller->index();
                    break;
                case 'create':
                    $controller->create();
                    break;
                case 'store':
                    $controller->store();
                    break;
                case 'show':
                    $controller->show($id);
                    break;
                case 'edit':
                    $controller->edit($id);
                    break;
                case 'update':
                    $controller->update($id);
                    break;
                case 'destroy':
                    $controller->destroy($id);
                    break;
                default:
                    $controller->index();
            }
            break;
            
        // Serviços
        case 'servicos':
            $controller = new ServicoController();
            
            switch ($action) {
                case 'index':
                    $controller->index();
                    break;
                case 'create':
                    $controller->create();
                    break;
                case 'store':
                    $controller->store();
                    break;
                case 'show':
                    $controller->show($id);
                    break;
                case 'edit':
                    $controller->edit($id);
                    break;
                case 'update':
                    $controller->update($id);
                    break;
                case 'destroy':
                    $controller->destroy($id);
                    break;
                default:
                    $controller->index();
            }
            break;
            
        // Contratos
        case 'contratos':
            $controller = new ContratoController();
            
            switch ($action) {
                case 'index':
                    $controller->index();
                    break;
                case 'create':
                    $controller->create();
                    break;
                case 'store':
                    $controller->store();
                    break;
                case 'show':
                    $controller->show($id);
                    break;
                case 'edit':
                    $controller->edit($id);
                    break;
                case 'update':
                    $controller->update($id);
                    break;
                case 'destroy':
                    $controller->destroy($id);
                    break;
                default:
                    $controller->index();
            }
            break;
            
        default:
            // Página 404
            http_response_code(404);
            echo "Página não encontrada";
    }
    
} catch (Exception $e) {
    // Tratamento de erro
    if ($config['debug']) {
        echo "Erro: " . $e->getMessage();
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    } else {
        echo "Ocorreu um erro. Por favor, tente novamente.";
    }
}
```

---

## 🔍 DIAGNÓSTICO ADICIONAL

Se ainda não funcionar, faça estas verificações:

### 1. Verificar arquivo index.php existe
Via File Manager: `public_html/index.php` deve existir

### 2. Verificar erro no log
No painel Hostinger:
1. Vá em "Arquivos" → "Logs"
2. Abra `error_log`
3. Veja o erro específico

### 3. Testar sem .htaccess
Temporariamente renomeie `.htaccess` para `.htaccess.backup` e tente acessar:
- Se funcionar: problema é no .htaccess
- Se não funcionar: problema é permissões ou estrutura

### 4. Criar arquivo de teste
Crie `public_html/test.php`:

```php
<?php
phpinfo();
?>
```

Acesse: `https://seudominio.com.br/test.php`
- Se mostrar info do PHP: Apache está OK, problema é na aplicação
- Se der 403: problema é permissões

---

## ✅ CHECKLIST FINAL

Antes de testar, confirme:

- [ ] Arquivos estão em `public_html/` (não em `public_html/public/`)
- [ ] `index.php` está em `public_html/index.php`
- [ ] `.htaccess` está em `public_html/.htaccess`
- [ ] Permissões: Diretórios 755, Arquivos 644
- [ ] `config/database.php` tem credenciais corretas do Hostinger
- [ ] `config/config.php` tem 'base_url' correto
- [ ] Pasta `uploads/` tem permissão 755 ou 777

---

## 🚨 SOLUÇÃO DE EMERGÊNCIA

Se nada funcionar, use esta estrutura simplificada:

**1. Mantenha APENAS em public_html/:**
```
public_html/
├── index.php  (arquivo de teste abaixo)
└── .htaccess  (vazio ou comentado)
```

**2. Arquivo index.php de teste:**
```php
<?php
echo "✅ PHP está funcionando!<br>";
echo "PHP Version: " . phpinfo(INFO_GENERAL);
?>
```

**3. Depois que funcionar, adicione os outros arquivos gradualmente**

---

## 📞 SE AINDA TIVER ERRO 403

Me informe:

1. **Estrutura exata** onde colocou os arquivos
2. **Conteúdo do error_log** do Hostinger
3. **Resultado** do test.php
4. **URL** que está tentando acessar

---

## 📋 RESUMO DA SOLUÇÃO MAIS COMUM

**O erro 403 no Hostinger geralmente é:**

1. ✅ **Arquivos na pasta errada** (deixar em `public/`, Hostinger espera raiz)
2. ✅ **Permissões erradas** (diretórios devem ser 755)
3. ✅ **.htaccess com erro** (testar sem ele primeiro)

**AÇÃO IMEDIATA:**
Mova todo conteúdo de `public/` para raiz `public_html/` e teste!

---

**FIM DO GUIA** ✅

Siga os passos na ordem e me avise o resultado!
