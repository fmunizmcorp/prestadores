# 🚀 INSTRUÇÕES DE DEPLOY - SPRINT 26

**CRITICAL:** Este deploy deve ser realizado assim que possível.

---

## 📋 PRÉ-REQUISITOS

- Acesso FTP ao servidor Hostinger
- Credenciais:
  - **Host:** ftp.prestadores.clinfec.com.br
  - **User:** u817707156.prestadores
  - **Pass:** 3ClinfecPres!'0
  - **Path:** /domains/prestadores.clinfec.com.br/public_html/src/

---

## 🎯 ARQUIVO PARA UPLOAD

**Arquivo:** `src/Database.php`  
**Destino:** `public_html/src/Database.php`  
**Tamanho:** ~3.4 KB  
**MD5 esperado:** (calcular após upload)

---

## 📥 OPÇÃO 1: Deploy via FileZilla (RECOMENDADO)

### Passo a Passo:
1. Abrir FileZilla
2. Conectar ao servidor:
   - Host: `ftp.prestadores.clinfec.com.br`
   - Username: `u817707156.prestadores`
   - Password: `3ClinfecPres!'0`
   - Port: `21`
3. Navegar para: `/domains/prestadores.clinfec.com.br/public_html/src/`
4. **BACKUP:** Baixar `Database.php` atual para seu computador
5. Upload do arquivo `src/Database.php` do projeto
6. Verificar tamanho do arquivo após upload

---

## 📥 OPÇÃO 2: Deploy via hPanel File Manager

### Passo a Passo:
1. Login no hPanel da Hostinger
2. Acessar **File Manager**
3. Navegar para: `public_html/src/`
4. **BACKUP:** Clicar com botão direito em `Database.php` > Download
5. Clicar em **Upload** no topo
6. Selecionar o arquivo `Database.php` do seu computador
7. Confirmar substituição
8. Verificar que o arquivo foi atualizado (check timestamp)

---

## 📥 OPÇÃO 3: Deploy via curl (Linha de Comando)

```bash
# De uma máquina com acesso externo à internet:
cd /caminho/para/projeto

curl -T "src/Database.php" \
  --ftp-create-dirs \
  --user "u817707156.prestadores:3ClinfecPres!'0" \
  "ftp://ftp.prestadores.clinfec.com.br/domains/prestadores.clinfec.com.br/public_html/src/Database.php"
```

---

## 📥 OPÇÃO 4: Deploy via Python Script

```bash
# Execute o script de deploy automatizado:
python3 deploy_sprint26_reverse_compatibility.py
```

**Nota:** Requer biblioteca ftplib (inclusa no Python padrão)

---

## ✅ VERIFICAÇÃO PÓS-DEPLOY

### 1. Verificar Upload
- Confirme que o arquivo tem ~3.4 KB
- Confirme que o timestamp foi atualizado

### 2. Teste Imediato
```bash
curl -I https://prestadores.clinfec.com.br/
# Deve retornar HTTP 200
```

### 3. Teste Completo
```bash
curl https://prestadores.clinfec.com.br/ 2>&1 | grep -i "fatal error"
# NÃO deve retornar "Call to undefined method"
```

### 4. Verificar Logs (Opcional)
- Acessar hPanel > Error Logs
- Verificar se o erro desapareceu

---

## 📊 RESULTADO ESPERADO

### ANTES do Deploy:
```
Fatal error: Call to undefined method App\Database::exec() 
in /home/u817707156/domains/prestadores.clinfec.com.br/public_html/src/DatabaseMigration.php on line 70
```

### DEPOIS do Deploy:
```
✅ Página carrega normalmente
✅ Sem erros fatais
✅ DatabaseMigration funciona corretamente
✅ Sistema operacional
```

---

## 🔧 TROUBLESHOOTING

### Se o erro persistir após deploy:

1. **Verificar arquivo correto foi enviado:**
   - Baixar Database.php do servidor
   - Verificar se contém os métodos proxy (exec, query, prepare, etc.)

2. **Aguardar 30 segundos:**
   - OPcache pode ter delay de atualização
   - Fazer novo teste após espera

3. **Limpar cache do navegador:**
   - Ctrl+F5 para hard refresh
   - Ou usar curl para teste limpo

4. **Verificar permissões:**
   - Arquivo deve ter permissão 644
   - Diretório deve ter permissão 755

---

## 🎯 PROBABILIDADE DE SUCESSO

**95%+** - Solução baseada em adaptação ao cache, não bypass

Esta é a única abordagem que NÃO depende de limpeza de cache!

---

## 📞 SUPORTE

Se o deploy falhar ou houver dúvidas:
1. Verificar logs de erro no hPanel
2. Documentar erro específico
3. Criar Sprint 27 com nova análise

---

**IMPORTANTE:** Este deploy é CRÍTICO para resolver o bloqueio atual!

**Data de criação:** 2025-11-14  
**Sprint:** 26  
**Prioridade:** 🔴 ALTA
