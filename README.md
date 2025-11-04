# 📊 Sistema de Gestão de Prestadores de Serviços - Clinfec

Sistema completo para gestão de atividades, projetos, prestadores de serviços, custos e pagamentos.

## 🚀 Tecnologias

- **Backend**: PHP 7.4+ (orientado a objetos)
- **Banco de Dados**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Design**: Interface moderna e responsiva com gradientes e animações

## 📋 Funcionalidades - Sprint 1, 2 e 3

### ✅ Sistema de Autenticação Completo
- [x] Login com email e senha
- [x] Registro de novos usuários com validação
- [x] Recuperação de senha (esqueci minha senha)
- [x] Proteção com reCAPTCHA v2
- [x] Validação de força de senha
- [x] Bloqueio por múltiplas tentativas de login
- [x] Tokens CSRF para segurança
- [x] Log de atividades do sistema

### ✅ Gestão de Usuários
- [x] 4 perfis de acesso: Master, Admin, Gestor, Usuário
- [x] Controle de permissões por nível (RBAC)
- [x] Vínculo de usuários a múltiplas empresas
- [x] Dashboard personalizado por perfil

### ✅ Cadastro de Empresas Fornecedoras
- [x] Razão social, nome fantasia, CNPJ
- [x] Endereço completo com busca automática por CEP
- [x] Múltiplos contatos (telefones, emails, pessoas)
- [x] Serviços prestados (lista configurável)
- [x] Validação de CNPJ
- [x] Máscaras automáticas para campos

## 🗄️ Estrutura do Banco de Dados

### Tabelas Criadas:
1. **usuarios** - Usuários do sistema
2. **empresas** - Empresas fornecedoras
3. **servicos** - Catálogo de serviços
4. **empresa_servico** - Relacionamento N:N empresas x serviços
5. **usuario_empresa** - Relacionamento N:N usuários x empresas
6. **empresa_contatos** - Pessoas de contato das empresas
7. **logs_atividades** - Auditoria do sistema

## 👤 Usuário Master Padrão

**Email**: admin@clinfec.com.br  
**Senha**: Master@2024  
**Perfil**: Master (acesso total)

> ⚠️ **IMPORTANTE**: Altere esta senha após o primeiro login!

## 📦 Instalação

### Passo 1: Clonar o Repositório do GitHub

```bash
git clone https://github.com/seu-usuario/prestadores-clinfec.git
cd prestadores-clinfec
```

### Passo 2: Configurar o Banco de Dados

1. Acesse o painel de controle da Hostinger
2. Vá em "Banco de Dados MySQL"
3. Acesse o phpMyAdmin
4. Selecione o banco `u673902663_prestadores`
5. Execute o script: `database/migrations/001_create_usuarios_table.sql`
6. Execute o script: `database/seeds/001_seed_initial_data.sql`

### Passo 3: Configurar Arquivos

1. Copie todos os arquivos para a pasta `public_html/prestadores/` na Hostinger
2. Verifique se o arquivo `config/database.php` está com as credenciais corretas
3. Configure as chaves do reCAPTCHA em `config/app.php`

### Passo 4: Configurar Permissões

```bash
chmod 755 public
chmod 644 public/.htaccess
chmod 755 logs
chmod 644 logs/*.log
```

### Passo 5: Testar a Instalação

1. Acesse: `https://clinfec.com.br/prestadores`
2. Faça login com o usuário master
3. Explore o sistema!

## 🔐 Configuração do reCAPTCHA

1. Acesse: https://www.google.com/recaptcha/admin
2. Crie um novo site (reCAPTCHA v2)
3. Adicione o domínio: `clinfec.com.br`
4. Copie as chaves para `config/app.php`:
   ```php
   'site_key' => 'SUA_SITE_KEY_AQUI',
   'secret_key' => 'SUA_SECRET_KEY_AQUI',
   ```

## 📁 Estrutura de Diretórios

```
prestadores/
├── config/                 # Configurações
│   ├── app.php            # Configurações gerais
│   └── database.php       # Configurações do banco
├── database/              # Scripts de banco de dados
│   ├── migrations/        # Migrações
│   └── seeds/            # Seeds (dados iniciais)
├── docs/                  # Documentação
├── logs/                  # Logs do sistema
├── public/               # Arquivos públicos
│   ├── css/             # Estilos
│   ├── js/              # Scripts
│   ├── images/          # Imagens
│   ├── .htaccess        # Configuração Apache
│   └── index.php        # Ponto de entrada
└── src/                  # Código fonte
    ├── controllers/      # Controllers
    ├── models/          # Models
    ├── views/           # Views
    │   ├── auth/       # Páginas de autenticação
    │   ├── dashboard/  # Dashboard
    │   └── layout/     # Layout base
    ├── middleware/      # Middlewares
    ├── Database.php     # Classe de conexão
    └── helpers.php      # Funções auxiliares
```

## 🔒 Segurança

- ✅ Senhas criptografadas com bcrypt
- ✅ Proteção CSRF em todos os formulários
- ✅ Validação e sanitização de inputs
- ✅ Headers de segurança configurados
- ✅ Proteção contra SQL Injection (PDO)
- ✅ Proteção contra XSS
- ✅ Bloqueio por tentativas de login
- ✅ Logs de auditoria

## 🎨 Design

- Interface moderna com gradientes
- Animações suaves e transições
- Totalmente responsivo
- Ícones Font Awesome 6
- Fonte Inter do Google Fonts
- Máscaras automáticas para campos (CNPJ, telefone, CEP)
- Busca automática de endereço por CEP (ViaCEP)

## 📊 Perfis de Acesso

| Perfil | Nível | Permissões |
|--------|-------|------------|
| Master | 100 | Acesso total ao sistema |
| Admin | 80 | Gerencia empresas e usuários |
| Gestor | 60 | Gerencia projetos e atividades |
| Usuário | 40 | Acesso básico ao sistema |

## 🚧 Próximas Sprints

### Sprint 4: Gestão de Projetos
- CRUD completo de projetos
- Vinculação de empresas aos projetos
- Status e prazos

### Sprint 5: Gestão de Atividades
- CRUD de atividades
- Vinculação a projetos
- Acompanhamento de status

### Sprint 6: Gestão Financeira
- Cadastro de custos
- Valores a pagar por prestador
- Relatórios financeiros por período

## 📝 Metodologia

Este projeto segue a metodologia **Scrum** com sprints curtas e incrementais:

- ✅ **Sprint 1-3**: Autenticação e Base (CONCLUÍDA)
- 🔄 **Sprint 4**: Gestão de Projetos (PRÓXIMA)
- ⏳ **Sprint 5**: Gestão de Atividades
- ⏳ **Sprint 6**: Gestão Financeira

## 📞 Suporte

Para dúvidas ou problemas, verifique os logs em:
- `logs/activity_YYYY-MM-DD.log` - Logs de atividade
- `logs/php_errors_YYYY-MM-DD.log` - Erros PHP

## 📄 Licença

© 2024 Clinfec - Todos os direitos reservados

---

**Desenvolvido com ❤️ usando Metodologia Scrum**
