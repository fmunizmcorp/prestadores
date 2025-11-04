<?php
/**
 * Clinfec Prestadores - Configurações para SUBPASTA
 * Use este arquivo como referência para config/config.php
 * quando instalar em public_html/prestadores/
 */

return [
    // Informações da Aplicação
    'app_name' => 'Clinfec Prestadores',
    'app_version' => '1.0.0',
    
    // URL Base - IMPORTANTE: Incluir /prestadores
    // Exemplo: https://seudominio.com.br/prestadores
    'base_url' => 'https://seudominio.com.br/prestadores',  // ← ALTERE AQUI!
    
    // Timezone
    'timezone' => 'America/Sao_Paulo',
    
    // Caminhos para uploads
    'upload_path' => __DIR__ . '/../uploads/',  // Caminho absoluto (não mude)
    'upload_url' => '/prestadores/uploads/',  // ← URL com /prestadores !
    
    // Upload
    'upload_max_size' => 10485760,  // 10MB em bytes
    'allowed_extensions' => [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 
        'jpg', 'jpeg', 'png', 'gif'
    ],
    
    // Paginação
    'items_per_page' => 25,
    'pagination_options' => [10, 25, 50, 100],
    
    // Sessão
    'session_lifetime' => 7200,  // 2 horas em segundos
    
    // Segurança
    'password_min_length' => 6,
    'csrf_token_name' => 'csrf_token',
    
    // Debug (DESABILITAR EM PRODUÇÃO!)
    'debug' => false,  // false em produção
    'display_errors' => false,  // false em produção
    
    // Logs
    'log_errors' => true,
    'error_log_path' => __DIR__ . '/../logs/error.log',
];
