# 🚀 Guia de Instalação na Hostinger - Passo a Passo

## 📋 Pré-requisitos

- Acesso ao painel da Hostinger
- Acesso ao phpMyAdmin
- Chaves do Google reCAPTCHA v2

## 🔧 Passo 1: Preparar Arquivos Localmente

### 1.1 Baixar do GitHub

```bash
# Clone o repositório
git clone [URL_DO_SEU_REPOSITORIO]
cd prestadores-clinfec
```

### 1.2 Configurar reCAPTCHA

1. Acesse: https://www.google.com/recaptcha/admin
2. Crie um novo site:
   - Tipo: reCAPTCHA v2
   - Domínio: `clinfec.com.br`
3. Copie a Site Key e Secret Key
4. Edite `config/app.php`:

```php
'recaptcha' => [
    'site_key' => 'COLE_SUA_SITE_KEY_AQUI',
    'secret_key' => 'COLE_SUA_SECRET_KEY_AQUI',
    'enabled' => true
]
```

## 📤 Passo 2: Upload para Hostinger

### 2.1 Via File Manager (Recomendado)

1. Acesse o **File Manager** no painel Hostinger
2. Navegue até `public_html/`
3. Crie a pasta `prestadores/`
4. Entre na pasta `prestadores/`
5. Faça upload de **TODOS os arquivos** do projeto
6. Mantenha a estrutura de pastas:
   ```
   prestadores/
   ├── config/
   ├── database/
   ├── docs/
   ├── logs/
   ├── public/
   └── src/
   ```

### 2.2 Via FTP (Alternativa)

```bash
# Configure seu cliente FTP
Host: ftp.clinfec.com.br
Porta: 21
Usuário: seu_usuario
Senha: sua_senha

# Upload recursivo da pasta prestadores/
```

## 🗄️ Passo 3: Criar Banco de Dados

### 3.1 Acessar phpMyAdmin

1. No painel Hostinger, vá em **Banco de Dados MySQL**
2. Clique em **Gerenciar** no banco `u673902663_prestadores`
3. Abre o **phpMyAdmin**

### 3.2 Executar Migrations

1. Na aba **SQL**, execute o conteúdo completo do arquivo:
   ```
   database/migrations/001_create_usuarios_table.sql
   ```
2. Clique em **Executar**
3. Aguarde confirmação de sucesso ✅

### 3.3 Executar Seeds (Dados Iniciais)

1. Na mesma aba **SQL**, execute:
   ```
   database/seeds/001_seed_initial_data.sql
   ```
2. Clique em **Executar**
3. Confirme que o usuário master foi criado ✅

### 3.4 Verificar Tabelas

No menu lateral, você deve ver estas tabelas:
- ✅ usuarios
- ✅ empresas
- ✅ servicos
- ✅ empresa_servico
- ✅ usuario_empresa
- ✅ empresa_contatos
- ✅ logs_atividades

## 🔐 Passo 4: Configurar Permissões

### 4.1 Via File Manager

1. Selecione a pasta `logs/`
2. Clique em **Permissões**
3. Configure: `755` (rwxr-xr-x)
4. Clique em **Alterar**

### 4.2 Via SSH (se disponível)

```bash
cd public_html/prestadores
chmod 755 logs
chmod 755 public
chmod 644 public/.htaccess
```

## 🌐 Passo 5: Configurar .htaccess Principal

Se necessário, adicione no `.htaccess` da raiz do site:

```apache
# Em public_html/.htaccess
RewriteEngine On
RewriteBase /

# Redireciona /prestadores para /prestadores/public/
RewriteCond %{REQUEST_URI} ^/prestadores/?$
RewriteRule ^prestadores/?$ /prestadores/public/ [L,R=301]
```

## ✅ Passo 6: Testar Instalação

### 6.1 Acessar o Sistema

Abra o navegador e acesse:
```
https://prestadores.clinfec.com.br/public/
```

Ou se configurou o .htaccess:
```
https://prestadores.clinfec.com.br/
```

### 6.2 Fazer Login

```
Email: admin@clinfec.com.br
Senha: Master@2024
```

### 6.3 Verificações

- [ ] Página de login carrega corretamente
- [ ] CSS e JS estão funcionando
- [ ] reCAPTCHA aparece no registro
- [ ] Login com usuário master funciona
- [ ] Dashboard é exibido após login
- [ ] Sidebar está funcionando
- [ ] Logout funciona

## 🔧 Passo 7: Configuração Final

### 7.1 Alterar Senha Master

1. Faça login com o usuário master
2. Vá em **Configurações** (quando disponível)
3. Altere a senha
4. Use uma senha forte!

### 7.2 Configurar SMTP (Opcional)

Para envio de emails de recuperação de senha, edite `config/app.php`:

```php
'mail' => [
    'from_email' => 'noreply@clinfec.com.br',
    'from_name' => 'Sistema Clinfec',
    'smtp_host' => 'smtp.hostinger.com',
    'smtp_port' => 587,
    'smtp_username' => 'seu_email@clinfec.com.br',
    'smtp_password' => 'sua_senha_email',
    'smtp_secure' => 'tls'
]
```

### 7.3 Forçar HTTPS (Recomendado)

No arquivo `public/.htaccess`, descomente:

```apache
# Força HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## 🐛 Solução de Problemas

### Erro: "Página não encontrada"

**Solução**: Verifique se o módulo `mod_rewrite` está ativo no Apache.

### Erro: "Erro ao conectar ao banco de dados"

**Solução**: Verifique as credenciais em `config/database.php`:
```php
'host' => 'localhost',
'database' => 'u673902663_prestadores',
'username' => 'u673902663_admin',
'password' => ';>?I4dtn~2Ga',
```

### CSS/JS não carregam

**Solução**: Verifique se os arquivos estão em `public/css/` e `public/js/`

### reCAPTCHA não aparece

**Solução**: Verifique se as chaves estão corretas em `config/app.php`

### Erro 500

**Solução**: 
1. Verifique os logs em `logs/php_errors_YYYY-MM-DD.log`
2. Verifique permissões das pastas
3. Ative display_errors temporariamente em `public/index.php`

## 📊 Checklist Final

Antes de considerar a instalação completa, verifique:

- [ ] ✅ Todos os arquivos foram enviados
- [ ] ✅ Banco de dados criado e populado
- [ ] ✅ reCAPTCHA configurado
- [ ] ✅ Permissões corretas nas pastas
- [ ] ✅ .htaccess funcionando
- [ ] ✅ Login do usuário master funciona
- [ ] ✅ Dashboard é exibido
- [ ] ✅ CSS e design estão corretos
- [ ] ✅ Senha master foi alterada
- [ ] ✅ HTTPS está funcionando (se configurado)

## 📞 Suporte

### Logs do Sistema

Sempre verifique os logs em caso de problemas:
```
logs/activity_YYYY-MM-DD.log  - Atividades do sistema
logs/php_errors_YYYY-MM-DD.log - Erros PHP
```

### Informações Técnicas

- **PHP**: Mínimo 7.4
- **MySQL**: Mínimo 5.7
- **Apache**: mod_rewrite necessário
- **Extensões PHP**: PDO, PDO_MySQL, mbstring, openssl

## 🎉 Instalação Completa!

Se todos os itens do checklist estão marcados, sua instalação está completa!

Próximos passos:
1. Criar novos usuários
2. Cadastrar empresas
3. Iniciar Sprint 4 (Gestão de Projetos)

---

**Desenvolvido com ❤️ usando Metodologia Scrum**
