/**
 * Validations.js - Validações customizadas para formulários
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        setupValidations();
    });
    
    /**
     * Configurar validações
     */
    function setupValidations() {
        // Validação de e-mail
        $('input[type="email"]').on('blur', function() {
            const email = $(this).val();
            if (email && !validateEmail(email)) {
                showError($(this), 'E-mail inválido');
            } else {
                clearError($(this));
            }
        });
        
        // Validação de senha forte
        $('.password-strength').on('input', function() {
            const password = $(this).val();
            const strength = checkPasswordStrength(password);
            showPasswordStrength($(this), strength);
        });
        
        // Confirmação de senha
        $('[name="senha_confirmacao"]').on('input', function() {
            const password = $('[name="senha"]').val();
            const confirmation = $(this).val();
            
            if (confirmation && password !== confirmation) {
                showError($(this), 'As senhas não coincidem');
            } else {
                clearError($(this));
            }
        });
        
        // Validação de URLs
        $('input[type="url"]').on('blur', function() {
            const url = $(this).val();
            if (url && !validateUrl(url)) {
                showError($(this), 'URL inválida');
            } else {
                clearError($(this));
            }
        });
        
        // Validação de datas
        $('.mask-date').on('blur', function() {
            const date = $(this).val();
            if (date && !validateDate(date)) {
                showError($(this), 'Data inválida');
            } else {
                clearError($(this));
            }
        });
        
        // Data mínima
        $('.date-min').on('blur', function() {
            const date = $(this).val();
            const minDate = $(this).data('min') || new Date().toISOString().split('T')[0];
            
            if (date && new Date(date) < new Date(minDate)) {
                showError($(this), 'Data não pode ser anterior a ' + formatDateBR(minDate));
            } else {
                clearError($(this));
            }
        });
        
        // Data máxima
        $('.date-max').on('blur', function() {
            const date = $(this).val();
            const maxDate = $(this).data('max') || new Date().toISOString().split('T')[0];
            
            if (date && new Date(date) > new Date(maxDate)) {
                showError($(this), 'Data não pode ser posterior a ' + formatDateBR(maxDate));
            } else {
                clearError($(this));
            }
        });
        
        // Valor mínimo
        $('input[type="number"]').on('blur', function() {
            const value = parseFloat($(this).val());
            const min = parseFloat($(this).attr('min'));
            
            if (!isNaN(min) && value < min) {
                showError($(this), `Valor mínimo: ${min}`);
            } else {
                clearError($(this));
            }
        });
        
        // Valor máximo
        $('input[type="number"]').on('blur', function() {
            const value = parseFloat($(this).val());
            const max = parseFloat($(this).attr('max'));
            
            if (!isNaN(max) && value > max) {
                showError($(this), `Valor máximo: ${max}`);
            } else {
                clearError($(this));
            }
        });
        
        // Upload de arquivos - validar tamanho
        $('input[type="file"]').on('change', function() {
            const maxSize = $(this).data('max-size') || 10 * 1024 * 1024; // 10MB padrão
            const file = this.files[0];
            
            if (file && file.size > maxSize) {
                showError($(this), `Arquivo muito grande. Tamanho máximo: ${formatBytes(maxSize)}`);
                $(this).val('');
            } else {
                clearError($(this));
            }
        });
        
        // Upload de arquivos - validar tipo
        $('input[type="file"]').on('change', function() {
            const allowedTypes = $(this).data('allowed-types');
            if (!allowedTypes) return;
            
            const file = this.files[0];
            if (file) {
                const fileType = file.type;
                const fileExt = file.name.split('.').pop().toLowerCase();
                const types = allowedTypes.split(',');
                
                let valid = false;
                for (let type of types) {
                    type = type.trim();
                    if (type.startsWith('.') && fileExt === type.substring(1)) {
                        valid = true;
                        break;
                    } else if (fileType.includes(type)) {
                        valid = true;
                        break;
                    }
                }
                
                if (!valid) {
                    showError($(this), `Tipo de arquivo não permitido. Permitidos: ${allowedTypes}`);
                    $(this).val('');
                } else {
                    clearError($(this));
                }
            }
        });
    }
    
    /**
     * Validar e-mail
     */
    function validateEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
    
    /**
     * Validar URL
     */
    function validateUrl(url) {
        try {
            new URL(url);
            return true;
        } catch (e) {
            return false;
        }
    }
    
    /**
     * Validar data (formato DD/MM/YYYY)
     */
    function validateDate(date) {
        const regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
        if (!regex.test(date)) return false;
        
        const [, day, month, year] = date.match(regex);
        const d = new Date(year, month - 1, day);
        
        return d.getFullYear() == year && 
               d.getMonth() == month - 1 && 
               d.getDate() == day;
    }
    
    /**
     * Verificar força da senha
     */
    function checkPasswordStrength(password) {
        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;
        
        if (strength <= 2) return { level: 'weak', text: 'Fraca', color: 'danger' };
        if (strength <= 4) return { level: 'medium', text: 'Média', color: 'warning' };
        return { level: 'strong', text: 'Forte', color: 'success' };
    }
    
    /**
     * Mostrar indicador de força da senha
     */
    function showPasswordStrength($input, strength) {
        let $indicator = $input.next('.password-strength-indicator');
        
        if (!$indicator.length) {
            $indicator = $('<div class="password-strength-indicator mt-2"></div>');
            $input.after($indicator);
        }
        
        $indicator.html(`
            <div class="progress" style="height: 5px;">
                <div class="progress-bar bg-${strength.color}" 
                     role="progressbar" 
                     style="width: ${strength.level === 'weak' ? '33%' : strength.level === 'medium' ? '66%' : '100%'}">
                </div>
            </div>
            <small class="text-${strength.color}">Senha ${strength.text}</small>
        `);
    }
    
    /**
     * Mostrar erro de validação
     */
    function showError($input, message) {
        $input.addClass('is-invalid');
        
        let $feedback = $input.next('.invalid-feedback');
        if (!$feedback.length) {
            $feedback = $('<div class="invalid-feedback"></div>');
            $input.after($feedback);
        }
        
        $feedback.text(message);
    }
    
    /**
     * Limpar erro de validação
     */
    function clearError($input) {
        $input.removeClass('is-invalid');
        $input.next('.invalid-feedback').remove();
    }
    
    /**
     * Formatar data para BR
     */
    function formatDateBR(date) {
        const d = new Date(date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        return `${day}/${month}/${year}`;
    }
    
    /**
     * Formatar bytes
     */
    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
    
    /**
     * Validação customizada do Bootstrap
     */
    (function() {
        const forms = document.querySelectorAll('.needs-validation');
        
        Array.from(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    // Focar no primeiro campo inválido
                    const firstInvalid = form.querySelector(':invalid');
                    if (firstInvalid) {
                        firstInvalid.focus();
                    }
                }
                
                form.classList.add('was-validated');
            }, false);
        });
    })();
    
})(jQuery);
