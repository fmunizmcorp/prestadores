# 🎯 CORREÇÃO PARA INSTALAÇÃO EM SUBPASTA

## Situação: Arquivos em `public_html/prestadores/`

---

## ⚡ SOLUÇÃO RÁPIDA (3 Passos)

### PASSO 1: ESTRUTURA CORRETA NA SUBPASTA

Se seus arquivos estão em `public_html/prestadores/`, a estrutura deve ser:

```
public_html/
└── prestadores/              ← Sua subpasta
    ├── index.php            ← INDEX.PHP AQUI (não em prestadores/public/)
    ├── .htaccess            ← .HTACCESS AQUI
    ├── config/
    ├── database/
    ├── src/
    ├── css/
    ├── js/
    ├── uploads/
    └── docs/
```

**⚠️ IMPORTANTE:** Se você tem `public_html/prestadores/public/`, mova tudo de `public/` para `prestadores/`

---

### PASSO 2: .htaccess CORRETO PARA SUBPASTA

Edite `public_html/prestadores/.htaccess` e substitua por:

```apache
# Clinfec Prestadores - Hostinger Subpasta
# Este arquivo está em public_html/prestadores/.htaccess

RewriteEngine On
RewriteBase /prestadores/

# HTTPS (recomendado)
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Proteger pastas sensíveis
RewriteRule ^(config|database|src|docs|vendor)/ - [F,L]

# Proteger arquivos .md
RewriteRule \.md$ - [F,L]

# Front Controller
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Desabilitar listagem
Options -Indexes

# Proteção de arquivos ocultos
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

# Compressão Gzip
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>

# Cache
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

**🔴 DIFERENÇA PRINCIPAL:** 
```apache
RewriteBase /prestadores/  ← IMPORTANTE PARA SUBPASTA!
```

---

### PASSO 3: ATUALIZAR config/config.php

Edite `public_html/prestadores/config/config.php`:

```php
<?php
// Clinfec Prestadores - Configurações Hostinger Subpasta

return [
    // Informações da Aplicação
    'app_name' => 'Clinfec Prestadores',
    'app_version' => '1.0.0',
    
    // URL Base - IMPORTANTE: Adicionar /prestadores
    'base_url' => 'https://seudominio.com.br/prestadores',  // ← COM /prestadores !
    
    // Timezone
    'timezone' => 'America/Sao_Paulo',
    
    // Caminhos
    'upload_path' => __DIR__ . '/../uploads/',
    'upload_url' => '/prestadores/uploads/',  // ← COM /prestadores !
    
    // Upload
    'upload_max_size' => 10485760,  // 10MB
    'allowed_extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif'],
    
    // Paginação
    'items_per_page' => 25,
    'pagination_options' => [10, 25, 50, 100],
    
    // Sessão
    'session_lifetime' => 7200,  // 2 horas
    
    // Segurança
    'password_min_length' => 6,
    'csrf_token_name' => 'csrf_token',
    
    // Debug (DESABILITAR EM PRODUÇÃO!)
    'debug' => false,
    'display_errors' => false,
];
```

---

## 🧪 TESTE AGORA

### 1. Criar arquivo de teste:

Crie `public_html/prestadores/test.php`:

```php
<?php
echo "✅ PHP está funcionando!<br>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Diretório atual: " . __DIR__ . "<br>";
echo "URL acessada: " . $_SERVER['REQUEST_URI'];
?>
```

### 2. Acessar:

```
https://seudominio.com.br/prestadores/test.php
```

**Resultado esperado:**
- Deve mostrar as informações do PHP
- Se funcionar, PHP está OK!

---

## 🔍 CHECKLIST PARA SUBPASTA

Verifique:

```
☑ [ ] Estrutura: public_html/prestadores/index.php
☑ [ ] Estrutura: public_html/prestadores/.htaccess
☑ [ ] Não tem: public_html/prestadores/public/
☑ [ ] .htaccess tem: RewriteBase /prestadores/
☑ [ ] config.php tem: 'base_url' => '.../prestadores'
☑ [ ] config.php tem: 'upload_url' => '/prestadores/uploads/'
☑ [ ] Permissões: prestadores/ = 755
☑ [ ] Permissões: index.php = 644
☑ [ ] config/database.php tem credenciais corretas
```

---

## 📁 ESTRUTURA FINAL CORRETA

```
public_html/
└── prestadores/                          ← Permissão 755
    ├── index.php                        ← Permissão 644 (PRINCIPAL!)
    ├── .htaccess                        ← Permissão 644 (com RewriteBase /prestadores/)
    ├── test.php                         ← Para testar (delete depois)
    │
    ├── config/                          ← Permissão 755
    │   ├── config.php                  ← base_url com /prestadores
    │   └── database.php                ← Credenciais MySQL
    │
    ├── database/                        ← Permissão 755
    │   └── migrations/
    │       ├── 001_migration.sql
    │       └── 002_empresas_contratos.sql
    │
    ├── src/                             ← Permissão 755
    │   ├── controllers/
    │   ├── models/
    │   ├── views/
    │   └── helpers/
    │
    ├── css/                             ← Permissão 755
    ├── js/                              ← Permissão 755
    ├── uploads/                         ← Permissão 755 ou 777
    ├── docs/
    └── *.md
