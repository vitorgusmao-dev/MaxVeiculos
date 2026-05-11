# MaxVeículos - Sistema de Revenda de Veículos

## 📋 Descrição

Sistema completo para gerenciamento de revenda de veículos com site público (vitrine) e painel administrativo privado. Desenvolvido com PHP 8+, MySQL, HTML5, CSS3 e JavaScript moderno.

## ✨ Características Principais

### Site Público (Vitrine)
- ✅ Homepage com slider de banners (Swiper.js)
- ✅ Vitrine de veículos em destaque
- ✅ Listagem completa de veículos com filtros (marca, modelo, ano, preço)
- ✅ Paginação de resultados
- ✅ Página de detalhes do veículo com galeria de fotos (LightGallery)
- ✅ Integração com WhatsApp para solicitações de informações
- ✅ Design responsivo e moderno

### Painel Administrativo
- ✅ Sistema de login seguro com senha hash (password_hash)
- ✅ Dashboard com estatísticas
- ✅ CRUD completo de veículos
- ✅ Upload de múltiplas imagens (Dropzone.js)
- ✅ CRUD completo de banners
- ✅ Log de atividades
- ✅ Gerenciamento de perfil de usuário
- ✅ Alteração de senha

## 🛠️ Tecnologias Utilizadas

### Backend
- PHP 8.0+
- MySQL 5.7+
- PDO (PHP Data Objects)

### Frontend
- HTML5
- CSS3
- Bootstrap 5.3
- JavaScript (ES6+)
- Swiper.js (Carrossel de Banners)
- LightGallery (Galeria de Fotos)
- Font Awesome 6.5 (Ícones)
- SweetAlert2 (Diálogos)

## 📋 Requisitos do Sistema

- Servidor Web com suporte a PHP 8.0+
- MySQL 5.7 ou superior
- Módulo Apache `mod_rewrite` habilitado (para URLs amigáveis)
- Suporte a `.htaccess` (se usar Apache)
- Permissão de escrita nas pastas de upload

## 🚀 Instalação

### Passo 1: Preparar o Ambiente

1. **Instale XAMPP ou WAMP** (se ainda não o fez)
   - Download: https://www.apachefriends.org/

2. **Coloque os arquivos do projeto na pasta www (XAMPP) ou www (WAMP)**
   ```bash
   C:\xampp\htdocs\MaxVeiculos\  (XAMPP)
   C:\wamp\www\MaxVeiculos\      (WAMP)
   ```

### Passo 2: Criar o Banco de Dados

1. **Abra phpMyAdmin**
   - Acesse: http://localhost/phpmyadmin

2. **Importe o arquivo SQL**
   - Vá em: Importar
   - Selecione o arquivo: `database/schema.sql`
   - Clique em: Importar

   Alternativamente, execute no terminal MySQL:
   ```bash
   mysql -u root -p < database\schema.sql
   ```

### Passo 3: Configurar o Arquivo config.php

1. **Abra o arquivo**: `includes/config.php`

