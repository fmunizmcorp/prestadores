# PLANEJAMENTO SPRINT 32

## 🎯 OBJETIVO

Ativar o sistema no servidor e corrigir os 3 problemas principais identificados nos relatórios de teste: Dashboard vazio, Empresas Tomadoras em branco, e erro ao carregar Contratos.

---

## 📋 PRÉ-REQUISITO (BLOCKER)

### ⚠️ Deploy Manual Obrigatório (10 minutos)

**Status:** ⏳ **BLOQUEADOR** - Sprint 32 não pode iniciar sem isso  
**Responsável:** Usuário  
**Tempo:** 10 minutos  
**Guia:** Ver `ACAO_MANUAL_URGENTE.md`

**Checklist:**
- [ ] Acessar Hostinger File Manager
- [ ] Renomear `public/index.php` → `index.php.OLD_CACHE`
- [ ] Copiar `public/index_sprint31.php` → `public/index.php`
- [ ] Deletar `src/DatabaseMigration.php`
- [ ] Atualizar `public/.htaccess` com `.htaccess_nocache`
- [ ] Limpar cache no hPanel (Advanced → Clear website cache)
- [ ] Aguardar 2-3 minutos
- [ ] Testar acesso: http://clinfec.com.br/prestadores
- [ ] Validar que erro DatabaseMigration sumiu

**Critério de Aceitação:** Sistema carrega sem erro `Database::exec() not found`

---

## 🎯 BACKLOG DO SPRINT 32

### FASE 1: Validação e Testes (30 min)

#### US-32.1: Validar Acesso ao Sistema ✅
**Prioridade:** CRÍTICA  
**Tempo estimado:** 15 minutos  
**Dependência:** Deploy manual concluído

**Tarefas:**
- [ ] Acessar http://clinfec.com.br/prestadores
- [ ] Testar login com admin@clinfec.com.br
- [ ] Testar login com master@clinfec.com.br
- [ ] Testar login com gestor@clinfec.com.br
- [ ] Verificar redirecionamento para Dashboard
- [ ] Confirmar menu lateral carregando
- [ ] Confirmar header com nome do usuário

**Critérios de Aceitação:**
- Login funciona para os 3 usuários
- Sem erros 500/404
- Interface carrega completamente

#### US-32.2: Executar Testes Automáticos
**Prioridade:** ALTA  
**Tempo estimado:** 15 minutos

**Tarefas:**
- [ ] Executar `python3 scripts/test_system_access.py`
- [ ] Executar `python3 scripts/check_database_structure.py`
- [ ] Verificar logs de erro do servidor
- [ ] Documentar todos os problemas encontrados

**Critérios de Aceitação:**
- Relatório de testes gerado
- Problemas priorizados por severidade

---

### FASE 2: Correção Dashboard (2 horas)

#### US-32.3: Analisar Dashboard Vazio
**Prioridade:** ALTA  
**Tempo estimado:** 30 minutos

**Tarefas:**
- [ ] Ler código de `src/Controllers/DashboardController.php`
- [ ] Verificar queries SQL sendo executadas
- [ ] Verificar se dados estão no banco
- [ ] Identificar problema na view ou no controller
- [ ] Documentar causa raiz

**Critérios de Aceitação:**
- Causa raiz identificada e documentada
- Plano de correção definido

#### US-32.4: Implementar Cards de Resumo
**Prioridade:** ALTA  
**Tempo estimado:** 1 hora

**Tarefas:**
- [ ] Card "Total de Empresas Tomadoras" com COUNT
- [ ] Card "Total de Contratos Ativos" com COUNT + filtro status='ativo'
- [ ] Card "Atestados Pendentes" com COUNT + filtro status='emitido'
- [ ] Card "Faturas a Vencer" com COUNT + filtro data_vencimento > NOW()
- [ ] Adicionar ícones e cores nos cards
- [ ] Implementar links para as telas correspondentes

**Critérios de Aceitação:**
- 4 cards funcionais no Dashboard
- Números corretos vindos do banco
- Layout responsivo

#### US-32.5: Adicionar Gráficos ao Dashboard
**Prioridade:** MÉDIA  
**Tempo estimado:** 30 minutos

