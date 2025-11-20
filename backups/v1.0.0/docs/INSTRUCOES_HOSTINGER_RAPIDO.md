# ⚡ INSTRUÇÕES RÁPIDAS - CORRIGIR ERRO 403 NO HOSTINGER

## 🚨 PROBLEMA: Erro 403 Forbidden

## ✅ SOLUÇÃO EM 3 PASSOS (5 MINUTOS)

---

## PASSO 1: REORGANIZAR ARQUIVOS (MAIS IMPORTANTE!)

### O que está acontecendo:
Você provavelmente colocou os arquivos assim:
```
public_html/
├── config/
├── database/
├── public/        ← AQUI ESTÁ O PROBLEMA!
│   ├── index.php
│   ├── css/
│   └── js/
└── src/
```

### Como deve estar:
```
public_html/
├── config/
├── database/
├── src/
├── index.php      ← INDEX.PHP NA RAIZ!
├── css/
├── js/
├── uploads/
└── .htaccess
```

### COMO CORRIGIR (File Manager Hostinger):

1. **Abra File Manager** no painel Hostinger
2. **Entre na pasta `public_html`**
3. **Se você tem uma pasta `public/` dentro:**
   - Entre na pasta `public/`
   - **SELECIONE TUDO** (Ctrl+A)
   - Clique em **"Move"**
   - Volte para `public_html/` (pasta pai)
   - Clique em **"Move Here"**
   - **Delete** a pasta `public/` vazia
4. **Verifique:** O arquivo `index.php` deve estar em `public_html/index.php`

---

## PASSO 2: CORRIGIR PERMISSÕES

### No File Manager do Hostinger:

1. **Clique com botão direito** na pasta `public_html`
2. Selecione **"Permissions"** ou **"Change Permissions"**
3. Configure:
   - ☑ **Owner: Read, Write, Execute** (7)
   - ☑ **Group: Read, Execute** (5)
   - ☑ **Public: Read, Execute** (5)
   - Resultado: **755**
4. Marque **"Apply to subdirectories"**
5. Clique em **"Save"**

### Permissões específicas importantes:
```
public_html/              → 755
public_html/index.php     → 644
public_html/.htaccess     → 644
public_html/uploads/      → 755 (ou 777 se necessário)
```

**Como configurar 644 para arquivos:**
1. Clique com botão direito no arquivo
2. Permissions
3. Owner: Read+Write (6), Group: Read (4), Public: Read (4)

---

## PASSO 3: USAR .HTACCESS CORRETO

### Substitua o conteúdo de `public_html/.htaccess` por:

```apache
# Clinfec Prestadores - Hostinger
RewriteEngine On
RewriteBase /

# HTTPS (recomendado)
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Proteger pastas
RewriteRule ^(config|database|src|docs)/ - [F,L]

# Front Controller
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Desabilitar listagem
Options -Indexes
```

**Como editar no File Manager:**
1. Clique em `.htaccess`
2. Clique em **"Edit"**
3. **Apague tudo**
4. **Cole** o conteúdo acima
5. Clique em **"Save"**

---

## ⚡ TESTE IMEDIATO

### Criar arquivo de teste `public_html/test.php`:

```php
<?php
echo "✅ PHP está funcionando!<br>";
echo "PHP Version: " . PHP_VERSION;
?>
```

**Acesse:** `https://seudominio.com.br/test.php`

- ✅ **Se funcionar:** PHP OK, continue
- ❌ **Se der 403:** Problema é permissões (volte ao Passo 2)

---

## 🔍 CHECKLIST FINAL

Verifique ANTES de testar:

```
✅ [ ] index.php está em public_html/index.php (não em public_html/public/)
✅ [ ] .htaccess está em public_html/.htaccess
✅ [ ] Pasta public_html tem permissão 755
✅ [ ] Arquivo index.php tem permissão 644
✅ [ ] Pasta uploads tem permissão 755
✅ [ ] config/database.php tem credenciais do Hostinger corretas
```

---

## 🎯 ESTRUTURA FINAL CORRETA

```
public_html/                      ← Permissão 755
├── config/                       ← Permissão 755
│   ├── database.php             ← Permissão 644 (CONFIGURAR CREDENCIAIS!)
│   └── config.php               ← Permissão 644
├── database/                     ← Permissão 755
│   └── migrations/              ← Permissão 755
│       ├── 001_migration.sql    ← Permissão 644
│       └── 002_empresas_contratos.sql  ← Permissão 644
├── src/                          ← Permissão 755
│   ├── controllers/             ← Permissão 755
│   ├── models/                  ← Permissão 755
│   ├── views/                   ← Permissão 755
│   └── helpers/                 ← Permissão 755
├── css/                          ← Permissão 755
├── js/                           ← Permissão 755
├── uploads/                      ← Permissão 755 ou 777
├── docs/                         ← Permissão 755
├── index.php                     ← Permissão 644 (PRINCIPAL!)
├── .htaccess                     ← Permissão 644 (IMPORTANTE!)
└── *.md                          ← Permissão 644
```

---

## 🚨 SE AINDA DER ERRO 403

### 1. Teste sem .htaccess:
Renomeie `.htaccess` para `.htaccess.backup` e teste

### 2. Veja o log de erro:
No painel Hostinger → Arquivos → Logs → error_log

### 3. Verifique PHP:
Crie `test.php` (conforme mostrado acima)

### 4. Me informe:
- URL que está acessando
- Conteúdo do error_log
- Resultado do test.php
- Print da estrutura de pastas

---

## ⚡ SOLUÇÃO DE EMERGÊNCIA

Se **NADA** funcionar, faça isso:

### 1. Backup dos arquivos atuais
Baixe tudo via FTP

### 2. Limpe public_html
Delete TUDO

### 3. Suba APENAS:
```
public_html/
└── test.php
```

**Conteúdo test.php:**
```php
<?php phpinfo(); ?>
```

### 4. Teste:
Se `test.php` funcionar, o problema era estrutura/permissões.
Suba os arquivos corretos gradualmente.

---

## 📞 SUPORTE

Se o erro persistir, me envie:

1. **Print da estrutura** de `public_html/`
2. **Conteúdo do error_log**
3. **URL** do site
4. **Resultado** ao acessar test.php

---

## ✅ APÓS FUNCIONAR

1. **Delete test.php**
2. **Configure config/database.php** com suas credenciais
3. **Configure config/config.php** com sua URL
4. **Acesse:** https://seudominio.com.br
5. **Login:** admin / admin123 (TROQUE DEPOIS!)

---

**BOA SORTE! 🚀**

O problema mais comum é arquivos dentro de `public/` - mova para `public_html/` e funcionará!
