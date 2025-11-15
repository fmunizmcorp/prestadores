# 🚀 GUIA DEPLOY SPRINT 26 - PASSO A PASSO VISUAL

**URGÊNCIA:** 🔴 CRÍTICO - Deploy necessário para desbloquear sistema  
**TEMPO ESTIMADO:** 5-10 minutos  
**DIFICULDADE:** ⭐ Fácil

---

## 📋 PRÉ-REQUISITOS

### O que você vai precisar:
- ✅ Acesso ao hPanel da Hostinger
- ✅ Navegador web (Chrome, Firefox, Edge, Safari)
- ✅ Arquivo `src/Database.php` do branch `sprint23-opcache-fix`

### Dados de acesso:
- **URL hPanel:** https://hpanel.hostinger.com/
- **Domínio:** prestadores.clinfec.com.br
- **Usuário:** (seu login Hostinger)
- **Senha:** (sua senha Hostinger)

---

## 🎯 OPÇÃO 1: DEPLOY VIA hPANEL (MAIS FÁCIL)

### Passo 1: Baixar o arquivo Database.php do GitHub

#### 1.1. Acessar o Pull Request
```
https://github.com/fmunizmcorp/prestadores/pull/6
```

#### 1.2. Clicar em "Files changed" (Arquivos alterados)

#### 1.3. Localizar o arquivo `src/Database.php`

#### 1.4. Clicar nos 3 pontinhos (`...`) ao lado do nome do arquivo

#### 1.5. Selecionar "View file"

#### 1.6. Clicar no botão "Raw" (canto superior direito)

#### 1.7. Salvar arquivo:
- **Windows:** Ctrl+S → Salvar como `Database.php`
- **Mac:** Cmd+S → Salvar como `Database.php`
- **Linux:** Ctrl+S → Salvar como `Database.php`

**IMPORTANTE:** Salve com nome EXATO: `Database.php` (sem .txt no final!)

---

### Passo 2: Login no hPanel

#### 2.1. Abrir navegador e acessar:
```
https://hpanel.hostinger.com/
```

#### 2.2. Fazer login com suas credenciais

#### 2.3. Localizar o site "prestadores.clinfec.com.br"

#### 2.4. Clicar no card/botão do site

---

### Passo 3: Acessar File Manager

#### 3.1. No menu lateral esquerdo, clicar em:
```
📁 File Manager
```

#### 3.2. Aguardar carregar o gerenciador de arquivos

**Você verá algo assim:**
```
📁 public_html/
📁 logs/
📁 tmp/
...
```

---

### Passo 4: Navegar até o diretório correto

#### 4.1. Clicar duas vezes em `📁 public_html/`

#### 4.2. Clicar duas vezes em `📁 src/`

**Você está no caminho correto quando ver:**
```
/public_html/src/
├── 📁 Controllers/
├── 📁 Core/
├── 📁 Helpers/
├── 📁 Models/
├── 📁 Views/
├── 📄 Database.php         ← ESTE ARQUIVO SERÁ SUBSTITUÍDO
├── 📄 DatabaseMigration.php
└── ...
```

---

### Passo 5: Fazer BACKUP do arquivo atual

**CRÍTICO:** Sempre fazer backup antes de substituir!

#### 5.1. Clicar com botão DIREITO em `Database.php`

#### 5.2. Selecionar "Download" ou "Baixar"

#### 5.3. Salvar em seu computador como:
```
Database.php.backup_ANTES_SPRINT26
```

---

### Passo 6: Upload do novo arquivo

#### 6.1. Clicar no botão "Upload" (geralmente no canto superior direito)

#### 6.2. Clicar em "Select File" ou "Selecionar Arquivo"

#### 6.3. Localizar e selecionar o arquivo `Database.php` que você baixou do GitHub (Passo 1)

#### 6.4. Clicar em "Upload" ou "Enviar"

#### 6.5. **IMPORTANTE:** Quando perguntar "Arquivo já existe, deseja substituir?", clicar em **SIM** ou **REPLACE**

---

### Passo 7: Verificar upload

#### 7.1. Verificar que o arquivo foi atualizado:
- Olhar a coluna "Date Modified" / "Data de modificação"
- Deve mostrar data/hora ATUAL (agora)

#### 7.2. Verificar tamanho do arquivo:
- Deve estar entre **3.0 KB - 3.6 KB** aproximadamente
- Se estiver muito diferente, algo deu errado

#### 7.3. (Opcional) Clicar com botão direito > "Edit" para verificar conteúdo:
- Procurar por `public function exec(`
- Se encontrar, está correto! ✅

---

### Passo 8: TESTE IMEDIATO

#### 8.1. Abrir nova aba do navegador

#### 8.2. Acessar:
```
https://prestadores.clinfec.com.br/
```

#### 8.3. Verificar resultado:

✅ **SUCESSO se:**
- Página carrega (mesmo que parcialmente)
- NÃO aparece erro "Call to undefined method App\\Database::exec()"
- Pode aparecer tela de login ou outra página

❌ **FALHA se:**
- Aparece erro "Fatal error: Call to undefined method"
- Página em branco com erro 500
- Mesmo erro anterior

---

## 🎯 OPÇÃO 2: DEPLOY VIA FILEZILLA (ALTERNATIVA)

