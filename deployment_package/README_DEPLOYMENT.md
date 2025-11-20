# 🚀 SPRINT 67 - PACOTE DE DEPLOYMENT

## 📦 Conteúdo do Pacote

```
deployment_package/
├── scripts/
│   ├── deploy_sprint67_to_vps.sh    # Script original de deployment
│   ├── remote_execute.sh             # Deployment remoto via SSH
│   └── test_login.sh                 # Testes automatizados
├── sql/
│   ├── 026_fix_usuarios_role_enum.sql   # Migration para corrigir ENUM
│   └── sprint67_complete_fix.sql         # SQL completo (ENUM + usuários)
├── php/
│   └── AuthControllerDebug.php       # Controller com debug extensivo
├── docs/
│   ├── SPRINT_67_ANALISE_E_CORRECOES.md
│   ├── SPRINT_67_GUIA_DEPLOYMENT.md
│   ├── SPRINT_67_STATUS_ATUAL.md
│   └── USUARIOS_TESTE_SISTEMA_PRESTADORES.md
└── README_DEPLOYMENT.md              # Este arquivo
```

---

## 🎯 Objetivos do Deployment

✅ Corrigir ENUM incompatibilidade na tabela usuarios  
✅ Criar/atualizar 4 usuários de teste com senhas bcrypt  
✅ Ativar logging debug no AuthController  
✅ Validar login funcional para todos os perfis  
✅ Preparar sistema para QA retomar 47 testes  

---

## 🔧 MÉTODO 1: Deployment Automático via SSH

### Pré-requisitos
- Acesso SSH ao servidor (72.61.53.222)
- Chave SSH configurada
- Permissões root ou sudo

### Execução

```bash
# 1. Navegar para o diretório de scripts
cd deployment_package/scripts/

# 2. Executar deployment remoto
./remote_execute.sh [caminho_para_chave_ssh]

# Exemplo com chave padrão:
./remote_execute.sh ~/.ssh/id_rsa

# Exemplo com chave específica:
./remote_execute.sh /path/to/prestadores_key.pem
```

### O que o script faz:

1. ✅ Testa conexão SSH
2. 📤 Upload de arquivos (SQL, PHP, docs)
3. 📦 Backup do AuthController original
4. 🗄️ Executa SQL de correção
5. 🔍 Ativa AuthController com debug
6. ♻️ Reload PHP-FPM
7. 🗑️ Limpa OPcache
8. 👥 Valida usuários criados

### Tempo estimado: **2-3 minutos**

---

## 🔧 MÉTODO 2: Deployment Manual

Se não tiver acesso SSH automatizado, siga estes passos:

### Passo 1: Upload dos Arquivos

```bash
# Conectar ao servidor
ssh root@72.61.53.222

# Navegar para o diretório do projeto
cd /opt/webserver/sites/prestadores
```

Upload manual dos arquivos:
- `sql/sprint67_complete_fix.sql` → `/opt/webserver/sites/prestadores/database/`
- `php/AuthControllerDebug.php` → `/opt/webserver/sites/prestadores/src/Controllers/`

### Passo 2: Backup

```bash
# Backup do AuthController original
cp src/Controllers/AuthController.php \
   src/Controllers/AuthController.php.backup.$(date +%Y%m%d_%H%M%S)
```

### Passo 3: Executar SQL

```bash
# Executar correções no banco
mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP db_prestadores \
    < database/sprint67_complete_fix.sql
```

**Validação:**
```sql
-- Verificar ENUM atualizado
SHOW COLUMNS FROM usuarios LIKE 'role';

-- Verificar usuários criados
SELECT id, nome, email, role, LEFT(senha, 20) AS senha_hash
FROM usuarios 
WHERE email LIKE '%@clinfec.com.br'
ORDER BY 
    CASE role 
        WHEN 'master' THEN 1 
        WHEN 'admin' THEN 2 
        WHEN 'gestor' THEN 3 
        WHEN 'usuario' THEN 4 
    END;
```

### Passo 4: Ativar Debug

```bash
# Copiar versão debug do AuthController
cp src/Controllers/AuthControllerDebug.php src/Controllers/AuthController.php
```

