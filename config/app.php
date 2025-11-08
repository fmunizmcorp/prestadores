<?php
/**
 * Configuração da Aplicação
 * Sistema de Gestão de Prestadores - Clinfec
 */

return [
    'name' => 'Sistema de Gestão de Prestadores',
    'version' => '1.0.0',
    'url' => 'https://prestadores.clinfec.com.br',
    'timezone' => 'America/Sao_Paulo',
    
    // Configurações de sessão
    'session' => [
        'name' => 'PRESTADORES_SESSION',
        'lifetime' => 7200, // 2 horas
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ],
    
    // Configurações de segurança
    'security' => [
        'password_min_length' => 8,
        'password_require_special' => true,
        'password_require_numbers' => true,
        'password_require_uppercase' => true,
        'max_login_attempts' => 5,
        'lockout_time' => 900, // 15 minutos
        'token_expiry' => 3600, // 1 hora para tokens de recuperação
    ],
    
    // Google reCAPTCHA v2
    'recaptcha' => [
        'site_key' => '6LcxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxXX', // Substituir pela sua chave
        'secret_key' => '6LcxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxXX', // Substituir pela sua chave
        'enabled' => true
    ],
    
    // Perfis de acesso
    'roles' => [
        'master' => [
            'name' => 'Master',
            'description' => 'Acesso total ao sistema',
            'level' => 100
        ],
        'admin' => [
            'name' => 'Administrador',
            'description' => 'Gerencia empresas e usuários',
            'level' => 80
        ],
        'gestor' => [
            'name' => 'Gestor',
            'description' => 'Gerencia projetos e atividades',
            'level' => 60
        ],
        'usuario' => [
            'name' => 'Usuário',
            'description' => 'Acesso básico ao sistema',
            'level' => 40
        ]
    ],
    
    // Configurações de email
    'mail' => [
        'from_email' => 'noreply@clinfec.com.br',
        'from_name' => 'Sistema Clinfec',
        'smtp_host' => 'smtp.hostinger.com',
        'smtp_port' => 587,
        'smtp_username' => '', // Configurar
        'smtp_password' => '', // Configurar
        'smtp_secure' => 'tls'
    ]
];
