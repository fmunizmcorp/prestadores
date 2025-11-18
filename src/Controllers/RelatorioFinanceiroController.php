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
        try {
            $mesAtual = date('Y-m');
            $mesAnterior = date('Y-m', strtotime('-1 month'));
            
            $filtros = [
                'data_inicio' => $_GET['data_inicio'] ?? $mesAtual . '-01',
                'data_fim' => $_GET['data_fim'] ?? date('Y-m-t')
            ];
            
            // SPRINT 75 - Bug #30 & #31: Adicionar tratamento de erros
            $statsPagamentos = $this->pagamentoModel->getEstatisticas($filtros);
            if ($statsPagamentos === false || $statsPagamentos === null) {
                throw new \Exception('Erro ao buscar estatísticas de pagamentos');
            }
            
            $statsCustos = $this->custoModel->getEstatisticas($filtros);
            if ($statsCustos === false || $statsCustos === null) {
                throw new \Exception('Erro ao buscar estatísticas de custos');
            }
            
            $pagamentosPorForma = $this->pagamentoModel->getPorFormaPagamento($filtros);
            // getPorFormaPagamento pode retornar array vazio, não é erro
            if ($pagamentosPorForma === false || $pagamentosPorForma === null) {
                $pagamentosPorForma = [];
            }
            
            $receitas = $statsPagamentos['valor_total'] ?? 0;
            $despesas = $statsCustos['valor_total_geral'] ?? 0;
            $saldo = $receitas - $despesas;
            
            require __DIR__ . '/../Views/relatorios_financeiros/index.php';
            
        } catch (\Exception $e) {
            // SPRINT 75 - Bug #30 & #31: Capturar e logar erros
            error_log("RelatorioFinanceiroController error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // Mostrar mensagem amigável ao usuário
            $_SESSION['error'] = 'Erro ao carregar relatório financeiro. Por favor, tente novamente.';
            $_SESSION['error_details'] = $e->getMessage(); // Para debug
            
            // Redirecionar para dashboard ou mostrar view de erro
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=dashboard');
            exit;
        }
    }
}
