# 🚨 PLANO DE RECUPERAÇÃO COMPLETO - SISTEMA PRESTADORES

## 📊 SITUAÇÃO ATUAL (Resultado da Auditoria)

### Status Geral: **64% FUNCIONAL** (24/37 rotas OK)

**Data da Auditoria**: 10/11/2025  
**Método**: Teste automatizado de 37 rotas críticas  
**Ambiente**: https://prestadores.clinfec.com.br  
**Última Atualização**: Sprint 13 (migrations 001-015 executadas)

---

## 🎯 MÓDULOS - STATUS DETALHADO

### ✅ FUNCIONANDO 100% (6 módulos)
1. **Login/Autenticação** - Rotas: /login, /logout ✅
2. **Dashboard** - Rota: /dashboard ✅
3. **Empresas Tomadoras** - Rotas: /empresas-tomadoras, /create, /novo, /nova ✅
4. **Empresas Prestadoras** - Rotas: /empresas-prestadoras, /create, /novo ✅
5. **Serviços** - Rotas: /servicos, /create, /novo ✅
6. **Contratos** - Rotas: /contratos, /create, /novo ✅

### ❌ QUEBRADO 0% (3 módulos - 13 rotas falhando)
1. **Projetos** - HTTP 500 em:
   - /projetos
   - /projetos/create
   - /projetos/novo
   - /proj (alias)
   - /projects (alias)

2. **Atividades** - HTTP 500 em:
   - /atividades
   - /atividades/create
   - /atividades/nova
   - /ativ (alias)
   - /tasks (alias)

3. **Notas Fiscais** - HTTP 500 em:
   - /notas-fiscais
   - /nf (alias)
   - /invoices (alias)

### ⚠️ PARCIALMENTE FUNCIONANDO (1 módulo)
4. **Financeiro** - ✅ Rota principal OK (/financeiro), mas sub-rotas não testadas

### ✅ NOVOS MÓDULOS FUNCIONANDO (Sprint 13)
5. **Pagamentos** - /pagamentos ✅
6. **Custos** - /custos ✅
7. **Relatórios** - /relatorios ✅
8. **Perfil** - /perfil ✅
9. **Configurações** - /configuracoes ✅

---

## 🔍 DIAGNÓSTICO - CAUSA RAIZ DOS PROBLEMAS

### PROBLEMA 1: Over-Simplification das Queries (Projetos e Atividades)

#### SINTOMA:
- Rotas de Projetos e Atividades retornam HTTP 500

#### CAUSA IDENTIFICADA:
Em tentativa anterior de "corrigir" bugs, **SIMPLIFIQUEI DEMAIS** as queries SQL nos models, **REMOVENDO** JOINs e campos importantes.

#### EXEMPLO - Projeto.php (ANTES - CORRETO):
```php
$sql = "SELECT 
    p.*,
    et.nome_fantasia as tomadora_nome,
    ep.nome_fantasia as prestadora_nome,
    c.numero_contrato,
    u.nome as gestor_nome,
    pc.nome as categoria_nome,
    COUNT(DISTINCT pe.id) as total_etapas,
    COUNT(DISTINCT a.id) as total_atividades,
    DATEDIFF(p.data_fim_prevista, CURDATE()) as dias_restantes
FROM {$this->table} p
LEFT JOIN empresas_tomadoras et ON p.empresa_tomadora_id = et.id
LEFT JOIN empresas_prestadoras ep ON p.empresa_prestadora_id = ep.id
LEFT JOIN contratos c ON p.contrato_id = c.id
LEFT JOIN usuarios u ON p.gerente_id = u.id
LEFT JOIN projeto_categorias pc ON p.categoria_id = pc.id
LEFT JOIN projeto_etapas pe ON p.id = pe.projeto_id AND pe.deleted_at IS NULL
LEFT JOIN atividades a ON p.id = a.projeto_id AND a.deleted_at IS NULL
WHERE p.deleted_at IS NULL";
```

#### EXEMPLO - Projeto.php (DEPOIS - QUEBRADO):
```php
$sql = "SELECT 
    p.*,
    c.numero_contrato,
    DATEDIFF(p.data_fim_prevista, CURDATE()) as dias_restantes
FROM {$this->table} p
LEFT JOIN contratos c ON p.contrato_id = c.id
WHERE p.deleted_at IS NULL";
// ❌ REMOVIDOS: joins com empresas, usuários, categorias, etapas, atividades
// ❌ RESULTADO: Views tentam acessar $projeto['tomadora_nome'] → ERRO!
```

