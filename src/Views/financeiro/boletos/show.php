<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleto Bancário - <?= htmlspecialchars($boleto['nosso_numero'] ?? '') ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            padding: 20px;
        }
        
        .boleto-container {
            width: 666px;
            margin: 0 auto;
            border: 1px solid #000;
        }
        
        .boleto-header {
            display: flex;
            border-bottom: 2px dashed #000;
            padding: 10px;
        }
        
        .banco-logo {
            width: 150px;
            text-align: center;
            border-right: 2px solid #000;
            padding-right: 10px;
        }
        
        .banco-numero {
            font-size: 18px;
            font-weight: bold;
        }
        
        .codigo-banco {
            flex: 1;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            padding: 0 10px;
        }
        
        .linha-digitavel {
            flex: 2;
            text-align: right;
            font-size: 12px;
            font-weight: bold;
            padding-left: 10px;
        }
        
        .boleto-info {
            padding: 10px;
        }
        
        .info-row {
            display: flex;
            border-bottom: 1px solid #000;
            min-height: 25px;
        }
        
        .info-cell {
            padding: 5px;
            border-right: 1px solid #000;
        }
        
        .info-cell:last-child {
            border-right: none;
        }
        
        .info-label {
            font-size: 8px;
            color: #666;
        }
        
        .info-value {
            font-size: 11px;
            font-weight: bold;
            margin-top: 2px;
        }
        
        .codigo-barras {
            text-align: center;
            padding: 20px 10px;
            font-family: 'Courier New', monospace;
            font-size: 24px;
            letter-spacing: 2px;
            border-bottom: 2px dashed #000;
        }
        
        .instrucoes {
            padding: 15px;
            border-bottom: 2px dashed #000;
        }
        
        .instrucoes-titulo {
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .instrucoes-texto {
            line-height: 1.6;
            white-space: pre-line;
        }
        
        .recibo {
            padding: 10px;
            background-color: #f5f5f5;
        }
        
        .recibo-titulo {
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
            font-size: 12px;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .no-print {
                display: none;
            }
            
            .boleto-container {
                border: none;
                page-break-inside: avoid;
            }
        }
        
        .btn-imprimir {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        
        .btn-imprimir:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <button class="btn-imprimir no-print" onclick="window.print()">
        🖨️ Imprimir Boleto
    </button>

    <div class="boleto-container">
        <!-- Header do Boleto -->
        <div class="boleto-header">
            <div class="banco-logo">
                <div class="banco-numero"><?= htmlspecialchars($boleto['banco_codigo']) ?></div>
                <div style="font-size: 8px;">
                    <?php
                    $bancoNome = match($boleto['banco_codigo']) {
                        '001' => 'Banco do Brasil',
                        '237' => 'Bradesco',
                        '341' => 'Itaú',
                        '033' => 'Santander',
                        '104' => 'Caixa Econômica',
                        default => 'Banco'
                    };
                    echo $bancoNome;
                    ?>
                </div>
            </div>
            <div class="codigo-banco">
                <?= htmlspecialchars($boleto['banco_codigo']) ?>-<?= substr($boleto['banco_codigo'], -1) ?>
            </div>
            <div class="linha-digitavel">
                <?= htmlspecialchars($boleto['linha_digitavel'] ?? '') ?>
            </div>
        </div>

        <!-- Informações do Boleto -->
        <div class="boleto-info">
            <!-- Linha 1 -->
            <div class="info-row">
                <div class="info-cell" style="width: 60%;">
                    <div class="info-label">Local de Pagamento</div>
                    <div class="info-value">PAGÁVEL EM QUALQUER BANCO ATÉ O VENCIMENTO</div>
                </div>
                <div class="info-cell" style="width: 40%;">
                    <div class="info-label">Vencimento</div>
                    <div class="info-value"><?= date('d/m/Y', strtotime($boleto['data_vencimento'])) ?></div>
                </div>
            </div>

            <!-- Linha 2 -->
            <div class="info-row">
                <div class="info-cell" style="width: 60%;">
                    <div class="info-label">Beneficiário</div>
                    <div class="info-value"><?= htmlspecialchars($boleto['beneficiario_nome'] ?? '') ?></div>
                    <div class="info-value" style="font-size: 9px;">
                        CNPJ: <?= htmlspecialchars($boleto['beneficiario_cnpj'] ?? '') ?>
                    </div>
                </div>
                <div class="info-cell" style="width: 40%;">
                    <div class="info-label">Agência/Código Beneficiário</div>
                    <div class="info-value">
                        <?= htmlspecialchars($boleto['agencia'] ?? '0000') ?>/<?= htmlspecialchars($boleto['conta'] ?? '00000-0') ?>
                    </div>
                </div>
            </div>

            <!-- Linha 3 -->
            <div class="info-row">
                <div class="info-cell" style="width: 20%;">
                    <div class="info-label">Data do Documento</div>
                    <div class="info-value"><?= date('d/m/Y', strtotime($boleto['data_emissao'])) ?></div>
                </div>
                <div class="info-cell" style="width: 20%;">
                    <div class="info-label">Nº do Documento</div>
                    <div class="info-value"><?= htmlspecialchars($boleto['numero_documento'] ?? '') ?></div>
                </div>
                <div class="info-cell" style="width: 20%;">
                    <div class="info-label">Espécie Doc.</div>
                    <div class="info-value">DM</div>
                </div>
                <div class="info-cell" style="width: 20%;">
                    <div class="info-label">Aceite</div>
                    <div class="info-value">N</div>
                </div>
                <div class="info-cell" style="width: 20%;">
                    <div class="info-label">Data Processamento</div>
                    <div class="info-value"><?= date('d/m/Y') ?></div>
                </div>
            </div>

            <!-- Linha 4 -->
            <div class="info-row">
                <div class="info-cell" style="width: 20%;">
                    <div class="info-label">Uso do Banco</div>
                    <div class="info-value">-</div>
                </div>
                <div class="info-cell" style="width: 15%;">
                    <div class="info-label">Carteira</div>
                    <div class="info-value"><?= htmlspecialchars($boleto['carteira'] ?? '18') ?></div>
                </div>
                <div class="info-cell" style="width: 15%;">
                    <div class="info-label">Espécie</div>
                    <div class="info-value">R$</div>
                </div>
                <div class="info-cell" style="width: 25%;">
                    <div class="info-label">Quantidade</div>
                    <div class="info-value">-</div>
                </div>
                <div class="info-cell" style="width: 25%;">
                    <div class="info-label">Valor</div>
                    <div class="info-value">-</div>
                </div>
            </div>

            <!-- Linha 5 -->
            <div class="info-row">
                <div class="info-cell" style="width: 60%;">
                    <div class="info-label">Instruções (Texto de responsabilidade do beneficiário)</div>
                    <div class="info-value" style="font-size: 9px; white-space: pre-line;">
<?= htmlspecialchars($boleto['instrucoes'] ?? 'Sr. Caixa, não receber após vencimento.') ?>
                    </div>
                </div>
                <div class="info-cell" style="width: 40%;">
                    <div class="info-label">(-) Desconto / Abatimento</div>
                    <div class="info-value">R$ 0,00</div>
                    <div class="info-label" style="margin-top: 5px;">(-) Outras Deduções</div>
                    <div class="info-value">R$ 0,00</div>
                    <div class="info-label" style="margin-top: 5px;">(+) Mora / Multa</div>
                    <div class="info-value">
                        <?php
                        $multa = ($boleto['multa_atraso'] ?? 0) * ($boleto['valor_original'] ?? 0) / 100;
                        echo 'R$ ' . number_format($multa, 2, ',', '.');
                        ?>
                    </div>
                    <div class="info-label" style="margin-top: 5px;">(+) Outros Acréscimos</div>
                    <div class="info-value">R$ 0,00</div>
                    <div class="info-label" style="margin-top: 5px;">(=) Valor Cobrado</div>
                    <div class="info-value">-</div>
                </div>
            </div>

            <!-- Linha 6 - Pagador -->
            <div class="info-row">
                <div class="info-cell" style="width: 100%;">
                    <div class="info-label">Pagador</div>
                    <div class="info-value"><?= htmlspecialchars($boleto['pagador_nome'] ?? '') ?></div>
                    <div class="info-value" style="font-size: 9px;">
                        <?= htmlspecialchars($boleto['pagador_endereco'] ?? '') ?>
                    </div>
                    <div class="info-value" style="font-size: 9px;">
                        CPF/CNPJ: <?= htmlspecialchars($boleto['pagador_documento'] ?? '') ?>
                    </div>
                </div>
            </div>

            <!-- Linha 7 -->
            <div class="info-row">
                <div class="info-cell" style="width: 40%;">
                    <div class="info-label">Sacador/Avalista</div>
                    <div class="info-value">-</div>
                </div>
                <div class="info-cell" style="width: 30%;">
                    <div class="info-label">Nosso Número</div>
                    <div class="info-value"><?= htmlspecialchars($boleto['nosso_numero'] ?? '') ?></div>
                </div>
                <div class="info-cell" style="width: 30%;">
                    <div class="info-label">(=) Valor do Documento</div>
                    <div class="info-value" style="font-size: 14px;">
                        R$ <?= number_format($boleto['valor_original'] ?? 0, 2, ',', '.') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Código de Barras -->
        <div class="codigo-barras">
            <?= htmlspecialchars($boleto['codigo_barras'] ?? '') ?>
        </div>

        <!-- Instruções para o Caixa -->
        <div class="instrucoes">
            <div class="instrucoes-titulo">INSTRUÇÕES PARA O CAIXA</div>
            <div class="instrucoes-texto">
                - Este boleto pode ser pago em qualquer agência bancária até a data de vencimento
                - Após o vencimento, cobrar juros de <?= number_format($boleto['juros_ao_dia'] ?? 0, 3, ',', '.') ?>% ao dia
                - Multa por atraso de <?= number_format($boleto['multa_atraso'] ?? 0, 2, ',', '.') ?>%
                - Não receber após <?= ($boleto['dias_protesto'] ?? 0) > 0 ? ($boleto['dias_protesto'] . ' dias de atraso') : 'vencimento' ?>
            </div>
        </div>

        <!-- Recibo do Pagador -->
        <div class="recibo">
            <div class="recibo-titulo">RECIBO DO PAGADOR</div>
            <div style="display: flex; justify-content: space-between;">
                <div>
                    <strong>Beneficiário:</strong> <?= htmlspecialchars($boleto['beneficiario_nome'] ?? '') ?><br>
                    <strong>Pagador:</strong> <?= htmlspecialchars($boleto['pagador_nome'] ?? '') ?><br>
                    <strong>Vencimento:</strong> <?= date('d/m/Y', strtotime($boleto['data_vencimento'])) ?>
                </div>
                <div style="text-align: right;">
                    <strong>Valor:</strong><br>
                    <span style="font-size: 14px; font-weight: bold;">
                        R$ <?= number_format($boleto['valor_original'] ?? 0, 2, ',', '.') ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-imprimir ao carregar (opcional)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>
</html>
