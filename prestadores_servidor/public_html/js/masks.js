/**
 * Masks.js - Máscaras de entrada para formulários
 * Utiliza InputMask para aplicar máscaras em campos
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        applyMasks();
    });
    
    /**
     * Aplicar todas as máscaras
     */
    function applyMasks() {
        // CNPJ
        $('.mask-cnpj').inputmask('99.999.999/9999-99', {
            clearMaskOnLostFocus: false,
            removeMaskOnSubmit: false
        });
        
        // CPF
        $('.mask-cpf').inputmask('999.999.999-99', {
            clearMaskOnLostFocus: false,
            removeMaskOnSubmit: false
        });
        
        // CPF ou CNPJ
        $('.mask-cpf-cnpj').inputmask({
            mask: ['999.999.999-99', '99.999.999/9999-99'],
            clearMaskOnLostFocus: false,
            removeMaskOnSubmit: false
        });
        
        // Telefone (fixo)
        $('.mask-telefone').inputmask('(99) 9999-9999', {
            clearMaskOnLostFocus: false,
            removeMaskOnSubmit: false
        });
        
        // Celular
        $('.mask-celular').inputmask('(99) 99999-9999', {
            clearMaskOnLostFocus: false,
            removeMaskOnSubmit: false
        });
        
        // Telefone ou Celular
        $('.mask-phone').inputmask({
            mask: ['(99) 9999-9999', '(99) 99999-9999'],
            clearMaskOnLostFocus: false,
            removeMaskOnSubmit: false
        });
        
        // CEP
        $('.mask-cep').inputmask('99999-999', {
            clearMaskOnLostFocus: false,
            removeMaskOnSubmit: false,
            onComplete: function() {
                const $input = $(this);
                const cep = $input.val().replace(/\D/g, '');
                
                // Auto-buscar CEP se houver campos relacionados
                if ($('[name="logradouro"], [name="bairro"], [name="cidade"], [name="estado"]').length) {
                    buscarCepAutomatico(cep);
                }
            }
        });
        
        // Data (DD/MM/YYYY)
        $('.mask-date').inputmask('99/99/9999', {
            placeholder: 'dd/mm/aaaa',
            clearMaskOnLostFocus: false,
            removeMaskOnSubmit: false
        });
        
        // Hora (HH:MM)
        $('.mask-time').inputmask('99:99', {
            placeholder: 'hh:mm',
            clearMaskOnLostFocus: false,
            removeMaskOnSubmit: false
        });
        
        // Data e Hora (DD/MM/YYYY HH:MM)
        $('.mask-datetime').inputmask('99/99/9999 99:99', {
            placeholder: 'dd/mm/aaaa hh:mm',
            clearMaskOnLostFocus: false,
            removeMaskOnSubmit: false
        });
        
        // Moeda (R$)
        $('.mask-money').inputmask('currency', {
            prefix: 'R$ ',
            radixPoint: ',',
            groupSeparator: '.',
            autoGroup: true,
            digits: 2,
            digitsOptional: false,
            clearMaskOnLostFocus: false,
            removeMaskOnSubmit: false
        });
        
        // Número decimal (sem prefixo)
        $('.mask-decimal').inputmask('decimal', {
            radixPoint: ',',
            groupSeparator: '.',
            autoGroup: true,
            digits: 2,
            digitsOptional: false,
            clearMaskOnLostFocus: false,
            removeMaskOnSubmit: false
        });
        
        // Porcentagem
        $('.mask-percent').inputmask('decimal', {
            suffix: ' %',
            radixPoint: ',',
            groupSeparator: '.',
            autoGroup: true,
            digits: 2,
            digitsOptional: false,
            clearMaskOnLostFocus: false,
            removeMaskOnSubmit: false,
            min: 0,
            max: 100
        });
        
        // Número inteiro
        $('.mask-integer').inputmask('integer', {
            groupSeparator: '.',
            autoGroup: true,
            clearMaskOnLostFocus: false,
            removeMaskOnSubmit: false
        });
        
        // Placa de veículo
        $('.mask-placa').inputmask('AAA-9*99', {
            clearMaskOnLostFocus: false,
            removeMaskOnSubmit: false,
            casing: 'upper'
        });
        
        // Número de cartão de crédito
        $('.mask-credit-card').inputmask('9999 9999 9999 9999', {
            clearMaskOnLostFocus: false,
            removeMaskOnSubmit: false
        });
        
        // CVV do cartão
        $('.mask-cvv').inputmask('999[9]', {
            clearMaskOnLostFocus: false,
            removeMaskOnSubmit: false
        });
        
        // Código de barras
        $('.mask-barcode').inputmask('99999.99999 99999.999999 99999.999999 9 99999999999999', {
            clearMaskOnLostFocus: false,
            removeMaskOnSubmit: false
        });
    }
    
    /**
     * Buscar CEP automaticamente
     */
    function buscarCepAutomatico(cep) {
        if (cep.length !== 8) return;
        
        const $logradouro = $('[name="logradouro"]');
        const $bairro = $('[name="bairro"]');
        const $cidade = $('[name="cidade"]');
        const $estado = $('[name="estado"]');
        
        // Mostrar loading
        $logradouro.val('Buscando...');
        $bairro.val('Buscando...');
        $cidade.val('Buscando...');
        
        // Buscar CEP
        $.ajax({
            url: `https://viacep.com.br/ws/${cep}/json/`,
            dataType: 'json',
            success: function(data) {
                if (data.erro) {
                    ClinfecApp.toast('CEP não encontrado', 'error');
                    limparCamposEndereco();
                } else {
                    $logradouro.val(data.logradouro || '');
                    $bairro.val(data.bairro || '');
                    $cidade.val(data.localidade || '');
                    $estado.val(data.uf || '').trigger('change');
                    
                    // Focar no campo número
                    $('[name="numero"]').focus();
                    
                    ClinfecApp.toast('CEP encontrado!', 'success');
                }
            },
            error: function() {
                ClinfecApp.toast('Erro ao buscar CEP', 'error');
                limparCamposEndereco();
            }
        });
    }
    
    /**
     * Limpar campos de endereço
     */
    function limparCamposEndereco() {
        $('[name="logradouro"]').val('');
        $('[name="bairro"]').val('');
        $('[name="cidade"]').val('');
        $('[name="estado"]').val('').trigger('change');
    }
    
    /**
     * Validação de CNPJ em tempo real
     */
    $('.mask-cnpj').on('blur', function() {
        const cnpj = $(this).val().replace(/\D/g, '');
        if (cnpj.length === 14 && !ClinfecApp.validateCnpj(cnpj)) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">CNPJ inválido</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });
    
    /**
     * Validação de CPF em tempo real
     */
    $('.mask-cpf').on('blur', function() {
        const cpf = $(this).val().replace(/\D/g, '');
        if (cpf.length === 11 && !ClinfecApp.validateCpf(cpf)) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">CPF inválido</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });
    
    /**
     * Botão para buscar CEP manualmente
     */
    $(document).on('click', '.btn-buscar-cep', function() {
        const $input = $(this).closest('.input-group').find('.mask-cep');
        const cep = $input.val().replace(/\D/g, '');
        
        if (cep.length === 8) {
            buscarCepAutomatico(cep);
        } else {
            ClinfecApp.toast('CEP inválido', 'error');
        }
    });
    
})(jQuery);
