# 📘 MANUAL COMPLETO DE INSTALAÇÃO E USO - SISTEMA CLINFEC

## 🎯 Visão Geral

Este manual contém TODAS as instruções necessárias para instalar, configurar e usar o Sistema de Gestão Clinfec - Prestadores de Serviços.

**Sistema:** Gestão Completa de Empresas, Serviços e Contratos  
**Versão:** 1.0.0  
**Data:** Novembro 2025  
**Desenvolvido para:** Clinfec

---

## 📋 ÍNDICE COMPLETO

1. [Requisitos do Sistema](#requisitos)
2. [Instalação em Servidor Local](#instalacao-local)
3. [Instalação no Hostinger](#instalacao-hostinger)
4. [Configuração do Banco de Dados](#configuracao-banco)
5. [Configuração do Sistema](#configuracao-sistema)
6. [Primeiro Acesso](#primeiro-acesso)
7. [Manual de Uso Completo](#manual-uso)
8. [Troubleshooting](#troubleshooting)
9. [Manutenção e Backup](#manutencao)
10. [Suporte](#suporte)

---

## 1. REQUISITOS DO SISTEMA {#requisitos}

### Requisitos Mínimos do Servidor

#### Software Necessário:
- **PHP:** 7.4 ou superior (recomendado 8.0+)
- **MySQL:** 5.7 ou superior (recomendado 8.0+)
- **Apache:** 2.4 ou superior
- **Módulos PHP obrigatórios:**
  - pdo
  - pdo_mysql
  - mbstring
  - json
  - session
  - fileinfo
  - gd (para manipulação de imagens)

#### Configurações PHP Mínimas:
```ini
upload_max_filesize = 15M
post_max_size = 15M
max_execution_time = 300
memory_limit = 256M
```

#### Espaço em Disco:
- **Mínimo:** 50 MB para sistema
- **Recomendado:** 500 MB (incluindo espaço para uploads)

#### Navegadores Compatíveis:
- Google Chrome 90+
- Mozilla Firefox 88+
- Microsoft Edge 90+
- Safari 14+

---

## 2. INSTALAÇÃO EM SERVIDOR LOCAL {#instalacao-local}

### Passo 1: Preparar o Ambiente

#### Opção A: XAMPP (Windows/Mac/Linux)

1. **Baixar XAMPP:**
   - Acesse: https://www.apachefriends.org/
   - Baixe a versão com PHP 7.4 ou superior
   - Execute o instalador

2. **Instalar XAMPP:**
   ```
   - Selecione: Apache, MySQL, PHP
   - Pasta de instalação: C:\xampp (Windows) ou /opt/lampp (Linux)
   - Complete a instalação
   ```

3. **Iniciar Serviços:**
   - Abra o XAMPP Control Panel
   - Clique em "Start" para Apache
   - Clique em "Start" para MySQL

#### Opção B: WAMP (Windows)

1. **Baixar WAMP:**
   - Acesse: https://www.wampserver.com/
   - Baixe a versão 64 bits
   - Execute o instalador

2. **Configurar WAMP:**
   - Instale na pasta padrão: C:\wamp64
   - Inicie o WampServer
   - Aguarde o ícone ficar verde

### Passo 2: Obter os Arquivos do Sistema

#### Método 1: Clone do GitHub

```bash
# Navegue até a pasta do servidor web
cd C:\xampp\htdocs  # Windows XAMPP
# OU
cd /opt/lampp/htdocs  # Linux XAMPP
# OU
cd C:\wamp64\www  # Windows WAMP

# Clone o repositório
git clone https://github.com/fmunizmcorp/prestadores.git clinfec

# Entre na pasta
cd clinfec
```

#### Método 2: Download ZIP

1. Acesse: https://github.com/fmunizmcorp/prestadores
2. Clique em "Code" → "Download ZIP"
3. Extraia o ZIP para:
   - `C:\xampp\htdocs\clinfec` (XAMPP)
   - `C:\wamp64\www\clinfec` (WAMP)

### Passo 3: Configurar Permissões (Linux/Mac)

```bash
cd /opt/lampp/htdocs/clinfec

# Definir proprietário (substitua 'www-data' pelo usuário do Apache)
sudo chown -R www-data:www-data .

# Permissões para diretórios
sudo find . -type d -exec chmod 755 {} \;

# Permissões para arquivos
sudo find . -type f -exec chmod 644 {} \;

# Permissões especiais para uploads e logs
sudo chmod -R 777 public/uploads
sudo chmod -R 777 logs
```

### Passo 4: Criar o Banco de Dados

1. **Acessar phpMyAdmin:**
   - Abra o navegador
   - Acesse: http://localhost/phpmyadmin
   - Login: root (sem senha por padrão)

2. **Criar o Banco:**
   ```sql
   CREATE DATABASE clinfec_prestadores CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Criar Usuário (Recomendado):**
   ```sql
   CREATE USER 'clinfec_user'@'localhost' IDENTIFIED BY 'senha_forte_aqui';
   GRANT ALL PRIVILEGES ON clinfec_prestadores.* TO 'clinfec_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

### Passo 5: Configurar o Sistema

1. **Editar config/database.php:**

```php
<?php
return [
    'host' => 'localhost',
    'database' => 'clinfec_prestadores',
    'username' => 'clinfec_user',  // ou 'root'
    'password' => 'senha_forte_aqui',  // ou '' se for root local
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];
```

2. **Criar Diretórios de Upload:**

```bash
mkdir -p public/uploads/logos
mkdir -p public/uploads/documentos
mkdir -p public/uploads/contratos
mkdir -p logs
```

### Passo 6: Testar a Instalação

1. **Acessar o Sistema:**
   - Abra o navegador
   - Acesse: http://localhost/clinfec/public

2. **Verificar Migrations:**
   - O sistema executará automaticamente as migrations
   - Aguarde a mensagem de sucesso
   - Você será redirecionado para a tela de login

3. **Login Inicial:**
   - **Email:** admin@clinfec.com.br
   - **Senha:** admin123

---

## 3. INSTALAÇÃO NO HOSTINGER {#instalacao-hostinger}

### Passo 1: Acessar o Painel Hostinger

1. **Login no Hostinger:**
   - Acesse: https://www.hostinger.com.br
   - Faça login com suas credenciais
   - Vá para o painel de controle (hPanel)

### Passo 2: Preparar o Banco de Dados

1. **Criar Banco de Dados MySQL:**
   - No hPanel, clique em "Bancos de Dados MySQL"
   - Clique em "Criar novo banco de dados"
   - Nome sugerido: `u673902663_prestadores`
   - Anote o nome completo do banco

2. **Criar Usuário do Banco:**
   - Na mesma tela, crie um novo usuário
   - Nome de usuário: `u673902663_clinfec`
   - Senha: Gere uma senha forte
   - **IMPORTANTE:** Anote usuário e senha

3. **Associar Usuário ao Banco:**
   - Adicione o usuário ao banco de dados
   - Conceda TODOS os privilégios
   - Clique em "Adicionar"

### Passo 3: Upload dos Arquivos

#### Método 1: File Manager (Mais Fácil)

1. **Acessar File Manager:**
   - No hPanel, clique em "Gerenciador de Arquivos"
   - Navegue até a pasta `public_html`

2. **Upload dos Arquivos:**
   - Clique em "Upload"
   - Selecione TODOS os arquivos do sistema
   - Aguarde o upload completar

3. **Estrutura Final:**
   ```
   public_html/
   ├── config/
   ├── database/
   ├── docs/
   ├── logs/
   ├── public/
   ├── src/
   └── README.md
   ```

#### Método 2: FTP

1. **Obter Credenciais FTP:**
   - No hPanel, vá em "Contas FTP"
   - Use a conta principal ou crie uma nova
   - Anote: Host, Usuário, Senha, Porta

2. **Conectar via FTP:**
   - Use FileZilla ou outro cliente FTP
   - Host: ftp.seudominio.com.br
   - Usuário: usuario@seudominio.com.br
   - Senha: sua_senha_ftp
   - Porta: 21

3. **Fazer Upload:**
   - Navegue até `public_html`
   - Arraste todos os arquivos do sistema
   - Aguarde a transferência

### Passo 4: Configurar o Sistema no Hostinger

1. **Editar config/database.php:**
   - Use o File Manager do Hostinger
   - Abra `config/database.php`
   - Edite com as informações do banco:

```php
<?php
return [
    'host' => 'localhost',  // Sempre localhost no Hostinger
    'database' => 'u673902663_prestadores',  // Seu banco
    'username' => 'u673902663_clinfec',  // Seu usuário
    'password' => 'sua_senha_forte',  // Sua senha
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];
```

2. **Ajustar .htaccess (se necessário):**

```apache
# Se seu domínio está em uma subpasta
RewriteBase /
RewriteRule ^(.*)$ public/$1 [L]
```

### Passo 5: Configurar Permissões no Hostinger

1. **Via File Manager:**
   - Selecione a pasta `public/uploads`
   - Clique com botão direito → "Permissões"
   - Defina para 777 (todos)
   - Marque "Aplicar recursivamente"

2. **Repetir para:**
   - `logs/` - Permissão 777
   - `public/uploads/logos/` - Permissão 777
   - `public/uploads/documentos/` - Permissão 777
   - `public/uploads/contratos/` - Permissão 777

### Passo 6: Apontar o Domínio

1. **Configurar Document Root:**
   - No hPanel, vá em "Domínios"
   - Selecione seu domínio
   - Clique em "Configurações"
   - Document Root: `/public_html/public`
   - Salve

2. **Ou use .htaccess na raiz:**

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^$ public/ [L]
    RewriteRule (.*) public/$1 [L]
</IfModule>
```

### Passo 7: Testar no Hostinger

1. **Acessar o Sistema:**
   - Abra: https://seudominio.com.br
   - Aguarde as migrations automáticas
   - Você será redirecionado para o login

2. **Login Inicial:**
   - Email: admin@clinfec.com.br
   - Senha: admin123

3. **IMPORTANTE - Alterar Senha:**
   - Após primeiro login, vá em Perfil
   - Altere a senha padrão imediatamente

---

## 4. CONFIGURAÇÃO DO BANCO DE DADOS {#configuracao-banco}

### Estrutura do Banco

O sistema possui 14 tabelas principais:

#### Tabelas de Autenticação:
- `usuarios` - Usuários do sistema
- `perfis` - Perfis de acesso (Master, Admin, Gestor, Usuario)

#### Tabelas de Empresas:
- `empresas_tomadoras` - Empresas clientes
- `empresas_tomadoras_responsaveis` - Contatos das tomadoras
- `empresas_tomadoras_documentos` - Documentos anexados
- `empresas_prestadoras` - Empresas fornecedoras
- `empresas_prestadoras_certificacoes` - Certificações das prestadoras
- `empresas_prestadoras_servicos` - Serviços oferecidos

#### Tabelas de Serviços:
- `servicos` - Catálogo de serviços
- `servicos_requisitos` - Requisitos por serviço
- `servicos_valores_historico` - Histórico de valores

#### Tabelas de Contratos:
- `contratos` - Contratos principais
- `contratos_servicos` - Serviços por contrato
- `contratos_aditivos` - Aditivos contratuais

### Migrations Automáticas

O sistema executa migrations automaticamente na primeira requisição:

1. **001_migration.sql** - Estrutura base (usuários, perfis)
2. **002_empresas_contratos.sql** - Módulos principais

**Logs de Migration:**
- Localização: `logs/migrations.log`
- Verificar erros: Caso alguma migration falhe, o sistema registra no log

### Seed de Dados Iniciais

O sistema cria automaticamente:

1. **Usuário Master:**
   - Email: admin@clinfec.com.br
   - Senha: admin123
   - Perfil: Master (acesso total)

2. **Perfis Padrão:**
   - Master - Acesso total
   - Admin - Administrativo
   - Gestor - Gestão operacional
   - Usuario - Consulta

---

## 5. CONFIGURAÇÃO DO SISTEMA {#configuracao-sistema}

### Arquivo config/app.php

Configurações gerais da aplicação:

```php
<?php
return [
    // Informações da Aplicação
    'name' => 'Sistema Clinfec - Prestadores',
    'version' => '1.0.0',
    'timezone' => 'America/Sao_Paulo',
    
    // URLs
    'url' => 'http://localhost/clinfec/public',  // Local
    // 'url' => 'https://seudominio.com.br',  // Produção
    
    // Sessão
    'session_lifetime' => 7200,  // 2 horas em segundos
    'session_name' => 'clinfec_session',
    
    // Segurança
    'csrf_token_name' => 'csrf_token',
    'password_min_length' => 8,
    
    // Uploads
    'upload_max_size' => 15728640,  // 15MB em bytes
    'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'],
    
    // Logs
    'log_level' => 'debug',  // debug, info, warning, error
    'log_path' => ROOT_PATH . '/logs',
];
```

### Arquivo config/database.php

Já configurado nas seções anteriores. Sempre revise:

```php
<?php
return [
    'host' => 'localhost',
    'database' => 'nome_do_banco',
    'username' => 'usuario',
    'password' => 'senha',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',  // Prefixo para tabelas (opcional)
];
```

### Arquivo config/version.php

Controle de versão:

```php
<?php
return [
    'version' => '1.0.0',
    'release_date' => '2025-11-04',
    'database_version' => '002',  // Última migration executada
    'changelog' => [
        '1.0.0' => [
            'Sprint 4 completo',
            'CRUDs de Empresas, Serviços e Contratos',
            '30.000+ linhas de código',
        ],
    ],
];
```

---

## 6. PRIMEIRO ACESSO {#primeiro-acesso}

### Passo a Passo do Primeiro Login

1. **Acessar a URL:**
   - Local: http://localhost/clinfec/public
   - Produção: https://seudominio.com.br

2. **Tela de Login:**
   - Você verá uma tela moderna com gradiente azul
   - Campos: Email e Senha

3. **Credenciais Iniciais:**
   ```
   Email: admin@clinfec.com.br
   Senha: admin123
   ```

4. **Primeiro Login:**
   - Digite as credenciais
   - Clique em "Entrar"
   - Você será redirecionado para o Dashboard

5. **IMPORTANTE - Alterar Senha:**
   - No canto superior direito, clique no nome do usuário
   - Selecione "Meu Perfil"
   - Vá na aba "Segurança"
   - Altere a senha padrão para uma senha forte

### Dashboard Inicial

Após o login, você verá:

#### 4 Cards de Estatísticas:
1. **Empresas Tomadoras** - Total de clientes cadastrados
2. **Empresas Prestadoras** - Total de fornecedores
3. **Serviços Ativos** - Catálogo de serviços
4. **Contratos Ativos** - Contratos em vigência

#### Ações Rápidas:
- Nova Empresa Tomadora
- Nova Empresa Prestadora
- Novo Serviço
- Novo Contrato

#### Alertas:
- Contratos vencendo nos próximos 90 dias
- Documentos expirando
- Pendências importantes

---

## 7. MANUAL DE USO COMPLETO {#manual-uso}

### 7.1. GESTÃO DE EMPRESAS TOMADORAS (CLIENTES)

#### Listar Empresas Tomadoras

1. **Acessar:**
   - Menu lateral → "Empresas" → "Tomadoras"
   - Ou Dashboard → Card "Empresas Tomadoras" → "Ver todas"

2. **Filtros Disponíveis:**
   - **Busca:** Nome, razão social ou CNPJ
   - **Status:** Ativas / Inativas / Todas
   - **Estado:** UF
   - **Itens por página:** 10, 20, 50, 100

3. **Ações na Listagem:**
   - 👁️ **Visualizar** - Ver detalhes completos
   - ✏️ **Editar** - Modificar informações
   - 🗑️ **Excluir** - Remover (soft delete)

#### Cadastrar Nova Empresa Tomadora

1. **Acessar:**
   - Empresas → Tomadoras → Botão "Nova Empresa Tomadora"

2. **Seção 1 - Dados Principais:**
   - **CNPJ** (obrigatório) - com validação automática
   - **Razão Social** (obrigatório)
   - **Nome Fantasia** (obrigatório)
   - **Inscrição Estadual**
   - **Inscrição Municipal**
   - **Logo da Empresa** (upload JPG/PNG, max 2MB)
   - **Status:** Ativa/Inativa

3. **Seção 2 - Endereço:**
   - **CEP** - com busca automática (ViaCEP)
   - **Logradouro** - preenchido automaticamente
   - **Número**
   - **Complemento**
   - **Bairro** - preenchido automaticamente
   - **Cidade** - preenchida automaticamente
   - **Estado** - preenchido automaticamente

4. **Seção 3 - Contatos:**
   - **Email Principal** (obrigatório)
   - **Telefone Principal**
   - **Telefone Secundário**
   - **Site**
   - **Redes Sociais**

5. **Seção 4 - Dados Financeiros:**
   - **Dia de Fechamento** (1-31)
   - **Dia de Vencimento** (1-31)
   - **Forma de Pagamento Preferencial**
   - **Banco** / **Agência** / **Conta**

6. **Seção 5 - Observações:**
   - Campo livre para anotações gerais

7. **Salvar:**
   - Botão "Cadastrar Empresa Tomadora"
   - Sistema valida todos os campos
   - Redireciona para visualização

#### Visualizar Empresa Tomadora

Após cadastrar ou clicar em "Visualizar", você verá 5 abas:

##### Aba 1: Dados Principais
- Todas as informações da empresa
- Logo (se cadastrada)
- Endereço completo
- Contatos
- Dados financeiros

##### Aba 2: Responsáveis
- **Lista de Responsáveis:**
  - Nome
  - Cargo
  - Email
  - Telefone
  - Departamento

- **Adicionar Responsável:**
  - Botão "+ Adicionar Responsável"
  - Modal com formulário completo
  - Nome, cargo, email, telefone, celular, departamento

##### Aba 3: Documentos
- **Lista de Documentos:**
  - Tipo de documento
  - Número
  - Data de emissão
  - Data de validade
  - Status (válido/vencido)
  - Ações: Download / Excluir

- **Adicionar Documento:**
  - Botão "+ Adicionar Documento"
  - Tipo: Contrato Social, Certidões, Alvarás, etc.
  - Upload de arquivo (PDF, max 5MB)
  - Número do documento
  - Datas de emissão e validade

##### Aba 4: Contratos
- Lista de contratos desta empresa
- Número do contrato
- Valor
- Vigência
- Status
- Link para visualizar contrato

##### Aba 5: Projetos
- Lista de projetos vinculados
- (Será implementado no Sprint 5)

### 7.2. GESTÃO DE EMPRESAS PRESTADORAS (FORNECEDORES)

**Processo idêntico às Empresas Tomadoras**, com adições:

#### Campos Específicos de Prestadoras:

1. **Certificações:**
   - Nome da certificação
   - Entidade certificadora
   - Número do certificado
   - Data de emissão
   - Data de validade
   - Upload do certificado

2. **Serviços Oferecidos:**
   - Selecionar serviços do catálogo
   - Definir se está qualificada
   - Adicionar observações

3. **Qualificações:**
   - Áreas de atuação
   - Especialidades
   - Experiência

### 7.3. GESTÃO DE SERVIÇOS

#### Listar Serviços

1. **Acessar:**
   - Menu → "Cadastros" → "Serviços"

2. **Filtros:**
   - Busca por código, nome ou descrição
   - Status (Ativo/Inativo)
   - Tipo de serviço
   - Com/Sem requisitos
   - Itens por página

3. **Cards Estatísticos:**
   - Total de serviços
   - Ativos
   - Com requisitos
   - Com valor de referência

#### Cadastrar Novo Serviço

1. **Seção 1 - Dados Principais:**
   - **Código** (obrigatório, único)
   - **Nome do Serviço** (obrigatório)
   - **Descrição Detalhada**
   - **Tipo:** Técnico, Operacional, Administrativo, Gerencial, Especializado
   - **Categoria**
   - **CBO** - Classificação Brasileira de Ocupações
   - **Carga Horária Semanal** (horas)
   - **Jornada Padrão**
   - **Atividades Principais** (lista)
   - **Permite Teletrabalho:** Sim/Não
   - **Status:** Ativo/Inativo

2. **Seção 2 - Requisitos e Qualificações:**
   - **Escolaridade Mínima:**
     - Fundamental, Médio, Técnico, Superior, Pós, Mestrado, Doutorado
   - **Experiência Mínima** (anos)
   - **Certificações Obrigatórias** (lista)
   - **Certificações Desejáveis** (lista)
   - **Habilidades Técnicas** (lista)
   - **Habilidades Comportamentais** (lista)
   - **Idiomas Requeridos**
   - **CNH Obrigatória:** Categoria A, B, C, D, E, AB

3. **Seção 3 - Valores de Referência:**
   - **Valor de Referência** (R$)
   - **Tipo de Valor:** Por Hora, Por Dia, Mensal, Por Projeto
   - **Moeda:** BRL, USD, EUR
   - **Vigência do Valor:**
     - Data início
     - Data fim
   - **Observações sobre Valores**

4. **Seção 4 - Informações Complementares:**
   - **Local de Trabalho Padrão**
   - **Equipamentos Necessários**
   - **Uniformes e EPIs**
   - **Benefícios Padrão**
   - **Observações Gerais**
   - **Código Interno**
   - **Nível de Complexidade:** Básico, Intermediário, Avançado, Especialista

#### Visualizar Serviço

4 Abas Principais:

##### Aba 1: Dados Principais
- Informações básicas
- Descrição e atividades
- Informações complementares

##### Aba 2: Requisitos
- Requisitos básicos
- Certificações obrigatórias e desejáveis
- Habilidades técnicas e comportamentais

##### Aba 3: Valores
- Valor atual de referência
- Vigência do valor
- Histórico de valores (quando alterado)

##### Aba 4: Contratos
- Lista de contratos que utilizam este serviço
- Quantidade de postos
- Valor no contrato
- Link para o contrato

### 7.4. GESTÃO DE CONTRATOS

#### Listar Contratos

1. **Acessar:**
   - Menu → "Contratos"

2. **Alertas Automáticos:**
   - Contratos vencendo nos próximos 90 dias
   - Destacados em amarelo
   - Contagem de dias restantes

3. **Filtros:**
   - Busca por número ou empresa
   - Status: Ativo, Suspenso, Encerrado, Vencido
   - Tipo de contrato
   - Empresa tomadora
   - Itens por página

4. **Cards Estatísticos:**
   - Total de contratos
   - Contratos ativos
   - Vencendo em 90 dias
   - Valor total mensal

#### Cadastrar Novo Contrato

**Seção 1 - Dados Principais do Contrato:**

1. **Identificação:**
   - **Número do Contrato** (obrigatório, único)
   - **Empresa Tomadora** (obrigatório, select com busca)
   - **Objeto do Contrato** (obrigatório, textarea)

2. **Tipo e Modalidade:**
   - **Tipo:** Prestação de Serviços, Fornecimento, Outsourcing, Consultoria, Misto
   - **Número do Processo**
   - **Modalidade:** Concorrência, Tomada de Preços, Convite, Pregão, Dispensa, Inexigibilidade

3. **Datas:**
   - **Data de Assinatura** (obrigatório)
   - **Data de Início** (obrigatório)
   - **Data de Término** (obrigatório)
   - **Prazo em Meses** (calculado automaticamente)

4. **Renovação:**
   - **Renovação Automática:** Sim/Não
   - **Prazo de Renovação** (meses)

5. **Documento:**
   - **Status:** Ativo, Suspenso, Encerrado, Vencido
   - **Arquivo do Contrato** (PDF, max 15MB)

**Seção 2 - Informações Financeiras:**

1. **Valores:**
   - **Valor Total do Contrato** (obrigatório, R$)
   - **Tipo de Valor:** Mensal, Anual, Total, Por Demanda
   - **Moeda:** Real (BRL), Dólar (USD), Euro (EUR)

2. **Pagamento:**
   - **Forma de Pagamento:** Boleto, PIX, Transferência, Cheque
   - **Dia de Vencimento** (1-31)
   - **Periodicidade:** Mensal, Bimestral, Trimestral, Semestral, Anual

3. **Reajuste:**
   - **Índice de Reajuste:** IPCA, IGP-M, INPC, IPC
   - **Data do Próximo Reajuste**
   - **Observações Financeiras**

**Seção 3 - Gestores e Responsáveis:**

1. **Gestor do Contrato:**
   - Nome completo
   - Email

2. **Fiscal do Contrato:**
   - Nome completo
   - Email

**Seção 4 - Observações e Cláusulas:**

1. **Cláusulas Importantes** (textarea)
2. **Observações Gerais** (textarea)

**Validações Automáticas:**
- Data de término >= Data de início
- Data de assinatura <= Data de início
- Cálculo automático do prazo em meses
- CNPJ da empresa válido

#### Visualizar Contrato

5 Abas Completas:

##### Aba 1: Dados Principais
- **Informações do Contrato:**
  - Número, tipo, modalidade
  - Processo
  - Datas e prazos
  - Renovação
  - Status

- **Empresa Tomadora:**
  - Dados completos da empresa
  - Link para visualizar empresa

- **Objeto do Contrato:**
  - Descrição completa

- **Gestores:**
  - Gestor do contrato
  - Fiscal do contrato

- **Cláusulas e Observações**

##### Aba 2: Financeiro
- **Valor do Contrato:**
  - Valor total
  - Tipo de valor
  - Moeda

- **Informações de Pagamento:**
  - Forma de pagamento
  - Dia de vencimento
  - Periodicidade
  - Índice de reajuste
  - Próximo reajuste
  - Observações financeiras

##### Aba 3: Serviços
- **Lista de Serviços Contratados:**
  - Código do serviço
  - Nome do serviço
  - Quantidade de postos
  - Valor unitário
  - Valor total
  - **Total Geral dos Serviços**

- **Adicionar Serviço:**
  - Botão "+ Adicionar Serviço"
  - Modal com:
    - Seleção do serviço
    - Quantidade de postos
    - Valor unitário
    - Observações

##### Aba 4: Aditivos
- **Lista de Aditivos:**
  - Número do aditivo
  - Tipo: Prazo, Valor, Escopo, Misto
  - Data do aditivo
  - Descrição
  - Valor (se aplicável)
  - Arquivo PDF

- **Adicionar Aditivo:**
  - Botão "+ Adicionar Aditivo"
  - Modal com formulário completo
  - Upload do documento

##### Aba 5: Histórico
- **Timeline de Alterações:**
  - Data e hora
  - Usuário responsável
  - Ação realizada
  - Descrição da alteração

---

## 8. TROUBLESHOOTING {#troubleshooting}

### Problemas Comuns e Soluções

#### 8.1. Erro ao Acessar o Sistema

**Problema:** "Erro 500 - Internal Server Error"

**Soluções:**
1. Verificar logs de erro do Apache:
   ```bash
   # Linux
   tail -f /var/log/apache2/error.log
   
   # Windows XAMPP
   C:\xampp\apache\logs\error.log
   ```

2. Verificar permissões de diretórios:
   ```bash
   chmod -R 755 /caminho/do/sistema
   chmod -R 777 logs/
   chmod -R 777 public/uploads/
   ```

3. Verificar módulos PHP necessários:
   ```bash
   php -m | grep -E 'pdo|mysql|mbstring|json'
   ```

#### 8.2. Erro de Conexão com Banco de Dados

**Problema:** "SQLSTATE[HY000] [1045] Access denied"

**Soluções:**
1. Verificar credenciais em `config/database.php`
2. Testar conexão manualmente:
   ```bash
   mysql -u usuario -p -h localhost banco
   ```

3. Verificar se o banco existe:
   ```sql
   SHOW DATABASES LIKE 'clinfec%';
   ```

4. Recriar usuário se necessário:
   ```sql
   DROP USER 'usuario'@'localhost';
   CREATE USER 'usuario'@'localhost' IDENTIFIED BY 'senha';
   GRANT ALL ON banco.* TO 'usuario'@'localhost';
   FLUSH PRIVILEGES;
   ```

#### 8.3. Migrations Não Executam

**Problema:** Tabelas não são criadas automaticamente

**Soluções:**
1. Executar migrations manualmente:
   ```bash
   # Via phpMyAdmin
   # Importar: database/migrations/001_migration.sql
   # Importar: database/migrations/002_empresas_contratos.sql
   ```

2. Verificar logs:
   ```bash
   cat logs/migrations.log
   ```

3. Limpar cache e tentar novamente:
   ```bash
   rm -rf logs/*
   # Acessar sistema novamente
   ```

#### 8.4. Erro de Upload de Arquivos

**Problema:** "Arquivo muito grande" ou upload falha

**Soluções:**
1. Aumentar limites no PHP (php.ini):
   ```ini
   upload_max_filesize = 15M
   post_max_size = 15M
   max_execution_time = 300
   ```

2. Reiniciar Apache após alterar php.ini

3. Verificar permissões:
   ```bash
   chmod 777 public/uploads
   chmod 777 public/uploads/logos
   chmod 777 public/uploads/documentos
   chmod 777 public/uploads/contratos
   ```

#### 8.5. Sessão Expira Rapidamente

**Problema:** Logout automático frequente

**Soluções:**
1. Aumentar tempo de sessão em `config/app.php`:
   ```php
   'session_lifetime' => 14400,  // 4 horas
   ```

2. Verificar configuração de sessão do PHP:
   ```ini
   session.gc_maxlifetime = 14400
   session.cookie_lifetime = 14400
   ```

#### 8.6. Erro de CSRF Token

**Problema:** "Token CSRF inválido ou expirado"

**Soluções:**
1. Limpar cookies do navegador
2. Verificar se sessões estão funcionando:
   ```bash
   # Verificar se diretório de sessões existe e tem permissão
   ls -la /var/lib/php/sessions
   ```

3. Recarregar a página e tentar novamente

#### 8.7. Página em Branco

**Problema:** Tela branca sem erro visível

**Soluções:**
1. Ativar exibição de erros temporariamente:
   ```php
   // No início de public/index.php
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);
   ```

2. Verificar logs do PHP:
   ```bash
   tail -f /var/log/php_errors.log
   ```

3. Verificar se todos os arquivos foram enviados corretamente

#### 8.8. Estilo CSS Não Carrega

**Problema:** Layout quebrado, sem estilos

**Soluções:**
1. Verificar console do navegador (F12)
2. Verificar .htaccess:
   ```apache
   # Permitir acesso a CSS e JS
   <FilesMatch "\.(css|js)$">
       Allow from all
   </FilesMatch>
   ```

3. Limpar cache do navegador (Ctrl+Shift+Del)

4. Verificar caminhos no arquivo `layouts/header.php`

#### 8.9. JavaScript Não Funciona

**Problema:** Máscaras, validações ou modais não funcionam

**Soluções:**
1. Abrir console do navegador (F12) e verificar erros
2. Verificar se jQuery está carregando:
   ```javascript
   console.log(typeof jQuery);  // Deve retornar "function"
   ```

3. Verificar ordem de carregamento dos scripts em `layouts/footer.php`

4. Limpar cache do navegador

---

## 9. MANUTENÇÃO E BACKUP {#manutencao}

### 9.1. Backup do Banco de Dados

#### Via phpMyAdmin:

1. Acessar phpMyAdmin
2. Selecionar banco `clinfec_prestadores`
3. Clicar na aba "Exportar"
4. Método: Rápido
5. Formato: SQL
6. Clicar em "Executar"
7. Salvar arquivo .sql

#### Via Linha de Comando:

```bash
# Backup completo
mysqldump -u usuario -p clinfec_prestadores > backup_$(date +%Y%m%d).sql

# Backup compactado
mysqldump -u usuario -p clinfec_prestadores | gzip > backup_$(date +%Y%m%d).sql.gz

# Backup apenas estrutura
mysqldump -u usuario -p --no-data clinfec_prestadores > estrutura.sql

# Backup apenas dados
mysqldump -u usuario -p --no-create-info clinfec_prestadores > dados.sql
```

#### Agendamento Automático (Linux):

```bash
# Criar script de backup
nano /usr/local/bin/backup_clinfec.sh

#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/clinfec"
mkdir -p $BACKUP_DIR
mysqldump -u usuario -p'senha' clinfec_prestadores | gzip > $BACKUP_DIR/backup_$DATE.sql.gz
# Manter apenas últimos 30 dias
find $BACKUP_DIR -type f -mtime +30 -delete

# Dar permissão de execução
chmod +x /usr/local/bin/backup_clinfec.sh

# Adicionar ao crontab
crontab -e
0 2 * * * /usr/local/bin/backup_clinfec.sh  # Todo dia às 2h
```

### 9.2. Backup dos Arquivos

#### Backup Manual:

```bash
# Linux
cd /var/www/html
tar -czf clinfec_backup_$(date +%Y%m%d).tar.gz clinfec/

# Windows (via 7zip ou WinRAR)
# Compactar pasta completa do sistema
```

#### Backup Apenas de Uploads:

```bash
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz public/uploads/
```

#### Sincronização com Cloud:

```bash
# Exemplo com rclone (Google Drive, Dropbox, etc)
rclone sync /caminho/do/sistema remote:clinfec_backup
```

### 9.3. Restauração de Backup

#### Restaurar Banco de Dados:

```bash
# Via linha de comando
mysql -u usuario -p clinfec_prestadores < backup.sql

# Se for arquivo compactado
gunzip < backup.sql.gz | mysql -u usuario -p clinfec_prestadores
```

#### Via phpMyAdmin:

1. Acessar phpMyAdmin
2. Selecionar banco
3. Clicar em "Importar"
4. Escolher arquivo .sql
5. Clicar em "Executar"

#### Restaurar Arquivos:

```bash
# Extrair backup
tar -xzf clinfec_backup.tar.gz

# Copiar para local correto
cp -r clinfec/* /var/www/html/clinfec/
```

### 9.4. Limpeza e Otimização

#### Limpar Logs Antigos:

```bash
# Limpar logs com mais de 30 dias
find logs/ -type f -mtime +30 -delete

# Ou limpar tudo
rm -rf logs/*.log
```

#### Otimizar Banco de Dados:

```sql
-- Analisar tabelas
ANALYZE TABLE empresas_tomadoras;
ANALYZE TABLE empresas_prestadoras;
ANALYZE TABLE servicos;
ANALYZE TABLE contratos;

-- Otimizar tabelas
OPTIMIZE TABLE empresas_tomadoras;
OPTIMIZE TABLE empresas_prestadoras;
OPTIMIZE TABLE servicos;
OPTIMIZE TABLE contratos;

-- Verificar integridade
CHECK TABLE empresas_tomadoras;
```

#### Limpar Sessões Expiradas:

```bash
# Linux - limpar sessões do PHP
find /var/lib/php/sessions -type f -mtime +7 -delete
```

### 9.5. Atualização do Sistema

#### Preparação:

1. **Fazer backup completo** (banco + arquivos)
2. **Anotar versão atual** (config/version.php)
3. **Colocar sistema em manutenção** (opcional)

#### Atualização:

```bash
# Clone da nova versão
cd /tmp
git clone https://github.com/fmunizmcorp/prestadores.git clinfec_new

# Backup da configuração atual
cp /var/www/html/clinfec/config/database.php /tmp/database.php.backup

# Substituir arquivos (exceto config e uploads)
rsync -av --exclude='config/' --exclude='public/uploads/' \
  /tmp/clinfec_new/ /var/www/html/clinfec/

# Restaurar configuração
cp /tmp/database.php.backup /var/www/html/clinfec/config/database.php

# Executar novas migrations (automático no primeiro acesso)
```

#### Rollback (se necessário):

```bash
# Restaurar backup de arquivos
tar -xzf clinfec_backup_YYYYMMDD.tar.gz -C /var/www/html/

# Restaurar banco
mysql -u usuario -p clinfec_prestadores < backup_YYYYMMDD.sql
```

---

## 10. SUPORTE {#suporte}

### Canais de Suporte

#### Documentação:
- **README.md** - Visão geral e instalação rápida
- **MANUAL_INSTALACAO_COMPLETO.md** - Este manual
- **docs/** - Documentação técnica detalhada

#### Logs do Sistema:
- **logs/migrations.log** - Execução de migrations
- **logs/errors.log** - Erros do sistema
- **logs/access.log** - Acessos e ações

#### Suporte Técnico:
- **Email:** suporte@clinfec.com.br
- **GitHub Issues:** https://github.com/fmunizmcorp/prestadores/issues

### Informações para Suporte

Ao solicitar suporte, forneça:

1. **Versão do Sistema:**
   - Verifique em: Dashboard → Rodapé → Versão

2. **Ambiente:**
   - SO: Windows/Linux/Mac
   - Servidor: Apache/Nginx
   - PHP: Versão
   - MySQL: Versão
   - Navegador: Chrome/Firefox/Edge/Safari

3. **Descrição do Problema:**
   - O que estava tentando fazer
   - O que aconteceu
   - Mensagem de erro (se houver)
   - Screenshot (se possível)

4. **Logs Relevantes:**
   - Copie as últimas linhas de logs/errors.log
   - Inclua mensagens de erro do navegador (Console F12)

### FAQ - Perguntas Frequentes

**Q: Posso usar em servidor compartilhado?**
A: Sim, funciona perfeitamente em hospedagens compartilhadas como Hostinger.

**Q: Preciso de conhecimentos técnicos?**
A: Para instalação básica, não. Para customizações, conhecimentos em PHP são recomendados.

**Q: Posso mudar as cores/logo?**
A: Sim, edite public/css/style.css e troque a logo em public/images/.

**Q: Quantos usuários suporta?**
A: Não há limite de usuários. O limite depende do seu servidor.

**Q: Posso usar em múltiplas empresas?**
A: Sim, é multi-tenant. Cada empresa tomadora é independente.

**Q: Como fazer backup automático?**
A: Configure cron jobs conforme seção 9.1 deste manual.

**Q: Funciona offline?**
A: Não, requer conexão com internet para APIs externas (ViaCEP, CDNs).

**Q: Posso integrar com outros sistemas?**
A: Sim, através da criação de APIs RESTful (requer desenvolvimento).

**Q: O sistema é seguro?**
A: Sim, utiliza prepared statements (SQL Injection), CSRF tokens, password hashing (bcrypt).

**Q: Tem app mobile?**
A: Não nativamente, mas é responsivo e funciona bem em navegadores mobile.

---

## APÊNDICES

### A. Estrutura de Diretórios Completa

```
clinfec/
├── config/                  # Configurações
│   ├── app.php
│   ├── database.php
│   └── version.php
├── database/               # Banco de dados
│   ├── migrations/        # Scripts SQL de migrations
│   │   ├── 001_migration.sql
│   │   └── 002_empresas_contratos.sql
│   └── seeds/             # Dados iniciais
│       └── 001_seed_initial_data.sql
├── docs/                   # Documentação
│   ├── COMECE_AQUI.md
│   ├── INDICE_MESTRE_COMPLETO.md
│   └── ...
├── logs/                   # Logs do sistema
│   ├── .gitkeep
│   ├── migrations.log
│   └── errors.log
├── public/                 # Pasta pública
│   ├── .htaccess
│   ├── index.php          # Entry point
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   ├── app.js
│   │   ├── masks.js
│   │   └── validations.js
│   ├── images/
│   └── uploads/           # Uploads de usuários
│       ├── logos/
│       ├── documentos/
│       └── contratos/
├── src/                    # Código fonte
│   ├── Database.php
│   ├── DatabaseMigration.php
│   ├── helpers.php
│   ├── controllers/       # Controllers MVC
│   │   ├── AuthController.php
│   │   ├── EmpresaTomadoraController.php
│   │   ├── EmpresaPrestadoraController.php
│   │   ├── ServicoController.php
│   │   └── ContratoController.php
│   ├── models/            # Models MVC
│   │   ├── Usuario.php
│   │   ├── EmpresaTomadora.php
│   │   ├── EmpresaPrestadora.php
│   │   ├── Servico.php
│   │   └── Contrato.php
│   └── views/             # Views MVC
│       ├── layouts/
│       │   ├── header.php
│       │   └── footer.php
│       ├── auth/
│       │   └── login.php
│       ├── dashboard/
│       │   └── index.php
│       ├── empresas-tomadoras/
│       │   ├── index.php
│       │   ├── create.php
│       │   ├── edit.php
│       │   └── show.php
│       ├── empresas-prestadoras/
│       │   └── ...
│       ├── servicos/
│       │   └── ...
│       └── contratos/
│           └── ...
├── .gitignore
├── README.md
└── MANUAL_INSTALACAO_COMPLETO.md  # Este arquivo
```

### B. Comandos Úteis

```bash
# Git
git clone https://github.com/fmunizmcorp/prestadores.git
git pull origin main
git status

# MySQL
mysql -u root -p
mysqldump -u usuario -p banco > backup.sql
mysql -u usuario -p banco < backup.sql

# Apache
sudo systemctl restart apache2  # Linux
sudo apachectl restart  # Mac
# Windows: Reiniciar via XAMPP Control Panel

# PHP
php -v  # Versão
php -m  # Módulos instalados
php -i  # Info completa

# Permissões (Linux)
chmod -R 755 .
chmod -R 777 logs/
chmod -R 777 public/uploads/

# Logs
tail -f logs/errors.log
tail -f /var/log/apache2/error.log
```

### C. Glossário de Termos

- **CRUD:** Create, Read, Update, Delete
- **MVC:** Model-View-Controller (arquitetura)
- **PDO:** PHP Data Objects (conexão com banco)
- **OOP:** Object-Oriented Programming
- **RBAC:** Role-Based Access Control
- **Soft Delete:** Exclusão lógica (marca como deletado)
- **Migration:** Script de alteração de banco de dados
- **Seed:** Dados iniciais do banco
- **CSRF:** Cross-Site Request Forgery (ataque)
- **XSS:** Cross-Site Scripting (ataque)
- **PSR-4:** Padrão de autoload do PHP
- **RESTful:** Architectural style para APIs
- **Tomadora:** Empresa cliente (contratante)
- **Prestadora:** Empresa fornecedora (contratada)

---

## CONCLUSÃO

Este manual cobre TODAS as funcionalidades do sistema, desde instalação até uso avançado.

### Checklist de Instalação Completa:

- [ ] Servidor web configurado (Apache)
- [ ] PHP 7.4+ instalado com módulos necessários
- [ ] MySQL 5.7+ instalado
- [ ] Banco de dados criado
- [ ] Arquivos do sistema copiados
- [ ] config/database.php configurado
- [ ] Permissões de diretórios ajustadas
- [ ] Sistema acessível via navegador
- [ ] Migrations executadas automaticamente
- [ ] Login realizado com credenciais padrão
- [ ] Senha padrão alterada

### Próximos Passos Recomendados:

1. **Cadastrar Usuários Reais:**
   - Remova ou desative o usuário admin padrão
   - Crie usuários para cada colaborador

2. **Cadastrar Empresas:**
   - Comece pelas Empresas Tomadoras
   - Depois Empresas Prestadoras

3. **Montar Catálogo de Serviços:**
   - Defina todos os serviços oferecidos
   - Configure requisitos e valores

4. **Registrar Contratos:**
   - Cadastre contratos existentes
   - Configure alertas de vencimento

5. **Treinar Usuários:**
   - Mostre este manual aos usuários
   - Faça testes práticos

### Suporte Contínuo:

Para dúvidas, problemas ou sugestões:
- Consulte primeiro este manual
- Verifique os logs do sistema
- Abra um issue no GitHub
- Entre em contato com o suporte

---

**Sistema Clinfec - Prestadores**  
**Versão 1.0.0**  
**© 2025 Clinfec - Todos os direitos reservados**

---

*Este manual foi gerado automaticamente pelo sistema de desenvolvimento.*  
*Última atualização: Novembro 2025*
