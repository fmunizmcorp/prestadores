# 📋 Planejamento Detalhado - Sprints 4 a 9

## 🎯 Visão Geral do Sistema

O sistema gerencia múltiplas **empresas tomadoras de serviços** que contratam **empresas prestadoras** para executar **projetos** compostos por **atividades**, com controle rigoroso de **custos**, **prazos**, **jornadas de trabalho** e **pagamentos**.

---

## 🏢 Conceitos Principais

### Empresas Tomadoras
Empresas/clientes que contratam serviços (Ex: Clinfec)

### Empresas Prestadoras  
Empresas fornecedoras que prestam serviços

### Profissionais/Prestadores
Pessoas físicas vinculadas às empresas prestadoras que executam as atividades

### Projetos
Conjunto de atividades com orçamento, prazo e objetivos definidos

### Atividades
Tarefas específicas dentro de um projeto que precisam ser executadas

---

## 📊 Sprint 4: CRUD de Empresas Tomadoras e Prestadoras

### 🎯 Objetivos
- Separar conceito de empresas tomadoras vs prestadoras
- Sistema multi-tenant (múltiplas empresas tomadoras)
- CRUD completo com todas as informações relevantes
- Contratos e serviços por período
- Upload de documentos

### 🗄️ Estrutura de Banco de Dados