### Passo 5: Reload de Serviços

```bash
# Reload PHP-FPM
systemctl reload php8.3-fpm-prestadores

# Limpar OPcache
echo "<?php opcache_reset(); echo 'OPcache cleared'; ?>" | php8.3
```

### Passo 6: Verificar Logs

```bash
# Monitorar logs em tempo real
tail -f /var/log/php8.3-fpm/error.log
```

---

## 🧪 MÉTODO 3: Testes Automatizados

Após o deployment, execute os testes automatizados:

```bash
# Executar script de testes
cd deployment_package/scripts/
./test_login.sh
```

### O que o script testa:

Para cada um dos 4 usuários:

1. ✅ Acesso à página de login (GET)
2. ✅ Envio de credenciais (POST)
3. ✅ Redirecionamento após login
4. ✅ Não retorna para tela de login
5. ✅ Acesso a página protegida (dashboard)
6. ✅ Persistência de sessão

### Resultado esperado:

```
================================================================
  📊 RELATÓRIO FINAL DE TESTES
================================================================

  Total de testes: 4
  Passou: 4
  Falhou: 0

🎉 TODOS OS TESTES PASSARAM! 🎉

✅ Sistema pronto para QA retomar os 47 testes
```

---

## 👥 Usuários de Teste

Após deployment, os seguintes usuários estarão disponíveis:

| Email | Senha | Role | Permissões |
|-------|-------|------|------------|
| master@clinfec.com.br | password | master | 12/12 (100%) |
| admin@clinfec.com.br | admin123 | admin | 10/12 (83%) |
| gestor@clinfec.com.br | password | gestor | 8/12 (67%) |
| usuario@clinfec.com.br | password | usuario | 4/12 (33%) |

**Detalhes completos:** Ver `docs/USUARIOS_TESTE_SISTEMA_PRESTADORES.md`

---

## 🔍 Análise de Logs

### Durante tentativa de login:

Os logs mostrarão (se debug ativo):

```
========== SPRINT 67 DEBUG - LOGIN ATTEMPT ==========
  - Email: master@clinfec.com.br
  - Password length: 8
DEBUG: User FOUND in database
  - User ID: 123
  - Password hash (first 20 chars): $2y$10$abcdefghijkl
DEBUG: Password verification result: SUCCESS ✅
DEBUG: Session created successfully
  - user_id: 123
  - usuario_nome: Master User
  - Session ID after: abc123def456
  ✅ Session persisted
DEBUG: Redirecting to dashboard
```

### Se login falhar:

```
DEBUG: Password verification result: FAILED ❌
  - This means:
    ✗ Senha incorreta no banco
    ✗ Hash bcrypt inválido
    ✗ Função password_verify() falhou
```

---

## 🐛 Troubleshooting

### Problema 1: "Too many authentication failures" (SSH)

**Causa:** Múltiplas chaves SSH sendo testadas

**Solução:**
```bash
# Especificar chave exata
ssh -i /path/to/key.pem root@72.61.53.222

# Ou adicionar ao ~/.ssh/config:
Host prestadores
    HostName 72.61.53.222
    User root
    IdentityFile ~/.ssh/prestadores_key.pem
    IdentitiesOnly yes
```

### Problema 2: SQL não executa

**Causa:** Credenciais incorretas ou banco não acessível

**Solução:**
```bash
# Testar conexão
mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP db_prestadores -e "SELECT 1"

# Verificar se o banco existe
mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP -e "SHOW DATABASES"
```

### Problema 3: Login ainda falha após deployment

**Diagnóstico:**

1. **Verificar logs:**
```bash
tail -f /var/log/php8.3-fpm/error.log
```

2. **Verificar usuários no banco:**
```sql
SELECT email, role, LEFT(senha, 30) AS hash 
FROM usuarios 
WHERE email = 'master@clinfec.com.br';
```

3. **Testar senha manualmente:**
```php
<?php
$hash = '$2y$10$...'; // Hash do banco
$senha = 'password';   // Senha testada
var_dump(password_verify($senha, $hash)); // Deve retornar true
?>
```

