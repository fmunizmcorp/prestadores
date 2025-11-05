# 📋 SPRINT 5: PROJETOS - DOCUMENTAÇÃO COMPLETA

## 📌 INFORMAÇÕES DA SPRINT

- **Duração**: 15 dias úteis (3 semanas)
- **CRUDs**: 3 principais (Projetos, Orçamentos, Alocações)
- **Complexidade**: Alta
- **Prioridade**: Core do sistema
- **Status**: ⏳ PLANEJADA (Aguardando Sprint 4)

---

## 🔧 CONTEXTO DAS CORREÇÕES

**IMPORTANTE:** Esta sprint será desenvolvida **APÓS** as correções aplicadas nas Sprints 1-3.

### Correções que Impactam Esta Sprint:

1. **Namespaces Corretos:**
   - Controllers: `namespace App\Controllers;`
   - Models: `namespace App\Models;`
   
2. **BASE_URL em Redirects:**
   - Todos os redirects: `header('Location: ' . BASE_URL . '/?page=projetos');`
   
3. **Session Variables:**
   - Usar `$_SESSION['user_id']` e `$_SESSION['usuario_id']`
   
4. **Autoloader PSR-4:**
   - Classes carregam automaticamente
   - Estrutura: `src/controllers/`, `src/models/`

Para detalhes completos das correções, ver:
- `docs/SPRINT_1_2_3_ATUALIZADO.md`
- `docs/RESUMO_CORRECOES_APLICADAS.md`

---

## 📚 CONTEXTO

Esta sprint implementa a **gestão completa de projetos**, incluindo:
- Criação e acompanhamento de projetos
- Orçamento detalhado e controle de custos
- Alocação de profissionais
- Fases, marcos e riscos
- Change requests
- Relatórios financeiros em tempo real

**Dependências**:
- ✅ Sprint 1, 2, 3 completas e corrigidas
- ⏳ Sprint 4 completa (Empresas e Contratos)
- ⏳ Tabelas de empresas, contratos e serviços criadas

---

## ✅ JÁ IMPLEMENTADO (Dias 1-4)

Ver arquivo principal `PLANEJAMENTO_ULTRA_DETALHADO.md`:
- ✅ Dias 1-2: Estrutura de banco (7 tabelas)
- ✅ Dias 3-4: Model Projeto.php (40+ métodos)

**NOTA:** Códigos deste documento devem ser adaptados com as correções de namespace e BASE_URL.

---

## 📅 DIAS 5-6: CONTROLLER E VIEWS DE PROJETOS

### 1. Controller: ProjetoController.php

**Localização**: `src/controllers/ProjetoController.php`

**Estrutura Completa**:

