<?php

namespace App\Models;

use App\Database;
use PDO;

class ProjetoOrcamento
{
    private $db;
    private $table = 'projeto_orcamento';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Busca todos os itens orçamentários de um projeto
     *
     * @param int $projetoId
     * @param array $filtros
     * @return array
     */
    public function getByProjeto($projetoId, $filtros = [])
    {
        $sql = "SELECT po.*, 
                       u.nome as responsavel_nome
                FROM {$this->table} po
                LEFT JOIN usuarios u ON po.responsavel_id = u.id
                WHERE po.projeto_id = :projeto_id 
                AND po.ativo = TRUE";

        $params = ['projeto_id' => $projetoId];

        // Filtros
        if (!empty($filtros['categoria'])) {
            $sql .= " AND po.categoria = :categoria";
            $params['categoria'] = $filtros['categoria'];
        }

        if (!empty($filtros['tipo'])) {
            $sql .= " AND po.tipo = :tipo";
            $params['tipo'] = $filtros['tipo'];
        }

        if (!empty($filtros['status'])) {
            $sql .= " AND po.status = :status";
            $params['status'] = $filtros['status'];
        }

        $sql .= " ORDER BY po.categoria, po.descricao";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca item orçamentário por ID
     *
     * @param int $id
     * @return array|false
     */
    public function findById($id)
    {
        $sql = "SELECT po.*, 
                       p.codigo as projeto_codigo,
                       p.nome as projeto_nome,
                       u.nome as responsavel_nome
                FROM {$this->table} po
                INNER JOIN projetos p ON po.projeto_id = p.id
                LEFT JOIN usuarios u ON po.responsavel_id = u.id
                WHERE po.id = :id 
                AND po.ativo = TRUE";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cria novo item orçamentário
     *
     * @param array $data
     * @return int ID do registro criado
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (
                    projeto_id, categoria, tipo, descricao, unidade, 
                    quantidade, valor_unitario, valor_total, responsavel_id, 
                    centro_custo, observacoes, data_prevista
                ) VALUES (
                    :projeto_id, :categoria, :tipo, :descricao, :unidade, 
                    :quantidade, :valor_unitario, :valor_total, :responsavel_id, 
                    :centro_custo, :observacoes, :data_prevista
                )";

        $valorTotal = ($data['quantidade'] ?? 1) * ($data['valor_unitario'] ?? 0);

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'projeto_id' => $data['projeto_id'],
            'categoria' => $data['categoria'],
            'tipo' => $data['tipo'] ?? 'despesa',
            'descricao' => $data['descricao'],
            'unidade' => $data['unidade'] ?? 'un',
            'quantidade' => $data['quantidade'] ?? 1,
            'valor_unitario' => $data['valor_unitario'] ?? 0,
            'valor_total' => $valorTotal,
            'responsavel_id' => $data['responsavel_id'] ?? null,
            'centro_custo' => $data['centro_custo'] ?? null,
            'observacoes' => $data['observacoes'] ?? null,
            'data_prevista' => $data['data_prevista'] ?? null
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Atualiza item orçamentário
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        $valorTotal = ($data['quantidade'] ?? 1) * ($data['valor_unitario'] ?? 0);

        $sql = "UPDATE {$this->table} SET 
                    categoria = :categoria,
                    tipo = :tipo,
                    descricao = :descricao,
                    unidade = :unidade,
                    quantidade = :quantidade,
                    valor_unitario = :valor_unitario,
                    valor_total = :valor_total,
                    responsavel_id = :responsavel_id,
                    centro_custo = :centro_custo,
                    observacoes = :observacoes,
                    data_prevista = :data_prevista
                WHERE id = :id 
                AND ativo = TRUE";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'categoria' => $data['categoria'],
            'tipo' => $data['tipo'] ?? 'despesa',
            'descricao' => $data['descricao'],
            'unidade' => $data['unidade'] ?? 'un',
            'quantidade' => $data['quantidade'] ?? 1,
            'valor_unitario' => $data['valor_unitario'] ?? 0,
            'valor_total' => $valorTotal,
            'responsavel_id' => $data['responsavel_id'] ?? null,
            'centro_custo' => $data['centro_custo'] ?? null,
            'observacoes' => $data['observacoes'] ?? null,
            'data_prevista' => $data['data_prevista'] ?? null
        ]);
    }

    /**
     * Registra valor realizado no item orçamentário
     *
     * @param int $id
     * @param float $valorRealizado
     * @param string|null $dataRealizada
     * @param string|null $observacoes
     * @return bool
     */
    public function registrarRealizado($id, $valorRealizado, $dataRealizada = null, $observacoes = null)
    {
        $sql = "UPDATE {$this->table} SET 
                    valor_realizado = valor_realizado + :valor_realizado,
                    data_realizada = :data_realizada,
                    observacoes_realizacao = :observacoes
                WHERE id = :id 
                AND ativo = TRUE";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'valor_realizado' => $valorRealizado,
            'data_realizada' => $dataRealizada ?? date('Y-m-d'),
            'observacoes' => $observacoes
        ]);
    }

    /**
     * Solicita aprovação do item orçamentário
     *
     * @param int $id
     * @param int $solicitanteId
     * @param string|null $justificativa
     * @return bool
     */
    public function solicitarAprovacao($id, $solicitanteId, $justificativa = null)
    {
        $sql = "UPDATE {$this->table} SET 
                    status = 'aguardando_aprovacao',
                    solicitante_id = :solicitante_id,
                    justificativa_solicitacao = :justificativa,
                    data_solicitacao = NOW()
                WHERE id = :id 
                AND ativo = TRUE";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'solicitante_id' => $solicitanteId,
            'justificativa' => $justificativa
        ]);
    }

    /**
     * Aprova item orçamentário
     *
     * @param int $id
     * @param int $aprovadorId
     * @param string|null $observacoes
     * @return bool
     */
    public function aprovar($id, $aprovadorId, $observacoes = null)
    {
        $sql = "UPDATE {$this->table} SET 
                    status = 'aprovado',
                    aprovador_id = :aprovador_id,
                    data_aprovacao = NOW(),
                    observacoes_aprovacao = :observacoes
                WHERE id = :id 
                AND ativo = TRUE";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'aprovador_id' => $aprovadorId,
            'observacoes' => $observacoes
        ]);
    }

    /**
     * Rejeita item orçamentário
     *
     * @param int $id
     * @param int $aprovadorId
     * @param string $motivo
     * @return bool
     */
    public function rejeitar($id, $aprovadorId, $motivo)
    {
        $sql = "UPDATE {$this->table} SET 
                    status = 'rejeitado',
                    aprovador_id = :aprovador_id,
                    data_aprovacao = NOW(),
                    observacoes_aprovacao = :motivo
                WHERE id = :id 
                AND ativo = TRUE";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'aprovador_id' => $aprovadorId,
            'motivo' => $motivo
        ]);
    }

    /**
     * Remove item orçamentário (soft delete)
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = FALSE WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Calcula totais do orçamento do projeto
     *
     * @param int $projetoId
     * @return array
     */
    public function getTotais($projetoId)
    {
        $sql = "SELECT 
                    tipo,
                    COUNT(*) as total_itens,
                    SUM(valor_total) as valor_previsto,
                    SUM(valor_realizado) as valor_realizado,
                    SUM(valor_total - valor_realizado) as saldo
                FROM {$this->table}
                WHERE projeto_id = :projeto_id 
                AND ativo = TRUE
                GROUP BY tipo";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['projeto_id' => $projetoId]);

        $resultado = [
            'receitas' => [
                'total_itens' => 0,
                'valor_previsto' => 0,
                'valor_realizado' => 0,
                'saldo' => 0
            ],
            'despesas' => [
                'total_itens' => 0,
                'valor_previsto' => 0,
                'valor_realizado' => 0,
                'saldo' => 0
            ]
        ];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['tipo'] === 'receita') {
                $resultado['receitas'] = $row;
            } else {
                $resultado['despesas'] = $row;
            }
        }

        // Calcular resultado geral
        $resultado['resultado'] = [
            'valor_previsto' => $resultado['receitas']['valor_previsto'] - $resultado['despesas']['valor_previsto'],
            'valor_realizado' => $resultado['receitas']['valor_realizado'] - $resultado['despesas']['valor_realizado'],
            'saldo' => $resultado['receitas']['saldo'] - $resultado['despesas']['saldo']
        ];

        return $resultado;
    }

    /**
     * Busca itens por categoria
     *
     * @param int $projetoId
     * @return array
     */
    public function getTotaisPorCategoria($projetoId)
    {
        $sql = "SELECT 
                    categoria,
                    tipo,
                    COUNT(*) as total_itens,
                    SUM(valor_total) as valor_previsto,
                    SUM(valor_realizado) as valor_realizado
                FROM {$this->table}
                WHERE projeto_id = :projeto_id 
                AND ativo = TRUE
                GROUP BY categoria, tipo
                ORDER BY categoria";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['projeto_id' => $projetoId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca itens pendentes de aprovação
     *
     * @param int|null $projetoId
     * @return array
     */
    public function getPendentesAprovacao($projetoId = null)
    {
        $sql = "SELECT po.*, 
                       p.codigo as projeto_codigo,
                       p.nome as projeto_nome,
                       u.nome as solicitante_nome
                FROM {$this->table} po
                INNER JOIN projetos p ON po.projeto_id = p.id
                LEFT JOIN usuarios u ON po.solicitante_id = u.id
                WHERE po.status = 'aguardando_aprovacao' 
                AND po.ativo = TRUE";

        $params = [];

        if ($projetoId) {
            $sql .= " AND po.projeto_id = :projeto_id";
            $params['projeto_id'] = $projetoId;
        }

        $sql .= " ORDER BY po.data_solicitacao";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca categorias disponíveis
     *
     * @return array
     */
    public function getCategoriasDisponiveis()
    {
        return [
            'pessoal' => 'Pessoal',
            'equipamentos' => 'Equipamentos',
            'materiais' => 'Materiais',
            'servicos' => 'Serviços Terceirizados',
            'software' => 'Software e Licenças',
            'infraestrutura' => 'Infraestrutura',
            'marketing' => 'Marketing',
            'treinamento' => 'Treinamento',
            'viagens' => 'Viagens e Deslocamentos',
            'consultoria' => 'Consultoria',
            'outros' => 'Outros'
        ];
    }

    /**
     * Busca unidades disponíveis
     *
     * @return array
     */
    public function getUnidadesDisponiveis()
    {
        return [
            'un' => 'Unidade',
            'hr' => 'Hora',
            'dia' => 'Dia',
            'mes' => 'Mês',
            'kg' => 'Quilograma',
            'lt' => 'Litro',
            'm' => 'Metro',
            'm2' => 'Metro Quadrado',
            'cx' => 'Caixa',
            'pc' => 'Pacote',
            'licenca' => 'Licença',
            'servico' => 'Serviço'
        ];
    }

    /**
     * Exporta orçamento para relatório
     *
     * @param int $projetoId
     * @return array
     */
    public function exportar($projetoId)
    {
        $totais = $this->getTotais($projetoId);
        $itens = $this->getByProjeto($projetoId);
        $porCategoria = $this->getTotaisPorCategoria($projetoId);

        return [
            'totais' => $totais,
            'itens' => $itens,
            'por_categoria' => $porCategoria,
            'data_exportacao' => date('Y-m-d H:i:s')
        ];
    }
}
