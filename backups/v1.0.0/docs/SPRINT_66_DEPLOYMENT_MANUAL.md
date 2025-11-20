# SPRINT 66 - MANUAL DE DEPLOYMENT BUG #7

**Data:** 2025-11-16  
**Sprint:** 66  
**Objetivo:** Corrigir Bug #7 - Login Crítico Bloqueador  
**Servidor:** 72.61.53.222 (VPS Ubuntu 24.04.3 LTS)

---

## 🔴 STATUS ATUAL

### ✅ COMPLETADO (GitHub):
- ✅ Commit Sprint 66 realizado (hash: 76d986e)
- ✅ Push para GitHub concluído
- ✅ PR #7 atualizado: https://github.com/fmunizmcorp/prestadores/pull/7
- ✅ Arquivos preparados e documentados

### ⏳ PENDENTE (Servidor VPS):
- ⏳ Upload de `src/Database.php` para produção
- ⏳ Upload de `database/create_test_users.sql` para produção
- ⏳ Execução SQL para criar usuários
- ⏳ Reload PHP-FPM
- ⏳ Clear OPcache
- ⏳ Validação de login

---

## 📋 DEPLOYMENT MANUAL - MÉTODO 1: Script Automatizado

### Pré-requisitos:
```bash
# Acesso SSH ao servidor VPS
ssh root@72.61.53.222

# Ou, se necessário usar porta alternativa:
ssh -p 2222 root@72.61.53.222
```

### Executar Script Automatizado:
```bash
cd /home/user/webapp
chmod +x database/fix_bug7_deploy.sh
./database/fix_bug7_deploy.sh
```

**O que o script faz:**
1. Cria arquivo SQL temporário com os 4 usuários
2. Upload via SCP para VPS: `database/create_test_users.sql`
3. Upload via SCP para VPS: `src/Database.php`
4. Executa SQL no banco db_prestadores
5. Reload PHP-FPM (systemctl reload php8.3-fpm-prestadores)
6. Clear OPcache (opcache_reset)

---

## 📋 DEPLOYMENT MANUAL - MÉTODO 2: Passo a Passo

### Etapa 1: Acesso ao Servidor VPS

```bash
# SSH para o servidor
ssh root@72.61.53.222

# Navegar para diretório do projeto
cd /opt/webserver/sites/prestadores
```

### Etapa 2: Upload do Database.php Corrigido

**Opção A - Via SCP (de outro terminal):**
```bash
cd /home/user/webapp
scp src/Database.php root@72.61.53.222:/opt/webserver/sites/prestadores/src/
```

**Opção B - Via cat/nano no servidor:**
```bash
# No servidor VPS, editar o arquivo
nano /opt/webserver/sites/prestadores/src/Database.php

# Copiar conteúdo completo de src/Database.php do GitHub
# Salvar e sair (Ctrl+O, Enter, Ctrl+X)
```

**Opção C - Via wget do GitHub (RAW):**
```bash
# No servidor VPS
cd /opt/webserver/sites/prestadores/src/
wget -O Database.php https://raw.githubusercontent.com/fmunizmcorp/prestadores/genspark_ai_developer/src/Database.php
```

### Etapa 3: Criar Usuários no Banco de Dados

**SQL a executar:**
```sql
-- Conectar ao banco
mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP db_prestadores

-- Executar INSERTs:

-- Master User
INSERT INTO usuarios (nome, email, senha, role, ativo, created_at, updated_at) VALUES
('Master User', 'master@clinfec.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'master', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE senha = VALUES(senha), role = VALUES(role), ativo = VALUES(ativo);

-- Admin User
INSERT INTO usuarios (nome, email, senha, role, ativo, created_at, updated_at) VALUES
('Admin User', 'admin@clinfec.com.br', '$2y$10$VJL2WmMq9Kh7FHPqYG8P2.Y8ZHPqT5xQwE0pXk7nOmKm3F9F/R5Wa', 'admin', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE senha = VALUES(senha), role = VALUES(role), ativo = VALUES(ativo);

-- Gestor User
INSERT INTO usuarios (nome, email, senha, role, ativo, created_at, updated_at) VALUES
('Gestor User', 'gestor@clinfec.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'gestor', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE senha = VALUES(senha), role = VALUES(role), ativo = VALUES(ativo);

-- Usuario Basico
INSERT INTO usuarios (nome, email, senha, role, ativo, created_at, updated_at) VALUES
('Usuario Basico', 'usuario@clinfec.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE senha = VALUES(senha), role = VALUES(role), ativo = VALUES(ativo);

-- Verificar criação
SELECT id, nome, email, role, ativo FROM usuarios WHERE email LIKE '%@clinfec.com.br' ORDER BY role DESC;
```

