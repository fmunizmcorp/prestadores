<?php
$pageTitle = 'Editar Empresa Prestadora';
$activeMenu = 'empresas-prestadoras';
$breadcrumb = [
    ['label' => 'Empresas'],
    ['label' => 'Empresas Prestadoras', 'url' => '/empresas-prestadoras'],
    ['label' => $empresa['nome_fantasia'], 'url' => '/empresas-prestadoras/' . $empresa['id']],
    ['label' => 'Editar']
];

require __DIR__ . '/../layouts/header.php';

// Recuperar dados do formulário em caso de erro, senão usar dados do banco
$formData = $_SESSION['form_data'] ?? $empresa;
unset($_SESSION['form_data']);
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="fas fa-edit"></i> Editar Empresa Prestadora</h2>
        <p class="text-muted">Atualizar dados da empresa <?= htmlspecialchars($empresa['nome_fantasia']) ?></p>
    </div>
</div>

<form method="POST" action="/empresas-prestadoras/<?= $empresa['id'] ?>/update" enctype="multipart/form-data" class="needs-validation" novalidate>
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
    
    <!-- Dados Principais -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-building"></i> Dados Principais</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="razao_social" class="form-label">Razão Social <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="razao_social" name="razao_social" 
                           value="<?= htmlspecialchars($formData['razao_social'] ?? '') ?>" required>
                    <div class="invalid-feedback">Por favor, informe a razão social.</div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="nome_fantasia" class="form-label">Nome Fantasia <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nome_fantasia" name="nome_fantasia" 
                           value="<?= htmlspecialchars($formData['nome_fantasia'] ?? '') ?>" required>
                    <div class="invalid-feedback">Por favor, informe o nome fantasia.</div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="cnpj" class="form-label">CNPJ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control mask-cnpj" id="cnpj" name="cnpj" 
                           value="<?= htmlspecialchars($formData['cnpj'] ?? '') ?>" required>
                    <div class="invalid-feedback">Por favor, informe um CNPJ válido.</div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="inscricao_estadual" class="form-label">Inscrição Estadual</label>
                    <input type="text" class="form-control" id="inscricao_estadual" name="inscricao_estadual" 
                           value="<?= htmlspecialchars($formData['inscricao_estadual'] ?? '') ?>">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="inscricao_municipal" class="form-label">Inscrição Municipal</label>
                    <input type="text" class="form-control" id="inscricao_municipal" name="inscricao_municipal" 
                           value="<?= htmlspecialchars($formData['inscricao_municipal'] ?? '') ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="logo" class="form-label">Logo da Empresa</label>
                    <?php if ($empresa['logo']): ?>
                        <div class="mb-2">
                            <img src="/public/uploads/logos/<?= $empresa['logo'] ?>" alt="Logo atual" class="img-thumbnail" style="max-height: 100px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                    <small class="form-text text-muted">Formatos: JPG, PNG, GIF, WEBP (máx. 2MB)</small>
                </div>
                
                <div class="col-md-9 mb-3">
                    <label for="site" class="form-label">Website</label>
                    <input type="url" class="form-control" id="site" name="site" 
                           value="<?= htmlspecialchars($formData['site'] ?? '') ?>" 
                           placeholder="https://www.exemplo.com.br">
                </div>
            </div>
            
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="ativo" name="ativo" 
                       <?= !isset($formData) || ($formData['ativo'] ?? '1') ? 'checked' : '' ?>>
                <label class="form-check-label" for="ativo">
                    Empresa Ativa
                </label>
            </div>
        </div>
    </div>
    
    <!-- Endereço -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Endereço</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="cep" class="form-label">CEP</label>
                    <div class="input-group">
                        <input type="text" class="form-control mask-cep" id="cep" name="cep" 
                               value="<?= htmlspecialchars($formData['cep'] ?? '') ?>">
                        <button class="btn btn-outline-secondary btn-buscar-cep" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <small class="form-text text-muted">Busca automática ao preencher</small>
                </div>
                
                <div class="col-md-7 mb-3">
                    <label for="logradouro" class="form-label">Logradouro</label>
                    <input type="text" class="form-control" id="logradouro" name="logradouro" 
                           value="<?= htmlspecialchars($formData['logradouro'] ?? '') ?>">
                </div>
                
                <div class="col-md-2 mb-3">
                    <label for="numero" class="form-label">Número</label>
                    <input type="text" class="form-control" id="numero" name="numero" 
                           value="<?= htmlspecialchars($formData['numero'] ?? '') ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="complemento" class="form-label">Complemento</label>
                    <input type="text" class="form-control" id="complemento" name="complemento" 
                           value="<?= htmlspecialchars($formData['complemento'] ?? '') ?>">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="bairro" class="form-label">Bairro</label>
                    <input type="text" class="form-control" id="bairro" name="bairro" 
                           value="<?= htmlspecialchars($formData['bairro'] ?? '') ?>">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="cidade" class="form-label">Cidade</label>
                    <input type="text" class="form-control" id="cidade" name="cidade" 
                           value="<?= htmlspecialchars($formData['cidade'] ?? '') ?>">
                </div>
                
                <div class="col-md-1 mb-3">
                    <label for="estado" class="form-label">UF</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="">--</option>
                        <?php foreach (['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'] as $uf): ?>
                            <option value="<?= $uf ?>" <?= ($formData['estado'] ?? '') === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Contatos -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-phone"></i> Contatos</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="email_principal" class="form-label">E-mail Principal <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email_principal" name="email_principal" 
                           value="<?= htmlspecialchars($formData['email_principal'] ?? '') ?>" required>
                    <div class="invalid-feedback">Por favor, informe um e-mail válido.</div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="email_financeiro" class="form-label">E-mail Financeiro</label>
                    <input type="email" class="form-control" id="email_financeiro" name="email_financeiro" 
                           value="<?= htmlspecialchars($formData['email_financeiro'] ?? '') ?>">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="email_projetos" class="form-label">E-mail Projetos</label>
                    <input type="email" class="form-control" id="email_projetos" name="email_projetos" 
                           value="<?= htmlspecialchars($formData['email_projetos'] ?? '') ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="telefone_principal" class="form-label">Telefone Principal</label>
                    <input type="text" class="form-control mask-phone" id="telefone_principal" name="telefone_principal" 
                           value="<?= htmlspecialchars($formData['telefone_principal'] ?? '') ?>">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="telefone_secundario" class="form-label">Telefone Secundário</label>
                    <input type="text" class="form-control mask-phone" id="telefone_secundario" name="telefone_secundario" 
                           value="<?= htmlspecialchars($formData['telefone_secundario'] ?? '') ?>">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="celular" class="form-label">Celular</label>
                    <input type="text" class="form-control mask-celular" id="celular" name="celular" 
                           value="<?= htmlspecialchars($formData['celular'] ?? '') ?>">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="whatsapp" class="form-label">WhatsApp</label>
                    <input type="text" class="form-control mask-celular" id="whatsapp" name="whatsapp" 
                           value="<?= htmlspecialchars($formData['whatsapp'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Dados Financeiros -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-dollar-sign"></i> Dados Financeiros</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="dia_fechamento" class="form-label">Dia de Fechamento</label>
                    <input type="number" class="form-control" id="dia_fechamento" name="dia_fechamento" 
                           min="1" max="31" value="<?= htmlspecialchars($formData['dia_fechamento'] ?? '') ?>">
                    <small class="form-text text-muted">Dia do mês (1-31)</small>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="dia_pagamento" class="form-label">Dia de Pagamento</label>
                    <input type="number" class="form-control" id="dia_pagamento" name="dia_pagamento" 
                           min="1" max="31" value="<?= htmlspecialchars($formData['dia_pagamento'] ?? '') ?>">
                    <small class="form-text text-muted">Dia do mês (1-31)</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="forma_pagamento_preferencial" class="form-label">Forma de Pagamento Preferencial</label>
                    <select class="form-select" id="forma_pagamento_preferencial" name="forma_pagamento_preferencial">
                        <option value="">Selecione...</option>
                        <option value="Boleto" <?= ($formData['forma_pagamento_preferencial'] ?? '') === 'Boleto' ? 'selected' : '' ?>>Boleto</option>
                        <option value="Transferência" <?= ($formData['forma_pagamento_preferencial'] ?? '') === 'Transferência' ? 'selected' : '' ?>>Transferência</option>
                        <option value="PIX" <?= ($formData['forma_pagamento_preferencial'] ?? '') === 'PIX' ? 'selected' : '' ?>>PIX</option>
                        <option value="Cheque" <?= ($formData['forma_pagamento_preferencial'] ?? '') === 'Cheque' ? 'selected' : '' ?>>Cheque</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="banco" class="form-label">Banco</label>
                    <input type="text" class="form-control" id="banco" name="banco" 
                           value="<?= htmlspecialchars($formData['banco'] ?? '') ?>">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="agencia" class="form-label">Agência</label>
                    <input type="text" class="form-control" id="agencia" name="agencia" 
                           value="<?= htmlspecialchars($formData['agencia'] ?? '') ?>">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="conta" class="form-label">Conta</label>
                    <input type="text" class="form-control" id="conta" name="conta" 
                           value="<?= htmlspecialchars($formData['conta'] ?? '') ?>">
                </div>
                
                <div class="col-md-2 mb-3">
                    <label for="tipo_conta" class="form-label">Tipo</label>
                    <select class="form-select" id="tipo_conta" name="tipo_conta">
                        <option value="">--</option>
                        <option value="Corrente" <?= ($formData['tipo_conta'] ?? '') === 'Corrente' ? 'selected' : '' ?>>Corrente</option>
                        <option value="Poupança" <?= ($formData['tipo_conta'] ?? '') === 'Poupança' ? 'selected' : '' ?>>Poupança</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Observações -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-comments"></i> Observações</h5>
        </div>
        <div class="card-body">
            <textarea class="form-control" id="observacoes" name="observacoes" rows="4"><?= htmlspecialchars($formData['observacoes'] ?? '') ?></textarea>
            <small class="form-text text-muted">Informações adicionais sobre a empresa</small>
        </div>
    </div>
    
    <!-- Botões -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Salvar Empresa
                    </button>
                    <a href="/empresas-prestadoras" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
