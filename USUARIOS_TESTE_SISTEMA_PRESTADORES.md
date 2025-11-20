# 👥 USUÁRIOS DE TESTE - SISTEMA PRESTADORES

**Sistema:** Clinfec Prestadores  
**URL:** https://prestadores.clinfec.com.br  
**Última Atualização:** 2025-11-16  
**Sprint:** 66-67  

---

## 📋 LISTA COMPLETA DE USUÁRIOS

### 1. Master User 👑
```
Email: master@clinfec.com.br
Senha: password
Role: master
Permissões: ACESSO TOTAL (todas funcionalidades)
```

**Uso em QA:**
- Testes de gestão completa
- Configurações do sistema
- Todas as 12 funcionalidades
- Validação de permissões master

---

### 2. Admin User 🔧
```
Email: admin@clinfec.com.br
Senha: admin123
Role: admin
Permissões: Gestão completa (exceto configurações master)
```

**Uso em QA:**
- Testes de gestão geral
- Usuários, projetos, financeiro
- 10 de 12 funcionalidades
- Validação de permissões admin

---

### 3. Gestor User 📊
```
Email: gestor@clinfec.com.br
Senha: password
Role: gestor (ou gerente conforme ENUM)
Permissões: Projetos, equipes, atividades, serviços
```

**Uso em QA:**
- Testes operacionais de gestão
- Projetos e equipes
- 8 de 12 funcionalidades
- Validação de permissões gestor

---

### 4. Usuario Básico 👤
```
Email: usuario@clinfec.com.br
Senha: password
Role: usuario
Permissões: Atividades, candidaturas (operações básicas)
```

**Uso em QA:**
- Testes de usuário final
- Operações básicas
- 4 de 12 funcionalidades
- Validação de permissões usuario

---

## 🔐 HASHES BCRYPT (Referência Técnica)

Para regenerar ou validar senhas:

```php
// Hash para 'password'
$hash_password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

// Hash para 'admin123'
$hash_admin123 = '$2y$10$VJL2WmMq9Kh7FHPqYG8P2.Y8ZHPqT5xQwE0pXk7nOmKm3F9F/R5Wa';

// Validar
password_verify('password', $hash_password);   // true
password_verify('admin123', $hash_admin123);  // true
```

---

## 📊 MATRIZ DE PERMISSÕES DETALHADA

```
┌────────────────────────┬────────┬───────┬────────┬─────────┐
│ Funcionalidade         │ Master │ Admin │ Gestor │ Usuario │
├────────────────────────┼────────┼───────┼────────┼─────────┤
│ 1. Dashboard           │   ✅   │  ✅   │   ✅   │   ✅   │
│ 2. Gestão Usuários     │   ✅   │  ✅   │   ❌   │   ❌   │
│ 3. Gestão Projetos     │   ✅   │  ✅   │   ✅   │   ❌   │
│ 4. Gestão Atividades   │   ✅   │  ✅   │   ✅   │   ✅   │
│ 5. Gestão Candidaturas │   ✅   │  ✅   │   ✅   │   ✅   │
│ 6. Gestão Equipes      │   ✅   │  ✅   │   ✅   │   ❌   │
│ 7. Gestão Serviços     │   ✅   │  ✅   │   ✅   │   ❌   │
│ 8. Gestão Notas Fiscais│   ✅   │  ✅   │   ✅   │   ❌   │
│ 9. Gestão Financeiro   │   ✅   │  ✅   │   ❌   │   ❌   │
│ 10. Notificações       │   ✅   │  ✅   │   ✅   │   ✅   │
│ 11. Configurações      │   ✅   │  ❌   │   ❌   │   ❌   │
│ 12. Relatórios         │   ✅   │  ✅   │   ✅   │   ❌   │
└────────────────────────┴────────┴───────┴────────┴─────────┘

Total de Funcionalidades Acessíveis:
- Master: 12/12 (100%)
- Admin: 10/12 (83%)
- Gestor: 8/12 (67%)
- Usuario: 4/12 (33%)
```

---

## 🧪 ROTEIRO DE TESTES QA

### Fase 1: Login (3 testes)
**Usuários:** Todos os 4

1. **Teste 1.1:** Acessar URL
   - URL: https://prestadores.clinfec.com.br
   - ✅ Esperado: Página de login carrega

2. **Teste 1.2:** Login Master
   - Email: master@clinfec.com.br
   - Senha: password
   - ✅ Esperado: Redirect para dashboard

3. **Teste 1.3:** Login Admin
   - Email: admin@clinfec.com.br
   - Senha: admin123
   - ✅ Esperado: Redirect para dashboard

4. **Teste 1.4:** Login Gestor
   - Email: gestor@clinfec.com.br
   - Senha: password
   - ✅ Esperado: Redirect para dashboard

5. **Teste 1.5:** Login Usuario
   - Email: usuario@clinfec.com.br
   - Senha: password
   - ✅ Esperado: Redirect para dashboard

