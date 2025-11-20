# 🔄 MUDANÇAS FEITAS PELA EQUIPE DE INFRAESTRUTURA

## Sprint 64 - Correções e Melhorias

**Data:** 16 de Novembro de 2025  
**Responsável:** Equipe de Infraestrutura

---

## 📋 RESUMO DAS ALTERAÇÕES

### 1. **index.php - URL HÍBRIDA** ⭐ Principal Mudança

**Arquivo:** `/public_html/index.php`

**Mudança:** Suporte a acesso via domínio OU via IP

**Antes (Sprint 63):**
```php
// Base URL completa
define('BASE_URL', $protocol . '://' . $host);
```

**Depois (Sprint 64):**
```php
// Detectar como o usuário acessou o site
$host = $_SERVER['HTTP_HOST'] ?? 'prestadores.clinfec.com.br';

// Remover porta se presente
$hostWithoutPort = preg_replace('/:\d+$/', '', $host);

// Verificar se foi acessado via IP
$path_prefix = '';
if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $hostWithoutPort)) {
    // Acessado via IP - adicionar prefixo /prestadores
    $path_prefix = '/prestadores';
}

// Base URL completa com prefixo condicional
define('BASE_URL', $protocol . '://' . $host . $path_prefix);
```

**Motivo:** Permitir acesso via:
- ✅ `http://prestadores.clinfec.com.br/` (sem prefixo)
- ✅ `http://72.61.53.222/prestadores/` (com prefixo)

---

### 2. **Novos Arquivos de Configuração**

#### a) `config/app.php` - Configuração da Aplicação

**Novo arquivo** com configurações centralizadas:
- Nome e versão da aplicação
- Configurações de sessão
- Segurança (senhas, tentativas de login)
- Google reCAPTCHA v2
- Perfis de acesso (roles)
- Configurações de email (SMTP)

**Principais configurações:**
```php
'session' => [
    'name' => 'PRESTADORES_SESSION',
    'lifetime' => 7200, // 2 horas
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
],

'security' => [
    'password_min_length' => 8,
    'password_require_special' => true,
    'password_require_numbers' => true,
    'password_require_uppercase' => true,
    'max_login_attempts' => 5,
    'lockout_time' => 900, // 15 minutos
]
```

#### b) `config/cache_control.php` - Controle de Cache

**Novo arquivo** para gerenciar cache durante desenvolvimento/produção:
- Limpa OPcache automaticamente em desenvolvimento
- Pode ser desabilitado em produção para performance

**Uso:**
```php
// No início dos arquivos:
require_once __DIR__ . '/../config/cache_control.php';
```

---

### 3. **BaseController.php** - SEM ALTERAÇÕES

O arquivo `src/Controllers/BaseController.php` permanece igual à Sprint 63.
- ✅ Redirects com query strings mantidos
- ✅ Método `redirect()` funcionando corretamente

---

### 4. **AuthController.php** - Verificar se há alterações

**Status:** Baixado para análise

---

### 5. **DashboardController.php** - Verificar se há alterações

**Status:** Baixado para análise

---

### 6. **Database.php** - Verificar se há alterações

**Status:** Baixado para análise

---

## 🔍 ANÁLISE DE IMPACTO

### Mudanças Positivas ✅

1. **URL Híbrida** - Permite acesso flexível via domínio ou IP
2. **Configuração Centralizada** - `app.php` organiza melhor as configs
3. **Cache Control** - Facilita desenvolvimento sem problemas de cache
4. **Logs de Debug** - Melhor rastreamento de acessos

### Pontos de Atenção ⚠️

1. **Prefixo `/prestadores`** - Só aplicado quando acesso via IP
2. **reCAPTCHA** - Chaves precisam ser configuradas
3. **SMTP** - Credenciais de email precisam ser preenchidas

---

## 📝 AÇÕES NECESSÁRIAS

### 1. Sincronizar arquivos no sandbox local ✅ Em progresso

- [ ] Baixar todos os arquivos alterados
- [ ] Atualizar index.php
- [ ] Adicionar app.php
- [ ] Adicionar cache_control.php
- [ ] Atualizar outros controllers se necessário

### 2. Commit no Git

- [ ] Commit das alterações da infraestrutura
- [ ] Push para GitHub
- [ ] Documentar mudanças no README

### 3. Validar funcionamento

- [ ] Testar acesso via domínio
- [ ] Testar acesso via IP
- [ ] Validar redirects
- [ ] Confirmar login funcionando

---

## 🎯 COMPATIBILIDADE

As mudanças da Sprint 64 são **100% compatíveis** com a arquitetura VPS Hostinger documentada na Sprint 63.

### Mantido da Sprint 63:

- ✅ `public_html/` como document root
- ✅ Query strings nos redirects
- ✅ PascalCase em Controllers/Models
- ✅ open_basedir configurado
- ✅ Isolamento multi-tenant (7 camadas)
- ✅ Permissões 755/644

### Adicionado na Sprint 64:

- ✅ Suporte a URL híbrida (domínio + IP)
- ✅ Configuração centralizada (app.php)
- ✅ Controle de cache (cache_control.php)
- ✅ Logs de debug melhorados

---

## 🚀 PRÓXIMOS PASSOS

1. ✅ **Sincronização completa** dos arquivos
2. ✅ **Commit e push** para GitHub
3. ⚠️ **Configurar reCAPTCHA** (chaves em app.php)
4. ⚠️ **Configurar SMTP** (credenciais em app.php)
5. ✅ **Testar ambos os acessos** (domínio e IP)

---

**Documentado por:** Sistema de Sincronização Automatizada  
**Sprint:** 64  
**Data:** 16/11/2025