2. **Atualize as credenciais do banco de dados** (se necessário):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Sua senha do MySQL
   define('DB_NAME', 'max_veiculos');
   ```

3. **Altere a URL base** conforme seu servidor:
   ```php
   define('BASE_URL', 'http://localhost/MaxVeiculos');
   ```

### Passo 4: Configurar Permissões de Pasta

As seguintes pastas precisam de permissão de escrita:
- `public/uploaded_images/` (fotos dos veículos)
- `admin/uploads/` (fotos dos banners)

**Windows (geralmente automático)**
**Linux/Mac:**
```bash
chmod 755 public/uploaded_images/
chmod 755 admin/uploads/
```

### Passo 5: Acessar o Sistema

1. **Site Público (Vitrine)**
   - URL: http://localhost/MaxVeiculos/
   - ou: http://localhost/MaxVeiculos/public/index.php

2. **Painel Administrativo**
   - URL: http://localhost/MaxVeiculos/admin/login.php

## 🔐 Credenciais Padrão

**Usuário:** admin  
**Senha:** admin123  

⚠️ **Importante:** Altere essas credenciais na primeira vez que acessar o painel administrativo!

### Para alterar a senha padrão:

1. Acesse o painel admin
2. Clique em "Meu Perfil"
3. Altere sua senha

## 📁 Estrutura do Projeto

```
MaxVeiculos/
├── admin/
│   ├── assets/
│   │   ├── css/         # Estilos do admin
│   │   └── js/          # Scripts do admin
│   ├── pages/
│   │   ├── veiculos.php # Gerenciamento de veículos
│   │   ├── banners.php  # Gerenciamento de banners
│   │   ├── atividades.php
│   │   └── perfil.php
│   ├── uploads/         # Pasta para uploads (banners)
│   ├── header.php       # Header padrão do admin
│   ├── footer.php       # Footer padrão do admin
│   ├── login.php        # Tela de login
│   ├── logout.php       # Logout
│   └── dashboard.php    # Dashboard principal
├── public/
│   ├── assets/
│   │   ├── css/
│   │   └── js/
│   ├── uploaded_images/ # Pasta para fotos dos veículos
│   ├── index.php        # Homepage do site público
│   ├── veiculos.php     # Listagem de veículos
│   └── detalhes.php     # Página de detalhes do veículo
├── includes/
│   └── config.php       # Configurações e funções globais
├── database/
│   └── schema.sql       # Script SQL do banco de dados
├── .htaccess            # Configuração de URLs amigáveis
└── README.md            # Este arquivo
```

## 🗂️ Banco de Dados

### Tabelas Criadas

1. **admin** - Usuários administradores
2. **veiculos** - Registro de veículos
3. **veiculo_fotos** - Fotos dos veículos
4. **banners** - Banners de publicidade
5. **logs_atividades** - Log de ações dos admins

## 🔒 Segurança Implementada

### Autenticação e Autorização
- ✅ Verificação de sessão em todas as páginas do admin
- ✅ Redirecionamento automático para login se não autenticado
- ✅ Logout automático ao fechar navegador (sessão)

### Proteção de Dados
- ✅ Senhas com hash bcrypt (password_hash)
- ✅ Sanitização de entradas do usuário
- ✅ Proteção contra SQL Injection (prepared statements)
- ✅ Token CSRF (implementado)
- ✅ Headers de segurança HTTP

### Proteção de Arquivos
- ✅ .htaccess bloqueia acesso direto a arquivos sensíveis
- ✅ Validação de tipo de arquivo no upload
- ✅ Nomes de arquivo únicos e aleatórios
- ✅ Proteção contra acesso de diretórios

## 📝 Guia de Uso

### Para o Administrador

#### Adicionar um Novo Veículo

1. Acesse: Admin → Veículos
2. Clique em: "Adicionar Novo Veículo"
3. Preencha os campos obrigatórios:
   - Marca
   - Modelo
   - Ano
   - Preço
4. Adicione as fotos do veículo
5. Clique em: "Salvar Veículo"

#### Gerenciar Banners

1. Acesse: Admin → Banners
2. Clique em: "Adicionar Novo Banner"
3. Preencha os dados:
   - Título
   - Descrição (opcional)
   - Upload da imagem (obrigatório)
   - Link de destino (opcional)
4. Ative ou desative o banner conforme necessário
5. Clique em: "Salvar Banner"

#### Visualizar Estatísticas

1. Acesse: Admin → Dashboard
2. Visualize:
   - Total de veículos em estoque
   - Veículos em destaque
   - Total de banners ativos
   - Últimas atividades do sistema

#### Consultar Log de Atividades

1. Acesse: Admin → Atividades
2. Veja todas as ações realizadas no sistema
3. Filtre por data, usuário ou ação se necessário

### Para o Usuário Final

#### Buscar Um Veículo

1. Acesse a seção "Veículos"
2. Use os filtros disponíveis:
   - Marca
   - Modelo
   - Ano
   - Preço mínimo/máximo
3. Clique em "Filtrar"
4. Navegue pelos resultados com as páginas

#### Ver Detalhes de um Veículo

1. Clique no botão "Detalhes" do veículo
2. Veja todas as informações e especificações
3. Explore a galeria de fotos (zoom, slideshow)
4. Clique em "Solicitar via WhatsApp" para entrar em contato

## 🐛 Troubleshooting

### Erro: "Erro ao conectar ao banco de dados"
- Verifique se o servidor MySQL está rodando
- Confirme as credenciais em `config.php`
- Certifique-se de que o banco `max_veiculos` foi criado

### Erro: "Acesso negado ao fazer upload"
- Verifique as permissões das pastas `public/uploaded_images/` e `admin/uploads/`
- Use `chmod 755` no Linux/Mac
- Verifique se o servidor web tem permissão de escrita

### URLs estão mostrando "404"
- Verifique se `mod_rewrite` está ativado (Apache)
- Confirme que `.htaccess` está Na raiz do projeto
- Reinicie o servidor web

### Admin não consegue fazer login
- Limpe os cookies do navegador
- Tente em modo anônimo
- Verifique se a tabela `admin` foi criada corretamente

## 📞 Suporte e Manutenção

### Backup do Banco de Dados

**Windows (Command Prompt):**
```bash
mysqldump -u root -p max_veiculos > backup.sql
```

**Restaurar:**
```bash
mysql -u root -p max_veiculos < backup.sql
```

### Atualizações Futuras

Possíveis melhorias:
- [ ] Sistema de avaliações de veículos
- [ ] Chat em tempo real
- [ ] Agendamento de test drive
- [ ] Sistema de e-mail automático
- [ ] API REST para integração com mobile
- [ ] Dark mode
- [ ] Multtidário idiomas

## 📄 Licença

Este projeto é fornecido como está, sem garantias. Sinta-se livre para usar, modificar e distribuir.

## 👨‍💻 Desenvolvimento

Desenvolvido como um sistema móderno de gerenciamento de revenda de veículos.

**Versão:** 1.0  
**Última Atualização:** 2024

---

**Dúvidas ou Sugestões?**  
Entre em contato através do formulário de contato do site ou envie um e-mail.

Obrigado por usar MaxVeículos! 🚗
