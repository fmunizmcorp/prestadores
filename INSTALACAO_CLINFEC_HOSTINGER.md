# 🚀 INSTALAÇÃO CLINFEC - HOSTINGER

## URL: https://clinfec.com.br/prestadores
## Data: 2025-11-04
## Status: ARQUIVOS CORRIGIDOS E PRONTOS

---

## ✅ ARQUIVOS JÁ CORRIGIDOS NO GITHUB

Todos os arquivos foram atualizados para funcionar em:
- **Local:** `public_html/prestadores/`
- **URL:** `https://clinfec.com.br/prestadores`

---

## 📦 PASSO 1: BAIXAR ARQUIVOS DO GITHUB

### Opção A: Download ZIP (Mais Fácil)

1. Acesse: https://github.com/fmunizmcorp/prestadores
2. Clique no botão verde **"Code"**
3. Clique em **"Download ZIP"**
4. Descompacte o arquivo no seu computador
5. Vá para o **PASSO 2**

### Opção B: Git Clone

```bash
git clone https://github.com/fmunizmcorp/prestadores.git
cd prestadores
```

---

## 📤 PASSO 2: UPLOAD PARA HOSTINGER

### Via File Manager (Recomendado):

1. **Acesse o File Manager** do Hostinger
2. **Navegue até:** `public_html/prestadores/`
3. **DELETE TUDO** que está lá (faça backup se necessário)
4. **Selecione todos os arquivos** do projeto (extraídos do ZIP)
5. **Arraste ou clique em Upload**
6. **Aguarde** o upload completar

### ⚠️ IMPORTANTE: Estrutura Final

Após upload, a estrutura deve ficar:

```
public_html/
└── prestadores/              ← Sua subpasta
    ├── index.php            ← ARQUIVO PRINCIPAL (novo)
    ├── .htaccess            ← CONFIGURAÇÃO APACHE (novo)
    ├── config/              
    │   ├── config.php       ← JÁ CONFIGURADO (novo)
    │   └── database.php     ← JÁ TEM SUAS CREDENCIAIS
    ├── database/
    │   └── migrations/
    ├── src/
    │   ├── controllers/
    │   ├── models/
    │   ├── views/
    │   └── helpers/
    ├── css/
    ├── js/
    ├── uploads/
    ├── docs/
    └── *.md (documentação)
```

---

## 🔧 PASSO 3: VERIFICAR PERMISSÕES

Via File Manager do Hostinger:

### 1. Pasta prestadores:
- Clique direito → **Permissions**
- Configure: **755** (rwxr-xr-x)
- Marque **"Apply to subdirectories"**
- Clique **"Save"**

### 2. Pasta uploads (IMPORTANTE!):
- Entre em `prestadores/uploads/`
- Clique direito → **Permissions**
- Configure: **777** (rwxrwxrwx) - necessário para uploads
- Clique **"Save"**

### 3. Verificar arquivos principais:
```
prestadores/index.php     → 644
prestadores/.htaccess     → 644
prestadores/config/       → 755
```

---

## 🗄️ PASSO 4: VERIFICAR BANCO DE DADOS

O arquivo `config/database.php` JÁ ESTÁ CONFIGURADO com:

```php
'host' => 'localhost',
'database' => 'u673902663_prestadores',
'username' => 'u673902663_admin',
'password' => ';>?I4dtn~2Ga',
```

**NÃO PRECISA ALTERAR** - já está correto!

### Confirmar no Hostinger:

1. Painel Hostinger → **Databases**
2. Verifique se existe: `u673902663_prestadores`
3. Verifique se usuário `u673902663_admin` tem acesso

---

## 🧪 PASSO 5: TESTAR A INSTALAÇÃO

### Teste 1: PHP Básico

Crie `prestadores/test.php`:

```php
<?php
phpinfo();
?>
```

Acesse: **https://clinfec.com.br/prestadores/test.php**

- ✅ Deve mostrar informações do PHP
- ❌ Se der erro 403/500, verifique permissões

### Teste 2: Sistema

Acesse: **https://clinfec.com.br/prestadores/**

- ✅ Deve redirecionar para login
- ❌ Se der erro 500, veja o log de erro (próximo passo)

---

## 🐛 PASSO 6: DEBUG (SE DER ERRO 500)

### Ver Log de Erro:

1. Painel Hostinger → **Files** → **Logs**
2. Abra `error_log`
3. Veja as últimas linhas
4. Me envie o erro exato

### Ativar Debug Temporário:

Edite `prestadores/index.php`, linha 14-15:

```php
// ANTES (produção):
error_reporting(E_ALL);
ini_set('display_errors', 0);  // Desabilitado

// DEPOIS (debug):
error_reporting(E_ALL);
ini_set('display_errors', 1);  // Habilitado
```

**⚠️ IMPORTANTE:** Depois de descobrir o erro, volte para `0`!

---

## 🔐 PASSO 7: PRIMEIRO ACESSO

