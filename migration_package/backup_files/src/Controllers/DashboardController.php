<?php

namespace App\Controllers;

use App\Database;
use PDO;

/**
 * DashboardController
 * Sprint 32 - Dashboard completo com cards e gráficos
 */
class DashboardController extends BaseController
{
    private $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Página principal do dashboard
     */
    public function index()
    {
        // Verificar autenticação
        $this->checkPermission();
        
        // Buscar dados para os cards
        $stats = $this->getStatistics();
        
        // Buscar dados para gráficos
        $chartData = $this->getChartData();
        
        // Buscar atividades recentes
        $recentActivities = $this->getRecentActivities();
        
        // Buscar alertas
        $alerts = $this->getAlerts();
        
        // Renderizar view
        $this->render('dashboard/index', [
            'pageTitle' => 'Dashboard',
            'stats' => $stats,
            'chartData' => $chartData,
            'recentActivities' => $recentActivities,
            'alerts' => $alerts
        ]);
    }
    
    /**
     * Obter estatísticas para os cards
     */
    private function getStatistics()
    {
        $stats = [];
        
        try {
            // Total de Empresas Tomadoras
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM empresas_tomadoras 
                WHERE ativo = 1
            ");
            $stats['empresas_tomadoras'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Total de Empresas Prestadoras
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM empresas_prestadoras 
                WHERE ativo = 1
            ");
            $stats['empresas_prestadoras'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Contratos Ativos
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM contratos 
                WHERE status = 'ativo'
            ");
            $stats['contratos_ativos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Contratos Totais
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM contratos
            ");
            $stats['contratos_total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Atestados Pendentes
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM atestados 
                WHERE status IN ('rascunho', 'emitido')
            ");
            $stats['atestados_pendentes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Faturas a Vencer (próximos 30 dias)
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM faturas 
                WHERE status NOT IN ('paga', 'cancelada')
                AND data_vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            ");
            $stats['faturas_a_vencer'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Valor Total de Contratos Ativos
            $stmt = $this->db->query("
                SELECT COALESCE(SUM(valor_total), 0) as total 
                FROM contratos 
                WHERE status = 'ativo'
            ");
            $stats['valor_contratos_ativos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Valor Executado
            $stmt = $this->db->query("
                SELECT COALESCE(SUM(valor_executado), 0) as total 
                FROM contratos 
                WHERE status = 'ativo'
            ");
            $stats['valor_executado'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Usuários Ativos
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM usuarios 
                WHERE ativo = 1
            ");
            $stats['usuarios_ativos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
        } catch (\PDOException $e) {
            error_log("Erro ao buscar estatísticas: " . $e->getMessage());
            // Retornar valores zerados em caso de erro
            $stats = [
                'empresas_tomadoras' => 0,
                'empresas_prestadoras' => 0,
                'contratos_ativos' => 0,
                'contratos_total' => 0,
                'atestados_pendentes' => 0,
                'faturas_a_vencer' => 0,
                'valor_contratos_ativos' => 0,
                'valor_executado' => 0,
                'usuarios_ativos' => 0
            ];
        }
        
        return $stats;
    }
    
    /**
     * Obter dados para gráficos
     */
    private function getChartData()
    {
        $chartData = [];
        
        try {
            // Gráfico: Contratos por Status
            $stmt = $this->db->query("
                SELECT 
                    status,
                    COUNT(*) as total
                FROM contratos
                GROUP BY status
                ORDER BY total DESC
            ");
            $chartData['contratos_por_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Gráfico: Contratos por Mês (últimos 12 meses)
            $stmt = $this->db->query("
                SELECT 
                    DATE_FORMAT(data_inicio, '%Y-%m') as mes,
                    COUNT(*) as total
                FROM contratos
                WHERE data_inicio >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY mes
                ORDER BY mes ASC
            ");
            $chartData['contratos_por_mes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Gráfico: Faturamento Mensal (últimos 6 meses)
            $stmt = $this->db->query("
                SELECT 
                    DATE_FORMAT(a.created_at, '%Y-%m') as mes,
                    COALESCE(SUM(a.valor_liquido), 0) as valor
                FROM atestados a
                WHERE a.created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                AND a.status IN ('aprovado', 'pago')
                GROUP BY mes
                ORDER BY mes ASC
            ");
            $chartData['faturamento_mensal'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Gráfico: Top 5 Empresas Tomadoras por Valor de Contratos
            $stmt = $this->db->query("
                SELECT 
                    et.nome_fantasia,
                    COALESCE(SUM(c.valor_total), 0) as valor_total
                FROM empresas_tomadoras et
                LEFT JOIN contratos c ON c.empresa_tomadora_id = et.id
                WHERE et.ativo = 1
                GROUP BY et.id, et.nome_fantasia
                ORDER BY valor_total DESC
                LIMIT 5
            ");
            $chartData['top_empresas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            error_log("Erro ao buscar dados de gráficos: " . $e->getMessage());
            $chartData = [
                'contratos_por_status' => [],
                'contratos_por_mes' => [],
                'faturamento_mensal' => [],
                'top_empresas' => []
            ];
        }
        
        return $chartData;
    }
    
    /**
     * Obter atividades recentes
     */
    private function getRecentActivities()
    {
        try {
            // Buscar últimas 10 atividades (contratos, atestados, faturas)
            $activities = [];
            
            // Contratos recentes
            $stmt = $this->db->query("
                SELECT 
                    'contrato' as tipo,
                    c.numero_contrato as titulo,
                    et.nome_fantasia as empresa,
                    c.created_at as data,
                    c.status
                FROM contratos c
                LEFT JOIN empresas_tomadoras et ON et.id = c.empresa_tomadora_id
                ORDER BY c.created_at DESC
                LIMIT 5
            ");
            $activities = array_merge($activities, $stmt->fetchAll(PDO::FETCH_ASSOC));
            
            // Atestados recentes
            $stmt = $this->db->query("
                SELECT 
                    'atestado' as tipo,
                    a.numero as titulo,
                    c.numero_contrato as empresa,
                    a.created_at as data,
                    a.status
                FROM atestados a
                LEFT JOIN contratos c ON c.id = a.contrato_id
                ORDER BY a.created_at DESC
                LIMIT 5
            ");
            $activities = array_merge($activities, $stmt->fetchAll(PDO::FETCH_ASSOC));
            
            // Ordenar por data
            usort($activities, function($a, $b) {
                return strtotime($b['data']) - strtotime($a['data']);
            });
            
            return array_slice($activities, 0, 10);
            
        } catch (\PDOException $e) {
            error_log("Erro ao buscar atividades recentes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter alertas e notificações
     */
    private function getAlerts()
    {
        $alerts = [];
        
        try {
            // Contratos vencendo (próximos 30 dias)
            $stmt = $this->db->query("
                SELECT 
                    c.numero_contrato,
                    c.data_fim,
                    et.nome_fantasia as empresa,
                    DATEDIFF(c.data_fim, CURDATE()) as dias_restantes
                FROM contratos c
                LEFT JOIN empresas_tomadoras et ON et.id = c.empresa_tomadora_id
                WHERE c.status = 'ativo'
                AND c.data_fim IS NOT NULL
                AND c.data_fim BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                ORDER BY c.data_fim ASC
                LIMIT 5
            ");
            $contratos_vencendo = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($contratos_vencendo as $contrato) {
                $alerts[] = [
                    'tipo' => 'warning',
                    'icone' => '⚠️',
                    'titulo' => 'Contrato vencendo',
                    'mensagem' => "Contrato {$contrato['numero_contrato']} ({$contrato['empresa']}) vence em {$contrato['dias_restantes']} dias",
                    'data' => $contrato['data_fim']
                ];
            }
            
            // Faturas vencidas
            $stmt = $this->db->query("
                SELECT 
                    f.numero_nf,
                    f.data_vencimento,
                    a.numero as atestado,
                    DATEDIFF(CURDATE(), f.data_vencimento) as dias_atraso
                FROM faturas f
                LEFT JOIN atestados a ON a.id = f.atestado_id
                WHERE f.status NOT IN ('paga', 'cancelada')
                AND f.data_vencimento < CURDATE()
                ORDER BY f.data_vencimento ASC
                LIMIT 5
            ");
            $faturas_vencidas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($faturas_vencidas as $fatura) {
                $alerts[] = [
                    'tipo' => 'danger',
                    'icone' => '🚨',
                    'titulo' => 'Fatura vencida',
                    'mensagem' => "NF {$fatura['numero_nf']} (Atestado {$fatura['atestado']}) vencida há {$fatura['dias_atraso']} dias",
                    'data' => $fatura['data_vencimento']
                ];
            }
            
            // Atestados pendentes de aprovação
            $stmt = $this->db->query("
                SELECT 
                    a.numero,
                    c.numero_contrato,
                    a.mes_referencia,
                    a.ano_referencia
                FROM atestados a
                LEFT JOIN contratos c ON c.id = a.contrato_id
                WHERE a.status = 'emitido'
                ORDER BY a.created_at ASC
                LIMIT 3
            ");
            $atestados_pendentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($atestados_pendentes as $atestado) {
                $alerts[] = [
                    'tipo' => 'info',
                    'icone' => 'ℹ️',
                    'titulo' => 'Atestado pendente',
                    'mensagem' => "Atestado {$atestado['numero']} (Contrato {$atestado['numero_contrato']}) aguardando aprovação",
                    'data' => null
                ];
            }
            
        } catch (\PDOException $e) {
            error_log("Erro ao buscar alertas: " . $e->getMessage());
        }
        
        return $alerts;
    }
    
    /**
     * API: Retornar estatísticas em JSON
     */
    public function api_stats()
    {
        $this->checkPermission();
        $stats = $this->getStatistics();
        $this->jsonSuccess($stats);
    }
    
    /**
     * API: Retornar dados de gráficos em JSON
     */
    public function api_charts()
    {
        $this->checkPermission();
        $chartData = $this->getChartData();
        $this->jsonSuccess($chartData);
    }
}
