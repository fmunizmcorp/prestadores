# 🚀 MANUAL DE MIGRAÇÃO - Clinfec Prestadores

## Sprint 62 - Migração Hostinger → VPS
**Data:** 2025-11-16  
**Autor:** GenSpark AI  
**Versão:** 1.0

---

## 📋 VISÃO GERAL

Este manual guia a migração completa do sistema Clinfec Prestadores do servidor compartilhado Hostinger para o VPS dedicado.

### ✅ O que foi preparado:
- ✅ 188 arquivos essenciais baixados (2.27 MB)
- ✅ 5 scripts automatizados de migração
- ✅ Documentação completa
- ✅ Procedimentos de rollback

### ⚠️ O que requer atenção manual:
- 📊 Export do banco de dados (via phpMyAdmin)
- 🌐 Configuração de DNS
- 🔒 Geração de certificado SSL

---

## 🎯 PRÉ-REQUISITOS

### Servidor de Origem (Hostinger):
- ✅ Acesso FTP: `ftp.clinfec.com.br`
- ✅ Acesso phpMyAdmin
- ✅ Banco: `u673902663_prestadores`

### Servidor de Destino (VPS):
- ✅ IP: `72.61.53.222`
- ✅ SSH: `root@72.61.53.222` (porta 22 ou 2222)
- ✅ Senha: `Jm@D@KDPnw7Q`
- ✅ Ubuntu 22.04 LTS
- ✅ NGINX + PHP 8.3-FPM + MariaDB

---

## 📝 ROTEIRO DE EXECUÇÃO

### **ETAPA 1: Export do Banco de Dados**

⏱️ **Tempo estimado:** 5-10 minutos

#### Opção A: Via phpMyAdmin (RECOMENDADO)

1. Acesse: https://hpanel.hostinger.com
2. Vá em **Bancos de Dados MySQL**
3. Clique em **Gerenciar** no banco: `u673902663_prestadores`
4. Aba **Exportar**:
   - Método: **Personalizado**
   - Formato: **SQL**
   - Marque: **Adicionar DROP TABLE**
   - Marque: **Adicionar CREATE TABLE**
5. Clique em **Executar**
6. Salve como: `prestadores_db_backup.sql`

#### Opção B: Via SSH (se disponível)

```bash
# Execute no servidor Hostinger
bash scripts/1_export_database_manual.sh
```

✅ **Validação:** Arquivo `.sql` criado com tamanho > 100 KB

---

### **ETAPA 2: Preparação do VPS**

⏱️ **Tempo estimado:** 5-10 minutos

```bash
# 1. Conectar ao VPS
ssh root@72.61.53.222
# Senha: Jm@D@KDPnw7Q

# 2. Fazer upload do script 2
# (use scp ou cole manualmente)

# 3. Executar
bash 2_prepare_vps.sh
```

📝 **IMPORTANTE:** Anote a senha do banco de dados gerada pelo script!

✅ **Validação:**
- Site criado em `/opt/webserver/sites/prestadores/`
- NGINX configurado
- PHP-FPM ativo
- Banco de dados criado

---

### **ETAPA 3: Transferência de Arquivos**

⏱️ **Tempo estimado:** 10-15 minutos

#### Opção A: Do seu computador local

```bash
# 1. Baixe os arquivos preparados
# (já estão em /home/user/webapp/migration_backup)

# 2. Execute o script
bash 3_transfer_files.sh
```

#### Opção B: Manual via SCP

```bash
# Transferir cada diretório
scp -P 22 -r migration_backup/src root@72.61.53.222:/opt/webserver/sites/prestadores/public_html/
scp -P 22 -r migration_backup/public root@72.61.53.222:/opt/webserver/sites/prestadores/public_html/
scp -P 22 -r migration_backup/config root@72.61.53.222:/opt/webserver/sites/prestadores/public_html/
scp -P 22 migration_backup/.htaccess root@72.61.53.222:/opt/webserver/sites/prestadores/public_html/
```

✅ **Validação:**
- Diretórios `src/`, `public/`, `config/` presentes no VPS
- Arquivo `.htaccess` transferido

---

### **ETAPA 4: Restauração do Banco de Dados**

⏱️ **Tempo estimado:** 5-15 minutos

```bash
# 1. Transferir o SQL para o VPS
scp -P 22 prestadores_db_backup.sql root@72.61.53.222:/tmp/

# 2. Conectar ao VPS
ssh root@72.61.53.222

# 3. Executar script de restauração
bash 4_restore_database.sh /tmp/prestadores_db_backup.sql

# 4. Digite a senha do banco (da Etapa 2)
```

✅ **Validação:**
- Todas as tabelas restauradas
- Contagem de registros correta
- Sem erros de SQL

---

### **ETAPA 5: Atualização de Configurações**

⏱️ **Tempo estimado:** 3-5 minutos

```bash
# No VPS, execute:
bash 5_update_config.sh

# Forneça as credenciais:
# - Database Name: prestadores_db
# - Database User: prestadores_user
# - Database Password: [senha da Etapa 2]
# - Database Host: localhost
```

✅ **Validação:**
- Arquivo `config/database.php` atualizado
- Arquivo `.env` criado
- Permissões corretas (640)

---

### **ETAPA 6: Configuração do NGINX**

⏱️ **Tempo estimado:** 5 minutos

Verifique e ajuste a configuração do NGINX:

```bash
# Editar configuração
nano /etc/nginx/sites-available/prestadores.conf
```

