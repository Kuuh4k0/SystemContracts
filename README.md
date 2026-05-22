# 📋 Sistema de Gerenciamento de Contratos

Sistema profissional para gerenciamento de contratos, clientes, pagamentos e usuários.

## ✨ Características

- ✅ Autenticação e controle de acesso
- ✅ Gestão completa de clientes
- ✅ Contratos com numeração automática
- ✅ Rastreamento de pagamentos
- ✅ Gerenciamento de usuários (admin)
- ✅ Dashboard com estatísticas
- ✅ Interface responsiva com Bootstrap 4.6
- ✅ Banco de dados MySQL com PDO

## 🚀 Instalação Rápida

### Passo 1: Verificar Instalação
Acesse: http://localhost/SystemContracts/verificacao.php

### Passo 2: Importar Banco de Dados

**Via phpMyAdmin:**
1. Acesse: http://localhost/phpmyadmin
2. Clique em "Novo" ou "Criar"
3. Digite o nome: `system_contratos`
4. Clique em "Criar"
5. Selecione o banco
6. Clique na aba "Importar"
7. Escolha o arquivo `database.sql`
8. Clique em "Executar"

**Via Terminal MySQL:**
```bash
mysql -u root < C:\xampp\htdocs\SystemContracts\database.sql
```

### Passo 3: Acessar o Sistema

1. Abra: http://localhost/SystemContracts
2. Login com:
   - **Email:** admin@contratos.com
   - **Senha:** admin123
3. **⚠️ ALTERE A SENHA IMEDIATAMENTE!**

## 📁 Estrutura de Arquivos

```
SystemContracts/
├── config/
│   ├── config.php          # Configurações principais
│   └── database.php        # Conexão com BD
├── includes/
│   ├── functions.php       # Funções auxiliares
│   ├── header.php          # Menu superior
│   └── footer.php          # Rodapé
├── css/
│   └── style.css          # Estilos personalizados
├── js/
│   └── script.js          # JavaScript
├── clientes/              # Módulo de clientes
│   ├── listar.php
│   ├── adicionar.php
│   ├── editar.php
│   ├── visualizar.php
│   └── deletar.php
├── contratos/             # Módulo de contratos
├── pagamentos/            # Módulo de pagamentos
├── usuarios/              # Módulo de usuários (admin)
├── documentos/            # Pasta para uploads
├── index.php              # Login
├── dashboard.php          # Dashboard
├── perfil.php             # Perfil do usuário
├── logout.php             # Sair
├── database.sql           # Schema do banco
└── verificacao.php        # Verificação do sistema
```

## 🔐 Credenciais Padrão

| Campo | Valor |
|-------|-------|
| Email | admin@contratos.com |
| Senha | admin123 |

**⚠️ IMPORTANTE:** Altere a senha na seção "Meu Perfil" após o primeiro acesso!

## 📊 Módulos do Sistema

### 👥 Clientes
- Listar, adicionar, editar e deletar clientes
- Visualizar contratos e pagamentos relacionados
- Filtro por nome, email ou CPF/CNPJ
- Status: Ativo/Inativo

### 📄 Contratos
- Numeração automática
- Associar a clientes
- Rastreamento de status
- Data de início e fim

### 💳 Pagamentos
- Múltiplos tipos (mensalidade, serviço, multa, outro)
- Status: Pendente, Pago, Atrasado, Cancelado
- Rastreamento de vencimento
- Associação com clientes

### 👨‍💼 Usuários (Admin Only)
- Criar novos usuários
- Definir perfis (admin/usuário)
- Ativar/desativar usuários
- Alterar dados

## 🛠️ Configurações

Edite `config/config.php` para customizar:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'system_contratos');
define('MOEDA_SIMBOLO', 'R$');
define('TIMEZONE', 'America/Sao_Paulo');
```

## 🐛 Troubleshooting

### Erro ao fazer login
- Verifique se o banco de dados foi importado
- Confira as credenciais em `config/config.php`
- Acesse `verificacao.php` para diagnóstico

### Página em branco
- Verifique `C:\xampp\apache\logs\error.log`
- Verifique `C:\xampp\php\error.log`
- Ative o modo desenvolvimento em `config/config.php`

### Arquivo não encontrado
- Verifique se todos os arquivos estão em `C:\xampp\htdocs\SystemContratos\`
- Nomes de arquivo devem estar em minúsculas
- Limpe o cache do navegador (Ctrl+Shift+Delete)

## 📝 Banco de Dados

### Tabelas Criadas

| Tabela | Descrição |
|--------|-----------|
| usuarios | Usuários do sistema |
| clientes | Clientes cadastrados |
| contratos | Contratos gerenciados |
| pagamentos | Rastreamento de pagamentos |
| logs | Registro de ações |

### Schema SQL

O arquivo `database.sql` contém:
- ✅ 5 tabelas com chaves estrangeiras
- ✅ Usuário admin padrão
- ✅ 1 cliente de exemplo
- ✅ 1 contrato de exemplo
- ✅ 1 pagamento de exemplo

## 🔧 Tecnologias Utilizadas

- **PHP 7.2+** - Backend
- **MySQL 5.7+** - Banco de dados
- **PDO** - Acesso ao banco de dados
- **Bootstrap 4.6** - Framework CSS
- **Font Awesome 6.0** - Ícones
- **jQuery 3.6** - JavaScript
- **SHA256** - Hash de senhas

## 📞 Suporte

Para problemas ou sugestões, acesse: `verificacao.php`

---

**Versão:** 1.0.0  
**Última atualização:** Maio 2026  
**Status:** ✅ Produção
