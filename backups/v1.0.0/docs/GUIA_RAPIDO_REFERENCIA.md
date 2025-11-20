# 🚀 GUIA RÁPIDO DE REFERÊNCIA - SISTEMA CLINFEC

## 📌 Acesso Rápido

### URLs Importantes
- **Sistema:** http://localhost/clinfec/public (local) ou https://seudominio.com.br (produção)
- **phpMyAdmin:** http://localhost/phpmyadmin
- **GitHub:** https://github.com/fmunizmcorp/prestadores

### Credenciais Padrão
```
Email: admin@clinfec.com.br
Senha: admin123
⚠️ ALTERE IMEDIATAMENTE APÓS PRIMEIRO LOGIN
```

---

## ⚡ Instalação Rápida (5 Minutos)

### Local (XAMPP/WAMP)
```bash
# 1. Clone o repositório
cd C:\xampp\htdocs
git clone https://github.com/fmunizmcorp/prestadores.git clinfec

# 2. Crie o banco via phpMyAdmin
CREATE DATABASE clinfec_prestadores;

# 3. Configure config/database.php
host: localhost
database: clinfec_prestadores
username: root
password: (vazio)

# 4. Acesse
http://localhost/clinfec/public
```

### Hostinger
```bash
# 1. Upload via File Manager para public_html/

# 2. Crie banco MySQL no hPanel

# 3. Configure config/database.php com dados do Hostinger

# 4. Defina Document Root: /public_html/public

# 5. Acesse seu domínio
```

---

## 🎯 Ações Mais Comuns

### Cadastrar Empresa Tomadora (Cliente)
```
Menu → Empresas → Tomadoras → + Nova Empresa
✓ Preencha CNPJ (validação automática)
✓ Preencha nome e razão social
✓ Digite CEP (busca automática de endereço)
✓ Preencha contatos
✓ Salvar
```

### Cadastrar Empresa Prestadora (Fornecedor)
```
Menu → Empresas → Prestadoras → + Nova Empresa
✓ Processo idêntico a Tomadora
✓ Adicional: Certificações e Serviços Oferecidos
```

### Cadastrar Serviço
```
Menu → Cadastros → Serviços → + Novo Serviço
✓ Código único
✓ Nome do serviço
✓ Tipo e categoria
✓ Requisitos (escolaridade, experiência)
✓ Valor de referência
✓ Salvar
```

### Cadastrar Contrato
```
Menu → Contratos → + Novo Contrato
✓ Número do contrato
✓ Selecionar empresa tomadora
✓ Objeto do contrato
✓ Datas (assinatura, início, término)
✓ Valor total
✓ Gestores
✓ Upload PDF (opcional)
✓ Salvar
```

### Adicionar Serviço ao Contrato
```
Visualizar Contrato → Aba "Serviços" → + Adicionar
✓ Selecionar serviço
✓ Quantidade de postos
✓ Valor unitário
✓ Salvar
```

### Adicionar Responsável à Empresa
```
Visualizar Empresa → Aba "Responsáveis" → + Adicionar
✓ Nome, cargo
✓ Email, telefone
✓ Departamento
✓ Salvar
```

### Anexar Documento à Empresa
```
Visualizar Empresa → Aba "Documentos" → + Adicionar
✓ Tipo de documento
✓ Upload arquivo (PDF, max 5MB)
✓ Número, datas
✓ Salvar
```

---

## 🔍 Filtros e Buscas

### Empresas Tomadoras/Prestadoras
```
Filtros disponíveis:
✓ Busca: Nome, razão social, CNPJ
✓ Status: Ativa/Inativa
✓ Estado: UF
✓ Itens por página: 10/20/50/100
```

### Serviços
```
Filtros disponíveis:
✓ Busca: Código, nome, descrição
✓ Status: Ativo/Inativo
✓ Tipo: Técnico, Operacional, etc.
✓ Requisitos: Com/Sem
```

### Contratos
```
Filtros disponíveis:
✓ Busca: Número, empresa
✓ Status: Ativo, Suspenso, Encerrado, Vencido
✓ Tipo: Prestação, Outsourcing, etc.
✓ Empresa: Selecionar específica
```

---

## 🛠️ Comandos Úteis