### Fase 2: Dashboard (3 testes)
**Usuários:** Todos os 4
- Verificar widgets visíveis
- Verificar dados carregam
- Verificar menu adequado ao perfil

### Fase 3: Gestão Usuários (5 testes)
**Usuários:** Master, Admin
- Listar usuários
- Criar novo usuário
- Editar usuário
- Desativar usuário
- Deletar usuário

### Fases 4-12: Demais Funcionalidades
**Usuários:** Conforme matriz de permissões
- Testes CRUD completos
- Validações de permissão
- Fluxos end-to-end

---

## 🔧 COMANDOS ÚTEIS PARA QA

### Verificar Usuários no Banco:
```sql
SELECT id, nome, email, role, ativo, created_at 
FROM usuarios 
WHERE email LIKE '%@clinfec.com.br' 
ORDER BY role DESC;
```

### Regenerar Senha (se necessário):
```php
<?php
// Gerar novo hash
$novo_hash = password_hash('nova_senha', PASSWORD_DEFAULT);
echo $novo_hash;

// Atualizar no banco
// UPDATE usuarios SET senha = '$novo_hash' WHERE email = 'usuario@clinfec.com.br';
?>
```

### Verificar Logs de Login:
```bash
# No servidor VPS
ssh root@72.61.53.222
tail -100 /var/log/php8.3-fpm/error.log | grep -i "login"
```

---

## ⚠️ NOTAS IMPORTANTES

### 1. Senhas Temporárias
As senhas listadas são TEMPORÁRIAS para testes.
- **Master/Gestor/Usuario:** password
- **Admin:** admin123

Em produção real, trocar por senhas fortes.

### 2. ENUM Roles
A tabela usuarios tem ENUM de roles que deve incluir:
```sql
role ENUM('master', 'admin', 'gerente', 'gestor', 'usuario', 'financeiro')
```

Verificar com:
```sql
SHOW COLUMNS FROM usuarios LIKE 'role';
```

### 3. Hashes Válidos
Os hashes bcrypt fornecidos foram validados com password_verify().
Se login falhar, NÃO é problema de hash incorreto.

### 4. reCAPTCHA
Sistema usa Google reCAPTCHA v2.
Em desenvolvimento, está configurado para skip:
```php
'recaptcha' => [
    'enabled' => true,
    'skip_in_development' => true
]
```

---

## 📊 HISTÓRICO DE CRIAÇÃO

### Sprint 66:
- Criação inicial dos 4 usuários
- Problema: ENUM incompatível rejeitou 'master' e 'gestor'
- Resultado: Apenas admin@clinfec.com.br foi criado

### Sprint 67:
- QA criou manualmente os 3 usuários faltantes
- Migration 026 corrigiu ENUM para aceitar todos os roles
- Script sprint67_complete_fix.sql garante todos os 4 usuários

### Deployment Sprint 67:
- Script automatizado deploy_sprint67_to_vps.sh
- Cria/atualiza os 4 usuários com ON DUPLICATE KEY UPDATE
- Garante idempotência (pode rodar múltiplas vezes)

---

## 🎯 VALIDAÇÃO RÁPIDA

### Checklist Pós-Deployment:

- [ ] 4 usuários existem no banco
- [ ] Todos com ativo = 1
- [ ] Roles corretas (master, admin, gestor, usuario)
- [ ] Login master funciona
- [ ] Login admin funciona
- [ ] Login gestor funciona
- [ ] Login usuario funciona
- [ ] Dashboard acessível após login
- [ ] Permissões respeitadas conforme role

### Comando de Validação:
```bash
# Executar no servidor VPS
mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP \
      db_prestadores -e \
"SELECT 
    CONCAT('✅ ', nome) AS usuario,
    email,
    role,
    CASE WHEN ativo = 1 THEN 'Ativo' ELSE 'Inativo' END AS status
FROM usuarios 
WHERE email LIKE '%@clinfec.com.br' 
ORDER BY 
    CASE role
        WHEN 'master' THEN 1
        WHEN 'admin' THEN 2
        WHEN 'gestor' THEN 3
        WHEN 'gerente' THEN 4
        WHEN 'usuario' THEN 5
    END;"
```

---

## 🔗 REFERÊNCIAS

### Documentação Relacionada:
- `SPRINT_66_FIX_BUG7_LOGIN_CRITICO.md` - Análise Bug #7
- `SPRINT_67_ANALISE_E_CORRECOES.md` - Correções Sprint 67
- `SPRINT_67_GUIA_DEPLOYMENT.md` - Guia de deployment
- `database/sprint67_complete_fix.sql` - Script SQL completo

### Servidor:
- **IP:** 72.61.53.222
- **Path:** /opt/webserver/sites/prestadores
- **URL:** https://prestadores.clinfec.com.br

### Database:
- **Host:** localhost
- **User:** user_prestadores
- **Password:** rN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP
- **Database:** db_prestadores

---

**Criado:** Sprint 66  
**Atualizado:** Sprint 67  
**Status:** ✅ PRONTO PARA USO  
**Validado:** ⏳ Aguardando deployment em produção
