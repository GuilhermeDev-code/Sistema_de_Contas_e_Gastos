-- Criação do banco de dados para gerenciamento de rendas da serralheria
CREATE DATABASE IF NOT EXISTS serralheria_rendas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE serralheria_rendas;

-- Tabela de clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    email VARCHAR(255),
    endereco TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE
);

-- Tabela de serviços
CREATE TABLE IF NOT EXISTS servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    descricao TEXT NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    data_servico DATE NOT NULL,
    status VARCHAR(50) DEFAULT 'Pendente',
    observacoes TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);

-- Inserção de dados de exemplo
INSERT INTO clientes (nome, telefone, email, endereco) VALUES
('João Silva', '(11) 99999-9999', 'joao@email.com', 'Rua das Flores, 123 - São Paulo, SP'),
('Maria Santos', '(11) 88888-8888', 'maria@email.com', 'Av. Principal, 456 - São Paulo, SP'),
('Pedro Oliveira', '(11) 77777-7777', 'pedro@email.com', 'Rua do Comércio, 789 - São Paulo, SP');

INSERT INTO servicos (cliente_id, descricao, valor, data_servico, status) VALUES
(1, 'Portão de ferro com grade decorativa', 1500.00, '2024-01-15', 'Concluído'),
(1, 'Janela de ferro para cozinha', 800.00, '2024-02-10', 'Pendente'),
(2, 'Grade de proteção para janela', 600.00, '2024-01-20', 'Concluído'),
(3, 'Corrimão para escada', 1200.00, '2024-02-05', 'Em andamento');