```php
<?php

namespace App\Controllers;

use App\Models\Projeto;
use App\Models\Contrato;
use App\Models\EmpresaTomadora;
use App\Models\EmpresaPrestadora;
use App\Models\Usuario;

class ProjetoController extends Controller {
    
    private $projetoModel;
    
    public function __construct() {
        parent::__construct();
        $this->projetoModel = new Projeto();
        
        // Verificar autenticação
        $this->requireAuth();
    }
    
    // ============================================
    // CRUD BÁSICO
    // ============================================
    
    /**
     * GET /projetos
     * Listagem de projetos com filtros
     */
    public function index() {
        // Verificar permissão
        $this->checkPermission(['admin', 'master', 'gestor']);
        
        // Obter filtros da query string
        $filtros = [
            'status' => $_GET['status'] ?? null,
            'contrato_id' => $_GET['contrato_id'] ?? null,
            'empresa_tomadora_id' => $_GET['empresa_tomadora_id'] ?? null,
            'empresa_prestadora_id' => $_GET['empresa_prestadora_id'] ?? null,
            'gestor_projeto_id' => $_GET['gestor_projeto_id'] ?? null,
            'data_inicio' => $_GET['data_inicio'] ?? null,
            'data_fim' => $_GET['data_fim'] ?? null,
            'search' => $_GET['search'] ?? null,
            'order_by' => $_GET['order_by'] ?? 'p.created_at',
            'order_dir' => $_GET['order_dir'] ?? 'DESC'
        ];
        
        // Paginação
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 20;
        
        // Buscar projetos
        $projetos = $this->projetoModel->all($filtros, $page, $limit);
        $total = $this->projetoModel->count($filtros);
        $totalPages = ceil($total / $limit);
        
        // Estatísticas para cards
        $stats = $this->projetoModel->getEstatisticasGerais();
        
        // Carregar dados para filtros
        $contratos = (new Contrato())->getAtivos();
        $empresasTomadoras = (new EmpresaTomadora())->getAtivas();
        $empresasPrestadoras = (new EmpresaPrestadora())->getAtivas();
        $gestores = (new Usuario())->getByPerfil(['gestor', 'admin', 'master']);
        
        // Carregar view
        $data = [
            'projetos' => $projetos,
            'stats' => $stats,
            'contratos' => $contratos,
            'empresasTomadoras' => $empresasTomadoras,
            'empresasPrestadoras' => $empresasPrestadoras,
            'gestores' => $gestores,
            'filtros' => $filtros,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => $totalPages,
            'titulo' => 'Gestão de Projetos'
        ];
        
        $this->view('projetos/index', $data);
    }
    
    /**
     * GET /projetos/{id}
     * Visualização detalhada do projeto
     */
    public function show($id) {
        // Buscar projeto
        $projeto = $this->projetoModel->findById($id);
        
        if (!$projeto) {
            $this->setFlash('error', 'Projeto não encontrado');
            return $this->redirect('/projetos');
        }
        
        // Verificar permissão (usuário vinculado ou admin)
        if (!$this->canAccessProjeto($projeto)) {
            $this->setFlash('error', 'Sem permissão para acessar este projeto');
            return $this->redirect('/projetos');
        }
        
        // Carregar dados relacionados
        $fases = $this->projetoModel->getFases($id);
        $marcos = $this->projetoModel->getMarcos($id);
        $riscos = $this->projetoModel->getRiscos($id);
        $mudancas = $this->projetoModel->getMudancas($id);
        $anexos = $this->projetoModel->getAnexos($id);
        $historico = $this->projetoModel->getHistorico($id);
        $alocacoes = $this->projetoModel->getAlocacoes($id);
        
        // Calcular estatísticas
        $estatisticas = [
            'total_fases' => count($fases),
            'fases_concluidas' => count(array_filter($fases, fn($f) => $f['status'] == 'concluida')),
            'total_marcos' => count($marcos),
            'marcos_concluidos' => count(array_filter($marcos, fn($m) => $m['concluido'])),
            'total_riscos' => count($riscos),
            'riscos_criticos' => count(array_filter($riscos, fn($r) => $r['nivel_risco'] >= 15)),
            'total_mudancas' => count($mudancas),
            'mudancas_pendentes' => count(array_filter($mudancas, fn($m) => $m['status'] == 'solicitada')),
            'total_profissionais' => count($alocacoes),
            'profissionais_ativos' => count(array_filter($alocacoes, fn($a) => $a['status'] == 'ativa'))
        ];
        
        $data = [
            'projeto' => $projeto,
            'fases' => $fases,
            'marcos' => $marcos,
            'riscos' => $riscos,
            'mudancas' => $mudancas,
            'anexos' => $anexos,
            'historico' => $historico,
            'alocacoes' => $alocacoes,
            'estatisticas' => $estatisticas,
            'titulo' => $projeto['codigo_projeto'] . ' - ' . $projeto['nome']
        ];
        
        $this->view('projetos/view', $data);
    }
    
    /**
     * GET /projetos/create
     * Formulário de criação
     */
    public function create() {
        $this->checkPermission(['admin', 'master', 'gestor']);
        
        // Carregar dados para selects
        $contratos = (new Contrato())->getVigentes();
        $empresasTomadoras = (new EmpresaTomadora())->getAtivas();
        $empresasPrestadoras = (new EmpresaPrestadora())->getAtivas();
        $gestores = (new Usuario())->getByPerfil(['gestor', 'admin', 'master']);
        
        $data = [
            'contratos' => $contratos,
            'empresasTomadoras' => $empresasTomadoras,
            'empresasPrestadoras' => $empresasPrestadoras,
            'gestores' => $gestores,
            'titulo' => 'Novo Projeto'
        ];
        
        $this->view('projetos/form', $data);
    }
    
    /**
     * POST /projetos
     * Salvar novo projeto
     */
    public function store() {
        $this->checkPermission(['admin', 'master', 'gestor']);
        $this->validateCsrfToken();
        
        try {
            // Sanitizar dados
            $data = [
                'codigo_projeto' => $this->sanitize($_POST['codigo_projeto'] ?? ''),
                'nome' => $this->sanitize($_POST['nome']),
                'descricao' => $this->sanitize($_POST['descricao'] ?? ''),
                'contrato_id' => (int)$_POST['contrato_id'],
                'empresa_tomadora_id' => (int)$_POST['empresa_tomadora_id'],
                'empresa_prestadora_id' => (int)$_POST['empresa_prestadora_id'],
                'data_inicio' => $_POST['data_inicio'],
                'data_fim_prevista' => $_POST['data_fim_prevista'],
                'endereco_execucao' => $this->sanitize($_POST['endereco_execucao'] ?? ''),
                'cidade' => $this->sanitize($_POST['cidade'] ?? ''),
                'estado' => $this->sanitize($_POST['estado'] ?? ''),
                'cep' => $this->sanitize($_POST['cep'] ?? ''),
                'latitude' => $_POST['latitude'] ?? null,
                'longitude' => $_POST['longitude'] ?? null,
                'requer_presenca_fisica' => isset($_POST['requer_presenca_fisica']) ? 1 : 0,
                'valor_orcado' => $this->parseMoeda($_POST['valor_orcado']),
                'valor_gasto' => 0,
                'valor_previsto_restante' => $this->parseMoeda($_POST['valor_orcado']),
                'percentual_gasto' => 0,
                'percentual_concluido' => 0,
                'status' => 'planejamento',
                'gestor_projeto_id' => (int)$_POST['gestor_projeto_id'],
                'responsavel_tomadora_id' => (int)($_POST['responsavel_tomadora_id'] ?? 0) ?: null,
                'responsavel_prestadora_id' => (int)($_POST['responsavel_prestadora_id'] ?? 0) ?: null,
                'permite_hora_extra' => isset($_POST['permite_hora_extra']) ? 1 : 0,
                'permite_trabalho_feriado' => isset($_POST['permite_trabalho_feriado']) ? 1 : 0,
                'permite_trabalho_fim_semana' => isset($_POST['permite_trabalho_fim_semana']) ? 1 : 0,
                'horas_semanais_padrao' => (float)($_POST['horas_semanais_padrao'] ?? 40),
                'alerta_orcamento_percentual' => (float)($_POST['alerta_orcamento_percentual'] ?? 80),
                'alerta_prazo_dias' => (int)($_POST['alerta_prazo_dias'] ?? 7),
                'notificar_estouro_orcamento' => isset($_POST['notificar_estouro_orcamento']) ? 1 : 0,
                'notificar_atraso_cronograma' => isset($_POST['notificar_atraso_cronograma']) ? 1 : 0,
                'observacoes' => $this->sanitize($_POST['observacoes'] ?? ''),
                'criado_por' => $this->getUsuarioId()
            ];
            
            // Validações
            $this->validateRequired($data, [
                'nome', 'contrato_id', 'empresa_tomadora_id', 
                'empresa_prestadora_id', 'data_inicio', 
                'data_fim_prevista', 'valor_orcado', 'gestor_projeto_id'
            ]);
            
            // Criar projeto
            $projetoId = $this->projetoModel->create($data);
            
            // Upload de arquivos (termo de abertura, plano de trabalho)
            if (!empty($_FILES['termo_abertura']['name'])) {
                $this->uploadDocumento($projetoId, 'termo_abertura');
            }
            
            if (!empty($_FILES['plano_trabalho']['name'])) {
                $this->uploadDocumento($projetoId, 'plano_trabalho');
            }
            
            // Enviar notificações
            $this->notificarCriacao($projetoId);
            
            // Registrar log
            $this->logAction('projeto_criado', [
                'projeto_id' => $projetoId,
                'codigo' => $data['codigo_projeto']
            ]);
            
            $this->setFlash('success', 'Projeto criado com sucesso!');
            return $this->redirect('/projetos/' . $projetoId);
            
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('/projetos/create');
        }
    }
    
    /**
     * GET /projetos/{id}/edit
     * Formulário de edição
     */
    public function edit($id) {
        $this->checkPermission(['admin', 'master', 'gestor']);
        
        $projeto = $this->projetoModel->findById($id);
        
        if (!$projeto) {
            $this->setFlash('error', 'Projeto não encontrado');
            return $this->redirect('/projetos');
        }
        
        // Não pode editar projeto concluído ou cancelado
        if (in_array($projeto['status'], ['concluido', 'cancelado'])) {
            $this->setFlash('error', 'Não é possível editar projeto ' . $projeto['status']);
            return $this->redirect('/projetos/' . $id);
        }
        
        // Carregar dados para selects
        $contratos = (new Contrato())->getVigentes();
        $empresasTomadoras = (new EmpresaTomadora())->getAtivas();
        $empresasPrestadoras = (new EmpresaPrestadora())->getAtivas();
        $gestores = (new Usuario())->getByPerfil(['gestor', 'admin', 'master']);
        
        $data = [
            'projeto' => $projeto,
            'contratos' => $contratos,
            'empresasTomadoras' => $empresasTomadoras,
            'empresasPrestadoras' => $empresasPrestadoras,
            'gestores' => $gestores,
            'titulo' => 'Editar Projeto: ' . $projeto['codigo_projeto']
        ];
        
        $this->view('projetos/form', $data);
    }
    
    /**
     * PUT /projetos/{id}
     * Atualizar projeto
     */
    public function update($id) {
        $this->checkPermission(['admin', 'master', 'gestor']);
        $this->validateCsrfToken();
        
        try {
            // Buscar projeto
            $projeto = $this->projetoModel->findById($id);
            
            if (!$projeto) {
                throw new \Exception('Projeto não encontrado');
            }
            
            // Validar status
            if (in_array($projeto['status'], ['concluido', 'cancelado'])) {
                throw new \Exception('Não é possível editar projeto ' . $projeto['status']);
            }
            
            // Sanitizar dados
            $data = [
                'nome' => $this->sanitize($_POST['nome']),
                'descricao' => $this->sanitize($_POST['descricao'] ?? ''),
                'data_inicio' => $_POST['data_inicio'],
                'data_fim_prevista' => $_POST['data_fim_prevista'],
                'endereco_execucao' => $this->sanitize($_POST['endereco_execucao'] ?? ''),
                'cidade' => $this->sanitize($_POST['cidade'] ?? ''),
                'estado' => $this->sanitize($_POST['estado'] ?? ''),
                'cep' => $this->sanitize($_POST['cep'] ?? ''),
                'latitude' => $_POST['latitude'] ?? null,
                'longitude' => $_POST['longitude'] ?? null,
                'requer_presenca_fisica' => isset($_POST['requer_presenca_fisica']) ? 1 : 0,
                'valor_orcado' => $this->parseMoeda($_POST['valor_orcado']),
                'gestor_projeto_id' => (int)$_POST['gestor_projeto_id'],
                'responsavel_tomadora_id' => (int)($_POST['responsavel_tomadora_id'] ?? 0) ?: null,
                'responsavel_prestadora_id' => (int)($_POST['responsavel_prestadora_id'] ?? 0) ?: null,
                'permite_hora_extra' => isset($_POST['permite_hora_extra']) ? 1 : 0,
                'permite_trabalho_feriado' => isset($_POST['permite_trabalho_feriado']) ? 1 : 0,
                'permite_trabalho_fim_semana' => isset($_POST['permite_trabalho_fim_semana']) ? 1 : 0,
                'horas_semanais_padrao' => (float)($_POST['horas_semanais_padrao'] ?? 40),
                'alerta_orcamento_percentual' => (float)($_POST['alerta_orcamento_percentual'] ?? 80),
                'alerta_prazo_dias' => (int)($_POST['alerta_prazo_dias'] ?? 7),
                'notificar_estouro_orcamento' => isset($_POST['notificar_estouro_orcamento']) ? 1 : 0,
                'notificar_atraso_cronograma' => isset($_POST['notificar_atraso_cronograma']) ? 1 : 0,
                'observacoes' => $this->sanitize($_POST['observacoes'] ?? ''),
                'atualizado_por' => $this->getUsuarioId()
            ];
            
            // Atualizar
            $this->projetoModel->update($id, $data);
            
            // Recalcular valores se orçamento mudou
            if ($data['valor_orcado'] != $projeto['valor_orcado']) {
                $this->projetoModel->atualizarValoresGastos($id);
            }
            
            $this->setFlash('success', 'Projeto atualizado com sucesso!');
            return $this->redirect('/projetos/' . $id);
            
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('/projetos/' . $id . '/edit');
        }
    }
    
    /**
     * DELETE /projetos/{id}
     * Excluir projeto
     */
    public function destroy($id) {
        $this->checkPermission(['admin', 'master']);
        $this->validateCsrfToken();
        
        try {
            $this->projetoModel->delete($id);
            
            $this->logAction('projeto_excluido', ['projeto_id' => $id]);
            
            $this->setFlash('success', 'Projeto excluído com sucesso!');
            return $this->redirect('/projetos');
            
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('/projetos/' . $id);
        }
    }
    
    // ============================================
    // AÇÕES DE STATUS
    // ============================================
    
    /**
     * PUT /projetos/{id}/iniciar
     * Iniciar projeto
     */
    public function iniciar($id) {
        $this->checkPermission(['admin', 'master', 'gestor']);
        $this->validateCsrfToken();
        
        try {
            $this->projetoModel->iniciar($id, $this->getUsuarioId());
            
            $this->setFlash('success', 'Projeto iniciado com sucesso!');
            return $this->redirect('/projetos/' . $id);
            
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('/projetos/' . $id);
        }
    }
    
    /**
     * PUT /projetos/{id}/pausar
     * Pausar projeto
     */
    public function pausar($id) {
        $this->checkPermission(['admin', 'master', 'gestor']);
        $this->validateCsrfToken();
        
        try {
            $motivo = $_POST['motivo'] ?? '';
            
            if (empty($motivo)) {
                throw new \Exception('Motivo da pausa é obrigatório');
            }
            
            $this->projetoModel->pausar($id, $motivo, $this->getUsuarioId());
            
            $this->setFlash('success', 'Projeto pausado');
            return $this->redirect('/projetos/' . $id);
            
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('/projetos/' . $id);
        }
    }
    
    /**
     * PUT /projetos/{id}/concluir
     * Concluir projeto
     */
    public function concluir($id) {
        $this->checkPermission(['admin', 'master', 'gestor']);
        $this->validateCsrfToken();
        
        try {
            $avaliacao = [
                'qualidade' => (float)$_POST['avaliacao_qualidade'],
                'prazo' => (float)$_POST['avaliacao_prazo'],
                'custo' => (float)$_POST['avaliacao_custo'],
                'comentario' => $this->sanitize($_POST['comentario_avaliacao'] ?? ''),
                'licoes' => $this->sanitize($_POST['licoes_aprendidas'] ?? '')
            ];
            
            $this->projetoModel->concluir($id, $avaliacao, $this->getUsuarioId());
            
            $this->setFlash('success', 'Projeto concluído com sucesso!');
            return $this->redirect('/projetos/' . $id);
            
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('/projetos/' . $id);
        }
    }
    
    /**
     * PUT /projetos/{id}/cancelar
     * Cancelar projeto
     */
    public function cancelar($id) {
        $this->checkPermission(['admin', 'master']);
        $this->validateCsrfToken();
        
        try {
            $motivo = $_POST['motivo_cancelamento'] ?? '';
            
            $this->projetoModel->cancelar($id, $motivo, $this->getUsuarioId());
            
            $this->setFlash('success', 'Projeto cancelado');
            return $this->redirect('/projetos/' . $id);
            
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('/projetos/' . $id);
        }
    }
    
    // [CONTINUA COM MAIS 30 MÉTODOS...]
    // Fases, Marcos, Riscos, Mudanças, Anexos, Relatórios, etc.
    
    // ============================================
    // MÉTODOS AUXILIARES
    // ============================================
    
    private function canAccessProjeto($projeto) {
        $usuario = $this->getUsuario();
        
        // Admin e Master podem tudo
        if (in_array($usuario['perfil'], ['admin', 'master'])) {
            return true;
        }
        
        // Gestor do projeto
        if ($projeto['gestor_projeto_id'] == $usuario['id']) {
            return true;
        }
        
        // Verificar se está alocado no projeto
        // ...
        
        return false;
    }
    
    private function uploadDocumento($projetoId, $tipo) {
        // Implementação de upload
        // ...
    }
    
    private function notificarCriacao($projetoId) {
        // Enviar notificações por email
        // ...
    }
}
```

