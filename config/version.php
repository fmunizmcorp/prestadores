<?php
/**
 * Controle de Versão do Sistema
 * Formato: MAJOR.MINOR.PATCH (Semantic Versioning)
 */

return [
    'version' => '1.0.0',
    'release_date' => '2024-01-10',
    'db_version' => 1, // Versão do schema do banco de dados
    
    'changelog' => [
        '1.0.0' => [
            'date' => '2024-01-10',
            'changes' => [
                'Sistema de autenticação completo',
                'Controle de acesso com 4 perfis',
                'Dashboard inicial',
                'Sistema de migrations automáticas',
                'Controle de versão integrado'
            ]
        ]
    ]
];
