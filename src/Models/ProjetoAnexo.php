<?php

namespace App\Models;

use App\Database;
use PDO;

class ProjetoAnexo
{
    private $db;
    private $table = 'projeto_anexos';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getByProjeto($projetoId, $filtros = [])
    {
        $sql = "SELECT pa.*, 
                       u.nome as usuario_nome,
                       pe.nome as etapa_nome
                FROM {$this->table} pa
                INNER JOIN usuarios u ON pa.usuario_id = u.id
                LEFT JOIN projeto_etapas pe ON pa.etapa_id = pe.id
                WHERE pa.projeto_id = :projeto_id 
                AND pa.ativo = TRUE";

        $params = ['projeto_id' => $projetoId];

        if (!empty($filtros['tipo'])) {
            $sql .= " AND pa.tipo = :tipo";
            $params['tipo'] = $filtros['tipo'];
        }

        if (!empty($filtros['etapa_id'])) {
            $sql .= " AND pa.etapa_id = :etapa_id";
            $params['etapa_id'] = $filtros['etapa_id'];
        }

        $sql .= " ORDER BY pa.versao DESC, pa.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $sql = "SELECT pa.*, 
                       p.codigo as projeto_codigo,
                       p.nome as projeto_nome,
                       u.nome as usuario_nome,
                       pe.nome as etapa_nome
                FROM {$this->table} pa
                INNER JOIN projetos p ON pa.projeto_id = p.id
                INNER JOIN usuarios u ON pa.usuario_id = u.id
                LEFT JOIN projeto_etapas pe ON pa.etapa_id = pe.id
                WHERE pa.id = :id 
                AND pa.ativo = TRUE";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (
                    projeto_id, etapa_id, tipo, nome_arquivo, 
                    nome_original, caminho, tamanho, mime_type,
                    versao, descricao, usuario_id
                ) VALUES (
                    :projeto_id, :etapa_id, :tipo, :nome_arquivo, 
                    :nome_original, :caminho, :tamanho, :mime_type,
                    :versao, :descricao, :usuario_id
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'projeto_id' => $data['projeto_id'],
            'etapa_id' => $data['etapa_id'] ?? null,
            'tipo' => $data['tipo'],
            'nome_arquivo' => $data['nome_arquivo'],
            'nome_original' => $data['nome_original'],
            'caminho' => $data['caminho'],
            'tamanho' => $data['tamanho'],
            'mime_type' => $data['mime_type'],
            'versao' => $data['versao'] ?? 1,
            'descricao' => $data['descricao'] ?? null,
            'usuario_id' => $data['usuario_id']
        ]);

        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                    tipo = :tipo,
                    descricao = :descricao
                WHERE id = :id 
                AND ativo = TRUE";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'tipo' => $data['tipo'],
            'descricao' => $data['descricao'] ?? null
        ]);
    }

    public function novaVersao($anexoAntigoId, $data)
    {
        // Buscar informações do anexo antigo
        $anexoAntigo = $this->findById($anexoAntigoId);
        
        if (!$anexoAntigo) {
            return false;
        }

        // Criar nova versão
        $data['projeto_id'] = $anexoAntigo['projeto_id'];
        $data['etapa_id'] = $anexoAntigo['etapa_id'];
        $data['tipo'] = $anexoAntigo['tipo'];
        $data['versao'] = $anexoAntigo['versao'] + 1;

        return $this->create($data);
    }

    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = FALSE WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function getVersoes($projetoId, $nomeBase)
    {
        $sql = "SELECT pa.*, 
                       u.nome as usuario_nome
                FROM {$this->table} pa
                INNER JOIN usuarios u ON pa.usuario_id = u.id
                WHERE pa.projeto_id = :projeto_id 
                AND pa.nome_original LIKE :nome_base
                AND pa.ativo = TRUE
                ORDER BY pa.versao DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'projeto_id' => $projetoId,
            'nome_base' => $nomeBase . '%'
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTiposDisponiveis()
    {
        return [
            'contrato' => 'Contrato',
            'proposta' => 'Proposta',
            'especificacao' => 'Especificação',
            'cronograma' => 'Cronograma',
            'ata' => 'Ata de Reunião',
            'relatorio' => 'Relatório',
            'apresentacao' => 'Apresentação',
            'planilha' => 'Planilha',
            'imagem' => 'Imagem',
            'outro' => 'Outro'
        ];
    }
}
