# Sistema de Administração PHP + MySQL

Sistema completo de administração desenvolvido em PHP puro com MySQL para gerenciamento de usuários, controle financeiro, gestão de planos e sistema de afiliados.

## 🚀 Funcionalidades

### Gestão de Usuários
- ✅ Listagem completa com filtros e busca
- ✅ Visualização detalhada do perfil
- ✅ Edição de dados pessoais
- ✅ Ativação/desativação de contas
- ✅ Histórico de atividades

### Controle Financeiro
- ✅ Gestão de saldos (visualizar, editar, adicionar, subtrair)
- ✅ Histórico completo de transações
- ✅ Controle de depósitos (aprovar/rejeitar/editar)
- ✅ Controle de saques (aprovar/rejeitar/editar)
- ✅ Relatórios financeiros com filtros por período

### Gestão de Planos
- ✅ CRUD completo de planos de investimento
- ✅ Visualização de planos ativos por usuário
- ✅ Controle de investimentos ativos
- ✅ Histórico de investimentos

### Sistema de Afiliados (até nível 10)
- ✅ Árvore genealógica visual de cada usuário
- ✅ Códigos de indicação únicos
- ✅ Valores depositados por nível da rede
- ✅ Valores sacados por nível da rede
- ✅ Valores investidos por nível da rede
- ✅ Comissões geradas por nível
- ✅ Relatórios de performance da rede

### Dashboard Principal
- ✅ Métricas gerais do sistema
- ✅ Gráficos de crescimento
- ✅ Alertas e notificações
- ✅ Resumo financeiro

## 🛠️ Tecnologias Utilizadas

- **Backend**: PHP 8.0+ (puro, sem frameworks)
- **Banco de Dados**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Bibliotecas**: 
  - DataTables (tabelas interativas)
  - Chart.js (gráficos)
  - Select2 (seletores avançados)
  - SweetAlert2 (alertas)
  - Font Awesome (ícones)

## 📋 Requisitos do Sistema

- PHP 8.0 ou superior
- MySQL 8.0 ou superior
- Apache/Nginx
- Extensões PHP: PDO, PDO_MySQL, mbstring, json

## 🔧 Instalação

1. **Clone ou baixe os arquivos**
   ```bash
   git clone [repositorio] admin-panel
   cd admin-panel
   ```

2. **Configure o banco de dados**
   - Importe o arquivo `database/sql_gtar_abit_be (1).sql`
   - Execute o arquivo `sql/admin_tables.sql` para criar tabelas administrativas

3. **Configure a conexão**
   - Edite o arquivo `config/database.php`
   - Ajuste as credenciais do banco de dados

4. **Configure permissões**
   ```bash
   chmod 755 uploads/
   chmod 644 config/*.php
   ```

5. **Acesse o sistema**
   - URL: `http://seu-dominio/admin`
   - Email: `admin@sistema.com`
   - Senha: `admin123`

## 📁 Estrutura do Projeto

```
admin-panel/
├── config/
│   ├── config.php          # Configurações gerais
│   └── database.php        # Configuração do banco
├── models/
│   ├── User.php           # Modelo de usuários
│   ├── Financial.php      # Modelo financeiro
│   └── Product.php        # Modelo de produtos
├── controllers/
│   └── AuthController.php # Controlador de autenticação
├── helpers/
│   ├── functions.php      # Funções auxiliares
│   └── security.php       # Funções de segurança
├── includes/
│   ├── header.php         # Cabeçalho comum
│   └── footer.php         # Rodapé comum
├── ajax/
│   └── update_wallet.php  # Atualização de saldo via AJAX
├── export/
│   └── users.php          # Exportação de usuários
├── sql/
│   └── admin_tables.sql   # Tabelas administrativas
├── dashboard.php          # Dashboard principal
├── users.php             # Gestão de usuários
├── deposits.php          # Gestão de depósitos
├── withdrawals.php       # Gestão de saques
├── products.php          # Gestão de produtos
├── affiliates.php        # Sistema de afiliados
├── reports.php           # Relatórios
├── login.php             # Página de login
└── logout.php            # Logout
```

## 🔐 Segurança

- **Autenticação**: Sistema seguro com hash de senhas
- **Proteção CSRF**: Tokens CSRF em formulários
- **Prevenção XSS**: Sanitização de dados de entrada
- **SQL Injection**: Prepared statements (PDO)
- **Controle de Sessão**: Timeout automático
- **Logs**: Registro de todas as ações administrativas
- **Tentativas de Login**: Bloqueio após múltiplas tentativas

## 📊 Funcionalidades Avançadas

### Dashboard Interativo
- Métricas em tempo real
- Gráficos responsivos
- Alertas de ações pendentes
- Resumo financeiro completo

### Sistema de Filtros
- Busca avançada em todas as listagens
- Filtros por data, status, tipo
- Exportação de dados filtrados

### Gestão de Saldos
- Adição/subtração de saldo via modal
- Histórico completo de transações
- Validações de segurança

### Relatórios Financeiros
- Relatórios por período
- Gráficos de crescimento
- Exportação em Excel/PDF

## 🎨 Interface

- **Design Responsivo**: Funciona em desktop, tablet e mobile
- **Tema Moderno**: Interface limpa e intuitiva
- **Componentes Interativos**: Modais, tooltips, alertas
- **Navegação Intuitiva**: Menu lateral colapsível
- **Feedback Visual**: Indicadores de status e progresso

## 🔄 Atualizações e Manutenção

### Logs do Sistema
- Todas as ações são registradas
- Rastreamento de alterações
- Auditoria completa

### Backup Recomendado
- Backup diário do banco de dados
- Backup dos arquivos de upload
- Versionamento do código

## 📞 Suporte

Para suporte técnico ou dúvidas sobre implementação:

1. Verifique a documentação
2. Consulte os logs de erro
3. Teste em ambiente de desenvolvimento

## 📝 Licença

Este projeto é proprietário e destinado ao uso interno da organização.

---

**Desenvolvido com ❤️ em PHP puro para máxima performance e segurança**