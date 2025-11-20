# SPRINT 67 - GUIA DE DEPLOYMENT

**Data:** 2025-11-16  
**Sprint:** 67  
**Objetivo:** Deploy completo das correções do login em produção  
**Servidor:** 72.61.53.222

---

## 🎯 OBJETIVO

Corrigir os problemas identificados no relatório QA do Sprint 66:
1. ✅ ENUM incompatível na tabela usuarios
2. ✅ Usuários teste faltantes
3. 🔄 Login que continua falhando (investigação com debug)

---

## 📋 PRÉ-REQUISITOS

### Acesso ao Servidor:
```bash
ssh root@72.61.53.222
Senha: Jm@D@KDPnw7Q
```

### Credenciais Database:
```
Host: localhost
User: user_prestadores
Pass: rN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP
DB: db_prestadores
```

### Arquivos Preparados:
- ✅ database/migrations/026_fix_usuarios_role_enum.sql
- ✅ database/sprint67_complete_fix.sql
- ✅ src/Controllers/AuthControllerDebug.php
- ✅ database/deploy_sprint67_to_vps.sh

---

## 🚀 MÉTODO 1: DEPLOYMENT AUTOMÁTICO (RECOMENDADO)

### Passo 1: Executar Script de Deploy

```bash
cd /home/user/webapp
./database/deploy_sprint67_to_vps.sh
```

**O script faz automaticamente:**
1. ✅ Verifica conexão SSH
2. ✅ Faz backup do AuthController original
3. ✅ Upload da migration 026 (ENUM fix)
4. ✅ Upload do script completo Sprint 67
5. ✅ Upload do AuthControllerDebug
6. ✅ Executa SQL no banco de dados
7. ✅ Substitui AuthController pela versão debug
8. ✅ Reload PHP-FPM
9. ✅ Clear OPcache
10. ✅ Exibe últimas linhas dos logs

### Passo 2: Testar Login

Após deployment, testar imediatamente com os 4 usuários:

```
URL: https://prestadores.clinfec.com.br

Usuário 1: master@clinfec.com.br / password
Usuário 2: admin@clinfec.com.br / admin123
Usuário 3: gestor@clinfec.com.br / password
Usuário 4: usuario@clinfec.com.br / password
```

### Passo 3: Monitorar Logs

Em outro terminal, acompanhar logs em tempo real:

```bash
ssh root@72.61.53.222
tail -f /var/log/php8.3-fpm/error.log
```

**Procurar por:**
- `========== SPRINT 67 DEBUG - LOGIN ATTEMPT ==========`
- `DEBUG: User FOUND in database`
- `DEBUG: Password verification result: SUCCESS ✅`
- `DEBUG: Session created successfully`
- `DEBUG: LOGIN SUCCESS`

### Passo 4: Análise dos Resultados

#### Se Login FUNCIONAR ✅:
1. Remover debug e restaurar AuthController original:
```bash
ssh root@72.61.53.222
cd /opt/webserver/sites/prestadores/src/Controllers
cp AuthController.php.backup.* AuthController.php
systemctl reload php8.3-fpm-prestadores
```

2. Documentar solução encontrada
3. Marcar Sprint 67 como concluído
4. Fornecer lista final de usuários teste

#### Se Login AINDA FALHAR ❌:
1. Analisar logs de debug detalhadamente
2. Identificar em que etapa falha
3. Aplicar correção específica
4. Repetir deploy e teste

---

## 📋 MÉTODO 2: DEPLOYMENT MANUAL

### Passo 1: Conectar ao Servidor

```bash
ssh root@72.61.53.222
cd /opt/webserver/sites/prestadores
```

### Passo 2: Backup do AuthController

```bash
cp src/Controllers/AuthController.php \
   src/Controllers/AuthController.php.backup.$(date +%Y%m%d_%H%M%S)
```

### Passo 3: Upload dos Arquivos

