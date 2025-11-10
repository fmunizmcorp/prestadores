<?php
/**
 * Executor Simples de Migration 016 
 * Adiciona colunas necessárias diretamente
 */

$host = 'localhost';
$dbname = 'u673902663_prestadores';
$username = 'u673902663_admin';
$password = ';>?I4dtn~2Ga';

header('Content-Type: text/plain; charset=utf-8');

echo "═══════════════════════════════════════════════════════════\n";
echo "  EXECUTANDO MIGRATION 016 - COLUNAS NOTAFISCAL\n";
echo "═══════════════════════════════════════════════════════════\n\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Conectado ao banco\n\n";
    
    // Array de colunas para adicionar
    $columnsToAdd = [
        "ADD COLUMN IF NOT EXISTS valor_produtos DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Valor de produtos' AFTER valor_bruto",
        "ADD COLUMN IF NOT EXISTS valor_servicos DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Valor de serviços' AFTER valor_produtos",
        "ADD COLUMN IF NOT EXISTS valor_total DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Valor total' AFTER valor_servicos",
        "ADD COLUMN IF NOT EXISTS valor_frete DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Valor de frete' AFTER valor_total",
        "ADD COLUMN IF NOT EXISTS valor_seguro DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Valor de seguro' AFTER valor_frete",
        "ADD COLUMN IF NOT EXISTS valor_outras_despesas DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Outras despesas' AFTER valor_seguro",
        "ADD COLUMN IF NOT EXISTS valor_base_calculo DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Base de cálculo' AFTER base_calculo_icms",
        "ADD COLUMN IF NOT EXISTS valor_icms_st DECIMAL(15,2) DEFAULT 0.00 COMMENT 'ICMS ST' AFTER valor_icms",
        "ADD COLUMN IF NOT EXISTS pdf_danfe VARCHAR(500) COMMENT 'PDF DANFE' AFTER pdf_path",
        "ADD COLUMN IF NOT EXISTS informacoes_adicionais TEXT COMMENT 'Informações adicionais' AFTER observacoes",
        "ADD COLUMN IF NOT EXISTS criado_por INT UNSIGNED COMMENT 'Criado por' AFTER emitido_por",
        "ADD COLUMN IF NOT EXISTS atualizado_por INT UNSIGNED COMMENT 'Atualizado por' AFTER criado_por",
        "ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL COMMENT 'Exclusão lógica' AFTER atualizado_em",
        "ADD COLUMN IF NOT EXISTS data_autorizacao DATETIME COMMENT 'Data autorização' AFTER protocolo_autorizacao",
        "ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Criado em (alias)' AFTER criado_em",
        "ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Atualizado em (alias)' AFTER created_at"
    ];
    
    $added = 0;
    $exists = 0;
    
    foreach ($columnsToAdd as $i => $alterStmt) {
        try {
            // Tentar adicionar usando IF NOT EXISTS (MariaDB 10.0.2+)
            $sql = "ALTER TABLE notas_fiscais $alterStmt";
            $pdo->exec($sql);
            $added++;
            echo "✓ Coluna " . ($i + 1) . "/16 adicionada\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') !== false || 
                strpos($e->getMessage(), 'already exists') !== false) {
                $exists++;
                echo "→ Coluna " . ($i + 1) . "/16 já existe\n";
            } else {
                echo "⚠ Erro na coluna " . ($i + 1) . ": " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n";
    echo "═══════════════════════════════════════════════════════════\n";
    echo "  RESULTADO:\n";
    echo "═══════════════════════════════════════════════════════════\n";
    echo "  Colunas adicionadas: $added\n";
    echo "  Já existiam: $exists\n";
    echo "  Total processado: 16\n";
    echo "\n✓ MIGRATION 016 COMPLETA!\n";
    echo "═══════════════════════════════════════════════════════════\n";
    
} catch (Exception $e) {
    echo "\n✗ ERRO: " . $e->getMessage() . "\n";
    exit(1);
}
