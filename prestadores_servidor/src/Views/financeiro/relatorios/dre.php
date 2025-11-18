<?php require_once ROOT_PATH . '/src/Views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-chart-pie"></i> DRE - Demonstração do Resultado do Exercício</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro">Financeiro</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/?page=financeiro&action=relatorios">Relatórios</a></li>
                    <li class="breadcrumb-item active">DRE</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <button type="button" class="btn btn-success" onclick="exportarPDF()">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-filter"></i> Período</div>
        <div class="card-body">
            <form method="GET">
                <input type="hidden" name="page" value="financeiro">
                <input type="hidden" name="action" value="relatorios">
                <input type="hidden" name="report" value="dre">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label>Data Início *</label>
                        <input type="date" class="form-control" name="data_inicio" required
                               value="<?= $_GET['data_inicio'] ?? date('Y-m-01') ?>">
                    </div>
                    <div class="col-md-3">
                        <label>Data Fim *</label>
                        <input type="date" class="form-control" name="data_fim" required
                               value="<?= $_GET['data_fim'] ?? date('Y-m-t') ?>">
                    </div>
                    <div class="col-md-3">
                        <label>Comparar com:</label>
                        <select class="form-select" name="comparar">
                            <option value="">Sem comparação</option>
                            <option value="mes_anterior">Mês Anterior</option>
                            <option value="ano_anterior">Mesmo período ano anterior</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-chart-line"></i> Gerar DRE
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- DRE -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-file-invoice-dollar"></i>
            Demonstração do Resultado - Período: <?= date('d/m/Y', strtotime($_GET['data_inicio'] ?? date('Y-m-01'))) ?> a <?= date('d/m/Y', strtotime($_GET['data_fim'] ?? date('Y-m-t'))) ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th class="text-end">Valor (R$)</th>
                            <th class="text-end">% Receita</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Receitas -->
                        <tr class="table-success fw-bold">
                            <td colspan="3">RECEITA BRUTA</td>
                        </tr>
                        <?php 
                        $receita_bruta_total = 0;
                        if (!empty($dre['receitas'])): 
                            foreach ($dre['receitas'] as $rec):
                                $receita_bruta_total += $rec['valor'];
                        ?>
                        <tr>
                            <td class="ps-4"><?= htmlspecialchars($rec['categoria']) ?></td>
                            <td class="text-end">R$ <?= number_format($rec['valor'], 2, ',', '.') ?></td>
                            <td class="text-end">-</td>
                        </tr>
                        <?php 
                            endforeach;
                        endif;
                        ?>
                        <tr class="table-success fw-bold">
                            <td>TOTAL RECEITA BRUTA</td>
                            <td class="text-end">R$ <?= number_format($receita_bruta_total, 2, ',', '.') ?></td>
                            <td class="text-end">100,00%</td>
                        </tr>

                        <tr><td colspan="3">&nbsp;</td></tr>

                        <!-- Deduções -->
                        <tr class="table-danger fw-bold">
                            <td colspan="3">(-) DEDUÇÕES DA RECEITA</td>
                        </tr>
                        <?php 
                        $deducoes_total = 0;
                        if (!empty($dre['deducoes'])): 
                            foreach ($dre['deducoes'] as $ded):
                                $deducoes_total += $ded['valor'];
                        ?>
                        <tr>
                            <td class="ps-4"><?= htmlspecialchars($ded['categoria']) ?></td>
                            <td class="text-end text-danger">(R$ <?= number_format($ded['valor'], 2, ',', '.') ?>)</td>
                            <td class="text-end"><?= number_format(($ded['valor'] / $receita_bruta_total) * 100, 2, ',', '.') ?>%</td>
                        </tr>
                        <?php 
                            endforeach;
                        endif;
                        ?>

                        <?php $receita_liquida = $receita_bruta_total - $deducoes_total; ?>
                        <tr class="table-info fw-bold">
                            <td>= RECEITA LÍQUIDA</td>
                            <td class="text-end">R$ <?= number_format($receita_liquida, 2, ',', '.') ?></td>
                            <td class="text-end"><?= number_format(($receita_liquida / $receita_bruta_total) * 100, 2, ',', '.') ?>%</td>
                        </tr>

                        <tr><td colspan="3">&nbsp;</td></tr>

                        <!-- Custos -->
                        <tr class="table-warning fw-bold">
                            <td colspan="3">(-) CUSTOS OPERACIONAIS</td>
                        </tr>
                        <?php 
                        $custos_total = 0;
                        if (!empty($dre['custos'])): 
                            foreach ($dre['custos'] as $custo):
                                $custos_total += $custo['valor'];
                        ?>
                        <tr>
                            <td class="ps-4"><?= htmlspecialchars($custo['categoria']) ?></td>
                            <td class="text-end text-danger">(R$ <?= number_format($custo['valor'], 2, ',', '.') ?>)</td>
                            <td class="text-end"><?= number_format(($custo['valor'] / $receita_bruta_total) * 100, 2, ',', '.') ?>%</td>
                        </tr>
                        <?php 
                            endforeach;
                        endif;
                        ?>

                        <?php $resultado_bruto = $receita_liquida - $custos_total; ?>
                        <tr class="table-info fw-bold">
                            <td>= RESULTADO BRUTO</td>
                            <td class="text-end">R$ <?= number_format($resultado_bruto, 2, ',', '.') ?></td>
                            <td class="text-end"><?= number_format(($resultado_bruto / $receita_bruta_total) * 100, 2, ',', '.') ?>%</td>
                        </tr>

                        <tr><td colspan="3">&nbsp;</td></tr>

                        <!-- Despesas Operacionais -->
                        <tr class="table-danger fw-bold">
                            <td colspan="3">(-) DESPESAS OPERACIONAIS</td>
                        </tr>
                        <?php 
                        $despesas_total = 0;
                        if (!empty($dre['despesas'])): 
                            foreach ($dre['despesas'] as $desp):
                                $despesas_total += $desp['valor'];
                        ?>
                        <tr>
                            <td class="ps-4"><?= htmlspecialchars($desp['categoria']) ?></td>
                            <td class="text-end text-danger">(R$ <?= number_format($desp['valor'], 2, ',', '.') ?>)</td>
                            <td class="text-end"><?= number_format(($desp['valor'] / $receita_bruta_total) * 100, 2, ',', '.') ?>%</td>
                        </tr>
                        <?php 
                            endforeach;
                        endif;
                        ?>

                        <?php $resultado_operacional = $resultado_bruto - $despesas_total; ?>
                        <tr class="table-info fw-bold">
                            <td>= RESULTADO OPERACIONAL (EBIT)</td>
                            <td class="text-end">R$ <?= number_format($resultado_operacional, 2, ',', '.') ?></td>
                            <td class="text-end"><?= number_format(($resultado_operacional / $receita_bruta_total) * 100, 2, ',', '.') ?>%</td>
                        </tr>

                        <tr><td colspan="3">&nbsp;</td></tr>

                        <!-- Resultado Financeiro -->
                        <tr class="table-secondary fw-bold">
                            <td colspan="3">RESULTADO FINANCEIRO</td>
                        </tr>
                        <?php 
                        $receitas_financeiras = $dre['receitas_financeiras'] ?? 0;
                        $despesas_financeiras = $dre['despesas_financeiras'] ?? 0;
                        $resultado_financeiro = $receitas_financeiras - $despesas_financeiras;
                        ?>
                        <tr>
                            <td class="ps-4">Receitas Financeiras</td>
                            <td class="text-end text-success">R$ <?= number_format($receitas_financeiras, 2, ',', '.') ?></td>
                            <td class="text-end"><?= number_format(($receitas_financeiras / $receita_bruta_total) * 100, 2, ',', '.') ?>%</td>
                        </tr>
                        <tr>
                            <td class="ps-4">Despesas Financeiras</td>
                            <td class="text-end text-danger">(R$ <?= number_format($despesas_financeiras, 2, ',', '.') ?>)</td>
                            <td class="text-end"><?= number_format(($despesas_financeiras / $receita_bruta_total) * 100, 2, ',', '.') ?>%</td>
                        </tr>

                        <?php $resultado_liquido = $resultado_operacional + $resultado_financeiro; ?>
                        <tr class="table-<?= $resultado_liquido >= 0 ? 'success' : 'danger' ?> fw-bold fs-5">
                            <td>= RESULTADO LÍQUIDO DO PERÍODO</td>
                            <td class="text-end">R$ <?= number_format($resultado_liquido, 2, ',', '.') ?></td>
                            <td class="text-end"><?= number_format(($resultado_liquido / $receita_bruta_total) * 100, 2, ',', '.') ?>%</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Indicadores -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Margem Bruta</h6>
                            <h4><?= number_format(($resultado_bruto / $receita_bruta_total) * 100, 2, ',', '.') ?>%</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Margem Operacional</h6>
                            <h4><?= number_format(($resultado_operacional / $receita_bruta_total) * 100, 2, ',', '.') ?>%</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Margem Líquida</h6>
                            <h4><?= number_format(($resultado_liquido / $receita_bruta_total) * 100, 2, ',', '.') ?>%</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>EBITDA</h6>
                            <h4>R$ <?= number_format($resultado_operacional, 2, ',', '.') ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportarPDF() {
    alert('Função de exportação para PDF será implementada no backend.');
}
</script>

<style media="print">
    .btn, nav, .breadcrumb, .card-header .btn {
        display: none !important;
    }
</style>

<?php require_once ROOT_PATH . '/src/Views/layout/footer.php'; ?>
