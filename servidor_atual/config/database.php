<?php
/**
 * Configuração do Banco de Dados - VPS
 * 
 * Atualizado automaticamente pela migração
 * Data: 2025-11-16
 */

return [
    'host' => 'localhost',
    'database' => 'db_prestadores',
    'username' => 'user_prestadores',
    'password' => 'rN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];