Certifique-se de que o `root` aponta para:
```nginx
root /opt/webserver/sites/prestadores/public_html/public;
```

E que há rewrite para index.php:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

Testar e recarregar:
```bash
nginx -t
systemctl reload nginx
```

---

### **ETAPA 7: Teste Inicial via IP**

⏱️ **Tempo estimado:** 5 minutos

```bash
# Obter IP do VPS
hostname -I

# Testar no navegador:
# http://72.61.53.222
```

✅ **Validação:**
- Página de login carrega
- CSS/JS funcionando
- Login funciona
- Dashboard acessível

---

### **ETAPA 8: Configuração de DNS**

⏱️ **Tempo estimado:** 5 minutos (propagação: 1-24h)

1. Acesse o painel de DNS do domínio `clinfec.com.br`
2. Crie/Edite registro:
   ```
   Tipo: A
   Nome: prestadores
   Valor: 72.61.53.222
   TTL: 3600
   ```
3. Aguarde propagação (teste com: `nslookup prestadores.clinfec.com.br`)

---

### **ETAPA 9: Certificado SSL**

⏱️ **Tempo estimado:** 5-10 minutos

```bash
# No VPS, após DNS propagado:
apt-get update
apt-get install certbot python3-certbot-nginx

certbot --nginx -d prestadores.clinfec.com.br

# Responda as perguntas:
# - Email: seu@email.com
# - Termos: Yes
# - Redirect HTTP → HTTPS: Yes
```

✅ **Validação:**
- Acesso via `https://prestadores.clinfec.com.br`
- Cadeado verde no navegador

---

### **ETAPA 10: Validação Final**

⏱️ **Tempo estimado:** 15-30 minutos

Teste completo de funcionalidades:

- [ ] Login/Logout
- [ ] Dashboard carrega dados
- [ ] CRUD de Empresas
- [ ] CRUD de Contratos
- [ ] CRUD de Serviços
- [ ] Módulo Financeiro
- [ ] Módulo de Projetos
- [ ] Geração de relatórios
- [ ] Upload de arquivos
- [ ] Logs funcionando

---

## 🔄 PROCEDIMENTO DE ROLLBACK

Se algo der errado:

### Rollback de Arquivos:
```bash
# Restaurar backup criado automaticamente
cp -r /tmp/prestadores_backup_* /opt/webserver/sites/prestadores/public_html/
```

### Rollback de Banco:
```bash
# Restaurar backup automático
mysql -u prestadores_user -p prestadores_db < /tmp/prestadores_db_backup_before_restore_*.sql
```

### Rollback de DNS:
- Revert registro A para o IP antigo do Hostinger

---

## 🆘 SOLUÇÃO DE PROBLEMAS

### Erro: "Database connection failed"
**Solução:**
```bash
# Verificar config/database.php
cat /opt/webserver/sites/prestadores/public_html/config/database.php

# Testar conexão manual
mysql -u prestadores_user -p prestadores_db
```

### Erro 404 nas rotas
**Solução:**
```bash
# Verificar .htaccess
cat /opt/webserver/sites/prestadores/public_html/public/.htaccess

# Verificar NGINX permite .htaccess
# Ou converter regras para NGINX
```

### CSS/JS não carregam
**Solução:**
```bash
# Verificar permissões
chmod -R 755 /opt/webserver/sites/prestadores/public_html/public/css
chmod -R 755 /opt/webserver/sites/prestadores/public_html/public/js

# Verificar owner
chown -R prestadores:prestadores /opt/webserver/sites/prestadores/public_html/
```

---

## 📊 CHECKLIST FINAL

Antes de considerar a migração completa:

- [ ] ✅ Banco de dados restaurado completamente
- [ ] ✅ Todos os arquivos transferidos
- [ ] ✅ Configurações atualizadas
- [ ] ✅ NGINX configurado corretamente
- [ ] ✅ PHP-FPM funcionando
- [ ] ✅ DNS apontando para VPS
- [ ] ✅ SSL configurado e ativo
- [ ] ✅ Aplicação acessível via HTTPS
- [ ] ✅ Login funciona
- [ ] ✅ Todas as funcionalidades testadas
- [ ] ✅ Logs estão sendo gerados
- [ ] ✅ Performance satisfatória
- [ ] ✅ Backup configurado (cron jobs)

---

## 📞 SUPORTE

### Logs do Sistema:
```bash
# Logs da aplicação
tail -f /opt/webserver/sites/prestadores/logs/*.log

# Logs do NGINX
tail -f /opt/webserver/sites/prestadores/logs/access.log
tail -f /opt/webserver/sites/prestadores/logs/error.log

# Logs do PHP-FPM
tail -f /var/log/php8.3-fpm.log
```

### Informações do Sistema:
```bash
# Status dos serviços
systemctl status nginx
systemctl status php8.3-fpm
systemctl status mariadb

# Uso de recursos
htop
df -h
free -h
```

---

## 🎉 CONCLUSÃO

Após completar todas as etapas e validações, a migração está completa!

**Próximos passos recomendados:**
1. Monitorar logs por 24-48h
2. Configurar backups automáticos
3. Otimizar performance (OPcache, Redis)
4. Configurar monitoring (Zabbix, Prometheus)
5. Desativar site antigo no Hostinger (após 1 semana)

---

**Desenvolvido com ❤️ usando Metodologia Scrum + PDCA**
