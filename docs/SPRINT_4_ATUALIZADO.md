# 📋 SPRINT 4: EMPRESAS E CONTRATOS - DOCUMENTAÇÃO ATUALIZADA

## 🎯 STATUS: ⏳ PLANEJADA (Aguardando início)

**Data de Atualização:** 2024-11-05  
**Versão:** 2.0 (Atualizada com correções das Sprints 1-3)

---

## 📌 INFORMAÇÕES DA SPRINT

- **Duração Estimada**: 10 dias úteis (2 semanas)
- **CRUDs Principais**: 7 (Tomadoras, Prestadoras, Serviços, Contratos, Valores, Responsáveis, Documentos)
- **Complexidade**: Média-Alta
- **Prioridade**: Core do sistema
- **Dependências**: Sprints 1, 2, 3 completas e corrigidas

---

## 🔧 CONTEXTO DAS CORREÇÕES APLICADAS

Esta sprint será desenvolvida **APÓS** a aplicação de todas as correções identificadas nas Sprints 1-3:

### Correções que Impactam Esta Sprint:

#### 1. Estrutura de Namespaces ✅
```php
// CORRETO (a ser seguido):
namespace App\Controllers;
namespace App\Models;

// INCORRETO (não usar):
namespace App\Helpers\Controllers;
namespace App\Helpers\Models;
```

**Impacto na Sprint 4:**
- Todos os controllers devem usar `namespace App\Controllers;`
- Todos os models devem usar `namespace App\Models;`
- Imports devem usar `use App\Controllers\EmpresaController;`

#### 2. Autoloader PSR-4 Corrigido ✅
```php
// index.php com autoloader funcional
spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\') === 0) {
        $class = substr($class, 4);
    }
    $file = SRC_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    $file = preg_replace_callback('/\/([A-Z][a-z]+)\//', function($matches) {
        return '/' . strtolower($matches[1]) . '/';
    }, $file);
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});
```

**Impacto na Sprint 4:**
- Classes serão carregadas automaticamente
- Não precisa de `require_once` manual nos controllers
- Estrutura de pastas: `src/controllers/`, `src/models/`

#### 3. BASE_URL para Subfolder ✅
```php
// config/config.php
'base_url' => 'https://prestadores.clinfec.com.br',

// .htaccess
RewriteBase /prestadores/
```

**Impacto na Sprint 4:**
- Todos os redirects devem usar `BASE_URL . '/?page=empresas'`
- Links de ações devem incluir `/prestadores/`
- Assets (CSS, JS, imagens) devem usar caminhos relativos ou com BASE_URL

#### 4. Session Variables Padronizadas ✅
```php
// AuthController corrigido
$_SESSION['user_id'] = $usuario['id'];
$_SESSION['usuario_id'] = $usuario['id'];  // Compatibilidade
$_SESSION['usuario_nome'] = $usuario['nome'];
$_SESSION['usuario_email'] = $usuario['email'];
$_SESSION['usuario_perfil'] = $usuario['perfil'];
```

**Impacto na Sprint 4:**
- Verificações de autenticação funcionarão corretamente
- RBAC (permissões por perfil) operacional
- Audit logs terão user_id correto

---

## 📚 OBJETIVOS DA SPRINT 4

### Principais Entregas:
1. **CRUD Empresas Tomadoras** (clientes que contratam)
2. **CRUD Empresas Prestadoras** (fornecedores de serviços)
3. **CRUD Serviços** (melhorado com categorização)
4. **CRUD Contratos** (entre tomadoras e prestadoras)
5. **CRUD Valores de Serviços por Período** (histórico de preços)
6. **CRUD Responsáveis** (contatos das empresas tomadoras)
7. **CRUD Documentos** (upload e gestão de arquivos)

### Conceitos Principais:

**Sistema Multi-Tenant:**
- Múltiplas empresas tomadoras (clientes)
- Cada tomadora contrata várias prestadoras
- Isolamento de dados por empresa tomadora

**Diferenciação de Empresas:**
- **Tomadoras:** Empresas que contratam serviços (ex: Clinfec)
- **Prestadoras:** Empresas que fornecem profissionais/serviços

