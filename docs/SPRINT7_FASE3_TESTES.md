# Sprint 7 - Fase 3: Integração Financeira - Documentação de Testes

## 📋 Índice
1. [Visão Geral](#visão-geral)
2. [Pré-requisitos](#pré-requisitos)
3. [Cenários de Teste](#cenários-de-teste)
4. [Checklist de Validação](#checklist-de-validação)
5. [Casos de Uso Detalhados](#casos-de-uso-detalhados)
6. [Testes de Integração](#testes-de-integração)
7. [Critérios de Aceitação](#critérios-de-aceitação)

---

## 🎯 Visão Geral

Esta documentação detalha todos os testes necessários para validar a **Integração Financeira** entre os módulos de Projetos, Contratos, Atividades e o Sistema Financeiro.

### Objetivo
Garantir que todos os módulos estejam completamente integrados, com cálculos automáticos funcionando corretamente e dashboards exibindo dados precisos em tempo real.

### Escopo
- **Migration 009**: Campos financeiros e foreign keys
- **Migration 006**: Sistema de atividades (criado para resolver bloqueio)
- **3 Models de Integração**: ProjetoFinanceiro, ContratoFinanceiro, AtividadeFinanceiro
- **3 Views de Integração**: projetos/financeiro, contratos/faturamento, atividades/custos
- **3 Controllers Atualizados**: ProjetoController, ContratoController, AtividadeController
- **Routing**: Novas rotas para dashboards financeiros

---

## ✅ Pré-requisitos

### 1. Ambiente Preparado
```bash
# Verificar que o servidor está rodando
php -S localhost:8000 -t public/

# Verificar migrations aplicadas
mysql -u root -p prestadores -e "SELECT * FROM migrations ORDER BY version DESC LIMIT 5;"

# Deve mostrar versões 1, 2, 4, 5, 6, 8, 9
```

### 2. Dados de Teste Necessários
- ✅ Pelo menos 1 usuário ativo (login funcional)
- ✅ Pelo menos 1 empresa tomadora cadastrada
- ✅ Pelo menos 1 empresa prestadora cadastrada
- ✅ Pelo menos 1 contrato ativo
- ✅ Pelo menos 1 projeto vinculado ao contrato
- ✅ Pelo menos 2 categorias financeiras cadastradas
- ✅ Pelo menos 1 centro de custo cadastrado

### 3. Verificar Estrutura do Banco
```sql
-- Verificar colunas adicionadas aos projetos
DESC projetos;
-- Deve ter: orcamento_total, custo_realizado, receita_realizada, margem_lucro

-- Verificar colunas adicionadas aos contratos
DESC contratos;
-- Deve ter: valor_total, faturamento_realizado, saldo_faturar, dia_vencimento_fatura, gerar_fatura_automatica

-- Verificar colunas adicionadas às atividades
DESC atividades;
-- Deve ter: custo_estimado, custo_realizado, horas_estimadas, horas_realizadas, custo_hora

-- Verificar foreign keys em contas_pagar
SHOW CREATE TABLE contas_pagar;
-- Deve ter: projeto_id, contrato_id, atividade_id

-- Verificar tabelas de consolidação
SHOW TABLES LIKE '%projeto_custos%';
SHOW TABLES LIKE '%contrato_faturamento%';

-- Verificar views criadas
SHOW FULL TABLES WHERE Table_type = 'VIEW';
-- Deve ter: vw_projeto_custos, vw_contrato_faturamento, vw_atividade_custos
```

---

## 🧪 Cenários de Teste

### 📊 Cenário 1: Dashboard Financeiro de Projeto

#### CT01.1 - Acessar Dashboard Financeiro do Projeto
**Pré-condição**: Usuário logado com projeto cadastrado

**Passos**:
1. Acessar lista de projetos: `/projetos`
2. Clicar em um projeto específico
3. Na página de detalhes, clicar no botão/link "Financeiro" ou acessar `/projetos/{id}/financeiro`

**Resultado Esperado**:
- ✅ Página carrega sem erros
- ✅ Exibe 4 cards no topo: Orçamento Total, Custo Realizado, Receita Realizada, Resultado
- ✅ Valores numéricos formatados corretamente (R$ X.XXX,XX)
- ✅ Indicador de Performance (ROI) exibido
- ✅ Barra de progresso do orçamento visível
- ✅ Cor da barra de progresso muda conforme percentual (verde <80%, amarelo 80-100%, vermelho >100%)

#### CT01.2 - Validar Cálculos de Custos
**Pré-condição**: Projeto com contas a pagar registradas

**Passos**:
1. Acessar `/financeiro?action=contas-pagar`
2. Criar uma conta a pagar no valor de R$ 1.000,00
3. Vincular ao projeto de teste
4. Marcar status como "pago"
5. Acessar `/projetos/{id}/financeiro`

**Resultado Esperado**:
- ✅ Campo "Custo Realizado" incrementado em R$ 1.000,00
- ✅ Tabela "Detalhamento de Custos" mostra a despesa
- ✅ Gráfico de evolução mensal atualizado
- ✅ Lista "Top 10 Fornecedores" atualizada (se fornecedor informado)

#### CT01.3 - Validar Cálculos de Receitas
**Pré-condição**: Projeto com contas a receber registradas

**Passos**:
1. Acessar `/financeiro?action=contas-receber`
2. Criar uma conta a receber no valor de R$ 5.000,00
3. Vincular ao projeto de teste
4. Marcar status como "recebido"
5. Acessar `/projetos/{id}/financeiro`

**Resultado Esperado**:
- ✅ Campo "Receita Realizada" incrementado em R$ 5.000,00
- ✅ Tabela "Detalhamento de Receitas" mostra o recebimento
- ✅ Margem de Lucro recalculada automaticamente
- ✅ ROI recalculado e exibido corretamente

#### CT01.4 - Validar Gráfico Chart.js
**Pré-condição**: Projeto com movimentações nos últimos 6 meses

**Passos**:
1. Acessar `/projetos/{id}/financeiro`
2. Rolar até a seção de gráficos

**Resultado Esperado**:
- ✅ Gráfico de linha renderizado corretamente
- ✅ Eixo X mostra meses (formato MMM/AAAA)
- ✅ Eixo Y mostra valores monetários
- ✅ Linha de custos em vermelho, linha de receitas em verde
- ✅ Tooltip ao passar mouse sobre pontos
- ✅ Legenda visível e clara

#### CT01.5 - Funcionalidade de Impressão
**Passos**:
1. Acessar `/projetos/{id}/financeiro`
2. Clicar no botão "Imprimir Relatório"

**Resultado Esperado**:
- ✅ Dialog de impressão do navegador aberto
- ✅ Layout otimizado para impressão (sem menu lateral, cabeçalhos corretos)
- ✅ Gráficos visíveis na pré-visualização
- ✅ Tabelas formatadas adequadamente

---

### 💰 Cenário 2: Dashboard de Faturamento de Contrato

#### CT02.1 - Acessar Dashboard de Faturamento
**Pré-condição**: Usuário logado com contrato cadastrado

**Passos**:
1. Acessar lista de contratos: `/contratos`
2. Clicar em um contrato específico
3. Na página de detalhes, clicar no botão/link "Faturamento" ou acessar `/contratos/{id}/faturamento`

**Resultado Esperado**:
- ✅ Página carrega sem erros
- ✅ Exibe 4 cards: Valor Total do Contrato, Faturado, Pendente, Saldo a Faturar
- ✅ Barra de progresso de faturamento visível
- ✅ Se houver inadimplência, alerta vermelho exibido
- ✅ Classificação de risco (baixo/médio/alto) calculada

#### CT02.2 - Análise de Inadimplência
**Pré-condição**: Contrato com faturas vencidas

**Passos**:
1. Criar conta a receber vinculada ao contrato
2. Definir data de vencimento no passado
3. Manter status como "pendente"
4. Acessar `/contratos/{id}/faturamento`

**Resultado Esperado**:
- ✅ Alerta de inadimplência visível (box vermelho)
- ✅ Quantidade de faturas atrasadas exibida
- ✅ Valor total em atraso calculado corretamente
- ✅ Dias de atraso calculados
- ✅ Badge de risco com cor correta:
  - Verde: Risco Baixo (< 30 dias ou < R$ 5.000)
  - Amarelo: Risco Médio (30-60 dias ou R$ 5.000-20.000)
  - Vermelho: Risco Alto (> 60 dias ou > R$ 20.000)

#### CT02.3 - Histórico Mensal de Faturamento
**Pré-condição**: Contrato com faturamentos nos últimos meses

**Passos**:
1. Acessar `/contratos/{id}/faturamento`
2. Verificar seção "Histórico Mensal de Faturamento"

**Resultado Esperado**:
- ✅ Tabela com colunas: Mês, Previsto, Recebido, Pendente, % Realizado
- ✅ Valores somados corretamente por mês
- ✅ Percentual calculado: (Recebido / Previsto) * 100
- ✅ Gráfico Chart.js de barras empilhadas renderizado
- ✅ Barra azul (recebido) + barra laranja (pendente) = total do mês

#### CT02.4 - Projeção de Receita
**Pré-condição**: Contrato com valor mensal definido

**Passos**:
1. Acessar `/contratos/{id}/faturamento`
2. Verificar seção "Projeção de Receita (12 meses)"

**Resultado Esperado**:
- ✅ Tabela com 12 linhas (meses futuros)
- ✅ Valores projetados calculados com base no valor médio mensal
- ✅ Totais acumulados corretos
- ✅ Formatação monetária adequada

#### CT02.5 - Geração Manual de Fatura Recorrente
**Pré-condição**: Contrato com `gerar_fatura_automatica = 1`

**Passos**:
1. Acessar `/contratos/{id}/faturamento`
2. Clicar no botão "Gerar Fatura do Mês"
3. Selecionar mês de referência
4. Confirmar

**Resultado Esperado**:
- ✅ Mensagem de sucesso exibida
- ✅ Nova conta a receber criada em `/financeiro?action=contas-receber`
- ✅ Conta vinculada ao contrato corretamente
- ✅ Valor da fatura = valor mensal do contrato
- ✅ Data de vencimento = dia especificado no contrato
- ✅ Histórico do contrato registra a geração da fatura

---

### 💵 Cenário 3: Dashboard de Custos de Atividade

#### CT03.1 - Acessar Dashboard de Custos da Atividade
**Pré-condição**: Usuário logado com atividade cadastrada

**Passos**:
1. Acessar lista de atividades: `/atividades`
2. Clicar em uma atividade específica
3. Na página de detalhes, clicar no botão/link "Custos" ou acessar `/atividades/{id}/custos`

**Resultado Esperado**:
- ✅ Página carrega sem erros
- ✅ Exibe 4 cards: Custo Estimado, Custo Realizado, Variação, Custo Pendente
- ✅ Badge de performance visível:
  - Verde: "Dentro do Orçamento" (variação ≤ 0)
  - Amarelo: "Atenção" (0 < variação ≤ 20%)
  - Vermelho: "Acima do Orçamento" (variação > 20%)
- ✅ Barra de progresso do orçamento exibida

#### CT03.2 - Validar Custo por Hora
**Pré-condição**: Atividade com horas registradas e custos lançados

**Passos**:
1. Registrar 10 horas trabalhadas na atividade
2. Lançar R$ 500,00 em custos vinculados à atividade
3. Acessar `/atividades/{id}/custos`

**Resultado Esperado**:
- ✅ Campo "Custo/Hora Realizado" = R$ 50,00
- ✅ Campo "Custo/Hora Estimado" calculado (custo_estimado / horas_estimadas)
- ✅ Variação de Custo/Hora exibida (diferença entre realizado e estimado)

#### CT03.3 - Tabela de Controle de Horas
**Pré-condição**: Atividade com horas estimadas e realizadas

**Passos**:
1. Definir horas_estimadas = 20h
2. Registrar 15h em atividade_tempo
3. Acessar `/atividades/{id}/custos`

**Resultado Esperado**:
- ✅ Tabela exibe: Estimadas (20h), Realizadas (15h), Restantes (5h), % Usado (75%)
- ✅ Barra de progresso em 75%
- ✅ Cor da barra: verde se < 80%, amarelo se 80-100%, vermelho se > 100%

#### CT03.4 - Alocação de Equipe
**Pré-condição**: Atividade com membros alocados

**Passos**:
1. Adicionar 2 membros à atividade via `atividade_equipe`
2. Definir custo/hora de cada membro (ex: R$ 50,00 e R$ 80,00)
3. Alocar 10h para cada membro
4. Acessar `/atividades/{id}/custos`

**Resultado Esperado**:
- ✅ Tabela "Equipe Alocada" exibe os 2 membros
- ✅ Colunas: Membro, Função, Horas, Custo/Hora, Custo Total
- ✅ Custo Total Membro 1 = R$ 500,00 (10h × R$ 50)
- ✅ Custo Total Membro 2 = R$ 800,00 (10h × R$ 80)
- ✅ Soma total = R$ 1.300,00

#### CT03.5 - Materiais e Recursos
**Pré-condição**: Atividade com recursos cadastrados

**Passos**:
1. Adicionar 3 recursos via `atividade_recursos`
2. Definir quantidades e custos unitários
3. Acessar `/atividades/{id}/custos`

**Resultado Esperado**:
- ✅ Tabela "Materiais e Recursos" exibe os 3 itens
- ✅ Colunas: Recurso, Tipo, Quantidade, Unidade, Custo Unit., Custo Total, Status
- ✅ Custo Total calculado = Quantidade × Custo Unitário
- ✅ Soma total dos recursos correta

#### CT03.6 - Gráficos Chart.js
**Pré-condição**: Atividade com custos estimados e realizados

**Passos**:
1. Acessar `/atividades/{id}/custos`
2. Verificar gráficos na página

**Resultado Esperado**:
- ✅ **Gráfico de Barras**: Comparação Estimado vs Realizado
  - Barra azul (Estimado)
  - Barra verde (Realizado)
  - Valores corretos no eixo Y
- ✅ **Gráfico de Rosca**: Distribuição de Custos
  - Fatia azul: Custo de Equipe
  - Fatia laranja: Custo de Recursos
  - Percentuais corretos
  - Legenda visível

---

## 📋 Checklist de Validação

### ✅ Funcionalidades Básicas

#### Navegação
- [ ] Todas as rotas funcionam sem erro 404
- [ ] Breadcrumbs corretos em todas as páginas
- [ ] Links de voltar funcionam corretamente
- [ ] Menu lateral exibe links para os dashboards financeiros

#### Autenticação e Permissões
- [ ] Apenas usuários logados acessam os dashboards
- [ ] Permissões respeitadas (master, admin, gestor, usuario)
- [ ] Redirecionamento correto ao tentar acessar sem permissão

#### Layout e UI
- [ ] Bootstrap 5 aplicado corretamente
- [ ] Responsivo (testar em mobile, tablet, desktop)
- [ ] Ícones Font Awesome carregando
- [ ] Cores consistentes com o tema da aplicação
- [ ] Sem elementos quebrados ou sobrepostos

---

### ✅ Integração de Dados

#### Projeto ↔ Financeiro
- [ ] Conta a pagar vinculada a projeto atualiza `projetos.custo_realizado`
- [ ] Conta a receber vinculada a projeto atualiza `projetos.receita_realizada`
- [ ] Margem de lucro calculada automaticamente
- [ ] Trigger `trg_contas_pagar_insert_projeto` funciona
- [ ] Trigger `trg_contas_receber_insert_projeto` funciona

#### Contrato ↔ Financeiro
- [ ] Conta a receber vinculada a contrato atualiza `contratos.faturamento_realizado`
- [ ] Saldo a faturar calculado corretamente
- [ ] Fatura recorrente gerada com valores corretos
- [ ] Trigger `trg_contas_receber_insert_contrato` funciona

#### Atividade ↔ Financeiro
- [ ] Conta a pagar vinculada a atividade atualiza `atividades.custo_realizado`
- [ ] Registro de tempo atualiza `atividades.horas_realizadas`
- [ ] Custo/hora calculado automaticamente
- [ ] Trigger `trg_contas_pagar_insert_atividade` funciona
- [ ] Trigger `trg_atividade_tempo_after_insert` funciona

---

### ✅ Cálculos e Totalizadores

#### Projeto
- [ ] ROI = ((Receita - Custo) / Custo) × 100
- [ ] Margem Percentual = ((Receita - Custo) / Receita) × 100
- [ ] Progresso do Orçamento = (Custo Realizado / Orçamento Total) × 100
- [ ] Consolidação mensal somando corretamente

#### Contrato
- [ ] Percentual Faturado = (Faturado / Valor Total) × 100
- [ ] Saldo a Faturar = Valor Total - Faturado
- [ ] Inadimplência considerando apenas faturas vencidas
- [ ] Projeção de receita usando média mensal

#### Atividade
- [ ] Custo/Hora = Custo Realizado / Horas Realizadas
- [ ] Variação = ((Custo Realizado - Custo Estimado) / Custo Estimado) × 100
- [ ] Percentual de Horas Usado = (Horas Realizadas / Horas Estimadas) × 100
- [ ] Custo Total Membro = Horas × Custo/Hora do Membro

---

### ✅ Visualizações (Chart.js)

#### Gráficos de Linha
- [ ] Renderiza sem erros no console
- [ ] Dados plotados corretamente
- [ ] Cores adequadas (vermelho para custos, verde para receitas)
- [ ] Tooltip funcional
- [ ] Responsivo

#### Gráficos de Barra
- [ ] Barras empilhadas funcionando (quando aplicável)
- [ ] Legendas visíveis e corretas
- [ ] Escala do eixo Y adequada
- [ ] Valores exibidos ao passar mouse

#### Gráficos de Rosca/Donut
- [ ] Fatias proporcionais aos valores
- [ ] Cores distintas
- [ ] Percentuais exibidos
- [ ] Legenda funcional

---

### ✅ Views e Tabelas

#### DataTables
- [ ] Ordenação funcionando
- [ ] Busca global funcionando
- [ ] Paginação operacional
- [ ] Exportação (CSV, Excel, PDF) se implementada
- [ ] Responsivo (colunas colapsam em mobile)

#### Formatação
- [ ] Valores monetários: R$ 1.234,56
- [ ] Percentuais: 12,34%
- [ ] Datas: DD/MM/AAAA ou AAAA-MM-DD
- [ ] Números: 1.234 ou 1.234,56

#### Conteúdo Dinâmico
- [ ] Mensagens de "sem dados" quando apropriado
- [ ] Loading indicators onde aplicável
- [ ] Mensagens de sucesso/erro exibidas corretamente

---

## 🔧 Testes de Integração

### Teste Integrado 1: Ciclo Completo de Projeto

**Objetivo**: Validar todo o ciclo de vida financeiro de um projeto.

**Passos**:
1. Criar novo projeto com orçamento de R$ 10.000,00
2. Criar 3 contas a pagar (R$ 2.000, R$ 1.500, R$ 3.000) vinculadas ao projeto
3. Pagar 2 contas (total R$ 3.500)
4. Criar 2 contas a receber (R$ 5.000, R$ 6.000) vinculadas ao projeto
5. Receber 1 conta (R$ 5.000)
6. Acessar dashboard financeiro do projeto

**Resultado Esperado**:
- Custo Realizado = R$ 3.500,00
- Receita Realizada = R$ 5.000,00
- Resultado = R$ 1.500,00 (lucro)
- Margem = 30% ((1.500 / 5.000) × 100)
- ROI = 42,86% ((5.000 - 3.500) / 3.500 × 100)
- Progresso Orçamento = 35% (3.500 / 10.000)
- Gráfico exibe movimentações corretamente

### Teste Integrado 2: Faturamento Recorrente de Contrato

**Objetivo**: Validar geração automática de faturas mensais.

**Passos**:
1. Criar contrato com:
   - Valor total: R$ 60.000,00
   - Prazo: 12 meses
   - Valor mensal: R$ 5.000,00
   - Dia vencimento: 10
   - `gerar_fatura_automatica = TRUE`
2. Acessar dashboard de faturamento
3. Gerar fatura manual para mês corrente
4. Gerar fatura para mês seguinte
5. Marcar primeira fatura como paga
6. Verificar histórico mensal

**Resultado Esperado**:
- 2 faturas criadas no sistema
- Primeira fatura: status "pago", atualiza faturamento_realizado
- Segunda fatura: status "pendente"
- Faturamento Realizado = R$ 5.000,00
- Saldo a Faturar = R$ 55.000,00
- Percentual Faturado = 8,33%
- Histórico mensal mostra os 2 meses
- Gráfico de barras empilhadas exibe corretamente

### Teste Integrado 3: Rastreamento de Custos de Atividade

**Objetivo**: Validar alocação de recursos e tracking de custos.

**Passos**:
1. Criar atividade com:
   - Custo estimado: R$ 5.000,00
   - Horas estimadas: 40h
2. Alocar 2 membros na equipe:
   - Membro A: 20h × R$ 80/h = R$ 1.600
   - Membro B: 15h × R$ 100/h = R$ 1.500
3. Adicionar 2 recursos:
   - Material X: 10 un × R$ 50 = R$ 500
   - Serviço Y: 1 un × R$ 800 = R$ 800
4. Registrar 8h de tempo trabalhado (Membro A)
5. Vincular 1 conta a pagar de R$ 500 à atividade e pagar
6. Acessar dashboard de custos da atividade

**Resultado Esperado**:
- Custo Estimado = R$ 5.000,00
- Custo Realizado = R$ 500,00 (conta paga)
- Horas Realizadas = 8h (registro de tempo)
- Custo/Hora Realizado = R$ 62,50 (500 / 8)
- Custo/Hora Estimado = R$ 125,00 (5.000 / 40)
- Variação = -90% (ainda bem abaixo do estimado)
- Tabela de equipe mostra 2 membros
- Tabela de recursos mostra 2 itens
- Gráfico de barras: Estimado R$ 5.000, Realizado R$ 500
- Gráfico de rosca: Distribuição entre equipe e recursos

---

## 🎯 Critérios de Aceitação

### Critério 1: Integridade dos Dados
- ✅ Nenhum erro de SQL durante operações
- ✅ Foreign keys respeitadas em todas as tabelas
- ✅ Triggers funcionando automaticamente
- ✅ Sem dados órfãos ou inconsistentes
- ✅ Transações ACID respeitadas

### Critério 2: Precisão dos Cálculos
- ✅ Todos os totalizadores corretos (diferença máxima de R$ 0,01 por arredondamento)
- ✅ Percentuais com 2 casas decimais
- ✅ Valores monetários com 2 casas decimais
- ✅ Datas calculadas corretamente (considera timezone)

### Critério 3: Performance
- ✅ Dashboard carrega em menos de 2 segundos
- ✅ Queries otimizadas (usar índices)
- ✅ Views materializadas se necessário
- ✅ Sem N+1 queries
- ✅ Paginação funcionando corretamente

### Critério 4: Usabilidade
- ✅ Interface intuitiva e fácil de usar
- ✅ Feedback visual para todas as ações
- ✅ Mensagens de erro claras e acionáveis
- ✅ Confirmação antes de ações destrutivas
- ✅ Layout consistente em todos os módulos

### Critério 5: Compatibilidade
- ✅ Funciona em Chrome, Firefox, Safari, Edge
- ✅ Responsivo em dispositivos móveis (320px+)
- ✅ Compatível com PHP 7.4+
- ✅ Compatível com MySQL 5.7+
- ✅ JavaScript funciona sem bibliotecas externas além das declaradas

### Critério 6: Segurança
- ✅ CSRF tokens validados em todos os forms
- ✅ Permissões verificadas em todos os controllers
- ✅ SQL injection prevenida (prepared statements)
- ✅ XSS prevenida (htmlspecialchars nas views)
- ✅ Logs de auditoria registrando alterações críticas

---

## 📊 Relatório de Testes

Após execução dos testes, preencher:

### Resumo de Execução
- **Data dos Testes**: ___/___/_____
- **Testador**: _________________
- **Ambiente**: Desenvolvimento / Homologação / Produção
- **Versão do Sistema**: Migration 9 / Sprint 7 Fase 3

### Resultados
- **Total de Casos de Teste**: 16
- **Passou**: ___ / 16
- **Falhou**: ___ / 16
- **Bloqueado**: ___ / 16
- **Não Executado**: ___ / 16

### Bugs Encontrados
| ID | Severidade | Descrição | Status |
|----|------------|-----------|--------|
| B01 | Alta | ... | Aberto |
| B02 | Média | ... | Corrigido |

### Observações Gerais
_Adicionar observações relevantes sobre os testes, ambiente, dificuldades encontradas, etc._

---

## 🚀 Conclusão

Este documento fornece uma cobertura completa de testes para a integração financeira. Todos os casos de teste devem passar para que a **Fase 3 do Sprint 7** seja considerada 100% completa.

### Próximos Passos
1. Executar todos os testes documentados
2. Corrigir bugs encontrados
3. Re-testar casos que falharam
4. Obter aprovação do usuário/product owner
5. Preparar para deploy em produção

### Contato para Dúvidas
- **Documentação**: `/docs/SPRINT7_FASE3_TESTES.md`
- **Código-fonte**: `/src/models/*Financeiro.php`, `/src/views/{projetos|contratos|atividades}/*`
- **Migrations**: `/database/migrations/009_integrar_financeiro_projetos.sql`

---

**Última Atualização**: 2024-11-07  
**Responsável**: Equipe de Desenvolvimento - Sprint 7
