<?php

namespace App\Models;

use App\Database;
use PDO;

class ProjetoTemplate
{
    private $db;
    private $table = 'projeto_templates';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function all($filtros = [])
    {
        $sql = "SELECT pt.*, 
                       u.nome as criador_nome,
                       pc.nome as categoria_nome
                FROM {$this->table} pt
                LEFT JOIN usuarios u ON pt.criador_id = u.id
                LEFT JOIN projeto_categorias pc ON pt.categoria_id = pc.id
                WHERE pt.ativo = TRUE";

        $params = [];

        if (!empty($filtros['categoria_id'])) {
            $sql .= " AND pt.categoria_id = :categoria_id";
            $params['categoria_id'] = $filtros['categoria_id'];
        }

        if (!empty($filtros['publico'])) {
            $sql .= " AND pt.publico = :publico";
            $params['publico'] = (int)$filtros['publico'];
        }

        $sql .= " ORDER BY pt.nome";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $sql = "SELECT pt.*, 
                       u.nome as criador_nome,
                       pc.nome as categoria_nome
                FROM {$this->table} pt
                LEFT JOIN usuarios u ON pt.criador_id = u.id
                LEFT JOIN projeto_categorias pc ON pt.categoria_id = pc.id
                WHERE pt.id = :id 
                AND pt.ativo = TRUE";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $template = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($template) {
            // Decodificar JSONs
            $template['etapas'] = json_decode($template['etapas'], true) ?? [];
            $template['equipe_padrao'] = json_decode($template['equipe_padrao'], true) ?? [];
            $template['orcamento_padrao'] = json_decode($template['orcamento_padrao'], true) ?? [];
        }

        return $template;
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (
                    nome, descricao, categoria_id, criador_id,
                    duracao_estimada, etapas, equipe_padrao, orcamento_padrao,
                    observacoes, publico
                ) VALUES (
                    :nome, :descricao, :categoria_id, :criador_id,
                    :duracao_estimada, :etapas, :equipe_padrao, :orcamento_padrao,
                    :observacoes, :publico
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'categoria_id' => $data['categoria_id'] ?? null,
            'criador_id' => $data['criador_id'],
            'duracao_estimada' => $data['duracao_estimada'] ?? null,
            'etapas' => json_encode($data['etapas'] ?? []),
            'equipe_padrao' => json_encode($data['equipe_padrao'] ?? []),
            'orcamento_padrao' => json_encode($data['orcamento_padrao'] ?? []),
            'observacoes' => $data['observacoes'] ?? null,
            'publico' => $data['publico'] ?? 0
        ]);

        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                    nome = :nome,
                    descricao = :descricao,
                    categoria_id = :categoria_id,
                    duracao_estimada = :duracao_estimada,
                    etapas = :etapas,
                    equipe_padrao = :equipe_padrao,
                    orcamento_padrao = :orcamento_padrao,
                    observacoes = :observacoes,
                    publico = :publico
                WHERE id = :id 
                AND ativo = TRUE";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'categoria_id' => $data['categoria_id'] ?? null,
            'duracao_estimada' => $data['duracao_estimada'] ?? null,
            'etapas' => json_encode($data['etapas'] ?? []),
            'equipe_padrao' => json_encode($data['equipe_padrao'] ?? []),
            'orcamento_padrao' => json_encode($data['orcamento_padrao'] ?? []),
            'observacoes' => $data['observacoes'] ?? null,
            'publico' => $data['publico'] ?? 0
        ]);
    }

    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = FALSE WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function duplicar($id, $novoNome = null)
    {
        $template = $this->findById($id);
        
        if (!$template) {
            return false;
        }

        $data = [
            'nome' => $novoNome ?? $template['nome'] . ' (Cópia)',
            'descricao' => $template['descricao'],
            'categoria_id' => $template['categoria_id'],
            'criador_id' => $template['criador_id'],
            'duracao_estimada' => $template['duracao_estimada'],
            'etapas' => $template['etapas'],
            'equipe_padrao' => $template['equipe_padrao'],
            'orcamento_padrao' => $template['orcamento_padrao'],
            'observacoes' => $template['observacoes'],
            'publico' => 0 // Cópias sempre privadas
        ];

        return $this->create($data);
    }

    public function aplicarEmProjeto($templateId, $projetoId)
    {
        $template = $this->findById($templateId);
        
        if (!$template) {
            return false;
        }

        $projetoModel = new Projeto();
        $etapaModel = new ProjetoEtapa();
        $orcamentoModel = new ProjetoOrcamento();

        // Aplicar etapas
        if (!empty($template['etapas'])) {
            foreach ($template['etapas'] as $etapa) {
                $etapaData = [
                    'projeto_id' => $projetoId,
                    'nome' => $etapa['nome'],
                    'descricao' => $etapa['descricao'] ?? null,
                    'duracao_estimada' => $etapa['duracao_estimada'] ?? null,
                    'ordem' => $etapa['ordem'] ?? 0
                ];
                $etapaModel->create($etapaData);
            }
        }

        // Aplicar orçamento padrão
        if (!empty($template['orcamento_padrao'])) {
            foreach ($template['orcamento_padrao'] as $item) {
                $itemData = [
                    'projeto_id' => $projetoId,
                    'categoria' => $item['categoria'],
                    'tipo' => $item['tipo'] ?? 'despesa',
                    'descricao' => $item['descricao'],
                    'quantidade' => $item['quantidade'] ?? 1,
                    'valor_unitario' => $item['valor_unitario'] ?? 0
                ];
                $orcamentoModel->create($itemData);
            }
        }

        return true;
    }

    public function criarDeProjeto($projetoId, $nome, $criadorId, $publico = false)
    {
        $projetoModel = new Projeto();
        $etapaModel = new ProjetoEtapa();
        $orcamentoModel = new ProjetoOrcamento();

        $projeto = $projetoModel->findById($projetoId);
        
        if (!$projeto) {
            return false;
        }

        // Buscar etapas do projeto
        $etapas = $etapaModel->getByProjeto($projetoId);
        $etapasTemplate = [];
        
        foreach ($etapas as $etapa) {
            $etapasTemplate[] = [
                'nome' => $etapa['nome'],
                'descricao' => $etapa['descricao'],
                'duracao_estimada' => $etapa['duracao_estimada'],
                'ordem' => $etapa['ordem']
            ];
        }

        // Buscar orçamento do projeto
        $orcamento = $orcamentoModel->getByProjeto($projetoId);
        $orcamentoTemplate = [];
        
        foreach ($orcamento as $item) {
            $orcamentoTemplate[] = [
                'categoria' => $item['categoria'],
                'tipo' => $item['tipo'],
                'descricao' => $item['descricao'],
                'quantidade' => $item['quantidade'],
                'valor_unitario' => $item['valor_unitario']
            ];
        }

        // Criar template
        $data = [
            'nome' => $nome,
            'descricao' => 'Template criado a partir do projeto: ' . $projeto['nome'],
            'categoria_id' => $projeto['categoria_id'],
            'criador_id' => $criadorId,
            'duracao_estimada' => $projeto['duracao_estimada'],
            'etapas' => $etapasTemplate,
            'equipe_padrao' => [],
            'orcamento_padrao' => $orcamentoTemplate,
            'observacoes' => null,
            'publico' => $publico ? 1 : 0
        ];

        return $this->create($data);
    }
}