### Backup Banco (mysqldump)
```bash
mysqldump -u root -p clinfec_prestadores > backup.sql
```

### Restaurar Banco
```bash
mysql -u root -p clinfec_prestadores < backup.sql
```

### Backup Arquivos
```bash
tar -czf backup_clinfec.tar.gz clinfec/
```

### Permissões (Linux)
```bash
chmod -R 755 clinfec/
chmod -R 777 clinfec/logs/
chmod -R 777 clinfec/public/uploads/
```

### Ver Logs de Erro
```bash
tail -f logs/errors.log
```

### Limpar Logs
```bash
rm -rf logs/*.log
```

---

## 📊 Estrutura de Perfis

### Master (Acesso Total)
- ✓ Tudo que Admin faz
- ✓ Gerenciar usuários Master
- ✓ Configurações críticas do sistema

### Admin (Administrativo)
- ✓ Cadastrar/Editar/Excluir empresas
- ✓ Cadastrar/Editar/Excluir serviços
- ✓ Cadastrar/Editar/Excluir contratos
- ✓ Gerenciar usuários Admin, Gestor e Usuario

### Gestor (Operacional)
- ✓ Visualizar todas as informações
- ✓ Cadastrar empresas e serviços
- ✓ Editar dados operacionais
- ✗ Não pode excluir
- ✗ Não pode gerenciar usuários

### Usuario (Consulta)
- ✓ Visualizar empresas
- ✓ Visualizar serviços
- ✓ Visualizar contratos
- ✗ Não pode cadastrar
- ✗ Não pode editar
- ✗ Não pode excluir

---

## 🚨 Troubleshooting Rápido

### Erro 500
```
1. Verificar logs: tail -f logs/errors.log
2. Verificar permissões: chmod 777 logs/
3. Verificar .htaccess: RewriteBase /
```

### Erro de Banco
```
1. Testar: mysql -u root -p
2. Verificar config/database.php
3. Recriar usuário se necessário
```

### Upload Falha
```
1. Verificar: upload_max_filesize no php.ini
2. Aumentar para: 15M
3. Reiniciar Apache
4. Verificar permissões: chmod 777 public/uploads/
```

### Página Branca
```
1. Ativar erros: ini_set('display_errors', 1);
2. Verificar logs do Apache
3. Verificar se todos os arquivos foram enviados
```

### CSS Não Carrega
```
1. Limpar cache: Ctrl+Shift+Del
2. Verificar .htaccess
3. Verificar console do navegador (F12)
```

### Sessão Expira Rápido
```
1. Aumentar em config/app.php:
   'session_lifetime' => 14400  // 4 horas
2. Reiniciar servidor
```

---

## 📋 Checklist Pós-Instalação

### Obrigatório
- [ ] Alterar senha do admin padrão
- [ ] Criar usuários reais
- [ ] Cadastrar pelo menos 1 empresa tomadora
- [ ] Cadastrar pelo menos 3 serviços
- [ ] Testar cadastro de contrato

### Recomendado
- [ ] Configurar backup automático
- [ ] Desabilitar display_errors em produção
- [ ] Configurar SSL (HTTPS)
- [ ] Monitorar logs periodicamente
- [ ] Documentar processos internos

### Opcional
- [ ] Customizar logo e cores
- [ ] Ajustar tempo de sessão
- [ ] Configurar envio de emails
- [ ] Integrar com outros sistemas
- [ ] Criar relatórios personalizados

---

## 🔐 Segurança

### Boas Práticas
```
✓ Sempre use HTTPS em produção
✓ Senhas fortes (min 8 caracteres, números, especiais)
✓ Altere credenciais padrão imediatamente
✓ Faça backup regular (diário recomendado)
✓ Mantenha PHP e MySQL atualizados
✓ Monitore logs de acesso
✓ Limite tentativas de login
```

### Desabilitar Usuário Padrão
```sql
-- Via phpMyAdmin ou MySQL
UPDATE usuarios SET ativo = 0 WHERE email = 'admin@clinfec.com.br';
-- Após criar seu próprio usuário Master
```

---

## 📞 Suporte

### Documentação
- **Manual Completo:** MANUAL_INSTALACAO_COMPLETO.md
- **Documentação Técnica:** docs/
- **README:** README.md

