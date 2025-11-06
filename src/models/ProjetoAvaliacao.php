<?php

namespace App\Models;

use App\Database;
use PDO;

class ProjetoAvaliacao
{
    private $db;
    private $table = 'projeto_avaliacoes';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByProjeto($projetoId)
    {
        $sql = "SELECT pa.*, 
                       u.nome as avaliador_nome
                FROM {$this->table} pa
                INNER JOIN usuarios u ON pa.avaliador_id = u.id
                WHERE pa.projeto_id = :projeto_id 
                AND pa.ativo = TRUE
                ORDER BY pa.data_avaliacao DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['projeto_id' => $projetoId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $sql = "SELECT pa.*, 
                       p.codigo as projeto_codigo,
                       p.nome as projeto_nome,
                       u.nome as avaliador_nome
                FROM {$this->table} pa
                INNER JOIN projetos p ON pa.projeto_id = p.id
                INNER JOIN usuarios u ON pa.avaliador_id = u.id
                WHERE pa.id = :id 
                AND pa.ativo = TRUE";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $notaGeral = round((
            $data['nota_qualidade'] + 
            $data['nota_prazo'] + 
            $data['nota_custo'] + 
            $data['nota_comunicacao'] + 
            $data['nota_equipe']
        ) / 5, 2);

        $sql = "INSERT INTO {$this->table} (
                    projeto_id, avaliador_id, data_avaliacao, 
                    nota_qualidade, nota_prazo, nota_custo, 
                    nota_comunicacao, nota_equipe, nota_geral,
                    pontos_fortes, pontos_melhoria, licoes_aprendidas,
                    recomendacoes, comentarios
                ) VALUES (
                    :projeto_id, :avaliador_id, :data_avaliacao, 
                    :nota_qualidade, :nota_prazo, :nota_custo, 
                    :nota_comunicacao, :nota_equipe, :nota_geral,
                    :pontos_fortes, :pontos_melhoria, :licoes_aprendidas,
                    :recomendacoes, :comentarios
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'projeto_id' => $data['projeto_id'],
            'avaliador_id' => $data['avaliador_id'],
            'data_avaliacao' => $data['data_avaliacao'] ?? date('Y-m-d'),
            'nota_qualidade' => $data['nota_qualidade'],
            'nota_prazo' => $data['nota_prazo'],
            'nota_custo' => $data['nota_custo'],
            'nota_comunicacao' => $data['nota_comunicacao'],
            'nota_equipe' => $data['nota_equipe'],
            'nota_geral' => $notaGeral,
            'pontos_fortes' => $data['pontos_fortes'] ?? null,
            'pontos_melhoria' => $data['pontos_melhoria'] ?? null,
            'licoes_aprendidas' => $data['licoes_aprendidas'] ?? null,
            'recomendacoes' => $data['recomendacoes'] ?? null,
            'comentarios' => $data['comentarios'] ?? null
        ]);

        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $notaGeral = round((
            $data['nota_qualidade'] + 
            $data['nota_prazo'] + 
            $data['nota_custo'] + 
            $data['nota_comunicacao'] + 
            $data['nota_equipe']
        ) / 5, 2);

        $sql = "UPDATE {$this->table} SET 
                    data_avaliacao = :data_avaliacao,
                    nota_qualidade = :nota_qualidade,
                    nota_prazo = :nota_prazo,
                    nota_custo = :nota_custo,
                    nota_comunicacao = :nota_comunicacao,
                    nota_equipe = :nota_equipe,
                    nota_geral = :nota_geral,
                    pontos_fortes = :pontos_fortes,
                    pontos_melhoria = :pontos_melhoria,
                    licoes_aprendidas = :licoes_aprendidas,
                    recomendacoes = :recomendacoes,
                    comentarios = :comentarios
                WHERE id = :id 
                AND ativo = TRUE";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'data_avaliacao' => $data['data_avaliacao'] ?? date('Y-m-d'),
            'nota_qualidade' => $data['nota_qualidade'],
            'nota_prazo' => $data['nota_prazo'],
            'nota_custo' => $data['nota_custo'],
            'nota_comunicacao' => $data['nota_comunicacao'],
            'nota_equipe' => $data['nota_equipe'],
            'nota_geral' => $notaGeral,
            'pontos_fortes' => $data['pontos_fortes'] ?? null,
            'pontos_melhoria' => $data['pontos_melhoria'] ?? null,
            'licoes_aprendidas' => $data['licoes_aprendidas'] ?? null,
            'recomendacoes' => $data['recomendacoes'] ?? null,
            'comentarios' => $data['comentarios'] ?? null
        ]);
    }

    public function delete($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = FALSE WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function getMedias($projetoId)
    {
        $sql = "SELECT 
                    COUNT(*) as total_avaliacoes,
                    AVG(nota_qualidade) as media_qualidade,
                    AVG(nota_prazo) as media_prazo,
                    AVG(nota_custo) as media_custo,
                    AVG(nota_comunicacao) as media_comunicacao,
                    AVG(nota_equipe) as media_equipe,
                    AVG(nota_geral) as media_geral
                FROM {$this->table}
                WHERE projeto_id = :projeto_id 
                AND ativo = TRUE";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['projeto_id' => $projetoId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
