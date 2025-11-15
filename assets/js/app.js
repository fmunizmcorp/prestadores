/**
 * App.js - JavaScript principal do Sistema Clinfec
 * Funções gerais e utilitárias
 */

(function($) {
    'use strict';
    
    // Inicialização global
    $(document).ready(function() {
        initGlobalFeatures();
    });
    
    /**
     * Inicializar recursos globais
     */
    function initGlobalFeatures() {
        // Configurar AJAX global
        setupAjax();
        
        // Inicializar tooltips e popovers (já feito no footer, mas mantém)
        initBootstrapComponents();
        
        // Configurar formulários
        setupForms();
        
        // Configurar tabelas
        setupTables();
        
        // Configurar botões de ação
        setupActionButtons();
    }
    
    /**
     * Configurar AJAX
     */
    function setupAjax() {
        // CSRF Token em todas as requisições AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || 
                                $('input[name="csrf_token"]').val()
            }
        });
        
        // Tratamento de erros global
        $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
            if (jqxhr.status === 401) {
                window.location.href = '/login';
            } else if (jqxhr.status === 403) {
                Swal.fire({
                    icon: 'error',
                    title: 'Acesso Negado',
                    text: 'Você não tem permissão para executar esta ação.'
                });
            } else if (jqxhr.status >= 500) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro no Servidor',
                    text: 'Ocorreu um erro ao processar sua solicitação. Tente novamente.'
                });
            }
        });
    }
    
    /**
     * Inicializar componentes Bootstrap
     */
    function initBootstrapComponents() {
        // Tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        // Popovers
        $('[data-bs-toggle="popover"]').popover();
    }
    
    /**
     * Configurar formulários
     */
    function setupForms() {
        // Validação HTML5
        $('form').on('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            $(this).addClass('was-validated');
        });
        
        // Prevenir duplo submit
        $('form').on('submit', function() {
            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            
            $submitBtn.prop('disabled', true);
            $submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Processando...');
            
            // Reabilitar após 5 segundos (caso haja erro de validação)
            setTimeout(function() {
                $submitBtn.prop('disabled', false);
                $submitBtn.html($submitBtn.data('original-text') || 'Salvar');
            }, 5000);
        });
        
        // Auto-uppercase em campos específicos
        $('.text-uppercase-auto').on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });
        
        // Limpar espaços em branco
        $('input[type="text"], input[type="email"]').on('blur', function() {
            $(this).val($.trim($(this).val()));
        });
    }
    
    /**
     * Configurar tabelas
     */
    function setupTables() {
        // DataTables padrão (se houver classe .datatable)
        if ($.fn.DataTable) {
            $('.datatable').each(function() {
                $(this).DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                    },
                    pageLength: 25,
                    responsive: true,
                    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p">>'
                });
            });
        }
        
        // Checkbox "Selecionar Todos"
        $('.table-check-all').on('change', function() {
            const checked = $(this).prop('checked');
            $(this).closest('table').find('.table-check-item').prop('checked', checked);
        });
    }
    
    /**
     * Configurar botões de ação
     */
    function setupActionButtons() {
        // Botão voltar
        $('.btn-back').on('click', function(e) {
            e.preventDefault();
            window.history.back();
        });
        
        // Botão imprimir
        $('.btn-print').on('click', function(e) {
            e.preventDefault();
            window.print();
        });
        
        // Botão exportar (exemplo genérico)
        $('.btn-export').on('click', function(e) {
            e.preventDefault();
            const format = $(this).data('format') || 'pdf';
            const url = $(this).data('url');
            
            if (url) {
                window.open(url + '?format=' + format, '_blank');
            }
        });
    }
    
    /**
     * Funções utilitárias globais
     */
    window.ClinfecApp = {
        /**
         * Formatar CNPJ
         */
        formatCnpj: function(cnpj) {
            cnpj = cnpj.replace(/\D/g, '');
            return cnpj.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
        },
        
        /**
         * Formatar CPF
         */
        formatCpf: function(cpf) {
            cpf = cpf.replace(/\D/g, '');
            return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
        },
        
        /**
         * Formatar telefone
         */
        formatTelefone: function(telefone) {
            telefone = telefone.replace(/\D/g, '');
            if (telefone.length === 11) {
                return telefone.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (telefone.length === 10) {
                return telefone.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            }
            return telefone;
        },
        
        /**
         * Formatar CEP
         */
        formatCep: function(cep) {
            cep = cep.replace(/\D/g, '');
            return cep.replace(/(\d{5})(\d{3})/, '$1-$2');
        },
        
        /**
         * Formatar moeda
         */
        formatMoney: function(value) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(value);
        },
        
        /**
         * Parsear moeda para float
         */
        parseMoney: function(value) {
            if (typeof value === 'number') return value;
            return parseFloat(value.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
        },
        
        /**
         * Formatar data
         */
        formatDate: function(date, format = 'dd/mm/yyyy') {
            const d = new Date(date);
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();
            
            if (format === 'dd/mm/yyyy') {
                return `${day}/${month}/${year}`;
            } else if (format === 'yyyy-mm-dd') {
                return `${year}-${month}-${day}`;
            }
            return date;
        },
        
        /**
         * Validar CNPJ
         */
        validateCnpj: function(cnpj) {
            cnpj = cnpj.replace(/\D/g, '');
            
            if (cnpj.length !== 14) return false;
            if (/^(\d)\1{13}$/.test(cnpj)) return false;
            
            let size = cnpj.length - 2;
            let numbers = cnpj.substring(0, size);
            const digits = cnpj.substring(size);
            let sum = 0;
            let pos = size - 7;
            
            for (let i = size; i >= 1; i--) {
                sum += numbers.charAt(size - i) * pos--;
                if (pos < 2) pos = 9;
            }
            
            let result = sum % 11 < 2 ? 0 : 11 - sum % 11;
            if (result != digits.charAt(0)) return false;
            
            size = size + 1;
            numbers = cnpj.substring(0, size);
            sum = 0;
            pos = size - 7;
            
            for (let i = size; i >= 1; i--) {
                sum += numbers.charAt(size - i) * pos--;
                if (pos < 2) pos = 9;
            }
            
            result = sum % 11 < 2 ? 0 : 11 - sum % 11;
            return result == digits.charAt(1);
        },
        
        /**
         * Validar CPF
         */
        validateCpf: function(cpf) {
            cpf = cpf.replace(/\D/g, '');
            
            if (cpf.length !== 11) return false;
            if (/^(\d)\1{10}$/.test(cpf)) return false;
            
            let sum = 0;
            for (let i = 0; i < 9; i++) {
                sum += parseInt(cpf.charAt(i)) * (10 - i);
            }
            let digit = 11 - (sum % 11);
            if (digit >= 10) digit = 0;
            if (digit != cpf.charAt(9)) return false;
            
            sum = 0;
            for (let i = 0; i < 10; i++) {
                sum += parseInt(cpf.charAt(i)) * (11 - i);
            }
            digit = 11 - (sum % 11);
            if (digit >= 10) digit = 0;
            return digit == cpf.charAt(10);
        },
        
        /**
         * Buscar CEP via ViaCEP
         */
        buscarCep: function(cep, callback) {
            cep = cep.replace(/\D/g, '');
            
            if (cep.length !== 8) {
                callback({ success: false, message: 'CEP inválido' });
                return;
            }
            
            $.ajax({
                url: `https://viacep.com.br/ws/${cep}/json/`,
                dataType: 'json',
                success: function(data) {
                    if (data.erro) {
                        callback({ success: false, message: 'CEP não encontrado' });
                    } else {
                        callback({
                            success: true,
                            data: {
                                logradouro: data.logradouro,
                                bairro: data.bairro,
                                cidade: data.localidade,
                                estado: data.uf
                            }
                        });
                    }
                },
                error: function() {
                    callback({ success: false, message: 'Erro ao buscar CEP' });
                }
            });
        },
        
        /**
         * Loading overlay
         */
        showLoading: function(message = 'Carregando...') {
            Swal.fire({
                title: message,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        },
        
        hideLoading: function() {
            Swal.close();
        },
        
        /**
         * Toast notification
         */
        toast: function(message, type = 'success') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
            
            Toast.fire({
                icon: type,
                title: message
            });
        }
    };
    
})(jQuery);