### Contato
- **Email:** suporte@clinfec.com.br
- **GitHub Issues:** https://github.com/fmunizmcorp/prestadores/issues

### Informações para Suporte
```
Sempre inclua:
✓ Versão do sistema
✓ Ambiente (SO, PHP, MySQL)
✓ Navegador utilizado
✓ Mensagem de erro completa
✓ Últimas linhas do logs/errors.log
✓ Screenshot (se possível)
```

---

## 📈 Métricas e Monitoramento

### KPIs Principais
- Total de empresas cadastradas
- Total de contratos ativos
- Valor total de contratos
- Contratos vencendo (90 dias)
- Taxa de uso do sistema

### Logs a Monitorar
```bash
# Erros críticos
grep "ERROR" logs/errors.log

# Migrations executadas
cat logs/migrations.log

# Acessos recentes
tail -100 logs/access.log
```

---

## 🎓 Dicas de Uso

### Atalhos Úteis
- **Ctrl+Click** em links: Abrir em nova aba
- **F5**: Recarregar página
- **Ctrl+Shift+Del**: Limpar cache
- **F12**: Console do desenvolvedor

### Fluxo Recomendado
```
1. Cadastrar Empresas Tomadoras
2. Cadastrar Serviços do Catálogo
3. Cadastrar Empresas Prestadoras
4. Vincular Serviços às Prestadoras
5. Cadastrar Contratos
6. Adicionar Serviços aos Contratos
7. Gerenciar Aditivos conforme necessário
8. Monitorar vencimentos
```

### Organização de Dados
```
✓ Use códigos padronizados (EMP-001, SRV-001, CONT-001)
✓ Preencha todos os campos possíveis
✓ Anexe documentos sempre que possível
✓ Adicione observações relevantes
✓ Mantenha dados de contato atualizados
```

---

## 🔄 Ciclo de Vida de Contrato

```
1. Cadastro Inicial
   ↓
2. Adicionar Serviços
   ↓
3. Upload do PDF do Contrato
   ↓
4. Acompanhamento (Dashboard mostra status)
   ↓
5. Aditivos (quando necessário)
   ↓
6. Alertas de Vencimento (automáticos 90 dias antes)
   ↓
7. Renovação ou Encerramento
```

---

## 📱 Acesso Mobile

### Navegadores Recomendados
- Chrome Mobile (Android)
- Safari (iOS)
- Firefox Mobile

### Funcionalidades
- ✓ Layout responsivo
- ✓ Todas as funcionalidades disponíveis
- ✓ Upload de fotos via câmera
- ✓ Máscaras de entrada adaptadas

---

## 🌐 APIs e Integrações

### APIs Externas Usadas
- **ViaCEP:** Busca automática de endereços
- **Bootstrap CDN:** Framework CSS/JS
- **FontAwesome CDN:** Ícones
- **jQuery CDN:** Biblioteca JavaScript

### Dependências Online
```
⚠️ Sistema requer internet para:
✓ Buscar CEP (ViaCEP)
✓ Carregar CDNs (Bootstrap, jQuery, etc.)
✓ Ícones (FontAwesome)

💡 Em produção, considere hospedar CDNs localmente
```

---

## 📦 Estrutura de Tabelas (Referência Rápida)

### Principais Entidades

**usuarios**
- id, nome, email, senha, perfil_id, ativo

**empresas_tomadoras**
- id, cnpj, razao_social, nome_fantasia, endereco, contatos, dados_financeiros

**empresas_prestadoras**
- id, cnpj, razao_social, certificacoes, servicos_oferecidos

**servicos**
- id, codigo, nome, tipo, requisitos, valor_referencia

**contratos**
- id, numero_contrato, empresa_tomadora_id, objeto, valor_total, datas, gestores

**contratos_servicos**
- id, contrato_id, servico_id, quantidade, valor_unitario

---

## 🎯 Objetivos por Sprint (Roadmap)

### ✅ Sprint 4 (COMPLETO)
- Empresas Tomadoras
- Empresas Prestadoras
- Serviços
- Contratos

### 🔜 Sprint 5 (Próximo)
- Projetos
- Atividades
- Vinculação Projeto-Contrato

### 🔜 Sprint 6
- Candidaturas
- Seleção de Prestadoras
- Avaliações

