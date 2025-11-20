-- Migration 025: Create system_settings table
-- Sprint 65 - Sistema de Configurações SMTP e Email
-- Data: 2025-11-16

CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    setting_type ENUM('string', 'integer', 'boolean', 'json', 'encrypted') DEFAULT 'string',
    category VARCHAR(50) NOT NULL DEFAULT 'general',
    description VARCHAR(255) NULL,
    is_encrypted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir configurações padrão de SMTP
INSERT INTO system_settings (setting_key, setting_value, setting_type, category, description) VALUES
('smtp_host', 'localhost', 'string', 'email', 'Servidor SMTP'),
('smtp_port', '587', 'integer', 'email', 'Porta SMTP (25, 465, 587)'),
('smtp_secure', 'tls', 'string', 'email', 'Segurança SMTP (tls, ssl, none)'),
('smtp_username', '', 'string', 'email', 'Usuário SMTP'),
('smtp_password', '', 'encrypted', 'email', 'Senha SMTP (criptografada)'),
('smtp_from_email', 'noreply@clinfec.com.br', 'string', 'email', 'Email remetente'),
('smtp_from_name', 'Sistema Clinfec', 'string', 'email', 'Nome do remetente'),
('smtp_enabled', '1', 'boolean', 'email', 'SMTP habilitado'),
('mail_driver', 'smtp', 'string', 'email', 'Driver de email (smtp, sendmail, mail)'),
('system_name', 'Sistema de Gestão de Prestadores', 'string', 'general', 'Nome do sistema'),
('system_timezone', 'America/Sao_Paulo', 'string', 'general', 'Fuso horário do sistema'),
('system_language', 'pt_BR', 'string', 'general', 'Idioma do sistema')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Registrar versão da migration
INSERT INTO database_version (version, description, executed_at) 
VALUES (25, 'Create system_settings table for SMTP and email configuration', NOW())
ON DUPLICATE KEY UPDATE executed_at = NOW();