#### IMPACTO:
- Views esperam campos como `$projeto['tomadora_nome']`, `$projeto['prestadora_nome']`, `$projeto['gestor_nome']`
- Queries simplificadas NÃO retornam esses campos
- PHP Fatal Error: "Undefined array key" em TODAS as views de Projetos

### PROBLEMA 2: Models Faltando Métodos

Alguns models foram criados/modificados sem implementar TODOS os métodos que controllers e views esperam.

#### EXEMPLO - Projeto.php falta métodos:
- `getFases($projetoId)`
- `getMarcos($projetoId)`
- `getRiscos($projetoId)`
- `getMudancas($projetoId)`
- `getAnexos($projetoId)`
- `getHistorico($projetoId)`
- `getAlocacoes($projetoId)`

#### IMPACTO:
- Controllers tentam chamar `$this->projetoModel->getFases($id)`
- PHP Fatal Error: "Call to undefined method"

### PROBLEMA 3: Queries com Campos Não-Existentes

Queries tentam acessar colunas que não existem no banco (migration não executada ou nome diferente).

#### EXEMPLO:
```php
SELECT p.*, p.gerente_id, p.categoria_id, p.empresa_tomadora_id
// ❌ Se tabela só tem: gestor_projeto_id, category_id, tomadora_id
```

---

## 📋 PLANO DE RECUPERAÇÃO DETALHADO

### FASE 1: ✅ AUDITORIA COMPLETA (CONCLUÍDA)

**Tempo**: 1h  
**Status**: ✅ CONCLUÍDA

#### Ações Realizadas:
1. ✅ Lido relatório de testes completo (PDF)
2. ✅ Lido docs/SPRINT_1_2_3_COMPLETO.md
3. ✅ Lido docs/SPRINT_4_ATUALIZADO.md
4. ✅ Lido docs/SPRINT_5_COMPLETO.md
5. ✅ Lido docs/PLANEJAMENTO_SPRINTS_4-9.md
6. ✅ Lido SPRINT_8_EMERGENCY_FIXES_2025.md
7. ✅ Lido SPRINT_9_SUMMARY.md
8. ✅ Executado teste de 37 rotas
9. ✅ Identificado status atual: 64% funcional (24/37 OK)

#### Descobertas:
- **Controllers existem**: ProjetoController.php, AtividadeController.php, NotaFiscalController.php ✅
- **Views existem**: src/Views/projetos/, src/Views/atividades/ ✅
- **Rotas existem**: case 'projetos', case 'atividades' no index.php ✅
- **Problema NÃO é** arquivos faltando
- **Problema É**: queries simplificadas demais, métodos faltando nos models

---

### FASE 2: 🔄 DIAGNÓSTICO PRECISO (EM ANDAMENTO)

**Tempo**: 30min  
**Status**: 🔄 EM ANDAMENTO

#### Ações Necessárias:
1. ⏳ Ler código completo de src/Models/Projeto.php
2. ⏳ Ler código completo de src/Models/Atividade.php
3. ⏳ Ler código completo de src/Models/NotaFiscal.php
4. ⏳ Comparar com queries documentadas na Sprint 5-6
5. ⏳ Listar TODOS os métodos faltando
6. ⏳ Verificar estrutura da tabela `projetos` no banco
7. ⏳ Verificar estrutura da tabela `atividades` no banco
8. ⏳ Mapear campos esperados vs campos reais

#### Ferramentas:
```bash
# Verificar estrutura das tabelas
mysql -u u673902663_admin -p';>?I4dtn~2Ga' -D u673902663_prestadores \
  -e "DESCRIBE projetos"

# Verificar métodos existentes no model
grep -n "public function" src/Models/Projeto.php

# Verificar campos usados nas views
grep -r "\$projeto\['" src/Views/projetos/ | cut -d"[" -f2 | cut -d"'" -f2 | sort -u
```

---

### FASE 3: ⏳ CORREÇÃO DOS MODELS (PENDENTE)

**Tempo**: 2h  
**Prioridade**: 🔴 CRÍTICA  
**Status**: ⏳ PENDENTE

