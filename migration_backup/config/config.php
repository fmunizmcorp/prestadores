<?php
/**
 * Clinfec Prestadores - Configurações Gerais
 * Hostinger - Domínio Dedicado
 * URL: https://prestadores.clinfec.com.br
 */

return [
    // Informações da Aplicação
    'app_name' => 'Clinfec Prestadores',
    'app_version' => '1.0.0',
    'app_description' => 'Sistema de Gestão de Prestadores de Serviço',
    
    // URL Base - Domínio dedicado (raiz)
    'base_url' => 'https://prestadores.clinfec.com.br',
    
    // Timezone
    'timezone' => 'America/Sao_Paulo',
    
    // Caminhos
    'upload_path' => __DIR__ . '/../uploads/',  // Caminho absoluto no servidor
    'upload_url' => '/uploads/',    // URL relativa (raiz)
    
    // Upload de Arquivos
    'upload_max_size' => 10485760,  // 10MB em bytes
    'allowed_extensions' => [
        // Documentos
        'pdf', 'doc', 'docx', 'odt',
        // Planilhas
        'xls', 'xlsx', 'ods', 'csv',
        // Imagens
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        // Compactados
        'zip', 'rar'
    ],
    
    // Paginação
    'items_per_page' => 25,
    'pagination_options' => [10, 25, 50, 100],
    
    // Sessão
    'session_name' => 'CLINFEC_SESSION',
    'session_lifetime' => 7200,  // 2 horas em segundos
    
    // Segurança
    'password_min_length' => 6,
    'csrf_token_name' => 'csrf_token',
    'max_login_attempts' => 5,
    'login_lockout_time' => 900,  // 15 minutos em segundos
    
    // Formato de Data e Hora
    'date_format' => 'd/m/Y',
    'datetime_format' => 'd/m/Y H:i:s',
    'time_format' => 'H:i',
    
    // Moeda
    'currency_symbol' => 'R$',
    'currency_decimal_separator' => ',',
    'currency_thousand_separator' => '.',
    
    // Debug e Logs
    'debug' => false,  // IMPORTANTE: false em produção, true apenas para debug
    'display_errors' => false,  // IMPORTANTE: false em produção
    'log_errors' => true,
    'error_log_path' => __DIR__ . '/../logs/error.log',
    
    // Email (configurar futuramente)
    'email_from' => 'noreply@clinfec.com.br',
    'email_from_name' => 'Clinfec Prestadores',
    
    // Empresa (Clinfec)
    'company_name' => 'Clinfec',
    'company_cnpj' => '',
    'company_email' => 'contato@clinfec.com.br',
    'company_phone' => '',
    
    // API Externa (ViaCEP)
    'viacep_api' => 'https://viacep.com.br/ws/',
    
    // Permissões de Perfis
    'perfis' => [
        'master' => [
            'nome' => 'Master',
            'descricao' => 'Acesso total ao sistema',
            'nivel' => 4
        ],
        'admin' => [
            'nome' => 'Administrador',
            'descricao' => 'Gestão completa exceto configurações críticas',
            'nivel' => 3
        ],
        'gestor' => [
            'nome' => 'Gestor',
            'descricao' => 'Operações limitadas de gestão',
            'nivel' => 2
        ],
        'usuario' => [
            'nome' => 'Usuário',
            'descricao' => 'Apenas visualização',
            'nivel' => 1
        ]
    ]
];