### Login Padrão:

Acesse: **https://clinfec.com.br/prestadores/**

```
Usuário: admin
Senha: admin123
```

### ⚠️ APÓS PRIMEIRO LOGIN:

1. Vá em **Usuários**
2. Edite o usuário **admin**
3. **TROQUE A SENHA** imediatamente!

---

## ✅ CHECKLIST FINAL

Antes de considerar instalado, verifique:

```
☑ [ ] Arquivos em public_html/prestadores/
☑ [ ] index.php novo (11KB) está no lugar
☑ [ ] .htaccess novo está no lugar
☑ [ ] config/config.php novo está no lugar
☑ [ ] config/database.php tem credenciais corretas
☑ [ ] Permissões: prestadores/ = 755
☑ [ ] Permissões: uploads/ = 777
☑ [ ] test.php funciona (depois delete)
☑ [ ] Sistema abre a tela de login
☑ [ ] Login com admin/admin123 funciona
☑ [ ] Dashboard carrega sem erros
```

---

## 🌐 URLS DO SISTEMA

### Principais:

```
Login:
https://clinfec.com.br/prestadores/

Dashboard:
https://clinfec.com.br/prestadores/?page=dashboard

Empresas Tomadoras:
https://clinfec.com.br/prestadores/?page=empresas-tomadoras

Empresas Prestadoras:
https://clinfec.com.br/prestadores/?page=empresas-prestadoras

Serviços:
https://clinfec.com.br/prestadores/?page=servicos

Contratos:
https://clinfec.com.br/prestadores/?page=contratos
```

---

## 🔄 DIFERENÇAS DOS ARQUIVOS NOVOS

### index.php (NOVO)
- ✅ ROOT_PATH correto para subpasta
- ✅ BASE_URL = '/prestadores'
- ✅ Roteamento via query string
- ✅ Error handling completo
- ✅ Display de erros para debug

### .htaccess (NOVO)
- ✅ RewriteBase /prestadores/
- ✅ HTTPS forçado
- ✅ Proteção de diretórios
- ✅ Gzip habilitado

### config/config.php (NOVO)
- ✅ base_url = 'https://clinfec.com.br/prestadores'
- ✅ upload_url = '/prestadores/uploads/'
- ✅ Todas configurações corretas

---

## 🚨 PROBLEMAS COMUNS

### Problema 1: Erro 403 Forbidden

**Causa:** Permissões incorretas

**Solução:**
```
prestadores/        → 755
prestadores/*.php   → 644
prestadores/uploads → 777
```

### Problema 2: Erro 500 Internal Server Error

**Causas possíveis:**

1. **Erro no código PHP:**
   - Ative `display_errors = 1` no index.php
   - Veja o erro na tela

2. **Erro de banco:**
   - Verifique credenciais no database.php
   - Teste conexão no phpMyAdmin

3. **Erro de .htaccess:**
   - Renomeie .htaccess para .htaccess.backup
   - Se funcionar, o problema é no .htaccess

4. **Classes não encontradas:**
   - Verifique se pasta src/ foi enviada
   - Verifique permissões de src/

### Problema 3: Página em branco

**Causa:** Erro fatal no PHP

**Solução:**
1. Veja o error_log
2. Ative display_errors
3. Verifique se todos os arquivos foram enviados

### Problema 4: CSS/JS não carregam

**Causa:** base_url incorreto

**Solução:**
- Verifique config.php tem: `'base_url' => 'https://clinfec.com.br/prestadores'`

---

## 📞 SUPORTE

Se ainda tiver problemas, me envie:

1. **URL exata** que está acessando
2. **Mensagem de erro** completa (se aparecer)
3. **Últimas 10 linhas** do error_log
4. **Print da estrutura** de public_html/prestadores/
5. **Resultado** ao acessar test.php

---

## 🎉 APÓS INSTALAÇÃO FUNCIONAR

### Próximos Passos:

1. **Delete test.php**
2. **Troque senha do admin**
3. **Configure informações da empresa** (se houver tela)
4. **Crie usuários** para sua equipe
5. **Faça backup** regular do banco de dados
6. **Desabilite debug:**
   - index.php: `display_errors = 0`
   - config.php: `'debug' => false`

### Backup Recomendado:

**Banco de Dados:** Diário via phpMyAdmin  
**Arquivos:** Semanal via FTP  
**Uploads:** Semanal (pasta uploads/)  

---

## 📚 DOCUMENTAÇÃO COMPLETA

No repositório você encontra:

- **MANUAL_INSTALACAO_COMPLETO.md** - Manual completo de uso
- **GUIA_RAPIDO_REFERENCIA.md** - Referência rápida
- **STATUS_FINAL_IMPLEMENTACAO.md** - Arquitetura
- **DEPLOYMENT_READY.md** - Checklist de deployment

---

**FIM DO GUIA DE INSTALAÇÃO** ✅

Sistema pronto para uso em: **https://clinfec.com.br/prestadores**