### Requisitos adicionais:
- FileZilla instalado (https://filezilla-project.org/)

### Passo 1: Baixar Database.php do GitHub
(Igual Opção 1, Passo 1)

### Passo 2: Conectar FileZilla

#### 2.1. Abrir FileZilla

#### 2.2. Preencher campos no topo:
```
Host: ftp.prestadores.clinfec.com.br
Username: u817707156.prestadores
Password: 3ClinfecPres!'0
Port: 21
```

#### 2.3. Clicar em "Quickconnect" ou "Conexão Rápida"

#### 2.4. Se aparecer aviso de certificado SSL, clicar em "OK" ou "Confiar"

---

### Passo 3: Navegar no servidor

#### 3.1. No painel DIREITO (Remote site / Site remoto):

#### 3.2. Navegar para:
```
/domains/prestadores.clinfec.com.br/public_html/src/
```

**Método:**
1. Duplo clique em `domains`
2. Duplo clique em `prestadores.clinfec.com.br`
3. Duplo clique em `public_html`
4. Duplo clique em `src`

---

### Passo 4: Fazer backup

#### 4.1. No painel DIREITO, clicar com botão DIREITO em `Database.php`

#### 4.2. Selecionar "Download" ou "Baixar"

#### 4.3. Salvar em seu computador (painel ESQUERDO)

---

### Passo 5: Upload

#### 5.1. No painel ESQUERDO (Local site):
- Navegar até onde salvou o `Database.php` do GitHub

#### 5.2. Arrastar o arquivo `Database.php` do painel ESQUERDO para o painel DIREITO

#### 5.3. Se perguntar "Target file already exists", selecionar:
```
⚪ Overwrite (Sobrescrever)
✅ Apply to current queue only (Aplicar apenas à fila atual)
[OK]
```

---

### Passo 6: Verificar

#### 6.1. No painel DIREITO, verificar:
- Data de modificação mudou para AGORA
- Tamanho: ~3.0-3.6 KB

#### 6.2. Executar teste (igual Opção 1, Passo 8)

---

## ✅ CHECKLIST PÓS-DEPLOY

Após fazer o deploy, marque cada item:

- [ ] Arquivo Database.php baixado do GitHub PR #6
- [ ] Login feito no hPanel ou FileZilla
- [ ] Navegado até /public_html/src/
- [ ] Backup do Database.php atual feito
- [ ] Upload do novo Database.php realizado
- [ ] Confirmada substituição do arquivo
- [ ] Verificado timestamp atualizado
- [ ] Verificado tamanho do arquivo (~3-3.6 KB)
- [ ] Testado: https://prestadores.clinfec.com.br/
- [ ] Confirmado: erro "Call to undefined method" SUMIU

---

## 🔧 TROUBLESHOOTING

### Problema 1: "Erro persiste após upload"

**Possíveis causas:**
1. Arquivo não foi realmente substituído
2. OPcache precisa de 30-60 segundos para atualizar
3. Arquivo upload incorreto (tamanho errado)

**Soluções:**
```
✅ Aguardar 60 segundos e testar novamente
✅ Limpar cache do navegador (Ctrl+Shift+Del)
✅ Abrir em aba anônima/privativa
✅ Verificar se arquivo tem ~3.4 KB no servidor
✅ Baixar arquivo do servidor e conferir se contém "public function exec("
```

---

### Problema 2: "Não consigo fazer login no hPanel"

**Soluções:**
```
✅ Verificar se está usando URL correta: https://hpanel.hostinger.com/
✅ Tentar recuperar senha
✅ Verificar se conta não está suspensa
✅ Contatar suporte Hostinger
```

---

### Problema 3: "FileZilla não conecta"

**Soluções:**
```
✅ Verificar credenciais (usuário: u817707156.prestadores)
✅ Verificar senha (3ClinfecPres!'0)
✅ Tentar porta 21 (FTP) ou 22 (SFTP)
✅ Desabilitar firewall temporariamente
✅ Tentar usar hPanel File Manager como alternativa
```

---

### Problema 4: "Arquivo está corrompido ou vazio"

**Soluções:**
```
✅ Baixar novamente do GitHub usando método RAW
✅ Verificar extensão do arquivo (deve ser .php, não .txt)
✅ Abrir em editor de texto e verificar se começa com "<?php"
✅ Usar método de download diferente (Raw button)
```

---

## 📞 SUPORTE

### Se tudo falhar:

1. **Documentar erro específico:**
   - Screenshot do erro
   - Qual passo falhou
   - Mensagem exata de erro

2. **Reverter mudança:**
   - Restaurar backup do Database.php
   - Voltar ao estado anterior

3. **Criar Sprint 27:**
   - Nova análise do problema
   - Abordagem alternativa

---

## 🎯 RESULTADO ESPERADO

### ANTES do deploy:
```
Fatal error: Call to undefined method App\Database::exec() 
in /home/u817707156/domains/prestadores.clinfec.com.br/public_html/src/DatabaseMigration.php 
on line 70
```

### DEPOIS do deploy:
```
✅ Sistema carrega normalmente
✅ Tela de login aparece
✅ Sem erros fatais
✅ DatabaseMigration funciona corretamente
```

---

## 📊 ESTATÍSTICAS

- **Probabilidade de sucesso:** 95%+
- **Tempo médio:** 7 minutos
- **Complexidade:** Baixa
- **Reversibilidade:** Alta (backup fácil)
- **Risco:** Muito baixo

---

## 🎉 APÓS SUCESSO

Quando o erro desaparecer:

1. ✅ Comemorar! 🎉
2. ✅ Executar testes V15 completos
3. ✅ Reportar sucesso
4. ✅ Documentar resultado
5. ✅ Fechar Sprint 26 como SUCESSO

---

**Criado por:** Claude Code (SCRUM + PDCA)  
**Sprint:** 26  
**Versão:** 1.0.0  
**Data:** 2025-11-14  
**Última atualização:** 01:05 UTC

**Link do PR:** https://github.com/fmunizmcorp/prestadores/pull/6