#### 3.1. Restaurar Projeto.php - Query Completa

**Arquivo**: `src/Models/Projeto.php`

**Ações**:
1. Abrir arquivo e localizar método `all()` ou `getAll()`
2. Restaurar query SQL COMPLETA com TODOS os JOINs:
   - ✅ LEFT JOIN empresas_tomadoras (nome_fantasia as tomadora_nome)
   - ✅ LEFT JOIN empresas_prestadoras (nome_fantasia as prestadora_nome)
   - ✅ LEFT JOIN contratos (numero_contrato)
   - ✅ LEFT JOIN usuarios (nome as gestor_nome)
   - ✅ LEFT JOIN projeto_categorias (nome as categoria_nome)
   - ✅ LEFT JOIN projeto_etapas (COUNT)
   - ✅ LEFT JOIN atividades (COUNT)

3. Adicionar métodos faltando:
```php
public function getFases($projetoId) {
    $sql = "SELECT * FROM projeto_etapas 
            WHERE projeto_id = :projeto_id AND deleted_at IS NULL
            ORDER BY ordem ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':projeto_id' => $projetoId]);
    return $stmt->fetchAll();
}

public function getMarcos($projetoId) { /* ... */ }
public function getRiscos($projetoId) { /* ... */ }
public function getMudancas($projetoId) { /* ... */ }
public function getAnexos($projetoId) { /* ... */ }
public function getHistorico($projetoId) { /* ... */ }
public function getAlocacoes($projetoId) { /* ... */ }
```

4. Verificar método `findById()` também tem JOINs

#### 3.2. Restaurar Atividade.php - Query Completa

**Arquivo**: `src/Models/Atividade.php`

**Ações**:
1. Localizar método `all()` ou `getAll()`
2. Restaurar query SQL COMPLETA:
   - ✅ LEFT JOIN projetos
   - ✅ LEFT JOIN servicos
   - ✅ LEFT JOIN usuarios (responsavel_id)
   - ✅ LEFT JOIN empresas_prestadoras

3. Adicionar métodos faltando:
```php
public function getProfissionais($atividadeId) { /* ... */ }
public function getCustos($atividadeId) { /* ... */ }
public function getHorasRegistradas($atividadeId) { /* ... */ }
```

#### 3.3. Restaurar NotaFiscal.php - Query Completa

**Arquivo**: `src/Models/NotaFiscal.php`

**Ações**:
1. Verificar se model existe e está completo
2. Restaurar query com JOINs necessários:
   - ✅ LEFT JOIN empresas_tomadoras
   - ✅ LEFT JOIN empresas_prestadoras
   - ✅ LEFT JOIN projetos
   - ✅ LEFT JOIN contratos

3. Implementar métodos CRUD completos

---

### FASE 4: ⏳ CORREÇÃO DOS CONTROLLERS (PENDENTE)

**Tempo**: 1h  
**Prioridade**: 🔴 CRÍTICA  
**Status**: ⏳ PENDENTE

#### 4.1. Verificar ProjetoController.php

**Arquivo**: `src/Controllers/ProjetoController.php`

**Ações**:
1. Verificar método `index()` não tem erros
2. Verificar método `show($id)` chama métodos corretos do model
3. Verificar método `create()` tem validações corretas
4. Verificar método `store()` salva dados corretamente
5. Garantir TODOS os métodos chamam model methods que EXISTEM

#### 4.2. Verificar AtividadeController.php

**Arquivo**: `src/Controllers/AtividadeController.php`

**Ações**:
1. Mesmas verificações de ProjetoController
2. Garantir integração com Projeto model
3. Verificar permissões de acesso

#### 4.3. Verificar NotaFiscalController.php

**Arquivo**: `src/Controllers/NotaFiscalController.php`

**Ações**:
1. Verificar TODOS os methods existem
2. Verificar models Cliente e Fornecedor são carregados corretamente
3. Verificar views existem

---

### FASE 5: ⏳ VALIDAÇÃO DE ROTAS (PENDENTE)

**Tempo**: 30min  
**Prioridade**: 🔴 CRÍTICA  
**Status**: ⏳ PENDENTE

#### Ações:
1. Executar `bash test_all_routes.sh` novamente
2. **Meta**: 37/37 rotas OK (100%)
3. Para cada rota falhando:
   - Capturar erro PHP exato
   - Corrigir
   - Re-testar

