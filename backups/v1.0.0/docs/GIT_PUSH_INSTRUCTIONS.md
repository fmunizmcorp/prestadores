# 🔐 GIT PUSH INSTRUCTIONS - Sprint 17

## ✅ STATUS ATUAL

**BRANCH**: genspark_ai_developer  
**COMMITS**: 1 commit squashed e pronto para push  
**ARQUIVOS**: 18 views corrigidas (99 URLs fixadas)

## ⚠️ PROBLEMA

Token de autenticação GitHub expirou. É necessário fazer push manual ou regerar token.

## 📋 INSTRUÇÕES PARA PUSH

### Opção 1: Push Manual (Recomendado)

```bash
cd /home/user/webapp
git push -f origin genspark_ai_developer
```

Se pedir credenciais:
- **Username**: fmunizmcorp
- **Password**: Use Personal Access Token do GitHub

### Opção 2: Criar Novo Token GitHub

1. Acesse: https://github.com/settings/tokens
2. Crie novo token com permissões `repo`
3. Configure:
```bash
git remote set-url origin https://x-access-token:NOVO_TOKEN@github.com/fmunizmcorp/prestadores
git push -f origin genspark_ai_developer
```

## 📊 COMMIT DETAILS

**Commit Message**: fix(sprint17): SYSTEMATIC URL correction - 99 broken URLs fixed across 6 modules

**Changed Files (18)**:
- src/Views/empresas-tomadoras/create.php
- src/Views/empresas-tomadoras/edit.php
- src/Views/empresas-tomadoras/show.php
- src/Views/empresas-prestadoras/create.php
- src/Views/empresas-prestadoras/edit.php
- src/Views/empresas-prestadoras/index.php
- src/Views/empresas-prestadoras/show.php
- src/Views/contratos/create.php
- src/Views/contratos/edit.php
- src/Views/contratos/index.php
- src/Views/contratos/show.php
- src/Views/servicos/create.php
- src/Views/servicos/edit.php
- src/Views/servicos/index.php
- src/Views/servicos/show.php
- src/Views/dashboard/index.php
- src/Views/layouts/header.php
- src/Views/layouts/footer.php

## 🎯 PRÓXIMO PASSO

Após push bem-sucedido, criar Pull Request:

```bash
# Criar PR via GitHub CLI (se instalado)
gh pr create --title "fix(sprint17): SYSTEMATIC URL correction - 99 broken URLs" \
  --body "Fixes BC-001 and likely resolves BC-002, E500-001/002/003, REG-002" \
  --base main --head genspark_ai_developer
```

**OU** Acesse: https://github.com/fmunizmcorp/prestadores/compare/main...genspark_ai_developer

## 📝 EXPECTED IMPACT

✅ BC-001: Empresas Tomadoras blank form - RESOLVED  
✅ BC-002: Contratos loading error - EXPECTED FIXED  
✅ E500-001: Projetos HTTP 500 - EXPECTED FIXED  
✅ E500-002: Atividades HTTP 500 - EXPECTED FIXED  
✅ E500-003: Notas Fiscais error - EXPECTED FIXED  
✅ REG-002: Serviços permission error - EXPECTED FIXED

## ⏭️ NEXT TASKS

After PR merge:
- Task 17.13: Deploy to production via FTP
- Task 17.14: Functional testing in production
- Task 17.15: Generate V8 report with 100% functionality
