# ⚠️ AÇÃO NECESSÁRIA: Configurar WordPress para Permitir /prestadores/

**Data**: 15/11/2025  
**Sprint**: 33  
**Prioridade**: 🔴 CRÍTICA - Bloqueando todo o sistema

---

## 🔍 PROBLEMA IDENTIFICADO

Durante a implementação do Sprint 33, descobrimos que:

1. ✅ **index.php** funciona em `/prestadores/` (mas tem erro HTTP 500)
2. ❌ **TODOS os outros arquivos .php** retornam erro 404 do WordPress
3. ❌ Mesmo arquivos `.html` são interceptados

**Evidência**:
```bash
# Testes realizados:
https://clinfec.com.br/prestadores/              → HTTP 500 (PHP executa!)
https://clinfec.com.br/prestadores/index.php     → HTTP 500 (PHP executa!)
https://clinfec.com.br/prestadores/test_basic.php → 404 WordPress ❌
https://clinfec.com.br/prestadores/test_direct.html → 404 WordPress ❌
```

## 🎯 CAUSA RAIZ

O WordPress está configurado para **interceptar TODAS as requisições** que vão para `/prestadores/`, EXCETO `index.php`.

Isso significa:
- Você mencionou ter modificado o `.htaccess` da raiz
- Mas a modificação **NÃO está funcionando**
- WordPress continua capturando as requisições

## 📝 SOLUÇÃO NECESSÁRIA

Você precisa adicionar regras ao `.htaccess` do WordPress (na raiz do site) para **EXCLUIR o diretório /prestadores/ do roteamento do WordPress**.

### Localização do arquivo

O `.htaccess` do WordPress está em:
- **Hostinger hPanel**: Gerenciador de Arquivos → `public_html/.htaccess` (do WordPress, não do prestadores)
- **Via FTP**: Não está acessível no nosso FTP (está em outro account/configuração)

### Regras a adicionar

Adicione estas linhas **ANTES** das regras de rewrite do WordPress:

```apache
# ==================== EXCLUIR /prestadores/ DO WORDPRESS ====================
# Sprint 33 - Permitir que aplicação prestadores funcione independentemente

<IfModule mod_rewrite.c>
    # Excluir diretório /prestadores/ do roteamento WordPress
    RewriteCond %{REQUEST_URI} ^/prestadores [NC]
    RewriteRule ^ - [L]
</IfModule>

# ==================== FIM DA EXCLUSÃO ====================
```

### Exemplo completo

Seu `.htaccess` do WordPress deve ficar assim:

```apache
# BEGIN WordPress
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # ==================== EXCLUIR /prestadores/ ====================
    # ADICIONE ESTAS LINHAS AQUI (ANTES das regras do WordPress)
    RewriteCond %{REQUEST_URI} ^/prestadores [NC]
    RewriteRule ^ - [L]
    # ==================== FIM ====================
    
    # Regras originais do WordPress (manter como estão)
    RewriteRule ^index\.php$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /index.php [L]
</IfModule>
# END WordPress
```

## 🧪 COMO TESTAR

Depois de adicionar as regras, teste:

```bash
# Este deve retornar "OK" (não erro 404)
https://clinfec.com.br/prestadores/test_basic.php

# Este deve mostrar HTML (não erro 404)
https://clinfec.com.br/prestadores/test_direct.html
```

Se continuar retornando 404, pode ser necessário:

### Alternativa 1: Configuração no Hostinger hPanel

1. Acesse **hPanel → Websites → clinfec.com.br**
2. Procure por **"WordPress Management"** ou **"File Manager"**
3. Encontre o `.htaccess` na raiz do WordPress
4. Edite e adicione as regras

### Alternativa 2: Desabilitar WordPress temporariamente

Se não conseguir modificar o `.htaccess`:

1. Renomeie `.htaccess` do WordPress para `.htaccess.bak`
2. Teste se `/prestadores/` funciona
3. Se funcionar, crie novo `.htaccess` com as regras corretas
4. Restaure as regras do WordPress depois da exclusão

### Alternativa 3: Usar subdomínio

Se as alternativas acima não funcionarem:

1. Criar subdomínio `prestadores.clinfec.com.br`
2. Apontar para `/public_html/prestadores/`
3. Subdomínios geralmente NÃO passam pelo WordPress

## 📊 IMPACTO

**SEM esta correção**:
- ❌ Sistema NÃO funcionará
- ❌ Todos os módulos retornarão 404
- ❌ AJAX requests falharão
- ❌ Assets (CSS/JS) podem falhar

**COM a correção**:
- ✅ Sistema funcionará normalmente
- ✅ Todos os módulos acessíveis
- ✅ AJAX funcionando
- ✅ Assets carregando

## 🔄 PRÓXIMOS PASSOS

Depois que você aplicar a correção:

1. ✅ Teste os URLs acima
2. ✅ Informe que a correção foi aplicada
3. ✅ Continuarei a correção do erro HTTP 500 no `index.php`
4. ✅ Sistema ficará 100% funcional

## 💡 INFORMAÇÕES ADICIONAIS

### Por que index.php funciona?

WordPress tem uma regra especial:
```apache
RewriteRule ^index\.php$ - [L]
```

Esta linha diz "se for index.php, não processar". Por isso `index.php` executa (mas tem erro 500) enquanto outros arquivos são interceptados.

### Por que não consigo corrigir via FTP?

O `.htaccess` do WordPress está em uma área não acessível pelo nosso FTP. Possíveis motivos:
- WordPress em account/subaccount diferente
- Configuração de permissões do Hostinger
- WordPress em diretório protegido

### Posso fazer isso pelo admin do WordPress?

Não diretamente. WordPress não tem interface para editar `.htaccess` com regras customizadas.

---

## 📞 PRECISA DE AJUDA?

Se tiver dúvidas sobre esta correção:

1. **Verificar o arquivo atual**: Peça para eu criar um script que baixa e analisa o `.htaccess` correto
2. **Criar arquivo corrigido**: Posso gerar o arquivo completo para você copiar/colar
3. **Alternativas**: Posso explorar outras soluções (subdomínio, nginx rules, etc.)

---

**Status**: ⏳ AGUARDANDO AÇÃO MANUAL  
**Bloqueador**: Acesso ao `.htaccess` do WordPress  
**Solução**: Adicionar regras de exclusão conforme acima

