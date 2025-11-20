# 🔴 AÇÃO IMEDIATA - DEPLOYMENT BUG #7

**URGENTE:** Login quebrado - Sistema 100% inacessível  
**Data:** 2025-11-16  
**Tempo Estimado:** 10-15 minutos

---

## ⚡ DEPLOYMENT MAIS RÁPIDO - Método wget GitHub

### Pré-requisito:
```bash
ssh root@72.61.53.222
```

### Comandos (copiar e colar):

```bash
# 1. Navegar para diretório do projeto
cd /opt/webserver/sites/prestadores

# 2. Backup do Database.php atual (segurança)
cp src/Database.php src/Database.php.backup.$(date +%Y%m%d_%H%M%S)

# 3. Download Database.php corrigido do GitHub
wget -O src/Database.php \
  https://raw.githubusercontent.com/fmunizmcorp/prestadores/genspark_ai_developer/src/Database.php

# 4. Download SQL usuários teste do GitHub
wget -O database/create_test_users_temp.sql \
  https://raw.githubusercontent.com/fmunizmcorp/prestadores/genspark_ai_developer/database/create_test_users.sql

# 5. Executar SQL para criar usuários
mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP db_prestadores < database/create_test_users_temp.sql

# 6. Reload PHP-FPM
systemctl reload php8.3-fpm-prestadores

# 7. Clear OPcache
echo "<?php opcache_reset(); echo 'OPcache limpo OK'; ?>" | php8.3

# 8. Verificar deploy
echo ""
echo "✅ Deployment concluído!"
echo ""
echo "Verificando métodos Database.php..."
grep -c "public function prepare" src/Database.php
grep -c "public function query" src/Database.php
grep -c "public function exec" src/Database.php
echo ""
echo "Verificando usuários criados..."
mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP db_prestadores \
  -e "SELECT id, nome, email, role FROM usuarios WHERE email LIKE '%@clinfec.com.br' ORDER BY role DESC;"
```

---

## 🧪 TESTAR LOGIN IMEDIATAMENTE

### Teste 1: Login Master
```
URL: https://prestadores.clinfec.com.br
Email: master@clinfec.com.br
Senha: password
```
✅ **Esperado:** Redirect para dashboard sem erros

### Teste 2: Login Admin
```
URL: https://prestadores.clinfec.com.br
Email: admin@clinfec.com.br
Senha: admin123
```
✅ **Esperado:** Redirect para dashboard sem erros

---

## ❌ SE HOUVER PROBLEMAS

### Problema: wget não disponível
```bash
# Usar curl ao invés de wget
cd /opt/webserver/sites/prestadores
curl -o src/Database.php \
  https://raw.githubusercontent.com/fmunizmcorp/prestadores/genspark_ai_developer/src/Database.php
```

### Problema: Login ainda falha
```bash
# Verificar logs
tail -50 /var/log/php8.3-fpm-prestadores.log
tail -50 /var/log/nginx/prestadores_error.log

# Verificar se Database.php tem os métodos
grep "public function prepare" src/Database.php
```

### Rollback (se necessário)
```bash
cd /opt/webserver/sites/prestadores
cp src/Database.php.backup.* src/Database.php
systemctl reload php8.3-fpm-prestadores
```

---

## 📋 USUÁRIOS PARA TESTES QA

Após deployment bem-sucedido, usar estes usuários para QA:

| Email | Senha | Role | Uso |
|-------|-------|------|-----|
| master@clinfec.com.br | password | master | Testes admin completos |
| admin@clinfec.com.br | admin123 | admin | Testes gestão |
| gestor@clinfec.com.br | password | gestor | Testes projetos/equipes |
| usuario@clinfec.com.br | password | usuario | Testes operacionais |

---

## 📞 SUPORTE

**Se problemas persistirem:**
1. Verificar documentação completa: `SPRINT_66_DEPLOYMENT_MANUAL.md`
2. Checar status: `SPRINT_66_STATUS_FINAL.md`
3. Consultar QA report: `RELATORIO_QA_COMPLETO_NOVO.md.pdf`

**GitHub PR:** https://github.com/fmunizmcorp/prestadores/pull/7

---

**TUDO PRONTO!** Após executar os comandos acima, o login deve funcionar.