**Tarefas:**
- [ ] Instalar Chart.js ou biblioteca similar
- [ ] Gráfico de barras: Contratos por mês
- [ ] Gráfico de pizza: Status dos contratos
- [ ] Gráfico de linha: Evolução de faturamento
- [ ] Adicionar filtros de período

**Critérios de Aceitação:**
- 3 gráficos funcionais
- Dados dinâmicos do banco
- Visualmente agradável

---

### FASE 3: Correção Empresas Tomadoras (2 horas)

#### US-32.6: Analisar Formulário em Branco
**Prioridade:** ALTA  
**Tempo estimado:** 30 minutos

**Tarefas:**
- [ ] Ler código de `src/Controllers/EmpresaTomadoraController.php`
- [ ] Verificar método `create()` e `store()`
- [ ] Verificar view `views/empresas-tomadoras/form.php`
- [ ] Testar manualmente o formulário
- [ ] Identificar se é problema de roteamento, controller ou view
- [ ] Documentar causa raiz

**Critérios de Aceitação:**
- Causa raiz identificada
- Plano de correção definido

#### US-32.7: Implementar Formulário Completo
**Prioridade:** ALTA  
**Tempo estimado:** 1 hora

**Tarefas:**
- [ ] Seção "Dados Básicos": razão social, nome fantasia, CNPJ
- [ ] Seção "Endereço": CEP (busca automática), logradouro, número, complemento, bairro, cidade, estado
- [ ] Seção "Contatos": emails (principal, financeiro, projetos), telefones, whatsapp, site
- [ ] Seção "Financeiro": dia fechamento, dia pagamento, forma pagamento, dados bancários
- [ ] Seção "Logo": upload de imagem
- [ ] Validação frontend e backend
- [ ] Máscaras para CNPJ, CEP, telefone

**Critérios de Aceitação:**
- Formulário completo com todos os campos
- Validação funcionando
- Máscaras aplicadas
- Busca CEP automática
- Salva corretamente no banco

#### US-32.8: Implementar Listagem de Empresas
**Prioridade:** MÉDIA  
**Tempo estimado:** 30 minutos

**Tarefas:**
- [ ] Tabela com colunas: ID, Razão Social, CNPJ, Cidade/UF, Status, Ações
- [ ] Paginação (20 itens por página)
- [ ] Busca por razão social ou CNPJ
- [ ] Filtro por status (ativo/inativo)
- [ ] Botões: Editar, Ver detalhes, Excluir (soft delete)
- [ ] Ordenação por colunas

**Critérios de Aceitação:**
- Listagem funcional
- Busca e filtros operacionais
- Paginação correta
- Ações funcionando

---

### FASE 4: Correção Contratos (2 horas)

#### US-32.9: Analisar Erro ao Carregar Contratos
**Prioridade:** ALTA  
**Tempo estimado:** 30 minutos

**Tarefas:**
- [ ] Ler código de `src/Controllers/ContratoController.php`
- [ ] Verificar método `index()` e `create()`
- [ ] Verificar relacionamentos (foreign keys)
- [ ] Verificar se tabelas relacionadas existem (empresas_tomadoras, empresas_prestadoras)
- [ ] Testar query SQL diretamente no banco
- [ ] Identificar causa raiz
- [ ] Documentar problema e solução

**Critérios de Aceitação:**
- Causa raiz identificada
- Query SQL corrigida ou controller ajustado

#### US-32.10: Implementar Formulário de Contratos
**Prioridade:** ALTA  
**Tempo estimado:** 1 hora

**Tarefas:**
- [ ] Campo "Número do Contrato" (auto-gerado ou manual)
- [ ] Select "Empresa Tomadora" (busca dinâmica)
- [ ] Select "Empresa Prestadora" (busca dinâmica)
- [ ] Campo "Descrição" e "Objeto"
- [ ] Campos de data: início e fim da vigência
- [ ] Campos de valor: total e executado
- [ ] Select "Status": rascunho, ativo, suspenso, encerrado, cancelado
- [ ] Upload "Arquivo do Contrato" (PDF)
- [ ] Campo "Observações"
- [ ] Validação de datas (fim > início)
- [ ] Validação de valores (executado <= total)