4. **Verificar sessões:**
```bash
# Ver arquivos de sessão
ls -la /var/lib/php/sessions/

# Verificar permissões
ls -ld /var/lib/php/sessions/
# Deve ser: drwx-wx-wt (1733) com owner www-data
```

### Problema 4: OPcache não limpa

**Solução:**
```bash
# Método 1: Via CLI
echo "<?php opcache_reset(); ?>" | php8.3

# Método 2: Restart do serviço
systemctl restart php8.3-fpm-prestadores

# Método 3: Via web (criar arquivo temporário)
echo "<?php opcache_reset(); echo 'OK'; ?>" > /opt/webserver/sites/prestadores/public/opcache_reset.php
curl https://prestadores.clinfec.com.br/opcache_reset.php
rm /opt/webserver/sites/prestadores/public/opcache_reset.php
```

---

## 🔄 Rollback (Se Necessário)

Se algo der errado, restaurar o estado anterior:

```bash
# 1. Restaurar AuthController original
cd /opt/webserver/sites/prestadores
BACKUP_FILE=$(ls -t src/Controllers/AuthController.php.backup.* | head -n1)
cp "$BACKUP_FILE" src/Controllers/AuthController.php

# 2. Reload PHP-FPM
systemctl reload php8.3-fpm-prestadores

# 3. (Opcional) Reverter SQL - NÃO RECOMENDADO
# O SQL é idempotente e não precisa ser revertido
# Mas se necessário, fazer backup do banco antes de qualquer alteração
```

---

## 📊 Checklist de Validação

Após deployment, verificar:

- [ ] SQL executado sem erros
- [ ] 4 usuários criados/atualizados no banco
- [ ] ENUM da coluna `role` contém todos os valores necessários
- [ ] AuthController com debug ativo
- [ ] PHP-FPM recarregado
- [ ] OPcache limpo
- [ ] Logs PHP acessíveis
- [ ] Login de master@clinfec.com.br funciona
- [ ] Login de admin@clinfec.com.br funciona
- [ ] Login de gestor@clinfec.com.br funciona
- [ ] Login de usuario@clinfec.com.br funciona
- [ ] Sessão persiste após login
- [ ] Dashboard acessível após login
- [ ] Logs debug aparecem no error.log

---

## 📞 Próximos Passos

### 1. Após Deployment Bem-Sucedido:

✅ **Remover debug:**
```bash
# Restaurar AuthController original
cp src/Controllers/AuthController.php.backup.YYYYMMDD_HHMMSS \
   src/Controllers/AuthController.php
systemctl reload php8.3-fpm-prestadores
```

✅ **Informar QA:**
- Sistema pronto para retomar 47 testes
- 4 usuários disponíveis com permissões variadas
- Fornecer lista de credenciais (ver USUARIOS_TESTE_SISTEMA_PRESTADORES.md)

### 2. Se Login Ainda Falhar:

❌ **Manter debug ativo**  
❌ **Analisar logs detalhados**  
❌ **Identificar ponto exato de falha**  
❌ **Aplicar correção específica**  
❌ **Re-testar**  

---

## 📚 Documentação Adicional

- **Análise Completa:** `docs/SPRINT_67_ANALISE_E_CORRECOES.md`
- **Guia de Deployment:** `docs/SPRINT_67_GUIA_DEPLOYMENT.md`
- **Status Atual:** `docs/SPRINT_67_STATUS_ATUAL.md`
- **Usuários de Teste:** `docs/USUARIOS_TESTE_SISTEMA_PRESTADORES.md`

---

## ✅ Conclusão

Este pacote contém **TUDO** necessário para:

1. ✅ Corrigir o problema de ENUM na tabela usuarios
2. ✅ Criar usuários de teste padronizados
3. ✅ Ativar debug extensivo para diagnóstico
4. ✅ Testar login automaticamente
5. ✅ Validar sistema pronto para QA

**Tempo total estimado:** 5-10 minutos (deployment + testes)

**Suporte:** Ver documentação adicional em `docs/`

---

**Data:** 2025-11-16  
**Sprint:** 67  
**Objetivo:** LOGIN FUNCIONAL PARA QA  
**Status:** PRONTO PARA DEPLOYMENT ✅