```

---

## 🔧 PERMISSÕES CORRETAS

Via File Manager do Hostinger:

### 1. Pasta prestadores:
- Clique direito → Permissions
- Configure: **755** (rwxr-xr-x)
- Marque "Apply to subdirectories"

### 2. Arquivos importantes:
```
prestadores/index.php     → 644
prestadores/.htaccess     → 644
prestadores/config/       → 755
prestadores/uploads/      → 755 (ou 777 se necessário)
```

---

## 🌐 URLS DE ACESSO

Após configurar corretamente:

```
Teste PHP:
https://seudominio.com.br/prestadores/test.php

Sistema (login):
https://seudominio.com.br/prestadores/

Dashboard:
https://seudominio.com.br/prestadores/?page=dashboard

Empresas Tomadoras:
https://seudominio.com.br/prestadores/?page=empresas-tomadoras
```

---

## ⚠️ ERROS COMUNS EM SUBPASTA

### 1. ❌ Erro: CSS/JS não carregam
**Causa:** `base_url` sem `/prestadores`
**Solução:** Adicionar `/prestadores` no config.php

### 2. ❌ Erro: 404 ao clicar nos links
**Causa:** `.htaccess` sem `RewriteBase /prestadores/`
**Solução:** Adicionar `RewriteBase /prestadores/` no .htaccess

### 3. ❌ Erro: Upload não funciona
**Causa:** `upload_url` sem `/prestadores`
**Solução:** Usar `/prestadores/uploads/` no config.php

### 4. ❌ Erro: Ainda dá 403
**Causa:** Arquivos dentro de `prestadores/public/`
**Solução:** Mover tudo de `public/` para `prestadores/`

---

## 🚀 PASSO A PASSO COMPLETO

### 1. Verificar estrutura:
```bash
public_html/prestadores/index.php  ← DEVE EXISTIR
public_html/prestadores/public/    ← NÃO DEVE EXISTIR
```

### 2. Se tem `public/` dentro:
- Mova TODO conteúdo de `prestadores/public/` para `prestadores/`
- Delete pasta `prestadores/public/` vazia

### 3. Editar .htaccess:
- Abrir `prestadores/.htaccess`
- Adicionar linha: `RewriteBase /prestadores/`
- Salvar

### 4. Editar config.php:
- Abrir `prestadores/config/config.php`
- Mudar `base_url` para incluir `/prestadores`
- Mudar `upload_url` para `/prestadores/uploads/`
- Salvar

### 5. Verificar permissões:
- Pasta `prestadores/` = 755
- Arquivos `.php` = 644

### 6. Testar:
- Acessar: `https://seudominio.com.br/prestadores/test.php`
- Se funcionar, acessar: `https://seudominio.com.br/prestadores/`

---

## 🔄 ALTERNATIVA: MOVER PARA RAIZ

Se preferir NÃO usar subpasta, mova tudo para raiz:

```bash
# Estrutura atual (com subpasta):
public_html/prestadores/*

# Mover para raiz:
public_html/*
```

**Vantagem:** URLs mais limpas (`seudominio.com.br` em vez de `seudominio.com.br/prestadores`)

**Como fazer:**
1. Mova TODO conteúdo de `public_html/prestadores/` para `public_html/`
2. Delete pasta `prestadores/` vazia
3. Use .htaccess SEM `RewriteBase /prestadores/`
4. Use config.php SEM `/prestadores` nas URLs

---

## 📞 SE AINDA DER ERRO 403

Me informe:

1. **Estrutura exata:**
   - Tem pasta `prestadores/public/`? SIM ou NÃO
   - index.php está em qual caminho exato?

2. **Conteúdo do .htaccess atual:**
   - Tem `RewriteBase /prestadores/`? SIM ou NÃO
   - Cole as primeiras 10 linhas

3. **Conteúdo do config.php:**
   - Qual o valor de `base_url`?
   - Qual o valor de `upload_url`?

4. **Error log:**
   - Vá em Hostinger → Arquivos → Logs → error_log
   - Cole as últimas 5 linhas

5. **Teste:**
   - O que acontece ao acessar `prestadores/test.php`?

---

## ✅ RESUMO RÁPIDO

Para subpasta funcionar:

1. ✅ Arquivos em: `public_html/prestadores/` (não em `prestadores/public/`)
2. ✅ .htaccess com: `RewriteBase /prestadores/`
3. ✅ config.php com: `base_url` incluindo `/prestadores`
4. ✅ Permissões: 755 para pastas, 644 para arquivos
5. ✅ Acessar: `https://seudominio.com.br/prestadores/`

---

**FIM DO GUIA** ✅

Faça essas correções e me avise o resultado!
