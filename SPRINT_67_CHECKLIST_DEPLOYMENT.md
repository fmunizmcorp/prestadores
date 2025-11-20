# ✅ SPRINT 67 - CHECKLIST DE DEPLOYMENT

## 🎯 PRÉ-DEPLOYMENT

### Preparação
- [ ] Acesso SSH ao servidor (72.61.53.222) confirmado
- [ ] Credenciais do banco validadas
- [ ] Backup completo do banco de dados criado
- [ ] Backup do código atual criado
- [ ] Pacote `sprint67_deployment_package.tar.gz` baixado

---

## 🚀 DEPLOYMENT

### Passo 1: Upload do Pacote
```bash
scp sprint67_deployment_package.tar.gz root@72.61.53.222:/tmp/
```
- [ ] Upload concluído sem erros

### Passo 2: Conexão ao Servidor
```bash
ssh root@72.61.53.222
```
- [ ] Conexão estabelecida
- [ ] Terminal pronto

### Passo 3: Extração do Pacote
```bash
cd /tmp
tar -xzf sprint67_deployment_package.tar.gz
cd deployment_package/scripts
chmod +x *.sh
```
- [ ] Extração concluída
- [ ] Scripts executáveis

### Passo 4: Execução do Deployment
```bash
./deploy_sprint67_to_vps.sh
```
- [ ] SQL executado sem erros
- [ ] Usuários criados/atualizados
- [ ] AuthController backup criado
- [ ] Debug ativado
- [ ] PHP-FPM recarregado
- [ ] OPcache limpo

### Passo 5: Validação
```bash
./quick_validate.sh
```
- [ ] ✅ Arquivos verificados
- [ ] ✅ ENUM atualizado
- [ ] ✅ 4 usuários encontrados
- [ ] ✅ PHP-FPM rodando
- [ ] ✅ Logs acessíveis

---

## 🧪 TESTES

### Teste Manual (Cada Usuário)

#### Teste 1: Master
```bash
curl -X POST https://prestadores.clinfec.com.br/?page=login \
     -d 'email=master@clinfec.com.br&senha=password' \
     -c /tmp/cookies_master.txt -v
```
- [ ] HTTP 302 (redirect)
- [ ] Location: dashboard
- [ ] Cookie PHPSESSID recebido

#### Teste 2: Admin
```bash
curl -X POST https://prestadores.clinfec.com.br/?page=login \
     -d 'email=admin@clinfec.com.br&senha=admin123' \
     -c /tmp/cookies_admin.txt -v
```
- [ ] HTTP 302 (redirect)
- [ ] Location: dashboard
- [ ] Cookie PHPSESSID recebido

#### Teste 3: Gestor
```bash
curl -X POST https://prestadores.clinfec.com.br/?page=login \
     -d 'email=gestor@clinfec.com.br&senha=password' \
     -c /tmp/cookies_gestor.txt -v
```
- [ ] HTTP 302 (redirect)
- [ ] Location: dashboard
- [ ] Cookie PHPSESSID recebido

#### Teste 4: Usuário
```bash
curl -X POST https://prestadores.clinfec.com.br/?page=login \
     -d 'email=usuario@clinfec.com.br&senha=password' \
     -c /tmp/cookies_usuario.txt -v
```
- [ ] HTTP 302 (redirect)
- [ ] Location: dashboard
- [ ] Cookie PHPSESSID recebido

### Teste Automatizado
```bash
./test_login.sh
```
- [ ] 4/4 testes passaram
- [ ] Relatório gerado
- [ ] Sem erros

---

## 🔍 ANÁLISE DE LOGS

### Verificar Logs Durante Login
```bash
tail -f /var/log/php8.3-fpm/error.log
```

**Procurar por:**
- [ ] `SPRINT 67 DEBUG - LOGIN ATTEMPT`
- [ ] `User FOUND in database`
- [ ] `Password verification result: SUCCESS ✅`
- [ ] `Session created successfully`
- [ ] `✅ Session persisted`
- [ ] `Redirecting to dashboard`

### Logs Esperados (Sucesso)
```
========== SPRINT 67 DEBUG - LOGIN ATTEMPT ==========
  - Email: master@clinfec.com.br
  - Password length: 8
DEBUG: User FOUND in database
  - User ID: 123
  - Password hash (first 20 chars): $2y$10$...
DEBUG: Password verification result: SUCCESS ✅
DEBUG: Session created successfully
  - user_id: 123
  - usuario_nome: Master User
  - Session ID after: abc123...
  ✅ Session persisted
DEBUG: Redirecting to dashboard
```

---

## 🎯 PÓS-DEPLOYMENT (SE LOGIN FUNCIONOU)

### Remoção do Debug
```bash
cd /opt/webserver/sites/prestadores
BACKUP_FILE=$(ls -t src/Controllers/AuthController.php.backup.* | head -n1)
cp "$BACKUP_FILE" src/Controllers/AuthController.php
systemctl reload php8.3-fpm-prestadores
```
- [ ] Backup restaurado
- [ ] PHP-FPM recarregado
- [ ] Debug removido

