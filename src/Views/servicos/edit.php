<?php
/**
 * View: Editar Serviço Existente
 * Controller: ServicoController
 * 
 * Formulário completo para editar serviço com:
 * - Dados principais
 * - Requisitos e qualificações
 * - Valores de referência
 * - Serviços relacionados
 */

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/servicos">Serviços</a></li>
            <li class="breadcrumb-item active">Editar Serviço</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Editar Serviço</h1>
            <p class="text-muted mb-0">Atualize as informações do serviço</p>
        </div>
        <a href="/servicos" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <!-- Form Card -->
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="/servicos/<?= $servico['id'] ?>/update" id="formServico" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <!-- Section 1: Dados Principais -->
                <div class="mb-5">
                    <h4 class="border-bottom pb-2 mb-4">
                        <i class="fas fa-info-circle text-primary me-2"></i>Dados Principais
                    </h4>
                    
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="codigo" class="form-label required">Código do Serviço</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="codigo" 
                                   name="codigo" 
                                   maxlength="50"
                                   required
                                   value="<?= htmlspecialchars($servico['codigo'] ?? '') ?>"
                                   placeholder="Ex: SRV-001">
                            <div class="invalid-feedback">Por favor, informe o código do serviço.</div>
                            <small class="form-text text-muted">Código único de identificação</small>
                        </div>

                        <div class="col-md-9">
                            <label for="nome" class="form-label required">Nome do Serviço</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="nome" 
                                   name="nome" 
                                   maxlength="255"
                                   required
                                   value="<?= htmlspecialchars($servico['nome'] ?? '') ?>"
                                   placeholder="Ex: Técnico em Segurança do Trabalho">
                            <div class="invalid-feedback">Por favor, informe o nome do serviço.</div>
                        </div>

                        <div class="col-12">
                            <label for="descricao" class="form-label">Descrição Detalhada</label>
                            <textarea class="form-control" 
                                      id="descricao" 
                                      name="descricao" 
                                      rows="4"
                                      placeholder="Descreva as atividades e responsabilidades deste serviço..."><?= htmlspecialchars($servico['descricao'] ?? '') ?></textarea>
                            <small class="form-text text-muted">Descrição completa das atividades e responsabilidades</small>
                        </div>

                        <div class="col-md-4">
                            <label for="tipo" class="form-label">Tipo de Serviço</label>
                            <select class="form-select" id="tipo" name="tipo">
                                <option value="">Selecione...</option>
                                <option value="Técnico" <?= ($servico['tipo'] ?? '') === 'Técnico' ? 'selected' : '' ?>>Técnico</option>
                                <option value="Operacional" <?= ($servico['tipo'] ?? '') === 'Operacional' ? 'selected' : '' ?>>Operacional</option>
                                <option value="Administrativo" <?= ($servico['tipo'] ?? '') === 'Administrativo' ? 'selected' : '' ?>>Administrativo</option>
                                <option value="Gerencial" <?= ($servico['tipo'] ?? '') === 'Gerencial' ? 'selected' : '' ?>>Gerencial</option>
                                <option value="Especializado" <?= ($servico['tipo'] ?? '') === 'Especializado' ? 'selected' : '' ?>>Especializado</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="categoria" class="form-label">Categoria</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="categoria" 
                                   name="categoria" 
                                   maxlength="100"
                                   value="<?= htmlspecialchars($servico['categoria'] ?? '') ?>"
                                   placeholder="Ex: Segurança do Trabalho">
                        </div>

                        <div class="col-md-4">
                            <label for="cbo" class="form-label">CBO (Classificação Brasileira de Ocupações)</label>
                            <input type="text" 
                                   class="form-control mask-cbo" 
                                   id="cbo" 
                                   name="cbo" 
                                   maxlength="10"
                                   value="<?= htmlspecialchars($servico['cbo'] ?? '') ?>"
                                   placeholder="0000-00">
                            <small class="form-text text-muted">Código CBO relacionado</small>
                        </div>

                        <div class="col-md-6">
                            <label for="carga_horaria_semanal" class="form-label">Carga Horária Semanal</label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control" 
                                       id="carga_horaria_semanal" 
                                       name="carga_horaria_semanal" 
                                       min="0" 
                                       max="44" 
                                       step="0.5"
                                       value="<?= htmlspecialchars($servico['carga_horaria_semanal'] ?? '') ?>"
                                       placeholder="Ex: 44">
                                <span class="input-group-text">horas/semana</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="jornada_padrao" class="form-label">Jornada Padrão</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="jornada_padrao" 
                                   name="jornada_padrao" 
                                   maxlength="50"
                                   placeholder="Ex: Segunda a Sexta, 08:00-17:00">
                        </div>

                        <div class="col-12">
                            <label for="atividades_principais" class="form-label">Atividades Principais</label>
                            <textarea class="form-control" 
                                      id="atividades_principais" 
                                      name="atividades_principais" 
                                      rows="4"
                                      placeholder="Liste as principais atividades deste serviço (uma por linha)..."></textarea>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="permite_teletrabalho" 
                                       name="permite_teletrabalho" 
                                       value="1"
                                       <?= !empty($servico['permite_teletrabalho']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="permite_teletrabalho">
                                    Permite Teletrabalho/Home Office
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="ativo" 
                                       name="ativo" 
                                       value="1" 
                                       <?= !empty($servico['ativo']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="ativo">
                                    Serviço Ativo
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Requisitos e Qualificações -->
                <div class="mb-5">
                    <h4 class="border-bottom pb-2 mb-4">
                        <i class="fas fa-clipboard-list text-primary me-2"></i>Requisitos e Qualificações
                    </h4>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="escolaridade_minima" class="form-label">Escolaridade Mínima</label>
                            <select class="form-select" id="escolaridade_minima" name="escolaridade_minima">
                                <option value="">Não exigida</option>
                                <option value="Fundamental Incompleto">Fundamental Incompleto</option>
                                <option value="Fundamental Completo">Fundamental Completo</option>
                                <option value="Médio Incompleto">Médio Incompleto</option>
                                <option value="Médio Completo">Médio Completo</option>
                                <option value="Técnico">Técnico</option>
                                <option value="Superior Incompleto">Superior Incompleto</option>
                                <option value="Superior Completo">Superior Completo</option>
                                <option value="Pós-Graduação">Pós-Graduação</option>
                                <option value="Mestrado">Mestrado</option>
                                <option value="Doutorado">Doutorado</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="experiencia_minima" class="form-label">Experiência Mínima</label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control" 
                                       id="experiencia_minima" 
                                       name="experiencia_minima" 
                                       min="0" 
                                       max="50"
                                       placeholder="Ex: 2">
                                <span class="input-group-text">anos</span>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="certificacoes_obrigatorias" class="form-label">Certificações Obrigatórias</label>
                            <textarea class="form-control" 
                                      id="certificacoes_obrigatorias" 
                                      name="certificacoes_obrigatorias" 
                                      rows="3"
                                      placeholder="Liste as certificações obrigatórias (uma por linha)..."></textarea>
                            <small class="form-text text-muted">Ex: NR-35, Curso de Primeiros Socorros, etc.</small>
                        </div>

                        <div class="col-12">
                            <label for="certificacoes_desejaveis" class="form-label">Certificações Desejáveis</label>
                            <textarea class="form-control" 
                                      id="certificacoes_desejaveis" 
                                      name="certificacoes_desejaveis" 
                                      rows="3"
                                      placeholder="Liste as certificações desejáveis (uma por linha)..."></textarea>
                        </div>

                        <div class="col-12">
                            <label for="habilidades_tecnicas" class="form-label">Habilidades Técnicas Requeridas</label>
                            <textarea class="form-control" 
                                      id="habilidades_tecnicas" 
                                      name="habilidades_tecnicas" 
                                      rows="3"
                                      placeholder="Liste as habilidades técnicas necessárias..."></textarea>
                        </div>

                        <div class="col-12">
                            <label for="habilidades_comportamentais" class="form-label">Habilidades Comportamentais</label>
                            <textarea class="form-control" 
                                      id="habilidades_comportamentais" 
                                      name="habilidades_comportamentais" 
                                      rows="3"
                                      placeholder="Ex: Trabalho em equipe, comunicação, liderança..."></textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="idiomas_requeridos" class="form-label">Idiomas Requeridos</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="idiomas_requeridos" 
                                   name="idiomas_requeridos" 
                                   maxlength="255"
                                   placeholder="Ex: Inglês (intermediário), Espanhol (básico)">
                        </div>

                        <div class="col-md-6">
                            <label for="cnh_obrigatoria" class="form-label">CNH Obrigatória</label>
                            <select class="form-select" id="cnh_obrigatoria" name="cnh_obrigatoria">
                                <option value="">Não exigida</option>
                                <option value="A">Categoria A</option>
                                <option value="B">Categoria B</option>
                                <option value="C">Categoria C</option>
                                <option value="D">Categoria D</option>
                                <option value="E">Categoria E</option>
                                <option value="AB">Categorias A e B</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Valores de Referência -->
                <div class="mb-5">
                    <h4 class="border-bottom pb-2 mb-4">
                        <i class="fas fa-dollar-sign text-primary me-2"></i>Valores de Referência
                    </h4>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="valor_referencia" class="form-label">Valor de Referência</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="text" 
                                       class="form-control mask-money" 
                                       id="valor_referencia" 
                                       name="valor_referencia" 
                                       placeholder="0,00">
                            </div>
                            <small class="form-text text-muted">Valor base para cálculos</small>
                        </div>

                        <div class="col-md-4">
                            <label for="tipo_valor" class="form-label">Tipo de Valor</label>
                            <select class="form-select" id="tipo_valor" name="tipo_valor">
                                <option value="">Selecione...</option>
                                <option value="Por Hora">Por Hora</option>
                                <option value="Por Dia">Por Dia</option>
                                <option value="Mensal">Mensal</option>
                                <option value="Por Projeto">Por Projeto</option>
                                <option value="Por Demanda">Por Demanda</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="moeda" class="form-label">Moeda</label>
                            <select class="form-select" id="moeda" name="moeda">
                                <option value="BRL" selected>Real (BRL)</option>
                                <option value="USD">Dólar (USD)</option>
                                <option value="EUR">Euro (EUR)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="data_vigencia_inicio" class="form-label">Vigência do Valor - Início</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="data_vigencia_inicio" 
                                   name="data_vigencia_inicio">
                        </div>

                        <div class="col-md-6">
                            <label for="data_vigencia_fim" class="form-label">Vigência do Valor - Fim</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="data_vigencia_fim" 
                                   name="data_vigencia_fim">
                        </div>

                        <div class="col-12">
                            <label for="observacoes_valor" class="form-label">Observações sobre Valores</label>
                            <textarea class="form-control" 
                                      id="observacoes_valor" 
                                      name="observacoes_valor" 
                                      rows="2"
                                      placeholder="Informações adicionais sobre valores, reajustes, etc..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Informações Complementares -->
                <div class="mb-5">
                    <h4 class="border-bottom pb-2 mb-4">
                        <i class="fas fa-list text-primary me-2"></i>Informações Complementares
                    </h4>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="local_trabalho_padrao" class="form-label">Local de Trabalho Padrão</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="local_trabalho_padrao" 
                                   name="local_trabalho_padrao" 
                                   maxlength="255"
                                   placeholder="Ex: Escritório, Campo, Remoto">
                        </div>

                        <div class="col-md-6">
                            <label for="equipamentos_necessarios" class="form-label">Equipamentos Necessários</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="equipamentos_necessarios" 
                                   name="equipamentos_necessarios" 
                                   maxlength="255"
                                   placeholder="Ex: Notebook, Ferramentas específicas">
                        </div>

                        <div class="col-12">
                            <label for="uniformes_epis" class="form-label">Uniformes e EPIs Necessários</label>
                            <textarea class="form-control" 
                                      id="uniformes_epis" 
                                      name="uniformes_epis" 
                                      rows="2"
                                      placeholder="Liste os uniformes e EPIs necessários..."></textarea>
                        </div>

                        <div class="col-12">
                            <label for="beneficios_padrao" class="form-label">Benefícios Padrão</label>
                            <textarea class="form-control" 
                                      id="beneficios_padrao" 
                                      name="beneficios_padrao" 
                                      rows="2"
                                      placeholder="Ex: Vale transporte, vale refeição, plano de saúde..."></textarea>
                        </div>

                        <div class="col-12">
                            <label for="observacoes" class="form-label">Observações Gerais</label>
                            <textarea class="form-control" 
                                      id="observacoes" 
                                      name="observacoes" 
                                      rows="3"
                                      placeholder="Informações adicionais relevantes sobre o serviço..."></textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="codigo_interno" class="form-label">Código Interno</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="codigo_interno" 
                                   name="codigo_interno" 
                                   maxlength="50"
                                   placeholder="Código interno da empresa">
                        </div>

                        <div class="col-md-6">
                            <label for="nivel_complexidade" class="form-label">Nível de Complexidade</label>
                            <select class="form-select" id="nivel_complexidade" name="nivel_complexidade">
                                <option value="">Selecione...</option>
                                <option value="Básico">Básico</option>
                                <option value="Intermediário">Intermediário</option>
                                <option value="Avançado">Avançado</option>
                                <option value="Especialista">Especialista</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-between align-items-center pt-4 border-top">
                    <a href="/servicos" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Atualizar Serviço
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form Validation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formServico');
    
    // Bootstrap validation
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);

    // Money mask initialization
    $('.mask-money').inputmask('currency', {
        prefix: '',
        radixPoint: ',',
        groupSeparator: '.',
        digits: 2,
        digitsOptional: false,
        placeholder: '0',
        autoGroup: true,
        rightAlign: false
    });

    // CBO mask
    $('.mask-cbo').inputmask('9999-99');

    // Validate vigência dates
    const dataInicio = document.getElementById('data_vigencia_inicio');
    const dataFim = document.getElementById('data_vigencia_fim');

    if (dataInicio && dataFim) {
        dataFim.addEventListener('change', function() {
            if (dataInicio.value && dataFim.value) {
                if (new Date(dataFim.value) < new Date(dataInicio.value)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Inválida',
                        text: 'A data de fim da vigência não pode ser anterior à data de início.'
                    });
                    dataFim.value = '';
                }
            }
        });
    }
});
</script>

<!-- Footer Instructions -->
<div class="container-fluid mt-5 mb-3">
    <div class="alert alert-light border">
        <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Instruções de Preenchimento</h6>
        <ul class="mb-0 small">
            <li><strong>Campos Obrigatórios:</strong> Código e Nome do Serviço são obrigatórios</li>
            <li><strong>Código Único:</strong> O código do serviço deve ser único no sistema</li>
            <li><strong>Requisitos:</strong> Preencha os requisitos para facilitar a triagem de candidatos</li>
            <li><strong>Valores:</strong> Os valores de referência ajudam na precificação de contratos</li>
            <li><strong>Vigência:</strong> Defina o período de validade dos valores de referência</li>
            <li><strong>Status:</strong> Serviços inativos não aparecerão em novos contratos</li>
        </ul>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
