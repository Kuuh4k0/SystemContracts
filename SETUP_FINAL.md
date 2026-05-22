# 🎯 SETUP FINAL - Sistema de Contratos

## ⚡ 5 Passos para Começar

### 1️⃣ Verificar Sistema
Acesse e verifique se tudo está certo:
```
http://localhost/SystemContracts/verificacao.php
```

Se mostrar ✅ em tudo, continue. Se houver ❌, pare e verifique os erros.

---

### 2️⃣ Importar Banco de Dados

**Opção A: phpMyAdmin (mais fácil)**
1. Abra: http://localhost/phpmyadmin
2. Clique em "+ Novo" (lado esquerdo)
3. Digite: `system_contratos`
4. Clique em "Criar"
5. Clique na aba "Importar" (no topo)
6. Clique em "Selecionar arquivo"
7. Procure por: `C:\xampp\htdocs\SystemContracts\database.sql`
8. Clique em "Executar"

✅ Pronto! As 5 tabelas foram criadas.

**Opção B: Terminal (modo avançado)**
```bash
mysql -u root < C:\xampp\htdocs\SystemContracts\database.sql
```

---

### 3️⃣ Primeiro Login
1. Abra: http://localhost/SystemContracts
2. Use as credenciais:
   - **Email:** admin@contratos.com
   - **Senha:** admin123
3. Clique em "Entrar"

Se vir o Dashboard, sucesso! 🎉

---

### 4️⃣ Alterar Sua Senha
1. Clique no seu nome (canto superior direito)
2. Clique em "Meu Perfil"
3. Desça até "Alterar Senha"
4. Digite a senha atual: `admin123`
5. Digite uma nova senha (mínimo 6 caracteres)
6. Confirme a senha
7. Clique em "Salvar Alterações"

**⚠️ IMPORTANTE:** Guarde sua nova senha com segurança!

---

### 5️⃣ Criar Seu Primeiro Usuário
Se você quer outros usuários acessarem o sistema:

1. Clique em "Usuários" (no menu)
2. Clique em "Novo Usuário"
3. Preencha os dados:
   - Nome: Ex. "João Silva"
   - Email: Ex. "joao@empresa.com"
   - Senha: Gere uma senha segura
   - Perfil: Escolha "Usuário" (não admin)
4. Clique em "Criar Usuário"

✅ Novo usuário criado! Ele pode fazer login com o email e senha.

---

## 📋 Próximos Passos

### Adicionar Clientes
1. Clique em "Clientes" (no menu)
2. Clique em "Novo Cliente"
3. Preencha os dados
4. Clique em "Salvar Cliente"

### Criar Contratos
1. Clique em "Contratos" (no menu)
2. Clique em "Novo Contrato"
3. Selecione um cliente
4. Preencha os dados
5. Clique em "Salvar Contrato"

### Rastrear Pagamentos
1. Clique em "Pagamentos" (no menu)
2. Clique em "Novo Pagamento"
3. Selecione cliente e tipo
4. Defina valor e vencimento
5. Clique em "Salvar Pagamento"

---

## 🆘 Se Algo Quebrou

### Erro: "Erro de conexão com banco de dados"
```
Solução:
1. Verifique se MySQL está rodando
2. Acesse verificacao.php
3. Se houver ❌ em "Banco de Dados", importe database.sql novamente
```

### Erro: "Página em branco ou erro 500"
```
Solução:
1. Abra C:\xampp\apache\logs\error.log
2. Procure por linhas com "PHP" e "Fatal"
3. Se não souber o que fazer, acesse verificacao.php
```

### Erro: "Arquivo não encontrado"
```
Solução:
1. Verifique se os arquivos estão em: C:\xampp\htdocs\SystemContracts\
2. Limpe o cache do navegador: Ctrl + Shift + Delete
3. Recarregue a página: Ctrl + F5
```

---

## ✅ Checklist de Conclusão

- [ ] Acessou verificacao.php e viu ✅ em tudo
- [ ] Importou database.sql
- [ ] Fez login com admin@contratos.com / admin123
- [ ] Alterou a senha do admin
- [ ] Criou um novo usuário (opcional)
- [ ] Adicionou um cliente de teste
- [ ] Criou um contrato de teste

Se marcou tudo, 🎉 **Sistema pronto para usar!**

---

## 📚 Documentação

- **README.md** - Visão geral do sistema
- **database.sql** - Schema do banco de dados
- **verificacao.php** - Diagnóstico do sistema
- **config/config.php** - Configurações

---

**Versão:** 1.0.0  
**Status:** ✅ Pronto para Produção