### Validação Final
- [ ] Login de master funciona (sem debug)
- [ ] Login de admin funciona (sem debug)
- [ ] Login de gestor funciona (sem debug)
- [ ] Login de usuario funciona (sem debug)

### Limpeza
```bash
rm -rf /tmp/deployment_package
rm /tmp/sprint67_deployment_package.tar.gz
rm /tmp/cookies_*.txt
```
- [ ] Arquivos temporários removidos

---

## 📋 INFORMAR QA

### Preparar Email/Mensagem
```
Assunto: ✅ Sistema Prestadores - Login Restaurado - Pronto para Retomar Testes

Equipe QA,

O sistema está pronto para retomar os testes!

🎯 CORREÇÃO APLICADA:
- Problema de ENUM na tabela usuarios corrigido
- 4 usuários de teste criados/atualizados com senhas padronizadas
- Login funcionando para todos os perfis

👥 USUÁRIOS DE TESTE DISPONÍVEIS:

1. master@clinfec.com.br / password (master) - 100% permissões
2. admin@clinfec.com.br / admin123 (admin) - 83% permissões
3. gestor@clinfec.com.br / password (gestor) - 67% permissões
4. usuario@clinfec.com.br / password (usuario) - 33% permissões

📊 PRÓXIMOS PASSOS:
- Retomar 47 testes em 12 fases
- Iniciar pela Fase 1 (Autenticação)
- Reportar qualquer problema encontrado

📄 DOCUMENTAÇÃO:
- Detalhes: USUARIOS_TESTE_SISTEMA_PRESTADORES.md
- Roadmap: 47 testes mapeados em 12 fases

Sistema testado e validado. Bons testes!
```

- [ ] Email/mensagem preparado
- [ ] Credenciais incluídas
- [ ] Documentação referenciada
- [ ] Enviado para QA

---

## 🆘 SE LOGIN AINDA FALHAR

### Diagnóstico

#### 1. Verificar Logs
```bash
tail -100 /var/log/php8.3-fpm/error.log | grep "SPRINT 67"
```
- [ ] Logs encontrados
- [ ] Ponto de falha identificado

#### 2. Possíveis Causas

**Se: "User NOT FOUND"**
- [ ] Verificar se usuários existem no banco
- [ ] Executar: `SELECT * FROM usuarios WHERE email LIKE '%@clinfec.com.br'`

**Se: "Password verification FAILED"**
- [ ] Verificar hash no banco: `SELECT LEFT(senha, 50) FROM usuarios WHERE email='master@clinfec.com.br'`
- [ ] Hash deve começar com `$2y$10$`
- [ ] Re-executar SQL de correção

**Se: "Session NOT persisted"**
- [ ] Verificar permissões: `ls -ld /var/lib/php/sessions/`
- [ ] Deve ser: `drwx-wx-wt` (1733) owner `www-data`
- [ ] Verificar php.ini: `session.save_path`

**Outro erro:**
- [ ] Copiar stacktrace completo
- [ ] Analisar erro específico
- [ ] Aplicar correção

#### 3. Rollback (Se Necessário)
```bash
cd /opt/webserver/sites/prestadores
BACKUP_FILE=$(ls -t src/Controllers/AuthController.php.backup.* | head -n1)
cp "$BACKUP_FILE" src/Controllers/AuthController.php
systemctl reload php8.3-fpm-prestadores
```
- [ ] Sistema restaurado ao estado anterior

---

## 📊 CHECKLIST FINAL

### Antes de Informar QA
- [ ] Deployment executado com sucesso
- [ ] 4 usuários de teste validados
- [ ] Login funcional para todos os perfis
- [ ] Debug removido (ou mantido se necessário)
- [ ] Logs limpos sem erros críticos
- [ ] Documentação disponível
- [ ] Email para QA preparado

### Documentação Atualizada
- [ ] Sprint 67 marcado como CONCLUÍDO
- [ ] Solução final documentada
- [ ] Problemas encontrados registrados
- [ ] Lições aprendidas anotadas

---

## 🎉 CONCLUSÃO

**Se todos os checkboxes acima estão marcados:**

✅ DEPLOYMENT COMPLETO E VALIDADO!  
✅ Sistema pronto para QA retomar 47 testes!  
✅ Sprint 67 CONCLUÍDO com sucesso!  

---

## 📞 CONTATOS E REFERÊNCIAS

**Documentação:**
- `SPRINT_67_RESUMO_EXECUTIVO_FINAL.md`
- `SPRINT_67_GUIA_EXECUTIVO_DEPLOYMENT.md`
- `deployment_package/README_DEPLOYMENT.md`

**PR GitHub:**
- https://github.com/fmunizmcorp/prestadores/pull/7

**Commits:**
- 71f1f14 (docs)
- 012de96 (deployment package)
- 14742dc (executive summary)

---

**Data:** 2025-11-16  
**Sprint:** 67  
**Versão:** 1.0  

**USO:**
□ Imprimir este checklist  
□ Marcar cada item durante execução  
□ Manter como registro do deployment
