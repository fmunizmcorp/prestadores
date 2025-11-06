<?php

namespace App\Models;

use App\Database;
use PDO;
use PDOException;

/**
 * Model EmpresaTomadora
 * Gerencia empresas tomadoras de serviços (clientes)
 */
class EmpresaTomadora {
    
    private $db;
    private $table = 'empresas_tomadoras';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Buscar todas as empresas tomadoras
     * 
     * @param array $filtros Filtros de busca
     * @param int $page Página atual
     * @param int $limit Registros por página
     * @return array
     */
    public function all($filtros = [], $page = 1, $limit = 25) {
        $sql = "SELECT 
                    et.*,
                    CONCAT(et.cidade, '/', et.estado) as localizacao,
                    (SELECT COUNT(*) FROM contratos c WHERE c.empresa_tomadora_id = et.id AND c.status = 'ativo') as total_contratos_ativos,
                    (SELECT COUNT(*) FROM empresa_tomadora_responsaveis r WHERE r.empresa_tomadora_id = et.id AND r.ativo = 1) as total_responsaveis
                FROM {$this->table} et
                WHERE 1=1";
        
        $params = [];
        
        // Filtro: Ativo/Inativo
        if (isset($filtros['ativo']) && $filtros['ativo'] !== '') {
            $sql .= " AND et.ativo = :ativo";
            $params[':ativo'] = $filtros['ativo'];
        }
        
        // Filtro: Busca por nome/razão social/CNPJ
        if (!empty($filtros['search'])) {
            $sql .= " AND (et.razao_social LIKE :search 
                         OR et.nome_fantasia LIKE :search 
                         OR et.cnpj LIKE :search
                         OR et.email_principal LIKE :search)";
            $params[':search'] = '%' . $filtros['search'] . '%';
        }
        
        // Filtro: Cidade
        if (!empty($filtros['cidade'])) {
            $sql .= " AND et.cidade = :cidade";
            $params[':cidade'] = $filtros['cidade'];
        }
        
        // Filtro: Estado
        if (!empty($filtros['estado'])) {
            $sql .= " AND et.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }
        
        // Ordenação
        $orderBy = $filtros['order_by'] ?? 'et.nome_fantasia';
        $orderDir = $filtros['order_dir'] ?? 'ASC';
        $sql .= " ORDER BY {$orderBy} {$orderDir}";
        
        // Paginação
        $offset = ($page - 1) * $limit;
        $sql .= " LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        // Bind dos parâmetros
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Contar total de registros
     * 
     * @param array $filtros
     * @return int
     */
    public function count($filtros = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} et WHERE 1=1";
        $params = [];
        
        if (isset($filtros['ativo']) && $filtros['ativo'] !== '') {
            $sql .= " AND et.ativo = :ativo";
            $params[':ativo'] = $filtros['ativo'];
        }
        
        if (!empty($filtros['search'])) {
            $sql .= " AND (et.razao_social LIKE :search 
                         OR et.nome_fantasia LIKE :search 
                         OR et.cnpj LIKE :search)";
            $params[':search'] = '%' . $filtros['search'] . '%';
        }
        
        if (!empty($filtros['cidade'])) {
            $sql .= " AND et.cidade = :cidade";
            $params[':cidade'] = $filtros['cidade'];
        }
        
