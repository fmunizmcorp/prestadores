# 📊 Sistema de Gestão de Prestadores de Serviços - Clinfec

Sistema completo para gestão de atividades, projetos, prestadores de serviços, custos e pagamentos.

**URL de Produção:** https://prestadores.clinfec.com.br

---

## 🚀 Tecnologias

- **Backend**: PHP 7.4+ (orientado a objetos, PSR-4)
- **Banco de Dados**: MySQL 5.7+ / MariaDB 10.3+
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla + Bootstrap 5)
- **Design**: Interface moderna e responsiva
- **Arquitetura**: MVC + RESTful Routing
- **Segurança**: CSRF Protection, BCrypt, RBAC

---

## 📋 Funcionalidades Completas

### ✅ Sprint 1-3: Sistema de Autenticação
- [x] Login com email e senha
- [x] Registro de novos usuários com validação
- [x] Recuperação de senha
- [x] Proteção com reCAPTCHA v2
- [x] Validação de força de senha
- [x] Bloqueio por múltiplas tentativas
- [x] Tokens CSRF
- [x] Log de atividades

### ✅ Sprint 4: Gestão de Empresas e Contratos
- [x] Cadastro de Empresas Tomadoras
- [x] Cadastro de Empresas Prestadoras
- [x] Gestão de Contratos
- [x] Vínculo de Serviços a Contratos
- [x] Valores de Referência
- [x] Aditivos Contratuais
- [x] Busca automática de CEP
- [x] Validação de CNPJ

### ✅ Sprint 5: Gestão de Projetos
- [x] Criação e gestão de projetos
- [x] Vínculo de projetos a contratos
- [x] Acompanhamento de status
- [x] Timeline de atividades
- [x] Dashboard de projetos
- [x] Gestão financeira por projeto

### ✅ Sprint 6: Sistema de Atividades (Vagas) e Candidaturas
- [x] Criação de atividades (vagas de trabalho)
- [x] Sistema completo de workflow
- [x] Gestão de candidaturas
- [x] Análise de perfil dos candidatos
- [x] Algoritmo de matchmaking (6 critérios)
- [x] Agendamento de entrevistas
- [x] Avaliação de candidatos
- [x] Sistema de notificações
- [x] Integração completa com projetos

### ✅ Sprint 7: Módulo Financeiro Completo
- [x] Categorias Financeiras
- [x] Contas a Pagar
- [x] Contas a Receber
- [x] Boletos
- [x] Lançamentos Financeiros
- [x] Conciliação Bancária
- [x] Fluxo de Caixa
- [x] DRE (Demonstrativo de Resultados)
- [x] Balancete
- [x] Notas Fiscais Eletrônicas (NF-e)
- [x] Integração com Projetos e Contratos

---

## 🗄️ Estrutura do Banco de Dados

### Tabelas Principais (50+ tabelas):

**Autenticação e Usuários:**
- usuarios
- logs_atividades

**Empresas:**
- empresas_tomadoras
- empresas_prestadoras
- empresa_responsaveis
- empresa_documentos

**Contratos e Serviços:**
- contratos
- servicos
- contrato_servicos
- contrato_aditivos
- servico_valores

**Projetos:**
- projetos
- projeto_custos
- projeto_timeline

**Atividades (Vagas):**
- atividades
- atividades_categorias
- candidaturas
- candidaturas_avaliacoes

**Financeiro:**
- categorias_financeiras
- contas_pagar
- contas_receber
- boletos
- lancamentos_financeiros
- conciliacoes_bancarias
- notas_fiscais
- pagamentos

---

## 👥 Usuários Padrão do Sistema

**Documento completo:** `USUARIOS_SISTEMA.md`

### MASTER (Nível 100)
- **E-mail:** master@clinfec.com.br
- **Senha:** password
- **Permissões:** Acesso total ao sistema

### ADMIN (Nível 80)
- **E-mail:** admin@clinfec.com.br
- **Senha:** password
- **Permissões:** Gestão de empresas, contratos, usuários

### GESTOR (Nível 60)
- **E-mail:** gestor@clinfec.com.br
- **Senha:** password
- **Permissões:** Gestão de projetos e atividades

**⚠️ IMPORTANTE:** Altere TODAS as senhas após primeiro acesso!

---

## 🚀 Instalação e Configuração

### Requisitos do Servidor

- PHP 7.4 ou superior
- MySQL 5.7 ou MariaDB 10.3+
- Apache com mod_rewrite habilitado
- Extensões PHP:
  - pdo_mysql
  - mbstring
  - json
  - openssl
  - fileinfo

### Passo a Passo

1. **Clone o repositório:**
   ```bash
   git clone https://github.com/fmunizmcorp/prestadores.git
   cd prestadores
   ```

2. **Configure o banco de dados:**
   - Edite `config/database.php` com suas credenciais
   - As migrations serão executadas automaticamente no primeiro acesso

3. **Configure as permissões:**
   ```bash
   chmod -R 755 .
   chmod -R 777 uploads/
   chmod -R 777 logs/
   ```

4. **Configure o .htaccess:**
   - O arquivo já está configurado para domínio raiz
   - Para subpasta, ajuste o `RewriteBase`

5. **Acesse o sistema:**
   ```
   https://prestadores.clinfec.com.br
   ```

6. **Faça login com usuário master:**
   - E-mail: master@clinfec.com.br
   - Senha: password