#### Teste Manual:
```bash
# Testar rota específica com detalhes
curl -v https://prestadores.clinfec.com.br/projetos

# Verificar logs PHP
tail -f /path/to/php-error.log
```

---

### FASE 6: ⏳ DEPLOY COMPLETO (PENDENTE)

**Tempo**: 30min  
**Prioridade**: 🔴 CRÍTICA  
**Status**: ⏳ PENDENTE

#### Arquivos para Deploy:
```
src/Models/Projeto.php           (corrigido)
src/Models/Atividade.php         (corrigido)
src/Models/NotaFiscal.php        (corrigido)
src/Controllers/ProjetoController.php    (verificado)
src/Controllers/AtividadeController.php  (verificado)
src/Controllers/NotaFiscalController.php (verificado)
```

#### Método de Deploy:
```bash
# Via FTP usando curl
curl -T src/Models/Projeto.php \
  ftp://ftp.clinfec.com.br/src/Models/Projeto.php \
  --user u673902663.genspark1:Genspark1@

# Ou criar script Python de deploy
python3 ftp_deploy_recovery.py
```

#### Pós-Deploy:
1. Limpar cache PHP (clear_cache.php)
2. Testar novamente todas as rotas
3. Verificar funcionamento manual de cada módulo

---

### FASE 7: ⏳ TESTE FINAL (PENDENTE)

**Tempo**: 1h  
**Prioridade**: 🔴 CRÍTICA  
**Status**: ⏳ PENDENTE

#### Testes CRUD Completos:

1. **Projetos**:
   - [ ] Listar projetos
   - [ ] Ver detalhes de projeto
   - [ ] Criar novo projeto
   - [ ] Editar projeto
   - [ ] Excluir projeto
   - [ ] Ver equipe do projeto
   - [ ] Ver etapas do projeto
   - [ ] Ver orçamento do projeto

2. **Atividades**:
   - [ ] Listar atividades
   - [ ] Ver detalhes de atividade
   - [ ] Criar nova atividade
   - [ ] Editar atividade
   - [ ] Excluir atividade
   - [ ] Ver custos da atividade

3. **Notas Fiscais**:
   - [ ] Listar notas fiscais
   - [ ] Ver detalhes de nota fiscal
   - [ ] Criar nova nota fiscal
   - [ ] Upload de XML
   - [ ] Upload de PDF
   - [ ] Editar nota fiscal
   - [ ] Excluir nota fiscal

#### Teste de Permissões:
- [ ] Master pode fazer TUDO
- [ ] Admin pode fazer QUASE tudo
- [ ] Gestor pode fazer operações de projeto
- [ ] Operacional tem acesso limitado

---

### FASE 8: ⏳ COMMIT E PR (PENDENTE)

**Tempo**: 30min  
**Prioridade**: 🟡 ALTA  
**Status**: ⏳ PENDENTE

#### Ações Git:
```bash
# Commitar TODAS as correções
cd /home/user/webapp && git add .
cd /home/user/webapp && git commit -m "fix: Recuperação completa sistema - 64% → 100% funcional

PROBLEMAS CORRIGIDOS:
- Restauradas queries SQL completas em Projeto.php e Atividade.php
- Adicionados métodos faltando em Models
- Corrigidos controllers ProjetoController, AtividadeController, NotaFiscalController
- Validadas TODAS as 37 rotas (100% sucesso)
- Deploy completo em produção

MÓDULOS RESTAURADOS:
- ✅ Projetos (5 rotas)
- ✅ Atividades (5 rotas)
- ✅ Notas Fiscais (3 rotas)

RESULTADO:
- Antes: 24/37 rotas OK (64%)
- Depois: 37/37 rotas OK (100%)

SPRINT: Recuperação Pós-Sprint 13
METODOLOGIA: SCRUM + PDCA
DOCUMENTAÇÃO: PLANO_RECUPERACAO_COMPLETO.md
"

# Criar Pull Request
cd /home/user/webapp && git push origin genspark_ai_developer

# Criar PR via GitHub CLI ou interface web
```