### 2. Views: projetos/

#### a) index.php - Listagem de Projetos

**Localização**: `src/views/projetos/index.php`

**Layout Completo**:

```php
<?php
$this->layout('layout/main', ['titulo' => $titulo]);
?>

<!-- HEADER DA PÁGINA -->
<div class="page-header">
    <div class="header-left">
        <h1>
            <i class="fas fa-project-diagram"></i>
            Gestão de Projetos
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item active">Projetos</li>
            </ol>
        </nav>
    </div>
    <div class="header-right">
        <a href="/projetos/create" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Novo Projeto
        </a>
        <button class="btn btn-secondary" onclick="abrirModalExportacao()">
            <i class="fas fa-file-export"></i>
            Exportar
        </button>
    </div>
</div>

<!-- CARDS DE ESTATÍSTICAS -->
<div class="stats-row">
    <!-- Total de Projetos -->
    <div class="stat-card">
        <div class="stat-icon bg-blue">
            <i class="fas fa-project-diagram"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($stats['total_projetos']) ?></h3>
            <p>Total de Projetos</p>
            <small class="text-muted">Todos os status</small>
        </div>
    </div>
    
    <!-- Em Andamento -->
    <div class="stat-card">
        <div class="stat-icon bg-green">
            <i class="fas fa-play-circle"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($stats['em_andamento']) ?></h3>
            <p>Em Andamento</p>
            <small class="text-success">
                <i class="fas fa-arrow-up"></i>
                +<?= $stats['novos_mes'] ?> este mês
            </small>
        </div>
    </div>
    
    <!-- Valor Total -->
    <div class="stat-card">
        <div class="stat-icon bg-purple">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-content">
            <h3>R$ <?= number_format($stats['valor_total'], 2, ',', '.') ?></h3>
            <p>Valor Total Orçado</p>
            <small class="text-muted">Projetos ativos</small>
        </div>
    </div>
    
    <!-- Atrasados -->
    <div class="stat-card alert-card">
        <div class="stat-icon bg-red">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($stats['atrasados']) ?></h3>
            <p>Projetos Atrasados</p>
            <?php if ($stats['atrasados'] > 0): ?>
            <small class="text-danger">Requer atenção</small>
            <?php else: ?>
            <small class="text-success">Tudo em dia!</small>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Estouro Orçamento -->
    <div class="stat-card warning-card">
        <div class="stat-icon bg-orange">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($stats['estouro_orcamento']) ?></h3>
            <p>Estouro de Orçamento</p>
            <?php if ($stats['estouro_orcamento'] > 0): ?>
            <small class="text-warning">Acima de 100%</small>
            <?php else: ?>
            <small class="text-success">Dentro do previsto</small>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ÁREA PRINCIPAL COM FILTROS E LISTAGEM -->
<div class="content-with-sidebar">
    
    <!-- SIDEBAR DE FILTROS -->
    <aside class="filters-sidebar">
        <div class="filters-header">
            <h3><i class="fas fa-filter"></i> Filtros</h3>
            <button class="btn-link" onclick="limparFiltros()">
                <i class="fas fa-times"></i> Limpar
            </button>
        </div>
        
        <form id="formFiltros" method="GET" action="/projetos">
            
            <!-- Filtro: Status -->
            <div class="filter-group">
                <label>Status do Projeto</label>
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="status[]" value="planejamento" 
                               <?= in_array('planejamento', $filtros['status'] ?? []) ? 'checked' : '' ?>>
                        <span class="badge badge-secondary">Planejamento</span>
                    </label>
                    <label>
                        <input type="checkbox" name="status[]" value="orcamento"
                               <?= in_array('orcamento', $filtros['status'] ?? []) ? 'checked' : '' ?>>
                        <span class="badge badge-info">Orçamento</span>
                    </label>
                    <label>
                        <input type="checkbox" name="status[]" value="aprovado"
                               <?= in_array('aprovado', $filtros['status'] ?? []) ? 'checked' : '' ?>>
                        <span class="badge badge-primary">Aprovado</span>
                    </label>
                    <label>
                        <input type="checkbox" name="status[]" value="em_andamento"
                               <?= in_array('em_andamento', $filtros['status'] ?? []) ? 'checked' : '' ?>>
                        <span class="badge badge-success">Em Andamento</span>
                    </label>
                    <label>
                        <input type="checkbox" name="status[]" value="pausado"
                               <?= in_array('pausado', $filtros['status'] ?? []) ? 'checked' : '' ?>>
                        <span class="badge badge-warning">Pausado</span>
                    </label>
                    <label>
                        <input type="checkbox" name="status[]" value="concluido"
                               <?= in_array('concluido', $filtros['status'] ?? []) ? 'checked' : '' ?>>
                        <span class="badge badge-dark">Concluído</span>
                    </label>
                    <label>
                        <input type="checkbox" name="status[]" value="cancelado"
                               <?= in_array('cancelado', $filtros['status'] ?? []) ? 'checked' : '' ?>>
                        <span class="badge badge-danger">Cancelado</span>
                    </label>
                </div>
            </div>
            
            <!-- Filtro: Contrato -->
            <div class="filter-group">
                <label for="contrato_id">Contrato</label>
                <select name="contrato_id" id="contrato_id" class="form-control select2">
                    <option value="">Todos os contratos</option>
                    <?php foreach ($contratos as $contrato): ?>
                    <option value="<?= $contrato['id'] ?>" 
                            <?= ($filtros['contrato_id'] ?? '') == $contrato['id'] ? 'selected' : '' ?>>
                        <?= $contrato['numero_contrato'] ?> - <?= $contrato['empresa_tomadora'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Filtro: Empresa Tomadora -->
            <div class="filter-group">
                <label for="empresa_tomadora_id">Empresa Tomadora</label>
                <select name="empresa_tomadora_id" id="empresa_tomadora_id" class="form-control select2">
                    <option value="">Todas as empresas</option>
                    <?php foreach ($empresasTomadoras as $empresa): ?>
                    <option value="<?= $empresa['id'] ?>"
                            <?= ($filtros['empresa_tomadora_id'] ?? '') == $empresa['id'] ? 'selected' : '' ?>>
                        <?= $empresa['nome_fantasia'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Filtro: Empresa Prestadora -->
            <div class="filter-group">
                <label for="empresa_prestadora_id">Empresa Prestadora</label>
                <select name="empresa_prestadora_id" id="empresa_prestadora_id" class="form-control select2">
                    <option value="">Todas as empresas</option>
                    <?php foreach ($empresasPrestadoras as $empresa): ?>
                    <option value="<?= $empresa['id'] ?>"
                            <?= ($filtros['empresa_prestadora_id'] ?? '') == $empresa['id'] ? 'selected' : '' ?>>
                        <?= $empresa['nome_fantasia'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Filtro: Gestor do Projeto -->
            <div class="filter-group">
                <label for="gestor_projeto_id">Gestor do Projeto</label>
                <select name="gestor_projeto_id" id="gestor_projeto_id" class="form-control select2">
                    <option value="">Todos os gestores</option>
                    <?php foreach ($gestores as $gestor): ?>
                    <option value="<?= $gestor['id'] ?>"
                            <?= ($filtros['gestor_projeto_id'] ?? '') == $gestor['id'] ? 'selected' : '' ?>>
                        <?= $gestor['nome'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Filtro: Período -->
            <div class="filter-group">
                <label>Período de Execução</label>
                <div class="row">
                    <div class="col-6">
                        <label class="small">Data Início</label>
                        <input type="date" name="data_inicio" class="form-control"
                               value="<?= $filtros['data_inicio'] ?? '' ?>">
                    </div>
                    <div class="col-6">
                        <label class="small">Data Fim</label>
                        <input type="date" name="data_fim" class="form-control"
                               value="<?= $filtros['data_fim'] ?? '' ?>">
                    </div>
                </div>
                
                <!-- Atalhos de período -->
                <div class="period-shortcuts">
                    <button type="button" class="btn-shortcut" onclick="setPeriodo('hoje')">Hoje</button>
                    <button type="button" class="btn-shortcut" onclick="setPeriodo('semana')">Esta Semana</button>
                    <button type="button" class="btn-shortcut" onclick="setPeriodo('mes')">Este Mês</button>
                    <button type="button" class="btn-shortcut" onclick="setPeriodo('trimestre')">Trimestre</button>
                    <button type="button" class="btn-shortcut" onclick="setPeriodo('ano')">Este Ano</button>
                </div>
            </div>
            
            <!-- Filtros Rápidos -->
            <div class="filter-group">
                <label>Filtros Rápidos</label>
                <button type="button" class="btn btn-sm btn-outline-danger w-100 mb-2" 
                        onclick="filtrarAtrasados()">
                    <i class="fas fa-exclamation-triangle"></i> Atrasados
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning w-100 mb-2"
                        onclick="filtrarEstouroOrcamento()">
                    <i class="fas fa-chart-line"></i> Estouro de Orçamento
                </button>
                <button type="button" class="btn btn-sm btn-outline-info w-100 mb-2"
                        onclick="filtrarMeusProjetos()">
                    <i class="fas fa-user"></i> Meus Projetos
                </button>
            </div>
            
            <!-- Botões de Ação -->
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-search"></i>
                    Aplicar Filtros
                </button>
                <button type="button" class="btn btn-secondary w-100" onclick="limparFiltros()">
                    <i class="fas fa-eraser"></i>
                    Limpar Tudo
                </button>
            </div>
            
        </form>
    </aside>
    
    <!-- ÁREA DE LISTAGEM -->
    <main class="main-content">
        
        <!-- Barra de Busca e Ações -->
        <div class="list-toolbar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchProjetos" placeholder="Buscar por código ou nome do projeto..."
                       value="<?= $filtros['search'] ?? '' ?>" onkeyup="buscarProjetos(this.value)">
            </div>
            
            <div class="view-options">
                <span class="text-muted">Visualizar:</span>
                <button class="btn-view active" data-view="table" onclick="mudarVisualizacao('table')">
                    <i class="fas fa-list"></i>
                </button>
                <button class="btn-view" data-view="cards" onclick="mudarVisualizacao('cards')">
                    <i class="fas fa-th"></i>
                </button>
                <button class="btn-view" data-view="kanban" onclick="mudarVisualizacao('kanban')">
                    <i class="fas fa-columns"></i>
                </button>
            </div>
            
            <div class="bulk-actions" id="bulkActions" style="display: none;">
                <span class="selected-count">0 selecionados</span>
                <button class="btn btn-sm btn-primary" onclick="exportarSelecionados()">
                    <i class="fas fa-file-export"></i> Exportar
                </button>
                <button class="btn btn-sm btn-danger" onclick="excluirSelecionados()">
                    <i class="fas fa-trash"></i> Excluir
                </button>
            </div>
        </div>
        
        <!-- TABELA DE PROJETOS -->
        <div id="tableView" class="table-view active">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="selectAll" onchange="selecionarTodos(this.checked)">
                            </th>
                            <th style="width: 60px;">#</th>
                            <th class="sortable" onclick="ordenar('codigo_projeto')">
                                Código
                                <i class="fas fa-sort"></i>
                            </th>
                            <th class="sortable" onclick="ordenar('nome')">
                                Nome do Projeto
                                <i class="fas fa-sort"></i>
                            </th>
                            <th>Empresas</th>
                            <th>Gestor</th>
                            <th class="text-center">Progresso</th>
                            <th class="text-center">Orçamento</th>
                            <th class="sortable" onclick="ordenar('data_inicio')">
                                Período
                                <i class="fas fa-sort"></i>
                            </th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width: 120px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($projetos)): ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Nenhum projeto encontrado com os filtros aplicados.</p>
                                <button class="btn btn-primary" onclick="limparFiltros()">
                                    Limpar Filtros
                                </button>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($projetos as $projeto): ?>
                        <tr data-id="<?= $projeto['id'] ?>">
                            <td>
                                <input type="checkbox" class="select-item" value="<?= $projeto['id'] ?>"
                                       onchange="atualizarSelecao()">
                            </td>
                            <td><?= $projeto['id'] ?></td>
                            <td>
                                <a href="/projetos/<?= $projeto['id'] ?>" class="code-link">
                                    <?= $projeto['codigo_projeto'] ?>
                                </a>
                            </td>
                            <td>
                                <a href="/projetos/<?= $projeto['id'] ?>" class="project-name">
                                    <?= $projeto['nome'] ?>
                                </a>
                                <?php if (!empty($projeto['descricao'])): ?>
                                <small class="text-muted d-block">
                                    <?= substr($projeto['descricao'], 0, 60) ?>...
                                </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="empresa-info">
                                    <span class="badge badge-info" title="Tomadora">T</span>
                                    <?= $projeto['tomadora_nome'] ?>
                                </div>
                                <div class="empresa-info">
                                    <span class="badge badge-success" title="Prestadora">P</span>
                                    <?= $projeto['prestadora_nome'] ?>
                                </div>
                            </td>
                            <td>
                                <div class="user-info">
                                    <i class="fas fa-user-tie"></i>
                                    <?= $projeto['gestor_nome'] ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="progress-info">
                                    <div class="progress">
                                        <div class="progress-bar <?= $projeto['percentual_concluido'] >= 100 ? 'bg-success' : ($projeto['percentual_concluido'] >= 75 ? 'bg-primary' : 'bg-info') ?>"
                                             style="width: <?= $projeto['percentual_concluido'] ?>%">
                                        </div>
                                    </div>
                                    <small><?= number_format($projeto['percentual_concluido'], 1) ?>%</small>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="budget-info">
                                    <?php
                                    $percentualGasto = $projeto['percentual_gasto'];
                                    $badgeClass = $percentualGasto >= 100 ? 'danger' : ($percentualGasto >= 80 ? 'warning' : 'success');
                                    ?>
                                    <span class="badge badge-<?= $badgeClass ?>">
                                        <?= number_format($percentualGasto, 1) ?>%
                                    </span>
                                    <small class="d-block text-muted">
                                        R$ <?= number_format($projeto['valor_gasto'], 2, ',', '.') ?>
                                        /
                                        R$ <?= number_format($projeto['valor_orcado'], 2, ',', '.') ?>
                                    </small>
                                </div>
                            </td>
                            <td>
                                <?php
                                $dataInicio = new DateTime($projeto['data_inicio']);
                                $dataFim = new DateTime($projeto['data_fim_prevista']);
                                $hoje = new DateTime();
                                $diasRestantes = $hoje->diff($dataFim)->days;
                                $atrasado = $hoje > $dataFim && $projeto['status'] == 'em_andamento';
                                ?>
                                <div class="period-info">
                                    <small><?= $dataInicio->format('d/m/Y') ?></small>
                                    <i class="fas fa-arrow-right"></i>
                                    <small><?= $dataFim->format('d/m/Y') ?></small>
                                </div>
                                <?php if ($projeto['status'] == 'em_andamento'): ?>
                                <small class="badge badge-<?= $atrasado ? 'danger' : 'info' ?>">
                                    <?= $atrasado ? 'Atrasado ' : '' ?>
                                    <?= $diasRestantes ?> dias
                                </small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php
                                $statusConfig = [
                                    'planejamento' => ['class' => 'secondary', 'icon' => 'clipboard-list', 'text' => 'Planejamento'],
                                    'orcamento' => ['class' => 'info', 'icon' => 'calculator', 'text' => 'Orçamento'],
                                    'aprovado' => ['class' => 'primary', 'icon' => 'check-circle', 'text' => 'Aprovado'],
                                    'em_andamento' => ['class' => 'success', 'icon' => 'play-circle', 'text' => 'Em Andamento'],
                                    'pausado' => ['class' => 'warning', 'icon' => 'pause-circle', 'text' => 'Pausado'],
                                    'concluido' => ['class' => 'dark', 'icon' => 'check-double', 'text' => 'Concluído'],
                                    'cancelado' => ['class' => 'danger', 'icon' => 'times-circle', 'text' => 'Cancelado']
                                ];
                                $status = $statusConfig[$projeto['status']];
                                ?>
                                <span class="badge badge-<?= $status['class'] ?>">
                                    <i class="fas fa-<?= $status['icon'] ?>"></i>
                                    <?= $status['text'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="/projetos/<?= $projeto['id'] ?>" class="btn btn-sm btn-info" 
                                       title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (!in_array($projeto['status'], ['concluido', 'cancelado'])): ?>
                                    <a href="/projetos/<?= $projeto['id'] ?>/edit" class="btn btn-sm btn-primary"
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-secondary dropdown-toggle" 
                                            data-toggle="dropdown" title="Mais ações">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" href="/projetos/<?= $projeto['id'] ?>/relatorio">
                                            <i class="fas fa-file-pdf"></i> Relatório
                                        </a>
                                        <?php if ($projeto['status'] == 'aprovado'): ?>
                                        <a class="dropdown-item" href="#" 
                                           onclick="iniciarProjeto(<?= $projeto['id'] ?>)">
                                            <i class="fas fa-play"></i> Iniciar
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($projeto['status'] == 'em_andamento'): ?>
                                        <a class="dropdown-item" href="#"
                                           onclick="pausarProjeto(<?= $projeto['id'] ?>)">
                                            <i class="fas fa-pause"></i> Pausar
                                        </a>
                                        <a class="dropdown-item" href="#"
                                           onclick="concluirProjeto(<?= $projeto['id'] ?>)">
                                            <i class="fas fa-check-double"></i> Concluir
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($projeto['status'] == 'pausado'): ?>
                                        <a class="dropdown-item" href="#"
                                           onclick="retomarProjeto(<?= $projeto['id'] ?>)">
                                            <i class="fas fa-play"></i> Retomar
                                        </a>
                                        <?php endif; ?>
                                        <div class="dropdown-divider"></div>
                                        <?php if (!in_array($projeto['status'], ['concluido', 'cancelado'])): ?>
                                        <a class="dropdown-item text-danger" href="#"
                                           onclick="cancelarProjeto(<?= $projeto['id'] ?>)">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                        <?php endif; ?>
                                        <a class="dropdown-item text-danger" href="#"
                                           onclick="excluirProjeto(<?= $projeto['id'] ?>)">
                                            <i class="fas fa-trash"></i> Excluir
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- PAGINAÇÃO -->
            <?php if ($totalPages > 1): ?>
            <nav class="pagination-wrapper">
                <div class="pagination-info">
                    Mostrando <?= (($page - 1) * $limit) + 1 ?> a 
                    <?= min($page * $limit, $total) ?> de 
                    <?= number_format($total) ?> projetos
                </div>
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?><?= http_build_query($filtros) ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?><?= http_build_query($filtros) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?><?= http_build_query($filtros) ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <div class="items-per-page">
                    <label>Itens por página:</label>
                    <select onchange="mudarLimite(this.value)">
                        <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20</option>
                        <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                    </select>
                </div>
            </nav>
            <?php endif; ?>
        </div>
        
    </main>
</div>

<!-- RODAPÉ INSTRUCIONAL -->
<div class="page-footer-instructions">
    <div class="instruction-box">
        <i class="fas fa-info-circle"></i>
        <strong>INSTRUÇÕES:</strong>
    </div>
    <ul class="instruction-list">
        <li><i class="fas fa-filter"></i> Use os <strong>FILTROS</strong> à esquerda para encontrar projetos específicos</li>
        <li><i class="fas fa-search"></i> Digite no campo de <strong>BUSCA</strong> para localizar por código ou nome</li>
        <li><i class="fas fa-eye"></i> Clique no <strong>CÓDIGO</strong> ou <strong>NOME</strong> para ver detalhes completos</li>
        <li><i class="fas fa-chart-line"></i> Acompanhe o <strong>PROGRESSO</strong> e <strong>ORÇAMENTO</strong> em tempo real</li>
        <li><i class="fas fa-ellipsis-v"></i> Use o botão <strong>...</strong> para ações específicas de cada projeto</li>
        <li><i class="fas fa-file-export"></i> Clique em <strong>EXPORTAR</strong> para gerar relatórios</li>
        <li><i class="fas fa-plus"></i> Botão <strong>NOVO PROJETO</strong> para cadastrar um novo projeto</li>
    </ul>
</div>

<?php
// Incluir JavaScript específico
$this->section('scripts');
?>
<script src="/js/projetos/index.js"></script>
<script>
// Inicializar Select2 nos filtros
$(document).ready(function() {
    $('.select2').select2({
        placeholder: 'Selecione...',
        allowClear: true
    });
});
</script>
<?php $this->endSection(); ?>
```

#### [CONTINUA...]

💡 **CHECKPOINT:**
```
View index.php completa com:
- Header e breadcrumbs
- 5 cards de estatísticas
- Sidebar completa de filtros (10+ filtros)
- Tabela responsiva com 11 colunas
- Ações por linha (visualizar, editar, mais ações)
- Paginação completa
- Rodapé instrucional detalhado
- JavaScript integrado
Próximo: form.php (formulário com 8 abas)
```