```sql
-- Tabela de empresas tomadoras (clientes)
CREATE TABLE empresas_tomadoras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    razao_social VARCHAR(255) NOT NULL,
    nome_fantasia VARCHAR(255) NOT NULL,
    cnpj VARCHAR(18) NOT NULL UNIQUE,
    inscricao_estadual VARCHAR(50),
    inscricao_municipal VARCHAR(50),
    
    -- Endereço
    cep VARCHAR(9),
    logradouro VARCHAR(255),
    numero VARCHAR(20),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    
    -- Contatos
    email_principal VARCHAR(255),
    telefone_principal VARCHAR(20),
    telefone_secundario VARCHAR(20),
    celular VARCHAR(20),
    site VARCHAR(255),
    
    -- Informações financeiras
    dia_fechamento INT DEFAULT 30 COMMENT 'Dia do mês para fechamento de medição',
    dia_pagamento INT DEFAULT 5 COMMENT 'Dia do pagamento (após fechamento)',
    
    -- Observações e status
    observacoes TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_cnpj (cnpj),
    INDEX idx_razao_social (razao_social),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Renomear tabela empresas para empresas_prestadoras
RENAME TABLE empresas TO empresas_prestadoras;

-- Adicionar campo tipo_prestador
ALTER TABLE empresas_prestadoras 
ADD COLUMN tipo_prestador ENUM('pj', 'pf', 'mei') DEFAULT 'pj' AFTER cnpj,
ADD COLUMN cpf VARCHAR(14) NULL AFTER tipo_prestador;

-- Tabela de contratos entre tomadora e prestadora
CREATE TABLE contratos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_tomadora_id INT NOT NULL,
    empresa_prestadora_id INT NOT NULL,
    numero_contrato VARCHAR(100),
    descricao TEXT,
    data_inicio DATE NOT NULL,
    data_fim DATE,
    valor_total DECIMAL(15,2),
    status ENUM('ativo', 'suspenso', 'encerrado') DEFAULT 'ativo',
    observacoes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (empresa_tomadora_id) REFERENCES empresas_tomadoras(id),
    FOREIGN KEY (empresa_prestadora_id) REFERENCES empresas_prestadoras(id),
    INDEX idx_status (status),
    INDEX idx_datas (data_inicio, data_fim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de valores de serviços por período
CREATE TABLE servico_valores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contrato_id INT NOT NULL,
    servico_id INT NOT NULL,
    
    -- Período de validade
    data_inicio DATE NOT NULL,
    data_fim DATE NULL,
    
    -- Tipo de remuneração
    tipo_remuneracao ENUM(
        'por_hora', 
        'por_dia', 
        'por_mes', 
        'por_semana_5dias', 
        'por_semana_6dias', 
        'por_semana_7dias',
        'por_entrega'
    ) NOT NULL,
    
    -- Valores
    valor_base DECIMAL(10,2) NOT NULL COMMENT 'Valor base do serviço',
    valor_hora_extra DECIMAL(10,2) COMMENT 'Valor diferenciado para horas extras',
    valor_jornada_curta DECIMAL(10,2) COMMENT 'Valor para jornadas até 6h',
    
    -- Limites
    horas_mes_limite INT COMMENT 'Limite de horas por mês',
    horas_dia_limite INT DEFAULT 12 COMMENT 'Limite de horas por dia',
    
    observacoes TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (contrato_id) REFERENCES contratos(id),
    FOREIGN KEY (servico_id) REFERENCES servicos(id),
    INDEX idx_periodo (data_inicio, data_fim),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de documentos das empresas
CREATE TABLE empresa_documentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    tipo_empresa ENUM('tomadora', 'prestadora') NOT NULL,
    tipo_documento ENUM('contrato', 'certidao', 'licenca', 'outros') NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    arquivo_path VARCHAR(500) NOT NULL,
    descricao TEXT,
    data_validade DATE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    
    INDEX idx_empresa (empresa_id, tipo_empresa),
    INDEX idx_tipo (tipo_documento),
    FOREIGN KEY (created_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de responsáveis das empresas tomadoras
CREATE TABLE empresa_tomadora_responsaveis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_tomadora_id INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    cargo VARCHAR(100),
    email VARCHAR(255),
    telefone VARCHAR(20),
    celular VARCHAR(20),
    departamento VARCHAR(100),
    principal BOOLEAN DEFAULT FALSE,
    observacoes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (empresa_tomadora_id) REFERENCES empresas_tomadoras(id) ON DELETE CASCADE,
    INDEX idx_principal (principal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 📋 Funcionalidades

#### 1. Empresas Tomadoras - CRUD COMPLETO ✅
**Create**:
- [x] Formulário completo com validações
- [x] Validação de CNPJ único
- [x] Upload de logo/documentos
- [x] Token CSRF

**Read**:
- [x] Listagem paginada
- [x] Filtros: nome, CNPJ, ativo/inativo, cidade, estado
- [x] Busca avançada
- [x] Ordenação de colunas
- [x] Exportação (CSV/PDF)
- [x] Visualização detalhada

**Update**:
- [x] Editar todos os campos
- [x] Histórico de alterações
- [x] Validação de CNPJ único
- [x] Log de auditoria

**Delete**:
- [x] Soft delete (desativar)
- [x] Verificar contratos ativos
- [x] Verificar projetos em andamento
- [x] Confirmação obrigatória
- [x] Log de exclusão

**Adicional**:
- [x] Gestão de responsáveis (sub-CRUD)
- [x] Upload de documentos (sub-CRUD)
- [x] Configuração de fechamento/pagamento
- [x] Relatório de empresas

#### 2. Empresas Prestadoras - CRUD COMPLETO ✅
**Create**:
- [x] Diferenciação: PJ, PF, MEI
- [x] CPF (PF) ou CNPJ (PJ/MEI)
- [x] Validação de CPF/CNPJ único
- [x] Vincular serviços que pode prestar

**Read**:
- [x] Listagem paginada
- [x] Filtros: tipo, nome, CNPJ/CPF, serviços, ativo/inativo
- [x] Busca por serviço oferecido
- [x] Exportação (CSV/PDF)

**Update**:
- [x] Editar todos os campos
- [x] Atualizar serviços prestados
- [x] Histórico de alterações
- [x] Log de auditoria

**Delete**:
- [x] Soft delete
- [x] Verificar contratos ativos
- [x] Verificar atividades em andamento
- [x] Confirmação obrigatória

**Adicional**:
- [x] Gestão de serviços N:N
- [x] Gestão de profissionais vinculados
- [x] Upload de documentos e certificações
- [x] Relatório de prestadoras

#### 3. Serviços - CRUD COMPLETO MELHORADO ✅
**Create**:
- [x] Nome, descrição, categoria
- [x] Unidade de medida padrão
- [x] Valor sugerido (opcional)

**Read**:
- [x] Listagem paginada
- [x] Filtros: categoria, ativo/inativo
- [x] Busca por nome

**Update**:
- [x] Editar todos os campos
- [x] Histórico de alterações

**Delete**:
- [x] Soft delete
- [x] Verificar se está em uso em contratos
- [x] Confirmação obrigatória

#### 4. Contratos - CRUD COMPLETO ✅
**Create**:
- [x] Vincular tomadora + prestadora
- [x] Número, vigência, valor total
- [x] Upload do PDF do contrato
- [x] Validações de datas

**Read**:
- [x] Listagem paginada
- [x] Filtros: status, tomadora, prestadora, vigência
- [x] Alertas de vencimento próximo
- [x] Exportação (PDF)

**Update**:
- [x] Atualizar datas, valores, status
- [x] Aditivos contratuais
- [x] Histórico de alterações
- [x] Upload de novos documentos

**Delete**:
- [x] Soft delete (encerrar)
- [x] Verificar valores a pagar
- [x] Verificar atividades em andamento
- [x] Confirmação obrigatória

**Adicional**:
- [x] Gestão de aditivos
- [x] Upload de documentos relacionados
- [x] Histórico de revisões
- [x] Relatório por status

#### 5. Valores de Serviços por Período - CRUD ESPECIAL ✅
**Create**:
- [x] Contrato, serviço, período
- [x] Tipo remuneração (hora, dia, mês, semana 5/6/7, entrega)
- [x] Valores: base, hora extra, jornada curta
- [x] Limites de horas (dia, mês)
- [x] Validação de períodos sobrepostos

**Read**:
- [x] Listagem por contrato/serviço
- [x] Filtros: vigente, expirado, futuro
- [x] Histórico completo de valores
- [x] Comparativo de valores

**Update**:
- [x] Criar novo período automaticamente
- [x] Não permite alterar períodos passados
- [x] Atualização gera novo registro

**Delete**:
- [x] Não permite delete
- [x] Inativar período e criar novo

**Adicional**:
- [x] Timeline de valores
- [x] Cálculo automático baseado no período
- [x] Relatório de valores por serviço
- [x] Alertas de valores sem sucessor

#### 6. Responsáveis de Empresas Tomadoras - CRUD COMPLETO ✅
**Create/Read/Update/Delete**: Gestão completa de responsáveis

#### 7. Documentos de Empresas - CRUD COMPLETO ✅
**Create/Read/Update/Delete**: Upload e gestão de documentos com alertas de vencimento

---

## 📊 Sprint 5: Gestão Completa de Projetos

### 🎯 Objetivos
- CRUD completo de projetos
- Orçamento detalhado
- Controle de custos em tempo real
- Acompanhamento de execução
- Relatórios gerenciais
- Cópia de projetos
- Metas e bonificações

### 🗄️ Estrutura de Banco de Dados

```sql
-- Tabela de projetos
CREATE TABLE projetos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_tomadora_id INT NOT NULL,
    codigo VARCHAR(50) UNIQUE,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    
    -- Datas planejadas
    data_inicio_planejada DATE NOT NULL,
    data_fim_planejada DATE NOT NULL,
    
    -- Datas reais
    data_inicio_real DATE,
    data_fim_real DATE,
    
    -- Orçamento
    orcamento_total DECIMAL(15,2) NOT NULL,
    custo_realizado DECIMAL(15,2) DEFAULT 0,
    
    -- Esforço (em horas)
    esforco_planejado INT NOT NULL COMMENT 'Horas planejadas',
    esforco_realizado INT DEFAULT 0 COMMENT 'Horas realizadas',
    
    -- Controle
    status ENUM('planejamento', 'em_andamento', 'pausado', 'concluido', 'cancelado') DEFAULT 'planejamento',
    prioridade ENUM('baixa', 'media', 'alta', 'urgente') DEFAULT 'media',
    
    -- Responsáveis
    lider_projeto_id INT NOT NULL COMMENT 'Usuário líder do projeto',
    
    -- Flags de controle
    permite_horas_extras BOOLEAN DEFAULT TRUE,
    bloqueia_horas_excedentes BOOLEAN DEFAULT FALSE COMMENT 'Impede registro de horas além do planejado',
    
    observacoes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    
    FOREIGN KEY (empresa_tomadora_id) REFERENCES empresas_tomadoras(id),
    FOREIGN KEY (lider_projeto_id) REFERENCES usuarios(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    INDEX idx_status (status),
    INDEX idx_datas (data_inicio_planejada, data_fim_planejada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Empresas prestadoras vinculadas ao projeto
CREATE TABLE projeto_empresas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT NOT NULL,
    empresa_prestadora_id INT NOT NULL,
    contrato_id INT,
    orcamento_alocado DECIMAL(15,2),
    custo_realizado DECIMAL(15,2) DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE,
    FOREIGN KEY (empresa_prestadora_id) REFERENCES empresas_prestadoras(id),
    FOREIGN KEY (contrato_id) REFERENCES contratos(id),
    UNIQUE KEY unique_projeto_empresa (projeto_id, empresa_prestadora_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Metas e bonificações do projeto
CREATE TABLE projeto_metas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT NOT NULL,
    descricao TEXT NOT NULL,
    tipo_meta ENUM('prazo', 'qualidade', 'produtividade', 'economia') NOT NULL,
    meta_valor VARCHAR(100) COMMENT 'Ex: 30 dias, 95%, 100 atendimentos',
    valor_bonificacao DECIMAL(10,2),
    atingida BOOLEAN DEFAULT FALSE,
    data_atingimento DATE,
    observacoes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Histórico de alterações do projeto
CREATE TABLE projeto_historico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT NOT NULL,
    usuario_id INT NOT NULL,
    acao VARCHAR(100) NOT NULL,
    campo_alterado VARCHAR(100),
    valor_anterior TEXT,
    valor_novo TEXT,
    observacao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 📋 Funcionalidades

#### Gestão de Projetos
- [x] CRUD completo de projetos
- [x] Vinculação com empresas tomadoras
- [x] Vinculação com múltiplas empresas prestadoras
- [x] Definição de líder do projeto
- [x] Orçamento detalhado por empresa
- [x] Datas planejadas vs reais
- [x] Esforço planejado vs realizado
- [x] Status e prioridade

#### Controle de Execução
- [x] Percentual de esforço gasto (horas)
- [x] Percentual de custo consumido
- [x] Comparativo planejado x realizado
- [x] Alertas de desvios (budget, prazo)
- [x] Dashboard de acompanhamento
- [x] Gráficos de evolução

#### Controle por Entidade
- [x] Custo por empresa tomadora
- [x] Custo por empresa prestadora
- [x] Custo por profissional
- [x] Ranking de performance
- [x] Análise de produtividade

#### Gestão de Equipe
- [x] Alterar executores de atividades
- [x] Limitar horas por profissional
- [x] Cortar/ajustar pagamento de horas
- [x] Relatório de quem está fazendo mais horas
- [x] Impedir execução além do programado

#### Cópia de Projetos
- [x] Copiar apenas estrutura de atividades
- [x] Copiar atividades + empresas
- [x] Copiar atividades + equipes
- [x] Ajustar datas automaticamente

#### Metas e Bonificações
- [x] Cadastro de metas por projeto
- [x] Tipos: prazo, qualidade, produtividade, economia
- [x] Valor de bonificação
- [x] Controle de atingimento
- [x] Distribuição automática

---

## 📊 Sprint 6: Gestão de Atividades e Candidatura

### 🎯 Objetivos
- CRUD completo de atividades
- Candidatura espontânea de profissionais
- Controle de jornadas e limites
- Diferenciação presencial vs remoto
- Sistema de aprovação de atividades

### 🗄️ Estrutura de Banco de Dados

```sql
-- Tabela de atividades
CREATE TABLE atividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT NOT NULL,
    codigo VARCHAR(50),
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    
    -- Tipo e localização
    tipo_atividade ENUM('presencial', 'remoto', 'hibrido') DEFAULT 'remoto',
    local_execucao TEXT COMMENT 'Endereço se presencial',
    
    -- Datas planejadas
    data_inicio_planejada DATE NOT NULL,
    data_fim_planejada DATE NOT NULL,
    
    -- Datas reais
    data_inicio_real DATE,
    data_fim_real DATE,
    
    -- Esforço e orçamento
    horas_planejadas INT NOT NULL,
    horas_realizadas INT DEFAULT 0,
    custo_planejado DECIMAL(10,2) NOT NULL,
    custo_realizado DECIMAL(10,2) DEFAULT 0,
    
    -- Responsáveis
    lider_atividade_id INT COMMENT 'Líder da atividade',
    
    -- Controle de execução
    status ENUM('aguardando_recursos', 'disponivel', 'em_execucao', 'pausada', 'concluida', 'cancelada') DEFAULT 'aguardando_recursos',
    progresso INT DEFAULT 0 COMMENT 'Percentual 0-100',
    permite_candidatura BOOLEAN DEFAULT TRUE COMMENT 'Permite candidatura espontânea',
    
    -- Limites
    max_profissionais INT DEFAULT 1,
    horas_dia_limite INT DEFAULT 8,
    
    -- Remuneração
    servico_id INT NOT NULL,
    tipo_remuneracao ENUM('por_hora', 'por_dia', 'por_entrega') NOT NULL,
    valor_base DECIMAL(10,2),
    valor_bonificacao DECIMAL(10,2) COMMENT 'Bônus por produtividade/meta',
    
    observacoes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    
    FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE,
    FOREIGN KEY (lider_atividade_id) REFERENCES usuarios(id),
    FOREIGN KEY (servico_id) REFERENCES servicos(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    INDEX idx_status (status),
    INDEX idx_tipo (tipo_atividade),
    INDEX idx_candidatura (permite_candidatura)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Profissionais atribuídos à atividade
CREATE TABLE atividade_profissionais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    atividade_id INT NOT NULL,
    usuario_id INT NOT NULL,
    empresa_prestadora_id INT NOT NULL COMMENT 'Por qual empresa está prestando',
    
    tipo_atribuicao ENUM('designado', 'candidato', 'aprovado') DEFAULT 'designado',
    data_candidatura DATETIME,
    data_aprovacao DATETIME,
    aprovado_por INT,
    
    horas_alocadas INT COMMENT 'Horas destinadas a este profissional',
    horas_realizadas INT DEFAULT 0,
    valor_pago DECIMAL(10,2) DEFAULT 0,
    
    status ENUM('ativo', 'inativo', 'substituido') DEFAULT 'ativo',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (atividade_id) REFERENCES atividades(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (empresa_prestadora_id) REFERENCES empresas_prestadoras(id),
    FOREIGN KEY (aprovado_por) REFERENCES usuarios(id),
    INDEX idx_tipo_atribuicao (tipo_atribuicao),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Recursos necessários para a atividade
CREATE TABLE atividade_recursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    atividade_id INT NOT NULL,
    tipo_recurso VARCHAR(100) NOT NULL COMMENT 'Ex: computador, software, material',
    descricao TEXT,
    quantidade INT DEFAULT 1,
    disponivel BOOLEAN DEFAULT FALSE,
    observacoes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (atividade_id) REFERENCES atividades(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Certificações necessárias para executar a atividade
CREATE TABLE atividade_certificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    atividade_id INT NOT NULL,
    nome_certificacao VARCHAR(255) NOT NULL,
    obrigatoria BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (atividade_id) REFERENCES atividades(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Certificações dos profissionais
CREATE TABLE usuario_certificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome_certificacao VARCHAR(255) NOT NULL,
    instituicao VARCHAR(255),
    data_emissao DATE,
    data_validade DATE,
    numero_certificado VARCHAR(100),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_validade (data_validade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 📋 Funcionalidades

#### Gestão de Atividades
- [x] CRUD completo de atividades
- [x] Vinculação a projetos
- [x] Definição de líder da atividade
- [x] Tipo: presencial, remoto, híbrido
- [x] Recursos necessários
- [x] Certificações requeridas
- [x] Status e progresso

#### Candidatura Espontânea
- [x] Profissionais se candidatam a atividades disponíveis
- [x] Verificação automática de qualificação
- [x] Escolha da empresa pela qual vai prestar
- [x] Sistema de aprovação pelo gestor
- [x] Notificações de candidaturas

#### Controle de Jornada
- [x] Limite de 6 horas por jornada
- [x] Intervalo mínimo de 1-2h entre jornadas
- [x] Intervalo mínimo de 11h entre dias
- [x] Máximo de 12h por dia
- [x] Alertas de violação de limites

#### Limites de Horas
- [x] Cadastro de limites por prestador
- [x] Cadastro de limites por usuário
- [x] Relatório de limites
- [x] Alertas de aproximação do limite
- [x] Bloqueio automático ao atingir limite

#### Relatórios
- [x] Atividades não atribuídas
- [x] Profissionais que se candidataram mas não executaram
- [x] Horas prometidas vs executadas
- [x] Atividades em risco
- [x] Performance por profissional

---

## 📊 Sprint 7: Gestão Financeira Completa

### 🎯 Objetivos
- Controle total de custos e pagamentos
- Relatórios financeiros detalhados
- Fechamento de medição por período
- Controle de pagamentos realizados
- Dashboard financeiro

### 🗄️ Estrutura de Banco de Dados

```sql
-- Tabela de fechamentos de medição
CREATE TABLE medicoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_tomadora_id INT NOT NULL,
    empresa_prestadora_id INT NOT NULL,
    
    mes_referencia INT NOT NULL COMMENT '1-12',
    ano_referencia INT NOT NULL,
    
    data_fechamento DATE NOT NULL,
    data_vencimento DATE NOT NULL,
    
    valor_total DECIMAL(15,2) NOT NULL,
    valor_pago DECIMAL(15,2) DEFAULT 0,
    
    status ENUM('aberta', 'fechada', 'paga', 'cancelada') DEFAULT 'aberta',
    
    observacoes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    
    FOREIGN KEY (empresa_tomadora_id) REFERENCES empresas_tomadoras(id),
    FOREIGN KEY (empresa_prestadora_id) REFERENCES empresas_prestadoras(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    INDEX idx_referencia (ano_referencia, mes_referencia),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Itens da medição (detalhamento)
CREATE TABLE medicao_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medicao_id INT NOT NULL,
    atividade_id INT NOT NULL,
    usuario_id INT NOT NULL,
    
    descricao TEXT,
    quantidade DECIMAL(10,2) NOT NULL COMMENT 'Horas, dias, etc',
    unidade VARCHAR(20) NOT NULL COMMENT 'hora, dia, mês, entrega',
    valor_unitario DECIMAL(10,2) NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (medicao_id) REFERENCES medicoes(id) ON DELETE CASCADE,
    FOREIGN KEY (atividade_id) REFERENCES atividades(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de pagamentos realizados
CREATE TABLE pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medicao_id INT NOT NULL,
    
    data_pagamento DATE NOT NULL,
    valor_pago DECIMAL(15,2) NOT NULL,
    
    forma_pagamento ENUM('dinheiro', 'transferencia', 'pix', 'cheque', 'boleto', 'cartao') NOT NULL,
    
    banco VARCHAR(100),
    agencia VARCHAR(20),
    conta VARCHAR(20),
    comprovante_path VARCHAR(500),
    
    observacoes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    
    FOREIGN KEY (medicao_id) REFERENCES medicoes(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    INDEX idx_data (data_pagamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de ajustes financeiros
CREATE TABLE financeiro_ajustes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    atividade_id INT NOT NULL,
    usuario_id INT NOT NULL,
    
    tipo_ajuste ENUM('corte_horas', 'bonus', 'desconto', 'multa', 'adicional') NOT NULL,
    valor_ajuste DECIMAL(10,2) NOT NULL COMMENT 'Positivo ou negativo',
    motivo TEXT NOT NULL,
    
    aprovado_por INT NOT NULL,
    data_aprovacao DATE NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (atividade_id) REFERENCES atividades(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (aprovado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de custos extras do projeto
CREATE TABLE projeto_custos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projeto_id INT NOT NULL,
    
    tipo_custo ENUM('material', 'equipamento', 'deslocamento', 'hospedagem', 'alimentacao', 'outros') NOT NULL,
    descricao TEXT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_custo DATE NOT NULL,
    
    comprovante_path VARCHAR(500),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    
    FOREIGN KEY (projeto_id) REFERENCES projetos(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    INDEX idx_tipo (tipo_custo),
    INDEX idx_data (data_custo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 📋 Funcionalidades

#### Fechamento de Medição
- [x] Fechamento automático por período
- [x] Fechamento por empresa prestadora
- [x] Detalhamento de horas/serviços
- [x] Geração de relatório PDF
- [x] Aprovação de medição
- [x] Contestação de medição

#### Controle de Pagamentos
- [x] Registro de pagamentos realizados
- [x] Formas de pagamento
- [x] Upload de comprovantes
- [x] Histórico completo
- [x] Conciliação bancária

#### Ajustes Financeiros
- [x] Corte de horas executadas
- [x] Limite de pagamento (mesmo com horas realizadas)
- [x] Bônus e bonificações
- [x] Multas e descontos
- [x] Sistema de aprovação

#### Relatórios Financeiros
- [x] Por período (dia, semana, mês, ano)
- [x] Por projeto
- [x] Por tipo de atividade
- [x] Por profissional
- [x] Por empresa tomadora
- [x] Por empresa prestadora
- [x] Custos totais consolidados
- [x] Análise de rentabilidade

#### Dashboard Financeiro
- [x] Valores a pagar (pendentes)
- [x] Valores pagos (histórico)
- [x] Gráficos de evolução
- [x] Alertas de vencimento
- [x] Projeções futuras
- [x] Indicadores (ticket médio, custo/hora, etc)

---

## 📊 Sprint 8: Sistema de Ponto Eletrônico

### 🎯 Objetivos
- Controle rigoroso de início e fim de atividades
- Validação de localização (presencial)
- Contestações e ajustes
- Alertas automáticos
- Integração com pagamentos

### 🗄️ Estrutura de Banco de Dados

```sql
-- Tabela de registros de ponto
CREATE TABLE ponto_registros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    atividade_id INT NOT NULL,
    usuario_id INT NOT NULL,
    
    -- Início
    data_hora_inicio DATETIME NOT NULL,
    ip_inicio VARCHAR(45),
    localizacao_inicio VARCHAR(500) COMMENT 'Lat,Long ou descrição',
    dispositivo_inicio TEXT,
    
    -- Fim
    data_hora_fim DATETIME,
    ip_fim VARCHAR(45),
    localizacao_fim VARCHAR(500),
    dispositivo_fim TEXT,
    
    -- Cálculo
    duracao_minutos INT COMMENT 'Calculado automaticamente',
    duracao_paga_minutos INT COMMENT 'Após ajustes',
    
    -- Status
    status ENUM('em_andamento', 'finalizado', 'pendente_ajuste', 'ajustado', 'cancelado') DEFAULT 'em_andamento',
    
    -- Validações
    localizacao_validada BOOLEAN DEFAULT FALSE,
    fora_do_horario BOOLEAN DEFAULT FALSE,
    excede_jornada BOOLEAN DEFAULT FALSE,
    
    -- Observações
    observacoes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (atividade_id) REFERENCES atividades(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_usuario_data (usuario_id, data_hora_inicio),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contestações de ponto
CREATE TABLE ponto_contestacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ponto_registro_id INT NOT NULL,
    usuario_id INT NOT NULL,
    
    tipo_contestacao ENUM('esqueci_finalizar', 'erro_horario', 'erro_atividade', 'problema_tecnico', 'outro') NOT NULL,
    descricao TEXT NOT NULL,
    
    data_hora_correta_inicio DATETIME,
    data_hora_correta_fim DATETIME,
    
    -- Aprovação
    status ENUM('pendente', 'aprovada', 'rejeitada') DEFAULT 'pendente',
    analisado_por INT,
    data_analise DATETIME,
    justificativa_analise TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (ponto_registro_id) REFERENCES ponto_registros(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (analisado_por) REFERENCES usuarios(id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Configurações de localizações válidas
CREATE TABLE ponto_localizacoes_validas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    atividade_id INT NOT NULL,
    
    nome_local VARCHAR(255) NOT NULL,
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    raio_metros INT DEFAULT 100 COMMENT 'Raio de tolerância em metros',
    
    ativa BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (atividade_id) REFERENCES atividades(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Alertas automáticos de ponto
CREATE TABLE ponto_alertas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ponto_registro_id INT NOT NULL,
    usuario_id INT NOT NULL,
    
    tipo_alerta ENUM('tempo_acabando', 'excesso_jornada', 'sem_intervalo', 'localizacao_invalida', 'esqueceu_finalizar') NOT NULL,
    mensagem TEXT NOT NULL,
    
    enviado BOOLEAN DEFAULT FALSE,
    data_envio DATETIME,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (ponto_registro_id) REFERENCES ponto_registros(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_enviado (enviado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 📋 Funcionalidades

#### Registro de Ponto
- [x] Botão "Iniciar Atividade"
- [x] Botão "Finalizar Atividade"
- [x] Captura de IP e localização
- [x] Captura de dispositivo
- [x] Validação em tempo real

#### Controles Automáticos
- [x] Finalização automática após 10 minutos sem ação
- [x] Alerta 5 minutos antes do fim
- [x] Bloqueio após limite de horas
- [x] Validação de intervalo entre jornadas
- [x] Validação de localização (presencial)

#### Validação de Localização
- [x] GPS para atividades presenciais
- [x] Raio de tolerância configurável
- [x] Validação por IP (remoto)
- [x] Alertas de localização inválida

#### Contestações
- [x] Profissional solicita ajuste
- [x] Justificativa obrigatória
- [x] Gestor aprova/rejeita
- [x] Histórico de contestações
- [x] Penalidades por múltiplas contestações

#### Relatórios de Ponto
- [x] Espelho de ponto mensal
- [x] Horas por profissional
- [x] Irregularidades detectadas
- [x] Taxa de contestações
- [x] Exportação para folha de pagamento

---

## 📊 Sprint 9: Metas, Bonificações e Melhorias Finais

### 🎯 Objetivos
- Sistema completo de metas
- Cálculo automático de bonificações
- Gamificação
- Melhorias de UX
- Otimizações de performance

### 🗄️ Estrutura de Banco de Dados

```sql
-- Tabela de metas individuais
CREATE TABLE metas_individuais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    projeto_id INT,
    atividade_id INT,
    
    tipo_meta ENUM('horas', 'entregas', 'qualidade', 'prazo', 'atendimentos', 'custom') NOT NULL,
    descricao TEXT NOT NULL,
    
    meta_valor DECIMAL(10,2) NOT NULL,
    valor_atingido DECIMAL(10,2) DEFAULT 0,
    
    periodo_inicio DATE NOT NULL,
    periodo_fim DATE NOT NULL,
    
    valor_bonificacao DECIMAL(10,2) NOT NULL,
    bonificacao_paga DECIMAL(10,2) DEFAULT 0,
    
    status ENUM('ativa', 'atingida', 'nao_atingida', 'cancelada') DEFAULT 'ativa',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (projeto_id) REFERENCES projetos(id),
    FOREIGN KEY (atividade_id) REFERENCES atividades(id),
    INDEX idx_status (status),
    INDEX idx_periodo (periodo_inicio, periodo_fim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de ranking e gamificação
CREATE TABLE usuario_pontuacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL UNIQUE,
    
    pontos_total INT DEFAULT 0,
    nivel INT DEFAULT 1,
    
    -- Conquistas
    projetos_concluidos INT DEFAULT 0,
    atividades_concluidas INT DEFAULT 0,
    horas_trabalhadas INT DEFAULT 0,
    metas_atingidas INT DEFAULT 0,
    
    -- Performance
    taxa_sucesso DECIMAL(5,2) DEFAULT 0 COMMENT 'Percentual de atividades concluídas com sucesso',
    nota_media DECIMAL(3,2) DEFAULT 0 COMMENT 'Avaliação média 0-5',
    
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de conquistas/badges
CREATE TABLE badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    icone VARCHAR(255),
    pontos INT DEFAULT 0,
    criterio TEXT COMMENT 'Critério para ganhar o badge',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Badges dos usuários
CREATE TABLE usuario_badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    badge_id INT NOT NULL,
    data_conquista DATETIME NOT NULL,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (badge_id) REFERENCES badges(id),
    UNIQUE KEY unique_usuario_badge (usuario_id, badge_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Avaliações de desempenho
CREATE TABLE avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    atividade_id INT NOT NULL,
    avaliador_id INT NOT NULL,
    
    nota_tecnica DECIMAL(3,2) COMMENT '0-5',
    nota_prazo DECIMAL(3,2) COMMENT '0-5',
    nota_qualidade DECIMAL(3,2) COMMENT '0-5',
    nota_comunicacao DECIMAL(3,2) COMMENT '0-5',
    nota_geral DECIMAL(3,2) COMMENT '0-5 (média)',
    
    comentarios TEXT,
    pontos_positivos TEXT,
    pontos_melhoria TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (atividade_id) REFERENCES atividades(id),
    FOREIGN KEY (avaliador_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 📋 Funcionalidades

#### Sistema de Metas
- [x] Metas individuais por profissional
- [x] Metas por projeto/atividade
- [x] Tipos variados (horas, entregas, qualidade, etc)
- [x] Acompanhamento em tempo real
- [x] Notificações de progresso
- [x] Cálculo automático de bonificações

#### Bonificações
- [x] Por meta atingida
- [x] Por prazo antecipado
- [x] Por qualidade excepcional
- [x] Por produtividade
- [x] Pagamento junto com a medição

#### Gamificação
- [x] Sistema de pontos
- [x] Níveis de experiência
- [x] Badges/conquistas
- [x] Ranking de profissionais
- [x] Desafios mensais
- [x] Recompensas

#### Avaliações
- [x] Avaliação pós-atividade
- [x] Múltiplos critérios (técnica, prazo, qualidade, comunicação)
- [x] Histórico de avaliações
- [x] Nota média do profissional
- [x] Feedback construtivo

#### Melhorias Finais
- [x] Notificações push
- [x] Exportação de relatórios (Excel, PDF)
- [x] Dashboards personalizáveis
- [x] App mobile (PWA)
- [x] API REST completa
- [x] Documentação técnica
- [x] Backup automático

---

## 🎯 Resumo das Sprints

| Sprint | Tema | Duração Estimada |
|--------|------|------------------|
| Sprint 4 | Empresas Tomadoras e Prestadoras + Contratos | 2 semanas |
| Sprint 5 | Projetos Completos com Orçamento e Controle | 3 semanas |
| Sprint 6 | Atividades e Candidatura Espontânea | 2 semanas |
| Sprint 7 | Gestão Financeira Completa | 2 semanas |
| Sprint 8 | Sistema de Ponto Eletrônico | 2 semanas |
| Sprint 9 | Metas, Bonificações e Gamificação | 1 semana |

**TOTAL**: ~12 semanas (3 meses)

---

## 🚀 Próximos Passos

1. ✅ Revisar e aprovar o planejamento
2. ✅ Iniciar Sprint 4 (CRUD de Empresas)
3. ⏳ Desenvolvimento incremental
4. ⏳ Testes contínuos
5. ⏳ Deploy por sprint
6. ⏳ Feedback e ajustes

---

## 📊 RESUMO EXECUTIVO DE CRUDS

### Total de Cadastros com CRUD Completo: **25**

| Sprint | Cadastros | Tipo CRUD |
|--------|-----------|-----------|
| **Sprint 4** | Empresas Tomadoras | CRUD Completo |
| | Empresas Prestadoras | CRUD Completo |
| | Serviços | CRUD Melhorado |
| | Contratos | CRUD Completo |
| | Valores por Período | CRUD Especial |
| | Responsáveis | CRUD Completo |
| | Documentos | CRUD Completo |
| **Sprint 5** | Projetos | CRUD Completo + Cópia |
| | Empresas do Projeto | CRUD Completo |
| | Metas do Projeto | CRUD Completo |
| **Sprint 6** | Atividades | CRUD Completo |
| | Profissionais (Candidaturas) | CRUD Completo |
| | Recursos Necessários | CRUD Completo |
| | Certificações (Atividade) | CRUD Completo |
| | Certificações (Usuário) | CRUD Completo |
| **Sprint 7** | Medições | CRUD Completo |
| | Pagamentos | CRUD Completo |
| | Ajustes Financeiros | CRUD Especial |
| | Custos Extras | CRUD Completo |
| **Sprint 8** | Registros de Ponto | Create + Read |
| | Contestações | CRUD Completo |
| | Localizações Válidas | CRUD Completo |
| **Sprint 9** | Metas Individuais | CRUD Completo |
| | Badges | CRUD Completo (Admin) |
| | Avaliações | CRUD Completo |

### Cadastros SEM CRUD (Automáticos/Somente Leitura):
- Logs de Atividades
- Histórico de Projetos
- Itens da Medição
- Alertas de Ponto
- Pontuação dos Usuários
- Badges dos Usuários

### Template Padrão de CRUD:
Cada CRUD completo implementa:
- ✅ **Create**: Formulário + validações (client/server) + CSRF
- ✅ **Read**: Listagem paginada + filtros + busca + ordenação + exportação
- ✅ **Update**: Edição + validações + histórico + auditoria
- ✅ **Delete**: Soft delete + verificações + confirmação + log
- ✅ **Segurança**: Autorização por perfil + logs + sanitização
- ✅ **UX**: Design responsivo + feedback visual + tooltips

### Tempo Estimado:
- **Por CRUD**: 1-2 dias
- **Total**: ~12 semanas (conforme planejado)

### Documentação de Referência:
- `docs/REVISAO_CRUD_COMPLETO.md` - Análise detalhada de todos os CRUDs
- Checklist de implementação para cada cadastro
- Template padrão a seguir

---

**Documento preparado seguindo Metodologia Scrum**  
**Versão: 1.0.0**  
**Data: 2024-01-10**  
**Revisado: Com CRUD Completo Verificado**