#### Mensagem do PR:
```markdown
# 🚨 RECUPERAÇÃO COMPLETA DO SISTEMA - 64% → 100%

## 📊 Situação Antes
- **Funcionalidade**: 64% (24/37 rotas OK)
- **Módulos Quebrados**: Projetos, Atividades, Notas Fiscais
- **Causa Raiz**: Queries SQL simplificadas demais, métodos faltando

## ✅ Correções Implementadas
1. **Restauradas queries SQL completas** em Projeto.php e Atividade.php
2. **Adicionados métodos faltando** nos Models
3. **Corrigidos controllers** para usar métodos corretos
4. **Validadas TODAS as rotas** (37/37 = 100%)

## 🎯 Resultado
- **Funcionalidade**: 100% (37/37 rotas OK)
- **Módulos**: TODOS funcionando
- **Testes**: 100% passando
- **Deploy**: Produção validada

## 📋 Arquivos Modificados
- src/Models/Projeto.php
- src/Models/Atividade.php
- src/Models/NotaFiscal.php
- src/Controllers/ProjetoController.php
- src/Controllers/AtividadeController.php
- src/Controllers/NotaFiscalController.php

## 🔗 Links
- **Produção**: https://prestadores.clinfec.com.br
- **Documentação**: PLANO_RECUPERACAO_COMPLETO.md
- **Testes**: test_all_routes.sh (37/37 OK)
```

---

## 📊 CRONOGRAMA DE EXECUÇÃO

| Fase | Tempo Estimado | Status | Prioridade |
|------|----------------|--------|------------|
| 1. Auditoria | 1h | ✅ CONCLUÍDA | 🔴 CRÍTICA |
| 2. Diagnóstico | 30min | 🔄 EM ANDAMENTO | 🔴 CRÍTICA |
| 3. Correção Models | 2h | ⏳ PENDENTE | 🔴 CRÍTICA |
| 4. Correção Controllers | 1h | ⏳ PENDENTE | 🔴 CRÍTICA |
| 5. Validação Rotas | 30min | ⏳ PENDENTE | 🔴 CRÍTICA |
| 6. Deploy | 30min | ⏳ PENDENTE | 🔴 CRÍTICA |
| 7. Teste Final | 1h | ⏳ PENDENTE | 🔴 CRÍTICA |
| 8. Commit/PR | 30min | ⏳ PENDENTE | 🟡 ALTA |
| **TOTAL** | **~6.5 horas** | | |

---

## 🎯 META FINAL

### OBJETIVO:
- **100% de funcionalidade** (37/37 rotas OK)
- **0 erros** em produção
- **Todos os módulos** operacionais
- **CRUD completo** funcionando para TODAS as entidades

### VALIDAÇÃO DE SUCESSO:
```bash
# Executar teste automatizado
cd /home/user/webapp && bash test_all_routes.sh

# Resultado esperado:
Total Tests: 37
Passed: 37
Failed: 0
Success Rate: 100%
```

---

## 📝 LIÇÕES APRENDIDAS

### ❌ O QUE NÃO FAZER:
1. **NUNCA** simplificar queries SQL sem verificar impacto nas views
2. **NUNCA** remover JOINs sem saber se campos são usados
3. **NUNCA** "corrigir" código sem testar TODAS as rotas depois
4. **NUNCA** assumir que "menos código = melhor código"

### ✅ O QUE FAZER:
1. **SEMPRE** ler documentação das Sprints antes de modificar
2. **SEMPRE** manter queries completas com TODOS os JOINs necessários
3. **SEMPRE** testar TODAS as rotas após qualquer mudança
4. **SEMPRE** verificar que views recebem TODOS os campos esperados
5. **SEMPRE** implementar TODOS os métodos que controllers precisam
6. **SEMPRE** fazer commit após cada correção validada

---

## 🚀 PRÓXIMA AÇÃO IMEDIATA

**AGORA**: Iniciar FASE 2 - Diagnóstico Preciso

**Comando**:
```bash
# Ler Models completos
cd /home/user/webapp
cat src/Models/Projeto.php
cat src/Models/Atividade.php
cat src/Models/NotaFiscal.php
```

**Objetivo**: Identificar EXATAMENTE quais métodos e queries faltam

---

**Documento criado**: 10/11/2025  
**Última atualização**: 10/11/2025  
**Status**: FASE 2 EM ANDAMENTO  
**Próximo update**: Após completar diagnóstico
