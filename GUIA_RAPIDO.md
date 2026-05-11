# 🚀 Guia Rápido de Instalação - MaxVeículos

## ⚡ 3 Passos para Começar

### 1️⃣ Criar o Banco de Dados (1 minuto)

**No phpMyAdmin:**
1. Acesse: http://localhost/phpmyadmin
2. Importe o arquivo: `database/schema.sql`
3. Pronto! ✅

**OU no Terminal:**
```bash
mysql -u root -p < database\schema.sql
```

### 2️⃣ Ajustar Configurações (30 segundos)

**Abra: `includes/config.php`**

```php
// Linha 18-22: Atualize se necessário
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Sua senha
define('DB_NAME', 'max_veiculos');

// Linha 27: Atualize a URL base
define('BASE_URL', 'http://localhost/MaxVeiculos');
```

### 3️⃣ Acessar o Sistema

**Site Público:**  
→ http://localhost/MaxVeiculos/

**Painel Admin:**  
→ http://localhost/MaxVeiculos/admin/login.php

**Login padrão:**
- Usuário: `admin`
- Senha: `admin123`

---

## 📋 Checklist de Instalação

- [ ] XAMPP/WAMP instalado e rodando
- [ ] Arquivos baixados em `htdocs/` (XAMPP) ou `www/` (WAMP)
- [ ] Banco de dados criado e importado
- [ ] `includes/config.php` configurado
- [ ] Pastas `public/uploaded_images/` e `admin/uploads/` existem
- [ ] Site funciona: http://localhost/MaxVeiculos/
- [ ] Admin funciona: http://localhost/MaxVeiculos/admin/login.php

---

## 🚗 Primeiras Ações

1. **Altere a senha do admin**
   - Acesse: Admin → Meu Perfil
   - Clique em: "Alterar Senha"

2. **Adicione informações da empresa**
   - Edite o footer em: `public/index.php` (linhas 387-395)
   - Adicione telefone, email, endereço reais

3. **Customize o site**
   - Edite cores em `public/index.php` (seção `--primary: #667eea;`)
   - Adicione seu logo em vez de "🚗"

4. **Adicione veículos e banners**
   - Admin → Veículos → Adicionar Novo Veículo
   - Admin → Banners → Adicionar Novo Banner

---

## 📞 Contato Padrão

Hoje vem configurado com dados fictícios:
- Telefone: (27) 1234-5678
- Email: contato@maxveiculos.com.br
- Endereço: Cachoeiro de Itapemirim, ES

**Atualize estes dados em:**
- `public/index.php` (linha 387+)
- `public/veiculos.php` (similar)
- `public/detalhes.php` (similar)

---

## 🔐 Segurança Básica

1. ✅ Altere a senha do admin
2. ✅ Altere `define('DB_PASS', '')` se tiver senha no MySQL
3. ✅ Em produção, ative HTTPS
4. ✅ Em produção, defina `define('DEBUG_MODE', false)`

---

## 💡 Dicas Úteis

### Para adicionar mais imagens de um veículo:
1. Admin → Veículos → Editar Veículo
2. Role para baixo: "Fotos do Veículo"
3. Selecione múltiplas imagens (Ctrl+Click)
4. Salve

### As imagens ficam muito grandes?
Redimensione antes de fazer upload (recomendado: máx 2MB cada)

### Quer adicionar mais um admin?
No phpMyAdmin:
1. Vá para tabela: `admin`
2. Inserir uma nova linha
3. Gere uma senha hash em: https://www.php-hash.com/ (use bcrypt)

### Alterar cores do site:

**Em `public/index.php` procure:**
```css
:root {
    --primary: #667eea;        ← cor roxa
    --secondary: #764ba2;      ← cor roxa escura
    --light: #f8f9fa;          ← cor cinza claro
}
```

---

## 🆘 Problemas Comuns

| Problema | Solução |
|----------|---------|
| "Error: Conexão recusada" | MySQL não está rodando. Inicie XAMPP |
| "404 Not Found" | Verifique a URL e se `.htaccess` está na raiz |
| "Permissão negada upload" | Execute `chmod 755 public/uploaded_images/` |
| "Login não funciona" | Limpe cookies do navegador e tente novamente |
| "Falha ao enviar formulário" | Verifique se as pastas de upload existem |

---

## 📚 Próximas Etapas

1. Explore o pane admin
2. Adicione alguns veículos testes
3. Configure os banners home
4. Personalize o design com suas cores
5. Configure seus dados de contato

---

## ✉️ Suporte

Veja o arquivo `README.md` para documentação completa.

**Versão:** 1.0  
**Última Atualização:** 2024

Boa sorte! 🚗✨
