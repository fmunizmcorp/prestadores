# 👥 USUÁRIOS DO SISTEMA - CLINFEC PRESTADORES

**Sistema:** Gestão de Prestadores  
**URL:** https://prestadores.clinfec.com.br  
**Data:** 2025-11-08  
**Status:** ✅ ATUALIZADO

---

## 🔐 CREDENCIAIS DE ACESSO

### 1. USUÁRIO MASTER (Nível Máximo)

**Perfil:** Master - Acesso Total ao Sistema  
**Nome:** Administrador Master  
**E-mail:** `master@clinfec.com.br`  
**Senha:** `password`  
**Nível:** 100  

**Permissões:**
- ✅ Acesso total a TODOS os módulos
- ✅ Gerenciamento de usuários
- ✅ Configurações do sistema
- ✅ Visualização de logs e auditoria
- ✅ Exclusão de registros
- ✅ Alteração de configurações críticas

**URL de Login:**
```
https://prestadores.clinfec.com.br/login
```

---

### 2. USUÁRIO ADMINISTRADOR

**Perfil:** Admin - Administrador  
**Nome:** Administrador  
**E-mail:** `admin@clinfec.com.br`  
**Senha:** `password`  
**Nível:** 80  

**Permissões:**
- ✅ Gerenciamento de empresas (tomadoras e prestadoras)
- ✅ Gerenciamento de contratos
- ✅ Gerenciamento de serviços
- ✅ Gerenciamento de usuários (exceto master)
- ✅ Gestão financeira
- ✅ Relatórios completos
- ⚠️ Sem acesso a configurações críticas do sistema

**URL de Login:**
```
https://prestadores.clinfec.com.br/login
```

---

### 3. USUÁRIO GESTOR

**Perfil:** Gestor - Gerenciamento de Projetos  
**Nome:** Gestor  
**E-mail:** `gestor@clinfec.com.br`  
**Senha:** `password`  
**Nível:** 60  

**Permissões:**
- ✅ Gerenciamento de projetos
- ✅ Gerenciamento de atividades (vagas)
- ✅ Análise de candidaturas
- ✅ Visualização de empresas e contratos
- ✅ Relatórios de projetos
- ✅ Gestão de equipes
- ⚠️ Sem acesso a gestão financeira completa
- ⚠️ Sem acesso a gerenciamento de usuários

**URL de Login:**
```
https://prestadores.clinfec.com.br/login
```

---

## 🚨 IMPORTANTE - SEGURANÇA

### ⚠️ ALTERAÇÃO OBRIGATÓRIA DE SENHAS

**TODAS as senhas padrão são:** `password`

**VOCÊ DEVE ALTERAR IMEDIATAMENTE** após o primeiro acesso por segurança!

### Como Alterar a Senha:

1. Fazer login com as credenciais padrão
2. Ir em **Perfil** ou **Configurações**
3. Clicar em **Alterar Senha**
4. Inserir senha atual: `password`
5. Inserir nova senha forte (mínimo 8 caracteres)
6. Confirmar nova senha
7. Salvar

### Requisitos de Senha Forte:

- ✅ Mínimo 8 caracteres
- ✅ Pelo menos 1 letra maiúscula
- ✅ Pelo menos 1 letra minúscula
- ✅ Pelo menos 1 número
- ✅ Pelo menos 1 caractere especial (!@#$%^&*)

---

## 📊 HIERARQUIA DE PERFIS

```
MASTER (Nível 100)
├── Acesso total ao sistema
├── Gerencia ADMIN, GESTOR, USUARIO
└── Configurações críticas

ADMIN (Nível 80)
├── Gerencia empresas e contratos
├── Gerencia GESTOR, USUARIO
└── Sem acesso a configs críticas

GESTOR (Nível 60)
├── Gerencia projetos e atividades
├── Analisa candidaturas
└── Relatórios de projetos

USUARIO (Nível 40)
├── Acesso básico ao sistema
├── Visualização de dados
└── Sem permissões de gestão
```

---

## 🔗 URLS IMPORTANTES

### Produção
- **Login:** https://prestadores.clinfec.com.br/login
- **Dashboard:** https://prestadores.clinfec.com.br/dashboard
- **Empresas Tomadoras:** https://prestadores.clinfec.com.br/empresas-tomadoras
- **Empresas Prestadoras:** https://prestadores.clinfec.com.br/empresas-prestadoras
- **Contratos:** https://prestadores.clinfec.com.br/contratos
- **Serviços:** https://prestadores.clinfec.com.br/servicos
- **Projetos:** https://prestadores.clinfec.com.br/projetos
- **Atividades:** https://prestadores.clinfec.com.br/atividades
- **Financeiro:** https://prestadores.clinfec.com.br/financeiro

---

## 🧪 FLUXO DE TESTE

### Teste Completo do Sistema:

1. **Login como MASTER**
   ```
   URL: https://prestadores.clinfec.com.br/login
   Email: master@clinfec.com.br
   Senha: password
   ```
   - ✅ Verificar acesso ao Dashboard
   - ✅ Verificar menu completo
   - ✅ Testar criação de empresa tomadora
   - ✅ Testar criação de empresa prestadora

2. **Login como ADMIN**
   ```
   URL: https://prestadores.clinfec.com.br/login
   Email: admin@clinfec.com.br
   Senha: password
   ```
   - ✅ Verificar acesso limitado (sem configs críticas)
   - ✅ Testar criação de contrato
   - ✅ Testar gestão de serviços

3. **Login como GESTOR**
   ```
   URL: https://prestadores.clinfec.com.br/login
   Email: gestor@clinfec.com.br
   Senha: password
   ```
   - ✅ Verificar acesso a projetos
   - ✅ Testar criação de atividade
   - ✅ Testar análise de candidatura

---

## 💾 HASH DA SENHA PADRÃO

**Senha:** `password`  
**Hash (bcrypt):**
```
$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
```

Este hash é usado na migration `010_inserir_usuario_master.sql`

---

## 📋 CHECKLIST DE PRIMEIRO ACESSO

- [ ] Fazer login com cada um dos 3 usuários
- [ ] Alterar TODAS as senhas padrão
- [ ] Verificar permissões de cada perfil
- [ ] Testar criação de empresa tomadora
- [ ] Testar criação de empresa prestadora
- [ ] Testar criação de contrato
- [ ] Testar criação de serviço
- [ ] Verificar módulo financeiro
- [ ] Verificar sistema de atividades
- [ ] Confirmar que redirects funcionam corretamente

---

## 🔧 TROUBLESHOOTING

### Problema: Não consegue fazer login

**Solução:**
1. Verificar se as migrations foram executadas:
   ```sql
   SELECT * FROM usuarios WHERE email = 'master@clinfec.com.br';
   ```
2. Se usuário não existir, executar migration 010
3. Verificar se senha está correta: `password`

### Problema: Redirect após login não funciona

**Solução:**
1. Verificar arquivo `.htaccess` na raiz
2. Confirmar que `BASE_URL` está definida corretamente em `public/index.php`
3. Verificar logs do PHP para erros

### Problema: Permissões incorretas

**Solução:**
1. Verificar campo `role` na tabela `usuarios`
2. Confirmar que valor é: `master`, `admin`, `gestor` ou `usuario`
3. Atualizar se necessário:
   ```sql
   UPDATE usuarios SET role = 'master' WHERE email = 'master@clinfec.com.br';
   ```

---

## 📞 SUPORTE

**Em caso de problemas:**
1. Verificar logs do PHP: `/var/log/php-fpm/error.log`
2. Verificar logs do sistema: `logs/error.log`
3. Verificar configuração do banco de dados: `config/database.php`
4. Consultar documentação: `README.md`, `PDCA_REDIRECT_FIX_2025.md`

---

**Documento atualizado em:** 2025-11-08  
**Próxima atualização:** Após alteração de senhas  
**Status:** ✅ PRONTO PARA USO