**Critérios de Aceitação:**
- Formulário completo funcional
- Validações operando
- Selects com busca dinâmica
- Upload de arquivo funcional
- Salva corretamente no banco

#### US-32.11: Implementar Listagem de Contratos
**Prioridade:** MÉDIA  
**Tempo estimado:** 30 minutos

**Tarefas:**
- [ ] Tabela com: Número, Tomadora, Prestadora, Vigência, Valor, Status, Ações
- [ ] Paginação
- [ ] Busca por número ou empresa
- [ ] Filtro por status
- [ ] Badge colorido para status
- [ ] Indicador de contratos vencendo (próximos 30 dias)
- [ ] Botões: Ver detalhes, Editar, Atestados, Documentos

**Critérios de Aceitação:**
- Listagem completa
- Filtros e busca funcionais
- Indicadores visuais de status
- Navegação para telas relacionadas

---

### FASE 5: Manutenção do Banco de Dados (1 hora)

#### US-32.12: Otimização de Índices
**Prioridade:** MÉDIA  
**Tempo estimado:** 30 minutos

**Tarefas:**
- [ ] Analisar queries mais executadas
- [ ] Identificar colunas sem índice que precisam
- [ ] Criar índices em:
  - `empresas_tomadoras.razao_social`
  - `contratos.numero_contrato`
  - `contratos.data_inicio, data_fim`
  - `atestados.mes_referencia, ano_referencia`
- [ ] Testar performance antes e depois
- [ ] Documentar mudanças

**Critérios de Aceitação:**
- Índices criados
- Performance melhorada (medida)
- Documentação atualizada

#### US-32.13: Criação de Views de Relatório
**Prioridade:** BAIXA  
**Tempo estimado:** 30 minutos

**Tarefas:**
- [ ] View `vw_contratos_resumo`: contrato + empresas + valores
- [ ] View `vw_atestados_faturamento`: atestados + faturas + valores
- [ ] View `vw_documentos_vencimento`: documentos próximos do vencimento
- [ ] Documentar uso das views

**Critérios de Aceitação:**
- 3 views criadas
- Testadas e funcionais
- Documentação completa

---

## 📊 ESTIMATIVAS

| Fase | Tempo Estimado | Prioridade |
|------|----------------|------------|
| **BLOCKER: Deploy Manual** | 10 min | CRÍTICA |
| Fase 1: Validação | 30 min | CRÍTICA |
| Fase 2: Dashboard | 2h | ALTA |
| Fase 3: Empresas | 2h | ALTA |
| Fase 4: Contratos | 2h | ALTA |
| Fase 5: Banco de Dados | 1h | MÉDIA |
| **TOTAL** | **7h 40min** | - |

---

## ✅ DEFINITION OF DONE (Sprint 32)

### Critérios Globais:
- [ ] Deploy manual concluído (BLOCKER)
- [ ] Sistema acessível sem erros
- [ ] Dashboard com 4 cards + 3 gráficos funcionais
- [ ] Empresas Tomadoras: formulário completo + listagem funcional
- [ ] Contratos: formulário completo + listagem funcional
- [ ] Banco de dados otimizado (índices)
- [ ] Todos os testes passando
- [ ] Código commitado no git
- [ ] Pull Request atualizado
- [ ] Documentação atualizada
- [ ] Usuário final pode usar o sistema sem erros

### Critérios de Qualidade:
- [ ] Zero erros 500 no servidor
- [ ] Zero erros JavaScript no console
- [ ] Tempo de resposta < 1s para queries
- [ ] Interface responsiva (desktop + mobile)
- [ ] Validações funcionando (frontend + backend)
- [ ] Foreign keys íntegras
- [ ] Soft delete implementado onde necessário

---

## 🚀 VELOCITY E CAPACIDADE

### Velocidade Histórica:
- Sprint 31: 8 horas (setup banco de dados)
- Sprint 30: 6 horas (tentativas cache)
- Sprint 29: 4 horas (análise cache)
- **Média:** 6 horas por sprint