**Ou via arquivo SQL:**
```bash
# Fazer upload do SQL
scp database/create_test_users.sql root@72.61.53.222:/opt/webserver/sites/prestadores/database/

# No servidor VPS, executar:
cd /opt/webserver/sites/prestadores
mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP db_prestadores < database/create_test_users.sql
```

### Etapa 4: Reload PHP-FPM

```bash
# No servidor VPS
systemctl reload php8.3-fpm-prestadores

# Verificar status
systemctl status php8.3-fpm-prestadores
```

### Etapa 5: Clear OPcache

```bash
# No servidor VPS
echo "<?php opcache_reset(); echo 'OPcache limpo'; ?>" | php8.3

# Ou criar arquivo temporário e executar
cat > /tmp/clear_opcache.php << 'EOF'
<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache limpo com sucesso\n";
} else {
    echo "❌ OPcache não disponível\n";
}
?>
EOF

php8.3 /tmp/clear_opcache.php
```

### Etapa 6: Validação

```bash
# Verificar arquivo Database.php
ls -lah /opt/webserver/sites/prestadores/src/Database.php

# Verificar se métodos existem
grep -n "public function prepare" /opt/webserver/sites/prestadores/src/Database.php
grep -n "public function query" /opt/webserver/sites/prestadores/src/Database.php
grep -n "public function exec" /opt/webserver/sites/prestadores/src/Database.php
```

---

## 📋 DEPLOYMENT MANUAL - MÉTODO 3: Via FTP/SFTP

### Usando FileZilla ou outro cliente FTP:

**Conexão:**
- Host: `sftp://72.61.53.222`
- Usuário: `root`
- Senha: (solicitar acesso)
- Porta: `22`

**Arquivos a fazer upload:**

1. **Database.php**
   - Local: `/home/user/webapp/src/Database.php`
   - Remoto: `/opt/webserver/sites/prestadores/src/Database.php`

2. **create_test_users.sql**
   - Local: `/home/user/webapp/database/create_test_users.sql`
   - Remoto: `/opt/webserver/sites/prestadores/database/create_test_users.sql`

**Após upload:**
- SSH no servidor
- Executar SQL conforme Etapa 3
- Reload PHP-FPM conforme Etapa 4
- Clear OPcache conforme Etapa 5

---

## 🧪 VALIDAÇÃO PÓS-DEPLOYMENT

### Teste 1: Login Master
```
URL: https://prestadores.clinfec.com.br
Email: master@clinfec.com.br
Senha: password

✅ Esperado: Login bem-sucedido, redirecionamento para dashboard
❌ Se falhar: Verificar logs PHP-FPM e Database.php
```

### Teste 2: Login Admin
```
URL: https://prestadores.clinfec.com.br
Email: admin@clinfec.com.br
Senha: admin123

✅ Esperado: Login bem-sucedido, redirecionamento para dashboard
❌ Se falhar: Verificar tabela usuarios (email/senha)
```

### Teste 3: Verificar Métodos Database
```bash
# No servidor VPS
cd /opt/webserver/sites/prestadores
php8.3 -r "
require_once 'vendor/autoload.php';
\$db = App\Database::getInstance();
echo 'Database methods available: ';
echo method_exists(\$db, 'prepare') ? '✅ prepare() ' : '❌ prepare() ';
echo method_exists(\$db, 'query') ? '✅ query() ' : '❌ query() ';
echo method_exists(\$db, 'exec') ? '✅ exec() ' : '❌ exec() ';
echo PHP_EOL;
"
```

