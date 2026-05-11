# 📊 Documentação do Banco de Dados - MaxVeículos

## Visão Geral

O banco de dados `max_veiculos` é composto por 5 tabelas principais que armazenam todos os dados do sistema.

---

## 📋 Tabelas

### 1. Tabela: `admin`

Armazena informações dos usuários administradores do sistema.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | INT | ID único do admin (Primary Key) |
| `username` | VARCHAR(50) | Nome de usuário (Único) |
| `password` | VARCHAR(255) | Senha criptografada (bcrypt) |
| `email` | VARCHAR(100) | Email do administrador |
| `nome_completo` | VARCHAR(150) | Nome completo |
| `created_at` | TIMESTAMP | Data/hora de criação |
| `last_login` | TIMESTAMP | Último acesso |

**Índices:**
- PRIMARY KEY: `id`
- UNIQUE: `username`

**Exemplo de dados:**
```sql
INSERT INTO admin (username, password, email, nome_completo) 
VALUES ('admin', '$2y$10$...hash...', 'admin@maxveiculos.com.br', 'Administrador');
```

---

### 2. Tabela: `veiculos`

Armazena as informações de todos os veículos do estoque.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | INT | ID único do veículo (Primary Key) |
| `marca` | VARCHAR(50) | Marca do veículo (eg: Toyota) |
| `modelo` | VARCHAR(100) | Modelo do veículo (eg: Corolla) |
| `ano` | INT | Ano de fabricação |
| `preco` | DECIMAL(12,2) | Preço em reais (máx: 999.999.999,99) |
| `quilometragem` | INT | Quilometragem atual |
| `cor` | VARCHAR(50) | Cor do veículo |
| `cambio` | VARCHAR(50) | Tipo de câmbio (Manual, Automático, CVT) |
| `combustivel` | VARCHAR(50) | Tipo de combustível (Gasolina, Diesel, etc) |
| `descricao` | LONGTEXT | Descrição detalhada do veículo |
| `destaque` | BOOLEAN | Se é um veículo em destaque (0=não, 1=sim) |
| `ativo` | BOOLEAN | Se o veículo está ativo (0=desativado, 1=ativo) |
| `created_at` | TIMESTAMP | Data/hora de criação |
| `updated_at` | TIMESTAMP | Data/hora da última atualização |

**Índices:**
- PRIMARY KEY: `id`
- INDEX: `idx_marca` (marca)
- INDEX: `idx_modelo` (modelo)
- INDEX: `idx_ano` (ano)
- INDEX: `idx_preco` (preco)
- INDEX: `idx_ativo` (ativo)

**Exemplo:**
```sql
INSERT INTO veiculos 
(marca, modelo, ano, preco, quilometragem, cor, cambio, combustivel, descricao, destaque) 
VALUES 
('Toyota', 'Corolla', 2022, 95000.00, 15000, 'Prata', 'Automático', 'Gasolina', 'Veículo em excelente estado', 1);
```

---

### 3. Tabela: `veiculo_fotos`

Armazena as fotos de cada veículo (relação 1:N com veículos).

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | INT | ID único da foto (Primary Key) |
| `veiculo_id` | INT | ID do veículo (Foreign Key) |
| `caminho_foto` | VARCHAR(255) | Caminho relativo da foto no servidor |
| `ordem` | INT | Ordem de exibição (0 = primeira) |
| `created_at` | TIMESTAMP | Data/hora de upload |

**Indices:**
- PRIMARY KEY: `id`
- FOREIGN KEY: `veiculo_id` → `veiculos.id` (ON DELETE CASCADE)
- INDEX: `idx_veiculo` (veiculo_id)

**Exemplo:**
```sql
INSERT INTO veiculo_fotos (veiculo_id, caminho_foto, ordem)
VALUES (1, 'img_1683657600_abc123def.jpg', 0);
```

---

### 4. Tabela: `banners`

Armazena os banners/slides da página inicial do site público.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | INT | ID único do banner (Primary Key) |
| `titulo` | VARCHAR(200) | Título/texto do banner |
| `descricao` | TEXT | Descrição/subtítulo |
| `imagem_path` | VARCHAR(255) | Caminho da imagem no servidor |
| `link_destino` | VARCHAR(255) | URL de destino ao clicar |
| `ativo` | BOOLEAN | Se o banner está ativo (0=inativo, 1=ativo) |
| `ordem` | INT | Ordem de exibição no carrossel |
| `created_at` | TIMESTAMP | Data/hora de criação |
| `updated_at` | TIMESTAMP | Data/hora da última atualização |

**Índices:**
- PRIMARY KEY: `id`
- INDEX: `idx_ativo` (ativo)
- INDEX: `idx_ordem` (ordem)