**De outro terminal local:**
```bash
cd /home/user/webapp

# Upload migration
scp database/migrations/026_fix_usuarios_role_enum.sql \
    root@72.61.53.222:/opt/webserver/sites/prestadores/database/migrations/

# Upload script completo
scp database/sprint67_complete_fix.sql \
    root@72.61.53.222:/opt/webserver/sites/prestadores/database/

# Upload AuthControllerDebug
scp src/Controllers/AuthControllerDebug.php \
    root@72.61.53.222:/opt/webserver/sites/prestadores/src/Controllers/
```

### Passo 4: Executar SQL

**No servidor:**
```bash
cd /opt/webserver/sites/prestadores

mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP \
      db_prestadores < database/sprint67_complete_fix.sql
```

**Verificar usuários criados:**
```bash
mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP \
      db_prestadores -e \
"SELECT id, nome, email, role, ativo FROM usuarios WHERE email LIKE '%@clinfec.com.br' ORDER BY role;"
```

### Passo 5: Ativar Debug

```bash
cd /opt/webserver/sites/prestadores/src/Controllers
cp AuthControllerDebug.php AuthController.php
```

### Passo 6: Reload Serviços

```bash
# Reload PHP-FPM
systemctl reload php8.3-fpm-prestadores

# Clear OPcache
echo "<?php opcache_reset(); echo 'OPcache limpo'; ?>" | php8.3

# Verificar status
systemctl status php8.3-fpm-prestadores
```

### Passo 7: Testar e Monitorar

Seguir Passos 2-4 do Método 1 (Automático)

---

## 🔍 TROUBLESHOOTING

### Problema: Script de deploy falha na conexão SSH

**Causa:** Credenciais inválidas ou porta SSH diferente

**Solução:**
```bash
# Testar conexão manual
ssh root@72.61.53.222

# Se falhar, tentar porta alternativa
ssh -p 2222 root@72.61.53.222

# Editar script se necessário
vim database/deploy_sprint67_to_vps.sh
# Alterar VPS_SSH_PORT="2222"
```

### Problema: SQL falha ao executar

**Causa:** Banco de dados não acessível ou credenciais inválidas

**Solução:**
```bash
# Testar conexão ao banco
mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP \
      -e "SHOW DATABASES;"

# Verificar se db_prestadores existe
mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP \
      -e "USE db_prestadores; SHOW TABLES;"
```

### Problema: OPcache não limpa

**Causa:** Versão PHP incorreta ou OPcache desabilitado

**Solução:**
```bash
# Verificar versão PHP
php8.3 -v

# Verificar OPcache
php8.3 -i | grep opcache

# Forçar reload completo do PHP-FPM
systemctl restart php8.3-fpm-prestadores
```

### Problema: Logs não aparecem

**Causa:** Caminho dos logs diferente ou permissões

**Solução:**
```bash
# Encontrar logs PHP-FPM
find /var/log -name "*php*" -type f 2>/dev/null

# Encontrar logs NGINX
find /var/log/nginx -name "*prestadores*" -type f 2>/dev/null

# Verificar permissões
ls -lah /var/log/php8.3-fpm/error.log
ls -lah /var/log/nginx/prestadores-error.log
```

### Problema: Login continua falhando mesmo com debug

**Possíveis Causas Investigar:**

1. **Sessão não persiste:**
```bash
# Verificar session.save_path
php8.3 -i | grep session.save_path

# Verificar permissões
ls -lah $(php8.3 -i | grep session.save_path | awk '{print $3}')
```

2. **Redirecionamento incorreto:**
```bash
# Verificar BASE_URL no código
grep -r "BASE_URL" /opt/webserver/sites/prestadores/public/
```

3. **Cookies bloqueados:**
```bash
# Verificar configuração de cookies no nginx
cat /etc/nginx/sites-available/prestadores.conf | grep -A 10 "location"
```

4. **reCAPTCHA bloqueando:**
```bash
# Verificar config
cat /opt/webserver/sites/prestadores/config/app.php | grep -A 5 "recaptcha"
```

---

## 📊 VALIDAÇÃO PÓS-DEPLOYMENT

### Checklist de Validação:

#### 1. Verificar ENUM Atualizado
```sql
mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP \
      db_prestadores -e "SHOW COLUMNS FROM usuarios LIKE 'role';"
```