**Gestão de Contratos:**
- Contratos vinculam tomadora + prestadora
- Valores de serviços variam por período
- Histórico completo de negociações

---

## 🗄️ ESTRUTURA DE BANCO DE DADOS

### 1. Empresas Tomadoras

```sql
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2. Empresas Prestadoras (Renomear tabela existente)

```sql
-- Renomear tabela existente
RENAME TABLE empresas TO empresas_prestadoras;

-- Adicionar novos campos
ALTER TABLE empresas_prestadoras 
ADD COLUMN tipo_prestador ENUM('pj', 'pf', 'mei') DEFAULT 'pj' AFTER cnpj,
ADD COLUMN cpf VARCHAR(14) NULL AFTER tipo_prestador COMMENT 'Para PF';
```

### 3. Contratos

```sql
CREATE TABLE contratos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_tomadora_id INT NOT NULL,
    empresa_prestadora_id INT NOT NULL,
    numero_contrato VARCHAR(100),
    descricao TEXT,
    data_inicio DATE NOT NULL,
    data_fim DATE NULL COMMENT 'NULL = indeterminado',
    valor_total DECIMAL(15,2),
    status ENUM('ativo', 'suspenso', 'encerrado') DEFAULT 'ativo',
    observacoes TEXT,
    
    -- Arquivos
    arquivo_contrato VARCHAR(500) COMMENT 'Path do PDF do contrato',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    
    FOREIGN KEY (empresa_tomadora_id) REFERENCES empresas_tomadoras(id),
    FOREIGN KEY (empresa_prestadora_id) REFERENCES empresas_prestadoras(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    INDEX idx_status (status),
    INDEX idx_datas (data_inicio, data_fim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4. Valores de Serviços por Período

```sql
CREATE TABLE servico_valores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contrato_id INT NOT NULL,
    servico_id INT NOT NULL,
    
    -- Período de validade
    data_inicio DATE NOT NULL,
    data_fim DATE NULL COMMENT 'NULL = vigente até novo valor',
    
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
    valor_hora_extra DECIMAL(10,2) COMMENT 'Valor para horas extras (opcional)',
    valor_jornada_curta DECIMAL(10,2) COMMENT 'Valor para jornadas até 6h (opcional)',
    
    -- Limites
    horas_mes_limite INT COMMENT 'Limite de horas por mês',
    horas_dia_limite INT DEFAULT 12 COMMENT 'Limite de horas por dia',
    
    observacoes TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    
    FOREIGN KEY (contrato_id) REFERENCES contratos(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servicos(id),
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    INDEX idx_periodo (data_inicio, data_fim),
    INDEX idx_ativo (ativo),
    INDEX idx_contrato_servico (contrato_id, servico_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 5. Responsáveis das Empresas Tomadoras

```sql
CREATE TABLE empresa_tomadora_responsaveis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_tomadora_id INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    cargo VARCHAR(100),
    email VARCHAR(255),
    telefone VARCHAR(20),
    celular VARCHAR(20),
    departamento VARCHAR(100),
    principal BOOLEAN DEFAULT FALSE COMMENT 'Contato principal',
    observacoes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (empresa_tomadora_id) REFERENCES empresas_tomadoras(id) ON DELETE CASCADE,
    INDEX idx_principal (principal),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 6. Documentos das Empresas

```sql
CREATE TABLE empresa_documentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    tipo_empresa ENUM('tomadora', 'prestadora') NOT NULL,
    tipo_documento ENUM('contrato', 'certidao', 'licenca', 'alvara', 'outros') NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    arquivo_path VARCHAR(500) NOT NULL,
    descricao TEXT,
    data_validade DATE COMMENT 'Para documentos que expiram',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    INDEX idx_empresa (empresa_id, tipo_empresa),
    INDEX idx_tipo (tipo_documento),
    INDEX idx_validade (data_validade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 📋 FUNCIONALIDADES DETALHADAS

### 1. Empresas Tomadoras - CRUD Completo

#### Create (Criação)
- [x] Formulário completo com 6 seções:
  - Dados cadastrais (razão social, CNPJ, etc)
  - Endereço (com busca automática por CEP)
  - Contatos (múltiplos telefones, email, site)
  - Financeiro (dias de fechamento e pagamento)
  - Observações
  - Upload de logo
- [x] Validações:
  - CNPJ único (verificar duplicidade)
  - CNPJ válido (algoritmo de validação)
  - Email válido
  - CEP válido (formato)
  - Campos obrigatórios
- [x] Token CSRF
- [x] Sanitização de inputs
- [x] Log de criação

#### Read (Listagem/Visualização)
- [x] Listagem paginada (25 itens por página)
- [x] Filtros múltiplos:
  - Nome/Razão Social
  - CNPJ
  - Cidade
  - Estado
  - Status (ativo/inativo)
- [x] Busca avançada (qualquer campo)
- [x] Ordenação por colunas clicáveis
- [x] Exportação (CSV, Excel, PDF)
- [x] Visualização detalhada:
  - Todas as informações cadastrais
  - Lista de contratos ativos
  - Lista de responsáveis
  - Lista de documentos
  - Histórico de alterações
  - Estatísticas (total de contratos, valor total, etc)

#### Update (Edição)
- [x] Editar todos os campos
- [x] Validação de CNPJ único (exceto próprio)
- [x] Histórico de alterações (log de auditoria)
- [x] Campos auditáveis:
  - Campo alterado
  - Valor anterior
  - Valor novo
  - Usuário que alterou
  - Data/hora da alteração
- [x] Notificação ao responsável (opcional)

#### Delete (Exclusão)
- [x] Soft delete (ativo = FALSE)
- [x] Verificações antes de excluir:
  - Existem contratos ativos?
  - Existem projetos em andamento?
  - Existem pagamentos pendentes?
- [x] Confirmação obrigatória (modal)
- [x] Motivo da exclusão (campo obrigatório)
- [x] Log de exclusão
- [x] Apenas perfis Admin e Master

### 2. Empresas Prestadoras - CRUD Completo

#### Diferenciação por Tipo:
1. **PJ (Pessoa Jurídica):**
   - CNPJ obrigatório
   - Razão Social e Nome Fantasia
   - Inscrição Estadual/Municipal
   
2. **PF (Pessoa Física):**
   - CPF obrigatório
   - Nome completo
   - RG (opcional)
   
3. **MEI (Microempreendedor Individual):**
   - CNPJ obrigatório
   - CPF do proprietário
   - Nome Fantasia

#### Create
- [x] Seleção de tipo (PJ/PF/MEI)
- [x] Formulário dinâmico conforme tipo
- [x] Validação CPF ou CNPJ (únicos)
- [x] Vincular serviços prestados (select múltiplo)
- [x] Dados bancários (para pagamentos)
- [x] Token CSRF

#### Read
- [x] Listagem paginada
- [x] Filtros:
  - Tipo (PJ/PF/MEI)
  - Nome/Razão Social
  - CPF/CNPJ
  - Serviços prestados
  - Status (ativo/inativo)
  - Cidade/Estado
- [x] Busca por serviço oferecido
- [x] Exportação

#### Update
- [x] Editar conforme tipo
- [x] Atualizar serviços vinculados
- [x] Histórico de alterações
- [x] Log de auditoria

#### Delete
- [x] Soft delete
- [x] Verificações:
  - Contratos ativos?
  - Profissionais vinculados em atividades?
  - Pagamentos pendentes?
- [x] Confirmação obrigatória

### 3. Serviços - CRUD Melhorado

#### Create
- [x] Nome e descrição
- [x] Categoria do serviço (select)
- [x] Unidade de medida padrão (hora, dia, mês, etc)
- [x] Valor sugerido (opcional)
- [x] Habilidades requeridas (tags)

#### Read
- [x] Listagem agrupada por categoria
- [x] Filtros: categoria, ativo/inativo
- [x] Busca por nome ou descrição
- [x] Mostrar quantas empresas prestam cada serviço

#### Update
- [x] Editar todos os campos
- [x] Histórico de alterações

#### Delete
- [x] Soft delete
- [x] Verificar se está em uso:
  - Em contratos vigentes?
  - Em atividades ativas?
- [x] Confirmação obrigatória

### 4. Contratos - CRUD Completo

#### Create
- [x] Selecionar tomadora
- [x] Selecionar prestadora
- [x] Número do contrato (único)
- [x] Vigência (data início, data fim ou indeterminado)
- [x] Valor total estimado
- [x] Upload do PDF do contrato
- [x] Observações

#### Read
- [x] Listagem paginada
- [x] Filtros:
  - Status (ativo, suspenso, encerrado)
  - Tomadora
  - Prestadora
  - Vigência (por período)
- [x] Alertas:
  - Vencimento em 30 dias
  - Vencimento em 15 dias
  - Vencidos
- [x] Visualização detalhada:
  - Dados completos
  - Lista de valores de serviços
  - Histórico de aditivos
  - Documentos anexos
  - Projetos vinculados
  - Estatísticas financeiras

#### Update
- [x] Atualizar datas, valores, status
- [x] Registrar aditivos contratuais:
  - Número do aditivo
  - Data
  - Descrição das alterações
  - Valor acrescido/decrescido
  - Upload do documento
- [x] Histórico completo de alterações
- [x] Upload de novos documentos

#### Delete
- [x] Soft delete (status = 'encerrado')
- [x] Verificações:
  - Valores a pagar pendentes?
  - Atividades em andamento?
  - Medições em aberto?
- [x] Motivo do encerramento
- [x] Confirmação obrigatória

### 5. Valores de Serviços por Período - CRUD Especial

**Conceito:** Valores de serviços podem mudar ao longo do tempo. O sistema mantém histórico completo.

#### Create
- [x] Selecionar contrato
- [x] Selecionar serviço
- [x] Período de vigência (início, fim)
- [x] Tipo de remuneração:
  - Por hora
  - Por dia
  - Por mês
  - Por semana (5, 6 ou 7 dias)
  - Por entrega
- [x] Valores:
  - Valor base
  - Valor hora extra (opcional)
  - Valor jornada curta (opcional)
- [x] Limites:
  - Horas por dia (máximo)
  - Horas por mês (máximo)
- [x] Validações:
  - Não permitir períodos sobrepostos para mesmo contrato+serviço
  - Data início deve ser >= data início do contrato
  - Data fim deve ser <= data fim do contrato (se definida)

#### Read
- [x] Listagem por contrato
- [x] Timeline visual de valores
- [x] Filtros:
  - Vigente (data atual entre início e fim)
  - Expirado
  - Futuro
- [x] Comparativo de valores:
  - Valor atual vs anterior
  - Percentual de variação
  - Histórico completo

#### Update (Especial)
- [x] **NÃO permite alterar registros existentes**
- [x] Para "atualizar" um valor:
  1. Define data_fim do valor atual = data anterior ao novo
  2. Cria novo registro com nova data_inicio
- [x] Assim, mantém histórico imutável

#### Delete
- [x] **NÃO permite exclusão**
- [x] Apenas inativar (ativo = FALSE)
- [x] Se necessário corrigir:
  1. Inativar registro errado
  2. Criar novo registro correto

### 6. Responsáveis - CRUD Completo

Sub-CRUD dentro de Empresas Tomadoras

#### Create
- [x] Nome completo
- [x] Cargo/função
- [x] Departamento
- [x] Email
- [x] Telefone e celular
- [x] Marcar como principal (apenas um por empresa)
- [x] Observações

#### Read
- [x] Listagem na página da empresa
- [x] Destaque para responsável principal
- [x] Ordenação alfabética

#### Update
- [x] Editar todos os campos
- [x] Ao marcar como principal, desmarcar os outros

#### Delete
- [x] Exclusão permanente (se não houver registros vinculados)
- [x] Confirmação obrigatória
- [x] Não permitir excluir se for o único responsável

### 7. Documentos - CRUD Completo

Sub-CRUD para ambas as empresas (tomadoras e prestadoras)

#### Create (Upload)
- [x] Selecionar tipo de documento:
  - Contrato
  - Certidão (negativa, regularidade, etc)
  - Licença
  - Alvará
  - Outros
- [x] Upload de arquivo (PDF, DOC, XLS, imagens)
- [x] Descrição do documento
- [x] Data de validade (opcional)
- [x] Validações:
  - Tamanho máximo 10MB
  - Extensões permitidas
  - Nome do arquivo sanitizado

#### Read
- [x] Listagem na página da empresa
- [x] Filtros por tipo
- [x] Alertas de vencimento:
  - Vermelho: vencido
  - Amarelo: vence em 30 dias
  - Verde: válido
- [x] Download do arquivo

#### Update
- [x] Substituir arquivo
- [x] Atualizar descrição e validade

#### Delete
- [x] Exclusão permanente do registro e arquivo
- [x] Confirmação obrigatória
- [x] Log de exclusão

---

## 🛡️ SEGURANÇA E PERMISSÕES (RBAC)

### Perfis e Permissões:

| Ação | Master | Admin | Gestor | Usuario |
|------|--------|-------|--------|---------|
| **Empresas Tomadoras** |
| Criar | ✅ | ✅ | ❌ | ❌ |
| Visualizar | ✅ | ✅ | ✅ (própria) | ✅ (própria) |
| Editar | ✅ | ✅ | ❌ | ❌ |
| Excluir | ✅ | ✅ | ❌ | ❌ |
| **Empresas Prestadoras** |
| Criar | ✅ | ✅ | ❌ | ❌ |
| Visualizar | ✅ | ✅ | ✅ | ❌ |
| Editar | ✅ | ✅ | ❌ | ❌ |
| Excluir | ✅ | ✅ | ❌ | ❌ |
| **Serviços** |
| Criar | ✅ | ✅ | ❌ | ❌ |
| Visualizar | ✅ | ✅ | ✅ | ✅ |
| Editar | ✅ | ✅ | ❌ | ❌ |
| Excluir | ✅ | ✅ | ❌ | ❌ |
| **Contratos** |
| Criar | ✅ | ✅ | ❌ | ❌ |
| Visualizar | ✅ | ✅ | ✅ (vinculado) | ❌ |
| Editar | ✅ | ✅ | ❌ | ❌ |
| Encerrar | ✅ | ✅ | ❌ | ❌ |
| **Valores de Serviços** |
| Criar | ✅ | ✅ | ❌ | ❌ |
| Visualizar | ✅ | ✅ | ✅ (vinculado) | ❌ |
| Inativar | ✅ | ✅ | ❌ | ❌ |

---

## 📊 ESTRUTURA DE ARQUIVOS A CRIAR

```
src/
├── controllers/
│   ├── EmpresaTomadoraController.php (NOVO)
│   ├── EmpresaPrestadoraController.php (NOVO)
│   ├── ServicoController.php (ATUALIZAR)
│   ├── ContratoController.php (NOVO)
│   ├── ServicoValorController.php (NOVO)
│   ├── ResponsavelController.php (NOVO)
│   └── DocumentoController.php (NOVO)
├── models/
│   ├── EmpresaTomadora.php (NOVO)
│   ├── EmpresaPrestadora.php (RENOMEAR de Empresa.php)
│   ├── Servico.php (ATUALIZAR)
│   ├── Contrato.php (NOVO)
│   ├── ServicoValor.php (NOVO)
│   ├── Responsavel.php (NOVO)
│   └── Documento.php (NOVO)
├── views/
│   ├── empresas_tomadoras/
│   │   ├── index.php
│   │   ├── form.php
│   │   ├── view.php
│   │   └── _responsaveis.php (partial)
│   ├── empresas_prestadoras/
│   │   ├── index.php
│   │   ├── form.php
│   │   └── view.php
│   ├── contratos/
│   │   ├── index.php
│   │   ├── form.php
│   │   ├── view.php
│   │   └── _valores.php (partial)
│   ├── servicos/
│   │   ├── index.php (ATUALIZAR)
│   │   └── form.php (ATUALIZAR)
│   └── documentos/
│       ├── _lista.php (partial)
│       └── _upload.php (partial/modal)
└── database/
    └── migrations/
        └── 004_criar_empresas_contratos.sql (NOVO)
```

---

## 🧪 CHECKLIST DE TESTES

### Empresas Tomadoras:
- [ ] Criar nova empresa tomadora
- [ ] Validação de CNPJ duplicado
- [ ] Busca por CEP (ViaCEP API)
- [ ] Upload de logo
- [ ] Editar empresa
- [ ] Soft delete com verificações
- [ ] Adicionar responsáveis
- [ ] Upload de documentos
- [ ] Alertas de documentos vencidos

### Empresas Prestadoras:
- [ ] Criar PJ, PF, MEI
- [ ] Validação CPF/CNPJ únicos
- [ ] Vincular serviços
- [ ] Editar e atualizar serviços
- [ ] Soft delete com verificações
- [ ] Listagem filtrada por tipo

### Contratos:
- [ ] Criar contrato tomadora-prestadora
- [ ] Upload de PDF do contrato
- [ ] Alertas de vencimento
- [ ] Criar aditivo contratual
- [ ] Encerrar contrato
- [ ] Verificações ao encerrar

### Valores de Serviços:
- [ ] Criar valor para contrato+serviço
- [ ] Validar sobreposição de períodos
- [ ] Timeline de valores
- [ ] Criar novo período (inativa anterior)
- [ ] Comparativo de valores

### Segurança:
- [ ] CSRF tokens em todos os forms
- [ ] Sanitização de inputs
- [ ] Permissões RBAC
- [ ] Logs de auditoria
- [ ] Upload seguro de arquivos

---

## 📈 MÉTRICAS DE SUCESSO

- **CRUDs Completos:** 7/7
- **Testes Unitários:** 100% coverage nos models
- **Testes de Integração:** Fluxos completos
- **Performance:** < 100ms por requisição
- **Segurança:** 0 vulnerabilidades conhecidas
- **Documentação:** 100% das funcionalidades documentadas

---

## 🔗 DEPENDÊNCIAS E INTEGRAÇÕES

### Dependências Externas:
- **ViaCEP API:** Busca de endereço por CEP
- **ValidatorBR:** Validação de CPF e CNPJ

### Integrações Internas:
- **Sistema de Autenticação:** Verificação de perfis
- **Sistema de Logs:** Auditoria de ações
- **Upload Manager:** Gestão de arquivos
- **Database Migration:** Criação de tabelas

---

## 📝 NOTAS IMPORTANTES

1. **Esta Sprint NÃO deve ser iniciada antes de:**
   - ✅ Conclusão total das Sprints 1, 2, 3
   - ✅ Aplicação de TODAS as correções identificadas
   - ✅ Testes de integração das correções
   - ✅ Deploy e validação em produção

2. **Seguir rigorosamente:**
   - Namespaces corretos (`App\Controllers`, `App\Models`)
   - BASE_URL em todos os redirects e links
   - Session variables padronizadas
   - Estrutura PSR-4

3. **Validações críticas:**
   - CNPJ/CPF únicos no banco
   - Períodos de valores sem sobreposição
   - Verificações antes de soft delete
   - Upload de arquivos seguro

4. **Performance:**
   - Índices em todas as foreign keys
   - Índices em campos de busca frequente
   - Paginação em todas as listagens
   - Cache de queries complexas

---

## 🚀 PRÓXIMOS PASSOS

1. ✅ Revisar este documento atualizado
2. ⏳ Aguardar conclusão das Sprints 1-3
3. ⏳ Criar migrations do banco (arquivo 004)
4. ⏳ Implementar models com métodos CRUD
5. ⏳ Implementar controllers com ações
6. ⏳ Criar views responsivas
7. ⏳ Testes unitários e de integração
8. ⏳ Code review e ajustes
9. ⏳ Deploy em staging
10. ⏳ Validação e deploy em produção

---

**Documento atualizado em:** 2024-11-05  
**Versão:** 2.0  
**Status:** Aguardando início (dependente de Sprints 1-3)  
**Referências:** Correções aplicadas nos commits 2f69a28, fb4809e, 7c9e8a2, da648df
