#!/bin/bash
# Sprint 66 - Correção Bug #7: Login Quebrado
# Data: 2025-11-16
# Autor: GenSpark AI Developer

echo "=================================="
echo "  SPRINT 66 - FIX BUG #7 CRÍTICO"
echo "  Login Quebrado - Database.php"
echo "=================================="
echo ""

VPS_HOST="72.61.53.222"
VPS_USER="root"
VPS_PATH="/opt/webserver/sites/prestadores"

echo "📋 Etapa 1: Criar usuários no banco de dados"
echo "-------------------------------------------"

# SQL para criar usuários
cat > /tmp/create_users.sql << 'EOSQL'
-- Criar usuários de teste
USE db_prestadores;

-- Master
INSERT INTO usuarios (nome, email, senha, role, ativo, created_at, updated_at) VALUES
('Master User', 'master@clinfec.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'master', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE senha = VALUES(senha), role = VALUES(role), ativo = VALUES(ativo);

-- Admin
INSERT INTO usuarios (nome, email, senha, role, ativo, created_at, updated_at) VALUES
('Admin User', 'admin@clinfec.com.br', '$2y$10$VJL2WmMq9Kh7FHPqYG8P2.Y8ZHPqT5xQwE0pXk7nOmKm3F9F/R5Wa', 'admin', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE senha = VALUES(senha), role = VALUES(role), ativo = VALUES(ativo);

-- Gestor
INSERT INTO usuarios (nome, email, senha, role, ativo, created_at, updated_at) VALUES
('Gestor User', 'gestor@clinfec.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'gestor', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE senha = VALUES(senha), role = VALUES(role), ativo = VALUES(ativo);

-- Usuario
INSERT INTO usuarios (nome, email, senha, role, ativo, created_at, updated_at) VALUES
('Usuario Basico', 'usuario@clinfec.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE senha = VALUES(senha), role = VALUES(role), ativo = VALUES(ativo);

SELECT '✅ Usuários criados/atualizados' AS status;
SELECT id, nome, email, role, ativo FROM usuarios WHERE email LIKE '%@clinfec.com.br' ORDER BY role DESC;
EOSQL

echo "📤 Enviando SQL para servidor..."
scp /tmp/create_users.sql ${VPS_USER}@${VPS_HOST}:${VPS_PATH}/database/

echo ""
echo "📋 Etapa 2: Copiar Database.php corrigido"
echo "-------------------------------------------"
echo "📤 Enviando Database.php para servidor..."
scp src/Database.php ${VPS_USER}@${VPS_HOST}:${VPS_PATH}/src/

echo ""
echo "📋 Etapa 3: Executar no servidor VPS"
echo "-------------------------------------------"

ssh ${VPS_USER}@${VPS_HOST} << 'EOSSH'
cd /opt/webserver/sites/prestadores

echo "1️⃣ Criando usuários no banco..."
mysql -u user_prestadores -prN8u7u0ogbFPN3lfYqtF6wuAn5uJZFFP db_prestadores < database/create_users.sql

echo ""
echo "2️⃣ Verificando Database.php..."
ls -lah src/Database.php

echo ""
echo "3️⃣ Reiniciando PHP-FPM..."
systemctl reload php8.3-fpm-prestadores

echo ""
echo "4️⃣ Limpando OPcache..."
echo "<?php opcache_reset(); echo '✅ OPcache limpo'; ?>" | php8.3

echo ""
echo "✅ Deploy concluído!"
EOSSH

echo ""
echo "=================================="
echo "  ✅ FIX BUG #7 DEPLOYADO"
echo "=================================="
echo ""
echo "🧪 Testar agora em: https://prestadores.clinfec.com.br"
echo ""
echo "📋 Credenciais de Teste:"
echo "  master@clinfec.com.br / password"
echo "  admin@clinfec.com.br / admin123"
echo "  gestor@clinfec.com.br / password"
echo "  usuario@clinfec.com.br / password"
echo ""
