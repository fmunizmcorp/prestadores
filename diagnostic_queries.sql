-- DIAGNOSTIC QUERIES FOR PRODUCTION DATABASE
-- Execute these in phpMyAdmin or MySQL client

-- Check if projetos table exists
SHOW TABLES LIKE 'projetos';

-- Get projetos table structure
DESCRIBE projetos;

-- Count projetos records
SELECT COUNT(*) as total_projetos FROM projetos;

-- Sample projetos data
SELECT * FROM projetos LIMIT 3;

-- Check if atividades table exists  
SHOW TABLES LIKE 'atividades';

-- Get atividades table structure
DESCRIBE atividades;

-- Count atividades records
SELECT COUNT(*) as total_atividades FROM atividades;

-- Sample atividades data
SELECT * FROM atividades LIMIT 3;

-- Check if notas_fiscais table exists
SHOW TABLES LIKE 'notas_fiscais';

-- Get notas_fiscais table structure (para confirmar)
DESCRIBE notas_fiscais;

-- List ALL tables in database
SHOW TABLES;
