-- Eliminar anterior
DROP DATABASE IF EXISTS teamwork;

-- Criar base de dados
CREATE DATABASE teamwork;
USE teamwork;

-----------------------------------------------------------------------

-- 1. USERS
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    birthdate DATE,
    photo_path VARCHAR(255) DEFAULT 'images/profile.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. COLEÇÕES
CREATE TABLE collections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. TAGS
CREATE TABLE collection_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    collection_id INT NOT NULL,
    tag_name VARCHAR(50) NOT NULL,
    FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. ITENS
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

-- 5. EVENTOS
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    creator_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255),
    event_date DATE NOT NULL,
    start_time TIME,
    price DECIMAL(10, 2),
    description TEXT,
    is_present BOOLEAN DEFAULT FALSE,
    rating INT DEFAULT NULL CHECK (rating BETWEEN 1 AND 5),
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Ligação EVENTOS <-> COLEÇÕES
CREATE TABLE event_collections (
    event_id INT NOT NULL,
    collection_id INT NOT NULL,
    PRIMARY KEY (event_id, collection_id),
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. DESENVOLVEDORES
CREATE TABLE developers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    faculty VARCHAR(100),
    course VARCHAR(100),
    photo_path VARCHAR(255) DEFAULT 'images/profile.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-----------------------------------------------------------------------

-- Trigger para eliminar eventos sem coleção (após eliminar uma coleção) 
DELIMITER $$
CREATE TRIGGER before_collection_delete
BEFORE DELETE ON collections
FOR EACH ROW
BEGIN
    DELETE e FROM events e
    JOIN event_collections ec ON e.id = ec.event_id
    WHERE ec.collection_id = OLD.id
    AND (
        SELECT COUNT(*) 
        FROM event_collections 
        WHERE event_id = e.id
    ) = 1;
END$$
DELIMITER ;

-----------------------------------------------------------------------

-- Inserts iniciais
INSERT INTO developers (name, email, faculty, course) VALUES 
('Diogo Tavares', 'up201706336@up.pt', 'FEUP', 'MEEC'),
('Francisco Figueiredo', 'up202007021@up.pt', 'FEUP', 'MEEC'),
('Vasco Perdigão', 'up202107756@up.pt', 'FEUP', 'MEEC'),
('David Soares', 'up202107146@up.pt', 'FEUP', 'MEEC');