---

## 📁 Estrutura de Diretórios

```
prestadores/
├── config/              # Configurações do sistema
│   ├── database.php     # Configurações do banco de dados
│   ├── app.php          # Configurações gerais
│   └── config.php       # Configurações antigas (manter)
├── database/
│   └── migrations/      # Migrations do banco (010 arquivos)
├── docs/                # Documentação completa
│   ├── PDCA_REDIRECT_FIX_2025.md
│   ├── MERGE_COMPLETO_MAIN_2025.md
│   └── [outras documentações]
├── public/              # Pasta pública (DocumentRoot)
│   ├── index.php        # Front Controller
│   ├── css/             # Arquivos CSS
│   ├── js/              # Arquivos JavaScript
│   └── images/          # Imagens
├── src/
│   ├── controllers/     # Controllers MVC (15+ arquivos)
│   ├── models/          # Models (40+ arquivos)
│   ├── views/           # Views (80+ arquivos)
│   ├── helpers/         # Helper functions
│   └── DatabaseMigration.php
├── uploads/             # Arquivos enviados pelos usuários
├── logs/                # Logs do sistema
├── .htaccess            # Configurações Apache
├── README.md            # Este arquivo
├── USUARIOS_SISTEMA.md  # Lista de usuários e senhas
└── [outros arquivos de documentação]
```

---

## 🔐 Segurança

### Implementado:

- ✅ CSRF Protection em todos os formulários
- ✅ SQL Injection Prevention (PDO Prepared Statements)
- ✅ XSS Protection (htmlspecialchars)
- ✅ Password Hashing (BCrypt)
- ✅ RBAC (Role-Based Access Control)
- ✅ Session Security (HTTPOnly, Secure, SameSite)
- ✅ Input Validation (Server-side e Client-side)
- ✅ File Upload Validation
- ✅ Logs de Auditoria
- ✅ Headers de Segurança

### Recomendações:

- [ ] Alterar senhas padrão
- [ ] Configurar SSL/TLS (HTTPS)
- [ ] Backup regular do banco de dados
- [ ] Monitoramento de logs
- [ ] Rate limiting em endpoints críticos

---

## 🎯 URLs Importantes

**Produção:**
- Login: https://prestadores.clinfec.com.br/login
- Dashboard: https://prestadores.clinfec.com.br/dashboard
- Empresas Tomadoras: https://prestadores.clinfec.com.br/empresas-tomadoras
- Empresas Prestadoras: https://prestadores.clinfec.com.br/empresas-prestadoras
- Contratos: https://prestadores.clinfec.com.br/contratos
- Serviços: https://prestadores.clinfec.com.br/servicos
- Projetos: https://prestadores.clinfec.com.br/projetos
- Atividades: https://prestadores.clinfec.com.br/atividades
- Financeiro: https://prestadores.clinfec.com.br/financeiro

---

## 📚 Documentação Adicional

- **USUARIOS_SISTEMA.md** - Lista completa de usuários e senhas
- **PDCA_REDIRECT_FIX_2025.md** - Documentação do fix de redirects
- **MERGE_COMPLETO_MAIN_2025.md** - Documentação do merge na main
- **docs/SPRINT_*.md** - Documentação de cada sprint
- **docs/AUDITORIA_*.md** - Auditorias e testes

---

## 🐛 Troubleshooting

### Erro 500 - Internal Server Error

**Solução:**
1. Verificar logs: `tail -f /var/log/php-fpm/error.log`
2. Verificar permissões: `chmod 755` nos diretórios
3. Verificar configuração do banco em `config/database.php`

### Redirect Loop

**Solução:**
1. Verificar `.htaccess` está correto
2. Verificar `BASE_URL` em `public/index.php`
3. Limpar cache do navegador

### Usuário não consegue fazer login

**Solução:**
1. Verificar se migrations foram executadas
2. Verificar tabela `usuarios` no banco de dados
3. Senha padrão é: `password`

### Página em branco

**Solução:**
1. Habilitar display_errors em `config/config.php`
2. Verificar logs do PHP
3. Verificar se todas as classes estão sendo carregadas

---

## 📊 Estatísticas do Projeto

- **Linhas de Código:** 28,000+
- **Arquivos PHP:** 100+
- **Tabelas no Banco:** 50+
- **Migrations:** 10
- **Controllers:** 15+
- **Models:** 40+
- **Views:** 80+
- **Sprints Completados:** 7

---

## 🏆 Metodologia

- **Desenvolvimento:** SCRUM
- **Qualidade:** PDCA (Plan, Do, Check, Act)
- **Versionamento:** Git + GitHub
- **Arquitetura:** MVC + RESTful
- **Princípios:** SOLID, DRY, KISS

---

## 👨‍💻 Desenvolvimento

**Repositório:** https://github.com/fmunizmcorp/prestadores  
**Branch Principal:** main  
**Branch de Desenvolvimento:** genspark_ai_developer

---

## 📝 Licença

Proprietário - Clinfec  
Todos os direitos reservados.

---

## 📞 Suporte

**Em caso de problemas:**
1. Consultar documentação em `docs/`
2. Verificar `USUARIOS_SISTEMA.md`
3. Consultar logs em `logs/error.log`
4. Verificar issue tracker no GitHub

---

**Última atualização:** 2025-11-08  
**Versão:** 1.0.0  
**Status:** ✅ PRODUÇÃO