**Exemplo:**
```sql
INSERT INTO banners (titulo, descricao, imagem_path, link_destino, ativo, ordem)
VALUES ('Os Melhores Veículos', 'Encontre seu próximo carro', 'banner1.jpg', '/veiculos', 1, 0);
```

---

### 5. Tabela: `logs_atividades`

Registro de todas as ações realizadas pelos administradores do sistema.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | INT | ID único do log (Primary Key) |
| `admin_id` | INT | ID do admin que realizou a ação (Foreign Key) |
| `acao` | VARCHAR(100) | Tipo de ação (LOGIN, LOGOUT, ADD_VEICULO, etc) |
| `descricao` | TEXT | Descrição detalhada da ação |
| `ip_address` | VARCHAR(45) | Endereço IP de origem |
| `created_at` | TIMESTAMP | Data/hora da ação |

**Índices:**
- PRIMARY KEY: `id`
- FOREIGN KEY: `admin_id` → `admin.id` (ON DELETE SET NULL)
- INDEX: `idx_acao` (acao)
- INDEX: `idx_admin` (admin_id)

**Exemplo:**
```sql
INSERT INTO logs_atividades (admin_id, acao, descricao, ip_address)
VALUES (1, 'ADD_VEICULO', 'Veículo Toyota Corolla adicionado', '127.0.0.1');
```

---

## 🔗 Relacionamentos

```
admin (1) ----< (N) logs_atividades
          
veiculos (1) ----< (N) veiculo_fotos

banners (standalone - sem relacionamentos)
```

---

## 📊 Queries Úteis

### Obter veículos mais recentes
```sql
SELECT * FROM veiculos 
WHERE ativo = TRUE 
ORDER BY created_at DESC 
LIMIT 10;
```

### Obter veículos por marca e preço
```sql
SELECT * FROM veiculos 
WHERE marca = 'Toyota' 
AND preco BETWEEN 50000 AND 150000 
AND ativo = TRUE;
```

### Contar fotos de um veículo
```sql
SELECT COUNT(*) FROM veiculo_fotos 
WHERE veiculo_id = 1;
```

### Obter todos os logs de um admin
```sql
SELECT * FROM logs_atividades 
WHERE admin_id = 1 
ORDER BY created_at DESC;
```

### Veículos em destaque
```sql
SELECT * FROM veiculos 
WHERE destaque = TRUE 
AND ativo = TRUE 
ORDER BY created_at DESC;
```

### Banners ativos ordenados
```sql
SELECT * FROM banners 
WHERE ativo = TRUE 
ORDER BY ordem ASC;
```

---

## 🔐 Integridade de Dados

### Constraints Ativas

1. **CASCADE DELETE**: Ao deletar um veículo, suas fotos são deletadas automaticamente
2. **SET NULL**: Ao deletar um admin, seus logs ficam com `admin_id = NULL`
3. **UNIQUE**: Usernames de admins são únicos

### Backup Recomendado

```bash
# Fazer backup
mysqldump -u root -p max_veiculos > backup_$(date +%Y%m%d).sql

# Restaurar
mysql -u root -p max_veiculos < backup_20240101.sql
```

---

## 📈 Escalabilidade

### Limite de Dados

| Aspecto | Limite | Nota |
|--------|--------|------|
| Veículos | Ilimitado | Considere arquivar após 2-3 anos |
| Fotos por veículo | Ilimitado | Recomenda-se 3-10 por veículo |
| Tamanho de foto | Até 5MB | Compactar antes de upload |
| Banners | Recomenda-se 5-20 | Não afeta performance |
| Admins | Poucos (2-5) | Controle de acesso |

### Índices para Performance

Os índices estão configurados nos campos mais consultados:
- `marca`, `modelo`, `ano`, `preco` em veículos
- `ativo` em veículos e banners
- `ordem` em banners

Para grandes volumes, considere adicionar mais índices.

---

## 🔄 Manutenção

### Otimizar Tabelas
```sql
OPTIMIZE TABLE admin;
OPTIMIZE TABLE veiculos;
OPTIMIZE TABLE veiculo_fotos;
OPTIMIZE TABLE banners;
OPTIMIZE TABLE logs_atividades;
```

### Limpar Logs Antigos
```sql
-- Deletar logs com mais de 90 dias
DELETE FROM logs_atividades 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

### Ver Tamanho das Tabelas
```sql
SELECT TABLE_NAME, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Tamanho (MB)'
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = 'max_veiculos'
ORDER BY (data_length + index_length) DESC;
```

---

## 📝 Notas Importantes

1. **Charset**: Todas as tabelas usam UTF-8MB4 (suporta emojis)
2. **Collation**: utf8mb4_general_ci (case-insensitive)
3. **Engine**: InnoDB (suporta transações e integridade referencial)
4. **Backup**: Faça backups regularmente
5. **Senhas**: Nunca armazene senhas em texto plano (use `password_hash`)

---

**Versão:** 1.0  
**Última Atualização:** 2024