### Capacidade Sprint 32:
- **Tempo disponível:** 8 horas (1 dia de trabalho)
- **Tempo estimado:** 7h 40min
- **Folga:** 20 minutos (buffer)
- **Viabilidade:** ✅ ALTA

---

## 🔄 RISCOS E MITIGAÇÕES

| Risco | Probabilidade | Impacto | Mitigação |
|-------|---------------|---------|-----------|
| Deploy manual não realizado | ALTA | ALTO | Fornecer guia visual de 10 min |
| Cache PHP ainda ativo | MÉDIA | ALTO | Aguardar 5 min, reiniciar PHP no hPanel |
| Estrutura do banco diferente | BAIXA | MÉDIO | Scripts de verificação já criados |
| Problemas de foreign keys | BAIXA | MÉDIO | Verificar relacionamentos antes de criar |
| Performance lenta | BAIXA | BAIXO | Índices já identificados para criação |

---

## 📝 DAILY SCRUM (CHECKPOINTS)

### Checkpoint 1 (30 min):
- Deploy manual concluído?
- Sistema acessível?
- Login funciona?
- **Decisão:** GO / NO-GO para continuar

### Checkpoint 2 (2h 30min):
- Dashboard concluído?
- Cards mostrando dados corretos?
- **Decisão:** Seguir para Empresas ou ajustar?

### Checkpoint 3 (4h 30min):
- Empresas Tomadoras funcionais?
- Formulário salvando no banco?
- **Decisão:** Seguir para Contratos ou ajustar?

### Checkpoint 4 (6h 30min):
- Contratos funcionais?
- Listagem carregando?
- **Decisão:** Seguir para otimização ou encerrar?

### Checkpoint Final (7h 40min):
- Todos os critérios DoD atendidos?
- Testes passando?
- Pronto para produção?
- **Decisão:** Encerrar Sprint 32 ou criar Sprint 33?

---

## 🎯 MÉTRICAS DE SUCESSO

### KPIs do Sprint 32:
1. **Taxa de Conclusão:** 100% das US críticas
2. **Taxa de Bugs:** 0 bugs críticos em produção
3. **Performance:** Tempo de resposta < 1s
4. **Cobertura:** 3 módulos principais funcionais
5. **Satisfação:** Sistema usável pelo usuário final

### Métricas Técnicas:
- **Queries otimizadas:** +5 índices criados
- **Código limpo:** 0 warnings PSR
- **Documentação:** 100% das funções documentadas
- **Testes:** 90% de cobertura

---

## 📞 CONTATO E SUPORTE

### Em caso de bloqueios:
1. Consultar `SPRINT_31_COMPLETO.md` para contexto
2. Executar scripts de verificação
3. Verificar logs do servidor
4. Documentar problema e solicitar ajuda

### Scripts úteis:
```bash
# Verificar banco de dados
python3 scripts/check_database_structure.py

# Sincronizar código + banco
python3 scripts/sync_database_with_code.py

# Testar acesso ao sistema
python3 scripts/test_system_access.py
```

---

## ✅ APROVAÇÃO PARA INICIAR

### Pré-requisitos:
- [x] Sprint 31 concluído (banco instalado)
- [x] Documentação completa
- [x] Scripts de manutenção criados
- [ ] **Deploy manual executado (BLOCKER)**

**Status:** ⏳ **AGUARDANDO DEPLOY MANUAL**

**Próxima ação:** Usuário executar deploy manual (10 min) conforme guia em `ACAO_MANUAL_URGENTE.md`

---

**Planejamento por:** Claude Code (Assistente AI)  
**Metodologia:** SCRUM + PDCA  
**Sprint:** 32  
**Data:** 2024-11-14  
**Previsão de início:** Após deploy manual  
**Previsão de término:** +8 horas após início

---

## 🎉 MENSAGEM FINAL

O Sprint 32 está **completamente planejado** e pronto para execução. Todos os problemas identificados nos relatórios de teste terão correção aplicada de forma **cirúrgica** e **organizada**.

**Primeiro passo:** Executar deploy manual (10 minutos) seguindo o guia `ACAO_MANUAL_URGENTE.md`.

Após isso, o desenvolvimento seguirá sem interrupções até **tudo estar pronto para o usuário final!** 🚀