**Esperado:**
```
role | enum('master','admin','gerente','gestor','usuario','financeiro') | NO | MUL | usuario
```

#### 2. Verificar Usuários Criados
```sql
mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP \
      db_prestadores -e \
"SELECT id, nome, email, role, ativo FROM usuarios WHERE email LIKE '%@clinfec.com.br';"
```

**Esperado: 4 usuários**
- master@clinfec.com.br (role: master)
- admin@clinfec.com.br (role: admin)
- gestor@clinfec.com.br (role: gestor)
- usuario@clinfec.com.br (role: usuario)

#### 3. Verificar AuthController Debug Ativo
```bash
grep -c "SPRINT 67 DEBUG" /opt/webserver/sites/prestadores/src/Controllers/AuthController.php
```

**Esperado: >= 1** (deve conter strings de debug)

#### 4. Verificar PHP-FPM Rodando
```bash
systemctl status php8.3-fpm-prestadores
```

**Esperado: active (running)**

#### 5. Teste de Login Web

**Login Master:**
- URL: https://prestadores.clinfec.com.br
- Email: master@clinfec.com.br
- Senha: password
- **Resultado Esperado:** Acesso ao dashboard OU logs de debug detalhados

---

## 📈 PRÓXIMOS PASSOS APÓS DEPLOYMENT

### Se Login FUNCIONAR ✅:

1. **Desativar Debug:**
```bash
cd /opt/webserver/sites/prestadores/src/Controllers
cp AuthController.php.backup.YYYYMMDD_HHMMSS AuthController.php
systemctl reload php8.3-fpm-prestadores
```

2. **Documentar Solução:**
- Atualizar SPRINT_67_ANALISE_E_CORRECOES.md
- Adicionar seção "Solução Encontrada"
- Documentar causa raiz identificada

3. **Commit Solução:**
```bash
git add .
git commit -m "fix(auth): Sprint 67 - Login corrigido [CAUSA IDENTIFICADA]"
git push origin genspark_ai_developer
```

4. **Fornecer Lista Final Usuários:**
- Criar SPRINT_67_USUARIOS_TESTE_FINAL.md
- Incluir credenciais e permissões
- Marcar Sprint 67 como ✅ CONCLUÍDO

### Se Login AINDA FALHAR ❌:

1. **Analisar Logs Detalhadamente:**
```bash
tail -100 /var/log/php8.3-fpm/error.log | grep "SPRINT 67 DEBUG"
```

2. **Identificar Etapa que Falha:**
- User not found?
- Password verification failed?
- Session not persisting?
- Redirect loop?

3. **Aplicar Correção Específica:**
- Gerar novo hash de senha?
- Corrigir session.save_path?
- Ajustar BASE_URL?
- Desabilitar reCAPTCHA temporariamente?

4. **Criar Sprint 67.1:**
- Documentar nova descoberta
- Implementar correção específica
- Repetir ciclo PDCA

---

## 🔗 REFERÊNCIAS

### Documentação Sprint 67:
- `SPRINT_67_ANALISE_E_CORRECOES.md` - Análise detalhada
- `RELATORIO_DEPLOYMENT_QA_SPRINT67.txt` - Relatório QA original
- `database/sprint67_complete_fix.sql` - Script SQL completo
- `src/Controllers/AuthControllerDebug.php` - Controller com debug

### Servidor:
- **IP:** 72.61.53.222
- **Path:** /opt/webserver/sites/prestadores
- **PHP-FPM:** php8.3-fpm-prestadores
- **URL:** https://prestadores.clinfec.com.br

### Logs:
- `/var/log/php8.3-fpm/error.log` - Erros PHP
- `/var/log/nginx/prestadores-error.log` - Erros NGINX

### GitHub:
- **Branch:** genspark_ai_developer
- **Commit:** 2df6f06
- **PR:** #7

---

**Última Atualização:** 2025-11-16 21:45 UTC  
**Responsável:** GenSpark AI Developer  
**Status:** 🟡 PRONTO PARA DEPLOYMENT
