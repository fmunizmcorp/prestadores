# 🚀 Script de Cadastro Inicial - Instruções

## 📋 O que é este script?

O arquivo `cadastroinicial.php` é um script utilitário que cria o **usuário administrador inicial** do sistema Clinfec Prestadores.

## 🎯 Quando usar?

Use este script **apenas UMA VEZ** após:
- ✅ Deploy inicial do sistema
- ✅ Execução das migrations (tabelas criadas)
- ✅ Sistema instalado e funcionando

## 📝 Credenciais que serão criadas:

```
Nome: Flávio Administrador
Email: flavio@clinfec.com.br
Senha: admin123
Perfil: MASTER (acesso total)
```

## 🔧 Como usar:

### Opção 1: Via Navegador (Recomendado)

1. **Acesse a URL:**
   ```
   https://prestadores.clinfec.com.br/cadastroinicial.php
   ```

2. **O script irá:**
   - Conectar ao banco de dados
   - Verificar se o usuário já existe
   - Criar o usuário se não existir
   - Exibir as credenciais na tela

3. **Após executar:**
   - Anote as credenciais
   - Acesse o login: `https://prestadores.clinfec.com.br/?page=login`
   - Faça login com email e senha exibidos
   - **DELETE o arquivo `cadastroinicial.php` imediatamente!**

### Opção 2: Via SSH/Terminal

```bash
# 1. Acesse o diretório do projeto
cd /home/u673902663/domains/clinfec.com.br/public_html/prestadores

# 2. Execute o script via PHP CLI (se disponível)
php cadastroinicial.php

# 3. DELETE o arquivo após uso
rm cadastroinicial.php
```

### Opção 3: Via FTP

1. **Faça upload do arquivo** `cadastroinicial.php` para:
   ```
   /public_html/prestadores/
   ```

2. **Acesse pelo navegador:**
   ```
   https://prestadores.clinfec.com.br/cadastroinicial.php
   ```

3. **Delete o arquivo** via FTP após uso

## ⚠️ IMPORTANTE - SEGURANÇA

### 🔴 SEMPRE DELETE ESTE ARQUIVO APÓS O USO!

O arquivo `cadastroinicial.php` contém:
- ❌ Credenciais do banco de dados
- ❌ Informações sensíveis
- ❌ Pode ser usado para criar usuários não autorizados

### Como deletar:

**Via SSH:**
```bash
rm /home/u673902663/domains/clinfec.com.br/public_html/prestadores/cadastroinicial.php
```

**Via FTP:**
- Conecte ao servidor
- Navegue até `/public_html/prestadores/`
- Delete o arquivo `cadastroinicial.php`

**Via Hostinger File Manager:**
- Acesse o File Manager do Hostinger
- Navegue até o diretório
- Selecione o arquivo e delete

## 🔍 Verificações antes de executar:

### 1. Banco de dados criado
```sql
-- Deve existir o banco:
u673902663_prestadores
```

### 2. Tabela usuarios existe
```sql
-- Execute no phpMyAdmin ou MySQL CLI:
SHOW TABLES LIKE 'usuarios';
```

### 3. Estrutura da tabela correta
```sql
-- Campos necessários:
DESCRIBE usuarios;

-- Deve ter:
- id (INT AUTO_INCREMENT)
- nome (VARCHAR)
- email (VARCHAR UNIQUE)
- senha (VARCHAR)
- perfil (ENUM)
- ativo (BOOLEAN)
- created_at (TIMESTAMP)
```

## 🐛 Solução de Problemas

### Erro: "Não foi possível conectar ao banco de dados"

**Causa:** Credenciais incorretas ou banco não existe

**Solução:**
1. Verifique no painel Hostinger se o banco existe
2. Confirme as credenciais em `config/database.php`
3. Teste a conexão com `test.php`

### Erro: "Table 'usuarios' doesn't exist"

**Causa:** Migrations não foram executadas

**Solução:**
1. Acesse qualquer página do sistema (isso executa migrations automaticamente)
2. Ou execute manualmente o SQL em `database/migrations/`

### Aviso: "Usuário Já Existe"

**Causa:** Já existe um usuário com este email

**Solução:**
- Se você esqueceu a senha, redefina via banco de dados:
```sql
UPDATE usuarios 
SET senha = '$2y$10$YourHashedPasswordHere' 
WHERE email = 'flavio@clinfec.com.br';
```
- Ou crie um script de redefinição de senha
- Ou use outro email

## 📊 O que acontece ao executar:

```
1. Conecta ao banco de dados MySQL
   ↓
2. Verifica se email já existe
   ↓
3. Se NÃO existe:
   - Cria hash da senha (bcrypt)
   - Insere registro na tabela usuarios
   - Retorna ID do novo usuário
   ↓
4. Se JÁ existe:
   - Exibe dados do usuário existente
   - Não faz alterações
   ↓
5. Exibe credenciais na tela
   ↓
6. Aguarda que você DELETE o arquivo
```

## 🔐 Segurança da senha:

- A senha é armazenada com **bcrypt** (algoritmo seguro)
- Hash gerado: `$2y$10$...` (60 caracteres)
- Não é possível reverter o hash para a senha original
- A função `password_verify()` é usada no login

## 📱 Após criar o usuário:

1. ✅ Acesse: `https://prestadores.clinfec.com.br/?page=login`
2. ✅ Digite:
   - Email: `flavio@clinfec.com.br`
   - Senha: `admin123`
3. ✅ Clique em "Entrar"
4. ✅ Você será redirecionado para o Dashboard
5. ✅ **Altere a senha** pelo menu Perfil > Alterar Senha

## 🎓 Personalização:

Se quiser criar com dados diferentes, edite o arquivo antes de executar:

```php
// Altere estas linhas:
$userData = [
    'nome' => 'Seu Nome Aqui',              // ← Mude o nome
    'email' => 'seuemail@exemplo.com',      // ← Mude o email
    'senha' => 'SuaSenhaSegura123',         // ← Mude a senha
    'perfil' => 'master',                    // Mantenha 'master'
    'ativo' => 1                             // Mantenha 1
];
```

## 📞 Suporte:

Se tiver problemas:
1. Verifique os logs do PHP no servidor
2. Verifique o console do navegador (F12)
3. Consulte a documentação completa em `docs/`
4. Entre em contato com o suporte técnico

---

**Criado em:** 2024-11-05  
**Sistema:** Clinfec Prestadores v1.0.0  
**Ambiente:** Hostinger (https://prestadores.clinfec.com.br)