### 🔜 Sprint 7
- Gestão Financeira
- Faturamento
- Pagamentos

### 🔜 Sprint 8
- Ponto Eletrônico
- Marcação de ponto
- Relatórios de frequência

### 🔜 Sprint 9
- Metas
- Gamificação
- Rankings

### 🔜 Sprint 10
- Relatórios Personalizados
- Dashboards avançados
- Exportações

---

## ✅ Validações Automáticas

### CNPJ
- Algoritmo completo de validação
- Verificação de dígitos verificadores
- Formatação automática

### CPF
- Validação completa
- Formatação automática

### CEP
- Busca automática via ViaCEP
- Preenchimento de endereço

### Datas
- Validação de datas futuras/passadas
- Cálculo automático de prazos
- Alertas de vencimento

### Uploads
- Validação de tipo de arquivo
- Validação de tamanho (15MB max)
- Sanitização de nomes

---

## 🔑 Comandos SQL Úteis

### Listar Usuários
```sql
SELECT id, nome, email, perfil_id, ativo FROM usuarios;
```

### Criar Novo Usuário
```sql
INSERT INTO usuarios (nome, email, senha, perfil_id, ativo)
VALUES ('Nome', 'email@exemplo.com', '$2y$10$...', 2, 1);
-- Senha deve ser hash bcrypt
```

### Ver Contratos Vencendo
```sql
SELECT numero_contrato, data_termino, DATEDIFF(data_termino, NOW()) as dias
FROM contratos
WHERE status = 'Ativo' AND DATEDIFF(data_termino, NOW()) <= 90;
```

### Estatísticas Rápidas
```sql
SELECT 
  (SELECT COUNT(*) FROM empresas_tomadoras WHERE deleted_at IS NULL) as tomadoras,
  (SELECT COUNT(*) FROM empresas_prestadoras WHERE deleted_at IS NULL) as prestadoras,
  (SELECT COUNT(*) FROM servicos WHERE ativo = 1) as servicos,
  (SELECT COUNT(*) FROM contratos WHERE status = 'Ativo') as contratos_ativos;
```

---

## 🎨 Customização Visual

### Alterar Logo
```
1. Substituir arquivo: public/images/logo.png
2. Tamanho recomendado: 200x50px
3. Formatos: PNG ou SVG
4. Limpar cache do navegador
```

### Alterar Cores
```css
/* Editar: public/css/style.css */

:root {
    --primary: #0d6efd;  /* Azul principal */
    --success: #198754;  /* Verde */
    --danger: #dc3545;   /* Vermelho */
    --warning: #ffc107;  /* Amarelo */
}
```

### Customizar Footer
```
Editar: src/views/layouts/footer.php
Localizar: <footer class="footer">
```

---

## 📊 Relatórios Disponíveis

### Dashboard
- Cards de estatísticas gerais
- Gráfico de contratos por mês (próximo sprint)
- Alertas de vencimento
- Ações rápidas

### Empresas
- Listagem completa com filtros
- Exportação futura (CSV, Excel, PDF)

### Contratos
- Listagem com alertas
- Agrupamento por status
- Valor total por período

---

## 🌟 Boas Práticas de Uso

### Nomenclatura
```
✓ Empresas: Use nome fantasia como principal
✓ Serviços: Códigos descritivos (SRV-TI-001)
✓ Contratos: Número único padronizado
✓ Documentos: Nomes claros e data
```

### Preenchimento de Dados
```
✓ Preencha TODOS os campos possíveis
✓ Use campo "Observações" para detalhes
✓ Anexe documentos sempre que relevante
✓ Atualize dados regularmente
✓ Revise informações trimestralmente
```

### Gestão de Contratos
```
✓ Cadastre contratos assim que assinados
✓ Adicione todos os serviços contratados
✓ Anexe PDF do contrato
✓ Configure alertas de vencimento
✓ Registre aditivos prontamente
```

---

## 🎉 Está Pronto!

Sistema 100% funcional e documentado.

**Suporte:** suporte@clinfec.com.br  
**GitHub:** https://github.com/fmunizmcorp/prestadores  
**Versão:** 1.0.0  

---

*Guia gerado automaticamente - Última atualização: Novembro 2025*
