-- Criação da Base de Dados Se Não Existir
CREATE DATABASE IF NOT EXISTS teamwork;
USE teamwork;

-- 1. Tabela de UTILIZADORES
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    birthdate DATE,
    photo_path VARCHAR(255) DEFAULT 'images/profile.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabela de COLEÇÕES
CREATE TABLE collections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabela de TAGS da Coleção
-- Permite múltiplas tags por coleção
CREATE TABLE collection_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    collection_id INT NOT NULL,
    tag_name VARCHAR(50) NOT NULL,
    FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabela de ITENS
CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    collection_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    acquisition_date DATE,
    importance INT CHECK (importance BETWEEN 1 AND 10),
    price DECIMAL(10, 2),
    weight DECIMAL(10, 2),
    image_path VARCHAR(255),
    FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabela de EVENTOS
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    creator_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255),
    event_date DATE NOT NULL,
    start_time TIME,
    price DECIMAL(10, 2),
    description TEXT,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Tabela de ligação EVENTOS <-> COLEÇÕES
-- Tabela intermédia para suportar múltiplas coleções num evento
CREATE TABLE event_collections (
    event_id INT NOT NULL,
    collection_id INT NOT NULL,
    PRIMARY KEY (event_id, collection_id),
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Tabela de PRESENÇAS E CLASSIFICAÇÕES
-- Guarda se o user foi ao evento e a classificação (1-5)
CREATE TABLE event_attendance (
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    is_present BOOLEAN DEFAULT FALSE,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    PRIMARY KEY (user_id, event_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Tabela de DESENVOLVEDORES
-- Tabela isolada para a página "Sobre"
CREATE TABLE developers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    faculty VARCHAR(100),
    course VARCHAR(100),
    photo_path VARCHAR(255) DEFAULT 'images/profile.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- INSERTS OBRIGATÓRIOS (Para os desenvolvedores aparecerem logo)
INSERT INTO developers (name, email, faculty, course) VALUES 
('Dev 1', 'dev1@fe.up.pt', 'FEUP', 'MEEC'),
('Dev 2', 'dev2@fe.up.pt', 'FEUP', 'MEEC'),
('Dev 3', 'dev3@fe.up.pt', 'FEUP', 'MEEC'),
('Dev 4', 'dev4@fe.up.pt', 'FEUP', 'MEEC');