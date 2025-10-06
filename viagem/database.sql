-- Criar o banco de dados
CREATE DATABASE IF NOT EXISTS plataforma_viagens
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- Usar o banco de dados
USE plataforma_viagens;

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acesso TIMESTAMP NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Viagens
CREATE TABLE IF NOT EXISTS viagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    descricao TEXT,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    status ENUM('planejamento', 'ativa', 'concluida', 'cancelada') DEFAULT 'planejamento',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_datas (data_inicio, data_fim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Roteiros por Dia
CREATE TABLE IF NOT EXISTS roteiros_dias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    viagem_id INT NOT NULL,
    data DATE NOT NULL,
    ordem INT NOT NULL,
    notas TEXT,
    FOREIGN KEY (viagem_id) REFERENCES viagens(id) ON DELETE CASCADE,
    INDEX idx_viagem_data (viagem_id, data),
    UNIQUE KEY unique_viagem_data (viagem_id, data)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Locais
CREATE TABLE IF NOT EXISTS locais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo ENUM('atracao', 'restaurante', 'hotel', 'transporte', 'outro') NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    endereco TEXT,
    cidade VARCHAR(100),
    estado VARCHAR(50),
    pais VARCHAR(50),
    classificacao DECIMAL(2,1),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_coordenadas (latitude, longitude),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Locais Salvos (relacionamento entre roteiros_dias e locais)
CREATE TABLE IF NOT EXISTS locais_salvos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roteiro_dia_id INT NOT NULL,
    local_id INT NOT NULL,
    hora_inicio TIME,
    hora_fim TIME,
    ordem INT NOT NULL,
    notas TEXT,
    status ENUM('pendente', 'confirmado', 'cancelado') DEFAULT 'pendente',
    FOREIGN KEY (roteiro_dia_id) REFERENCES roteiros_dias(id) ON DELETE CASCADE,
    FOREIGN KEY (local_id) REFERENCES locais(id) ON DELETE CASCADE,
    INDEX idx_roteiro_ordem (roteiro_dia_id, ordem),
    UNIQUE KEY unique_roteiro_ordem (roteiro_dia_id, ordem)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar índices adicionais para otimização de consultas comuns
CREATE INDEX idx_viagens_status ON viagens(status);
CREATE INDEX idx_locais_cidade ON locais(cidade);
CREATE INDEX idx_locais_classificacao ON locais(classificacao);