        if (!empty($filtros['estado'])) {
            $sql .= " AND et.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Buscar empresa por ID
     * 
     * @param int $id
     * @return array|null
     */
    public function findById($id) {
        $sql = "SELECT 
                    et.*,
                    CONCAT(et.cidade, '/', et.estado) as localizacao,
                    u1.nome as criado_por_nome,
                    u2.nome as atualizado_por_nome
                FROM {$this->table} et
                LEFT JOIN usuarios u1 ON et.created_by = u1.id
                LEFT JOIN usuarios u2 ON et.updated_by = u2.id
                WHERE et.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Buscar por CNPJ
     * 
     * @param string $cnpj
     * @param int|null $exceptId ID a excluir da busca (para updates)
     * @return array|null
     */
    public function findByCnpj($cnpj, $exceptId = null) {
        $sql = "SELECT * FROM {$this->table} WHERE cnpj = :cnpj";
        
        if ($exceptId) {
            $sql .= " AND id != :except_id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':cnpj', $cnpj);
        
        if ($exceptId) {
            $stmt->bindValue(':except_id', $exceptId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Criar nova empresa tomadora
     * 
     * @param array $data
     * @return int ID da empresa criada
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (
                    razao_social, nome_fantasia, cnpj,
                    inscricao_estadual, inscricao_municipal,
                    cep, logradouro, numero, complemento, bairro, cidade, estado,
                    email_principal, email_financeiro, email_projetos,
                    telefone_principal, telefone_secundario, celular, whatsapp, site,
                    dia_fechamento, dia_pagamento, forma_pagamento_preferencial,
                    banco, agencia, conta, tipo_conta,
                    logo, observacoes, ativo,
                    created_by, created_at
                ) VALUES (
                    :razao_social, :nome_fantasia, :cnpj,
                    :inscricao_estadual, :inscricao_municipal,
                    :cep, :logradouro, :numero, :complemento, :bairro, :cidade, :estado,
                    :email_principal, :email_financeiro, :email_projetos,
                    :telefone_principal, :telefone_secundario, :celular, :whatsapp, :site,
                    :dia_fechamento, :dia_pagamento, :forma_pagamento_preferencial,
                    :banco, :agencia, :conta, :tipo_conta,
                    :logo, :observacoes, :ativo,
                    :created_by, NOW()
                )";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':razao_social', $data['razao_social']);
        $stmt->bindValue(':nome_fantasia', $data['nome_fantasia']);
        $stmt->bindValue(':cnpj', $data['cnpj']);
        $stmt->bindValue(':inscricao_estadual', $data['inscricao_estadual'] ?? null);
        $stmt->bindValue(':inscricao_municipal', $data['inscricao_municipal'] ?? null);
        $stmt->bindValue(':cep', $data['cep'] ?? null);
        $stmt->bindValue(':logradouro', $data['logradouro'] ?? null);
        $stmt->bindValue(':numero', $data['numero'] ?? null);
        $stmt->bindValue(':complemento', $data['complemento'] ?? null);
        $stmt->bindValue(':bairro', $data['bairro'] ?? null);
        $stmt->bindValue(':cidade', $data['cidade'] ?? null);
        $stmt->bindValue(':estado', $data['estado'] ?? null);
        $stmt->bindValue(':email_principal', $data['email_principal'] ?? null);
        $stmt->bindValue(':email_financeiro', $data['email_financeiro'] ?? null);
        $stmt->bindValue(':email_projetos', $data['email_projetos'] ?? null);
        $stmt->bindValue(':telefone_principal', $data['telefone_principal'] ?? null);
        $stmt->bindValue(':telefone_secundario', $data['telefone_secundario'] ?? null);
        $stmt->bindValue(':celular', $data['celular'] ?? null);
        $stmt->bindValue(':whatsapp', $data['whatsapp'] ?? null);
        $stmt->bindValue(':site', $data['site'] ?? null);
        $stmt->bindValue(':dia_fechamento', $data['dia_fechamento'] ?? 30, PDO::PARAM_INT);
        $stmt->bindValue(':dia_pagamento', $data['dia_pagamento'] ?? 5, PDO::PARAM_INT);
        $stmt->bindValue(':forma_pagamento_preferencial', $data['forma_pagamento_preferencial'] ?? null);
        $stmt->bindValue(':banco', $data['banco'] ?? null);
        $stmt->bindValue(':agencia', $data['agencia'] ?? null);
        $stmt->bindValue(':conta', $data['conta'] ?? null);
        $stmt->bindValue(':tipo_conta', $data['tipo_conta'] ?? null);
        $stmt->bindValue(':logo', $data['logo'] ?? null);
        $stmt->bindValue(':observacoes', $data['observacoes'] ?? null);
        $stmt->bindValue(':ativo', $data['ativo'] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(':created_by', $data['created_by'] ?? $_SESSION['user_id'], PDO::PARAM_INT);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Atualizar empresa tomadora
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET
                    razao_social = :razao_social,
                    nome_fantasia = :nome_fantasia,
                    cnpj = :cnpj,
                    inscricao_estadual = :inscricao_estadual,
                    inscricao_municipal = :inscricao_municipal,
                    cep = :cep,
                    logradouro = :logradouro,
                    numero = :numero,
                    complemento = :complemento,
                    bairro = :bairro,
                    cidade = :cidade,
                    estado = :estado,
                    email_principal = :email_principal,
                    email_financeiro = :email_financeiro,
                    email_projetos = :email_projetos,
                    telefone_principal = :telefone_principal,
                    telefone_secundario = :telefone_secundario,
                    celular = :celular,
                    whatsapp = :whatsapp,
                    site = :site,
                    dia_fechamento = :dia_fechamento,
                    dia_pagamento = :dia_pagamento,
                    forma_pagamento_preferencial = :forma_pagamento_preferencial,
                    banco = :banco,
                    agencia = :agencia,
                    conta = :conta,
                    tipo_conta = :tipo_conta,
                    logo = :logo,
                    observacoes = :observacoes,
                    ativo = :ativo,
                    updated_by = :updated_by,
                    updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':razao_social', $data['razao_social']);
        $stmt->bindValue(':nome_fantasia', $data['nome_fantasia']);
        $stmt->bindValue(':cnpj', $data['cnpj']);
        $stmt->bindValue(':inscricao_estadual', $data['inscricao_estadual'] ?? null);
        $stmt->bindValue(':inscricao_municipal', $data['inscricao_municipal'] ?? null);
        $stmt->bindValue(':cep', $data['cep'] ?? null);
        $stmt->bindValue(':logradouro', $data['logradouro'] ?? null);
        $stmt->bindValue(':numero', $data['numero'] ?? null);
        $stmt->bindValue(':complemento', $data['complemento'] ?? null);
        $stmt->bindValue(':bairro', $data['bairro'] ?? null);
        $stmt->bindValue(':cidade', $data['cidade'] ?? null);
        $stmt->bindValue(':estado', $data['estado'] ?? null);
        $stmt->bindValue(':email_principal', $data['email_principal'] ?? null);
        $stmt->bindValue(':email_financeiro', $data['email_financeiro'] ?? null);
        $stmt->bindValue(':email_projetos', $data['email_projetos'] ?? null);
        $stmt->bindValue(':telefone_principal', $data['telefone_principal'] ?? null);
        $stmt->bindValue(':telefone_secundario', $data['telefone_secundario'] ?? null);
        $stmt->bindValue(':celular', $data['celular'] ?? null);
        $stmt->bindValue(':whatsapp', $data['whatsapp'] ?? null);
        $stmt->bindValue(':site', $data['site'] ?? null);
        $stmt->bindValue(':dia_fechamento', $data['dia_fechamento'] ?? 30, PDO::PARAM_INT);
        $stmt->bindValue(':dia_pagamento', $data['dia_pagamento'] ?? 5, PDO::PARAM_INT);
        $stmt->bindValue(':forma_pagamento_preferencial', $data['forma_pagamento_preferencial'] ?? null);
        $stmt->bindValue(':banco', $data['banco'] ?? null);
        $stmt->bindValue(':agencia', $data['agencia'] ?? null);
        $stmt->bindValue(':conta', $data['conta'] ?? null);
        $stmt->bindValue(':tipo_conta', $data['tipo_conta'] ?? null);
        $stmt->bindValue(':logo', $data['logo'] ?? null);
        $stmt->bindValue(':observacoes', $data['observacoes'] ?? null);
        $stmt->bindValue(':ativo', $data['ativo'] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(':updated_by', $data['updated_by'] ?? $_SESSION['user_id'], PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Soft delete
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        // Verificar se tem contratos ativos
        $contratos = $this->getContratosAtivos($id);
        if (!empty($contratos)) {
            throw new \Exception('Não é possível excluir empresa com contratos ativos. Total: ' . count($contratos));
        }
        
        $sql = "UPDATE {$this->table} 
                SET ativo = 0, 
                    updated_by = :updated_by,
                    updated_at = NOW() 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':updated_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Buscar empresas ativas
     * 
     * @return array
     */
    public function getAtivas() {
        $sql = "SELECT id, nome_fantasia, razao_social, cnpj, cidade, estado
                FROM {$this->table} 
                WHERE ativo = 1 
                ORDER BY nome_fantasia ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar contratos da empresa
     * 
     * @param int $id
     * @param string|null $status
     * @return array
     */
    public function getContratos($id, $status = null) {
        $sql = "SELECT c.*, 
                       ep.nome_fantasia as prestadora_nome
                FROM contratos c
                INNER JOIN empresas_prestadoras ep ON c.empresa_prestadora_id = ep.id
                WHERE c.empresa_tomadora_id = :id";
        
        if ($status) {
            $sql .= " AND c.status = :status";
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        if ($status) {
            $stmt->bindValue(':status', $status);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar contratos ativos
     * 
     * @param int $id
     * @return array
     */
    public function getContratosAtivos($id) {
        return $this->getContratos($id, 'ativo');
    }
    
    /**
     * Buscar responsáveis da empresa
     * 
     * @param int $id
     * @return array
     */
    public function getResponsaveis($id) {
        $sql = "SELECT * FROM empresa_tomadora_responsaveis 
                WHERE empresa_tomadora_id = :id AND ativo = 1
                ORDER BY principal DESC, nome ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar documentos da empresa
     * 
     * @param int $id
     * @return array
     */
    public function getDocumentos($id) {
        $sql = "SELECT d.*, u.nome as uploader_nome
                FROM empresa_documentos d
                LEFT JOIN usuarios u ON d.created_by = u.id
                WHERE d.empresa_id = :id AND d.tipo_empresa = 'tomadora' AND d.ativo = 1
                ORDER BY d.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar estatísticas da empresa
     * 
     * @param int $id
     * @return array
     */
    public function getEstatisticas($id) {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM contratos WHERE empresa_tomadora_id = :id AND status = 'ativo') as total_contratos_ativos,
                    (SELECT COUNT(*) FROM contratos WHERE empresa_tomadora_id = :id) as total_contratos,
                    (SELECT COUNT(*) FROM empresa_tomadora_responsaveis WHERE empresa_tomadora_id = :id AND ativo = 1) as total_responsaveis,
                    (SELECT COUNT(*) FROM empresa_documentos WHERE empresa_id = :id AND tipo_empresa = 'tomadora' AND ativo = 1) as total_documentos";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Buscar cidades distintas
     * 
     * @return array
     */
    public function getCidadesDistintas() {
        $sql = "SELECT DISTINCT cidade FROM {$this->table} 
                WHERE cidade IS NOT NULL AND cidade != '' AND ativo = 1
                ORDER BY cidade ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Buscar estados distintos
     * 
     * @return array
     */
    public function getEstadosDistintos() {
        $sql = "SELECT DISTINCT estado FROM {$this->table} 
                WHERE estado IS NOT NULL AND estado != '' AND ativo = 1
                ORDER BY estado ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Validar CNPJ
     * 
     * @param string $cnpj
     * @return bool
     */
    public function validarCNPJ($cnpj) {
        // Remove caracteres não numéricos
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        // Valida tamanho
        if (strlen($cnpj) != 14) {
            return false;
        }
        
        // Valida se todos os dígitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }
        
        // Validação dos dígitos verificadores
        $tamanho = strlen($cnpj) - 2;
        $numeros = substr($cnpj, 0, $tamanho);
        $digitos = substr($cnpj, $tamanho);
        $soma = 0;
        $pos = $tamanho - 7;
        
        for ($i = $tamanho; $i >= 1; $i--) {
            $soma += $numeros[$tamanho - $i] * $pos--;
            if ($pos < 2) {
                $pos = 9;
            }
        }
        
        $resultado = $soma % 11 < 2 ? 0 : 11 - $soma % 11;
        
        if ($resultado != $digitos[0]) {
            return false;
        }
        
        $tamanho = $tamanho + 1;
        $numeros = substr($cnpj, 0, $tamanho);
        $soma = 0;
        $pos = $tamanho - 7;
        
        for ($i = $tamanho; $i >= 1; $i--) {
            $soma += $numeros[$tamanho - $i] * $pos--;
            if ($pos < 2) {
                $pos = 9;
            }
        }
        
        $resultado = $soma % 11 < 2 ? 0 : 11 - $soma % 11;
        
        return $resultado == $digitos[1];
    }
    
    /**
     * Formatar CNPJ
     * 
     * @param string $cnpj
     * @return string
     */
    public function formatarCNPJ($cnpj) {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        if (strlen($cnpj) == 14) {
            return substr($cnpj, 0, 2) . '.' . 
                   substr($cnpj, 2, 3) . '.' . 
                   substr($cnpj, 5, 3) . '/' . 
                   substr($cnpj, 8, 4) . '-' . 
                   substr($cnpj, 12, 2);
        }
        
        return $cnpj;
    }
    
    /**
     * Alias para validarCNPJ - compatibilidade com controller
     */
    public function validateCnpj($cnpj) {
        return $this->validarCNPJ($cnpj);
    }
    
    /**
     * Validar unicidade do CNPJ
     * 
     * @param string $cnpj
     * @param int|null $exceptId ID a excluir da verificação
     * @return bool
     */
    public function validateUniqueCnpj($cnpj, $exceptId = null) {
        $existing = $this->findByCnpj($cnpj, $exceptId);
        return $existing === null;
    }
    
    /**
     * Contar total de empresas
     * 
     * @return int
     */
    public function countTotal() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Contar empresas ativas
     * 
     * @return int
     */
    public function countAtivas() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE ativo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Adicionar responsável
     * 
     * @param int $empresaId
     * @param array $data
     * @return int
     */
    public function addResponsavel($empresaId, $data) {
        // Se marcar como principal, desmarcar os demais
        if (!empty($data['responsavel_principal']) && $data['responsavel_principal'] == 1) {
            $sql = "UPDATE empresa_tomadora_responsaveis 
                    SET responsavel_principal = 0 
                    WHERE empresa_tomadora_id = :empresa_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->execute();
        }
        
        $sql = "INSERT INTO empresa_tomadora_responsaveis (
                    empresa_tomadora_id, nome, cargo, departamento,
                    email, telefone, celular, ramal,
                    responsavel_principal, recebe_notificacoes,
                    ativo, observacoes, foto, created_by, created_at
                ) VALUES (
                    :empresa_id, :nome, :cargo, :departamento,
                    :email, :telefone, :celular, :ramal,
                    :responsavel_principal, :recebe_notificacoes,
                    :ativo, :observacoes, :foto, :created_by, NOW()
                )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':nome', $data['nome']);
        $stmt->bindValue(':cargo', $data['cargo'] ?? null);
        $stmt->bindValue(':departamento', $data['departamento'] ?? null);
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':telefone', $data['telefone'] ?? null);
        $stmt->bindValue(':celular', $data['celular'] ?? null);
        $stmt->bindValue(':ramal', $data['ramal'] ?? null);
        $stmt->bindValue(':responsavel_principal', $data['responsavel_principal'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':recebe_notificacoes', $data['recebe_notificacoes'] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(':ativo', $data['ativo'] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(':observacoes', $data['observacoes'] ?? null);
        $stmt->bindValue(':foto', $data['foto'] ?? null);
        $stmt->bindValue(':created_by', $data['criado_por'] ?? $_SESSION['user_id'], PDO::PARAM_INT);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Excluir responsável (soft delete)
     * 
     * @param int $responsavelId
     * @return bool
     */
    public function deleteResponsavel($responsavelId) {
        $sql = "UPDATE empresa_tomadora_responsaveis 
                SET ativo = 0, updated_at = NOW() 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $responsavelId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Adicionar documento
     * 
     * @param int $empresaId
     * @param array $data
     * @return int
     */
    public function addDocumento($empresaId, $data) {
        $sql = "INSERT INTO empresa_documentos (
                    empresa_id, tipo_empresa, tipo_documento, nome_documento,
                    descricao, caminho_arquivo, tamanho_bytes, mime_type,
                    data_emissao, data_validade, alertar_dias_antes,
                    ativo, created_by, created_at
                ) VALUES (
                    :empresa_id, 'tomadora', :tipo_documento, :nome_documento,
                    :descricao, :caminho_arquivo, :tamanho_bytes, :mime_type,
                    :data_emissao, :data_validade, :alertar_dias_antes,
                    1, :created_by, NOW()
                )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':tipo_documento', $data['tipo_documento']);
        $stmt->bindValue(':nome_documento', $data['nome_documento']);
        $stmt->bindValue(':descricao', $data['descricao'] ?? null);
        $stmt->bindValue(':caminho_arquivo', $data['arquivo']);
        $stmt->bindValue(':tamanho_bytes', $data['tamanho_bytes'], PDO::PARAM_INT);
        $stmt->bindValue(':mime_type', $data['mime_type']);
        $stmt->bindValue(':data_emissao', $data['data_emissao'] ?? null);
        $stmt->bindValue(':data_validade', $data['data_validade'] ?? null);
        $stmt->bindValue(':alertar_dias_antes', $data['alertar_dias_antes'] ?? 30, PDO::PARAM_INT);
        $stmt->bindValue(':created_by', $data['upload_por'] ?? $_SESSION['user_id'], PDO::PARAM_INT);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Excluir documento (soft delete)
     * 
     * @param int $documentoId
     * @return bool
     */
    public function deleteDocumento($documentoId) {
        $sql = "UPDATE empresa_documentos 
                SET ativo = 0, updated_at = NOW() 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $documentoId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Buscar contratos por empresa (contador)
     * 
     * @param int $empresaId
     * @return int
     */
    public function getContratosPorEmpresa($empresaId) {
        $sql = "SELECT COUNT(*) as total FROM contratos 
                WHERE empresa_tomadora_id = :empresa_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Buscar projetos por empresa (contador) - placeholder para Sprint 5
     * 
     * @param int $empresaId
     * @return int
     */
    public function getProjetosPorEmpresa($empresaId) {
        // Placeholder - será implementado na Sprint 5
        return 0;
    }
}
