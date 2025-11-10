<?php
/**
 * Controle de Versão do Sistema
 * Formato: MAJOR.MINOR.PATCH (Semantic Versioning)
 */

return [
    'version' => '1.9.0',
    'release_date' => '2025-11-10',
    'db_version' => 15, // Versão do schema do banco de dados (última migration = 015)
    
    'changelog' => [
        '1.4.0' => [
            'date' => '2024-11-05',
            'changes' => [
                'Sprint 4: Empresas Tomadoras e Contratos',
                'CRUD Empresas Tomadoras completo',
                'CRUD Contratos completo',
                'Sistema de Valores de Serviços por Período',
                'Gestão de Responsáveis',
                'Upload e gestão de Documentos',
                'Histórico de alterações em contratos',
                'Sistema de aditivos contratuais'
            ]
        ],
        '1.3.0' => [
            'date' => '2024-11-04',
            'changes' => [
                'Sprint 3: Empresas Prestadoras',
                'CRUD completo de Empresas Prestadoras',
                'Diferenciação PJ/PF/MEI',
                'Vinculação de serviços N:N'
            ]
        ],
        '1.2.0' => [
            'date' => '2024-11-03',
            'changes' => [
                'Sprint 2: Serviços',
                'CRUD completo de Serviços',
                'Categorização de serviços',
                'Unidades de medida'
            ]
        ],
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
