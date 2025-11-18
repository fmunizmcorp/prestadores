<?php

namespace App\Controllers;

use App\Models\Pagamento;
use App\Models\Custo;

/**
 * RelatorioFinanceiroController
 * Sprint 70.3 - Relatórios Financeiros
 */
class RelatorioFinanceiroController {
    private $pagamentoModel;
    private $custoModel;
    
    public function __construct() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /?page=login');
            exit;
        }
        
        $this->pagamentoModel = new Pagamento();
        $this->custoModel = new Custo();
    }
    
    public function index() {
        $mesAtual = date('Y-m');
        $mesAnterior = date('Y-m', strtotime('-1 month'));
        
        $filtros = [
            'data_inicio' => $_GET['data_inicio'] ?? $mesAtual . '-01',
            'data_fim' => $_GET['data_fim'] ?? date('Y-m-t')
        ];
        
        $statsPagamentos = $this->pagamentoModel->getEstatisticas($filtros);
        $statsCustos = $this->custoModel->getEstatisticas($filtros);
        
        $pagamentosPorForma = $this->pagamentoModel->getPorFormaPagamento($filtros);
        
        $receitas = $statsPagamentos['valor_total'] ?? 0;
        $despesas = $statsCustos['valor_total_geral'] ?? 0;
        $saldo = $receitas - $despesas;
        
        require __DIR__ . '/../Views/relatorios_financeiros/index.php';
    }
}
