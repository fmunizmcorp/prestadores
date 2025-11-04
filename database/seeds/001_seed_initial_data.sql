-- Seed inicial com usuário master e dados básicos
-- Sprint 1: Dados Iniciais

-- Inserir usuário admin
-- Email: admin@clinfec.com.br
-- Senha: admin
-- IMPORTANTE: Trocar a senha após primeiro login!
INSERT INTO usuarios (nome, email, senha, role, ativo, email_verificado) VALUES
('Administrador do Sistema', 'admin@clinfec.com.br', '$2y$10$KZwv5F3Y8qVNvFj3m5K8eO4JdXqrW.7LZ8vZn5nVQPKQDlKxIqXrO', 'master', TRUE, TRUE);

-- A senha acima é o hash de: admin
-- Para gerar novos hashes, use: password_hash('sua_senha', PASSWORD_DEFAULT);

-- Inserir serviços básicos (exemplos)
INSERT INTO servicos (nome, descricao, ativo) VALUES
('Consultoria em TI', 'Serviços de consultoria em tecnologia da informação', TRUE),
('Desenvolvimento de Software', 'Desenvolvimento de sistemas e aplicações', TRUE),
('Suporte Técnico', 'Suporte técnico e manutenção de sistemas', TRUE),
('Infraestrutura de Rede', 'Instalação e manutenção de infraestrutura de rede', TRUE),
('Segurança da Informação', 'Serviços de segurança e proteção de dados', TRUE),
('Treinamento', 'Treinamento e capacitação de equipes', TRUE),
('Design Gráfico', 'Criação de identidade visual e materiais gráficos', TRUE),
('Marketing Digital', 'Gestão de marketing e mídias sociais', TRUE),
('Contabilidade', 'Serviços contábeis e fiscais', TRUE),
('Jurídico', 'Assessoria jurídica e legal', TRUE);

-- Log da criação inicial
INSERT INTO logs_atividades (usuario_id, acao, descricao, ip_address) VALUES
(1, 'SISTEMA_INSTALADO', 'Sistema instalado e configurado com sucesso', '127.0.0.1');
