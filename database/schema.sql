-- MaxVeículos - Database Schema
-- Este script cria todas as tabelas necessárias para o sistema

-- CREATE DATABASE IF NOT EXISTS max_veiculos;
-- USE max_veiculos;

-- Tabela de Administradores
CREATE TABLE IF NOT EXISTS admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    nome_completo VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Tabela de Veículos
CREATE TABLE IF NOT EXISTS veiculos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    ano INT NOT NULL,
    preco DECIMAL(12, 2) NOT NULL,
    quilometragem INT NOT NULL,
    cor VARCHAR(50),
    cambio VARCHAR(50),
    combustivel VARCHAR(50),
    descricao LONGTEXT,
    destaque BOOLEAN DEFAULT FALSE,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_marca (marca),
    INDEX idx_modelo (modelo),
    INDEX idx_ano (ano),
    INDEX idx_preco (preco),
    INDEX idx_ativo (ativo)
);

-- Tabela de Fotos dos Veículos
CREATE TABLE IF NOT EXISTS veiculo_fotos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    veiculo_id INT NOT NULL,
    caminho_foto VARCHAR(255) NOT NULL,
    ordem INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (veiculo_id) REFERENCES veiculos(id) ON DELETE CASCADE,
    INDEX idx_veiculo (veiculo_id)
);

-- Tabela de Banners
CREATE TABLE IF NOT EXISTS banners (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT,
    imagem_path VARCHAR(255) NOT NULL,
    link_destino VARCHAR(255),
    ativo BOOLEAN DEFAULT TRUE,
    ordem INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ativo (ativo),
    INDEX idx_ordem (ordem)
);

-- Tabela de Logs de Atividades
CREATE TABLE IF NOT EXISTS logs_atividades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT,
    acao VARCHAR(100),
    descricao TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE SET NULL,
    INDEX idx_acao (acao),
    INDEX idx_admin (admin_id)
);

-- Inserir um usuário admin padrão (usuario: admin, senha: admin123)
-- A senha deve ser alterada na primeira vez que acessar o painel
INSERT INTO admin (username, password, email, nome_completo) VALUES 
('admin', '$2y$10$YOvVaKRYLJJqRlNj5r5/dOR0A1U5dXhqVLXXZXZZ0Y9D9Z1Zy2Yvi', 'admin@maxveiculos.com.br', 'Administrador');

-- Inserir alguns banners de exemplo
INSERT INTO banners (titulo, descricao, imagem_path, link_destino, ativo, ordem) VALUES 
('Os Melhores Veículos', 'Encontre seu próximo carro conosco', '/public/uploaded_images/banner1.jpg', '/veiculos', TRUE, 0),
('Promoção Especial', 'Descontos exclusivos neste mês', '/public/uploaded_images/banner2.jpg', '/veiculos', TRUE, 1);

-- Inserir um veículo de exemplo
INSERT INTO veiculos (marca, modelo, ano, preco, quilometragem, cor, cambio, combustivel, descricao, destaque, ativo) VALUES 
('Toyota', 'Corolla', 2022, 95000.00, 15000, 'Prata', 'Automático', 'Gasolina', 'Veículo em excelente estado, revisado e pronto para uso.', TRUE, TRUE),
('Honda', 'Civic', 2021, 110000.00, 20000, 'Preto', 'Automático', 'Gasolina', 'Veículo impecável com histórico de manutenção em dia.', TRUE, TRUE),
('Volkswagen', 'Gol', 2020, 65000.00, 35000, 'Branco', 'Manual', 'Gasolina', 'Veículo econômico e confiável para o dia a dia.', FALSE, TRUE);