### Teste 4: Verificar OPcache
```bash
# No servidor VPS
php8.3 -r "
echo 'OPcache status: ';
if (function_exists('opcache_get_status')) {
    \$status = opcache_get_status();
    echo 'Enabled: ' . (\$status['opcache_enabled'] ? '✅' : '❌') . PHP_EOL;
    echo 'Cached scripts: ' . count(\$status['scripts']) . PHP_EOL;
} else {
    echo '❌ Not available' . PHP_EOL;
}
"
```

---

## 📊 LOGS E TROUBLESHOOTING

### Logs Importantes:

```bash
# PHP-FPM Error Log
tail -f /var/log/php8.3-fpm-prestadores.log

# NGINX Error Log
tail -f /var/log/nginx/prestadores_error.log

# NGINX Access Log
tail -f /var/log/nginx/prestadores_access.log

# Application Log (se existir)
tail -f /opt/webserver/sites/prestadores/storage/logs/app.log
```

### Problemas Comuns:

**Problema 1: Login ainda falha após deployment**
```bash
# Verificar se Database.php foi atualizado corretamente
grep -A 5 "public function prepare" /opt/webserver/sites/prestadores/src/Database.php

# Se não encontrar, fazer upload novamente
```

**Problema 2: Usuários não aparecem no banco**
```sql
-- Conectar ao banco
mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP db_prestadores

-- Verificar tabela
SELECT * FROM usuarios WHERE email LIKE '%@clinfec.com.br';

-- Se vazia, executar INSERTs novamente
```

**Problema 3: OPcache não limpa**
```bash
# Forçar reload completo do PHP-FPM
systemctl restart php8.3-fpm-prestadores

# Verificar se processo reiniciou
ps aux | grep php8.3-fpm-prestadores
```

---

## 📋 CHECKLIST DE DEPLOYMENT

### Pré-Deployment:
- [ ] ✅ Código commitado no Git
- [ ] ✅ Push para GitHub realizado
- [ ] ✅ PR #7 atualizado
- [ ] ✅ Documentação criada

### Deployment:
- [ ] ⏳ Acesso SSH ao VPS obtido
- [ ] ⏳ Database.php uploadado para `/opt/webserver/sites/prestadores/src/`
- [ ] ⏳ create_test_users.sql uploadado para `/opt/webserver/sites/prestadores/database/`
- [ ] ⏳ SQL executado no banco db_prestadores
- [ ] ⏳ PHP-FPM reloadado
- [ ] ⏳ OPcache limpo

### Validação:
- [ ] ⏳ Login master@clinfec.com.br testado
- [ ] ⏳ Login admin@clinfec.com.br testado
- [ ] ⏳ Dashboard acessível após login
- [ ] ⏳ Métodos Database.php verificados

### Pós-Deployment:
- [ ] ⏳ Testes QA retomados (Fase 2)
- [ ] ⏳ Documentação atualizada com resultados
- [ ] ⏳ Lista final de usuários teste fornecida

---

## 🔗 REFERÊNCIAS

- **GitHub PR:** https://github.com/fmunizmcorp/prestadores/pull/7
- **Commit Sprint 66:** 76d986e
- **Documentação:** SPRINT_66_FIX_BUG7_LOGIN_CRITICO.md
- **QA Report:** RELATORIO_QA_COMPLETO_NOVO.md.pdf
- **URL Produção:** https://prestadores.clinfec.com.br
- **VPS IP:** 72.61.53.222

---

## 📞 SUPORTE

**Em caso de problemas durante deployment:**
1. Verificar logs (seção Logs e Troubleshooting)
2. Consultar documentação SPRINT_66_FIX_BUG7_LOGIN_CRITICO.md
3. Verificar se todos os arquivos foram uploadados corretamente
4. Testar métodos Database.php via CLI PHP
5. Verificar permissões de arquivos (644 para PHP, 755 para diretórios)

---

**Última Atualização:** 2025-11-16  
**Status:** 🟡 READY FOR DEPLOYMENT  
**Próximo Passo:** Executar deployment no VPS
