-- Eliminar anterior
DROP DATABASE IF EXISTS teamwork;

-- Criar base de dados
CREATE DATABASE teamwork;
USE teamwork;

-- ---------------------------------------------------------------------

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
    is_present BOOLEAN DEFAULT NULL,
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

-- --------------------------------------------------------------------------------------------

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

-- --------------------------------------------------------------------------------------------

-- DESENVOLVEDORES ----------------------------------------------------
INSERT INTO developers (name, email, faculty, course) VALUES 
('Diogo Tavares', 'up201706336@up.pt', 'FEUP', 'MEEC'),
('Francisco Figueiredo', 'up202007021@up.pt', 'FEUP', 'MEEC'),
('Vasco Perdigão', 'up202107756@up.pt', 'FEUP', 'MEEC'),
('David Soares', 'up202107146@up.pt', 'FEUP', 'MEEC');

-- USER ---------------------------------------------------------------
-- Email: admin@gmail.com
-- Pass: admin
INSERT INTO users (email, password, name, birthdate, photo_path) 
VALUES 
('admin@gmail.com', '$2y$10$oyw53IARSQj9i9Yd2Yhtm.37Xk3tQ2em7EZHWyXhu/9jYNXGDRc0O', 'Administrador', '2000-01-01', 'images/users/admin.png');

SET @user_id = LAST_INSERT_ID();

-- COLEÇÕES -----------------------------------------------------------
INSERT INTO collections (user_id, title, description, created_date) VALUES 
(@user_id, 'Ferraris de Elite', 'Os cavalos rampantes mais icónicos da história.', NOW());
SET @col_ferrari = LAST_INSERT_ID();
INSERT INTO collection_tags (collection_id, tag_name) VALUES (@col_ferrari, 'supercarro'), (@col_ferrari, 'italiano'), (@col_ferrari, 'carros');

INSERT INTO collections (user_id, title, description, created_date) VALUES 
(@user_id, 'Carros Mercedes-Benz AMG', 'Performance alemã e flechas de prata.', NOW());
SET @col_mercedes = LAST_INSERT_ID();
INSERT INTO collection_tags (collection_id, tag_name) VALUES (@col_mercedes, 'amg'), (@col_mercedes, 'f1'), (@col_mercedes, 'carros');

INSERT INTO collections (user_id, title, description, created_date) VALUES 
(@user_id, 'Carros BMW M Power', 'O derradeiro prazer de condução.', NOW());
SET @col_bmw = LAST_INSERT_ID();
INSERT INTO collection_tags (collection_id, tag_name) VALUES (@col_bmw, 'm-performance'), (@col_bmw, 'alemao'), (@col_bmw, 'carros');

INSERT INTO collections (user_id, title, description, created_date) VALUES 
(@user_id, 'Cartas Pokémon', 'Cartas holográficas e primeiras edições.', NOW());
SET @col_pokemon = LAST_INSERT_ID();
INSERT INTO collection_tags (collection_id, tag_name) VALUES (@col_pokemon, 'cartas'), (@col_pokemon, 'nintendo');

INSERT INTO collections (user_id, title, description, created_date) VALUES 
(@user_id, 'Cartas Yu-Gi-Oh!', 'É hora do duelo! As minhas cartas de armadilha e monstros.', NOW());
SET @col_yugioh = LAST_INSERT_ID();
INSERT INTO collection_tags (collection_id, tag_name) VALUES (@col_yugioh, 'anime'), (@col_yugioh, 'konami'), (@col_yugioh, 'cartas');


-- ITENS --------------------------------------------------------------
INSERT INTO items (collection_id, name, acquisition_date, importance, price, weight, image_path) VALUES 
(@col_ferrari, 'Ferrari F40', '2020-01-01', 10, 250.00, 1100.00, 'images/items/f40.jpeg'),
(@col_ferrari, 'Ferrari F50', '2021-02-15', 9, 300.00, 1230.00, 'images/items/f50.png'),
(@col_ferrari, 'Ferrari 458 Italia', '2022-05-20', 8, 180.00, 1380.00, 'images/items/458italia.png');

INSERT INTO items (collection_id, name, acquisition_date, importance, price, weight, image_path) VALUES 
(@col_mercedes, 'Mercedes-AMG F1 W13', '2023-01-10', 10, 150.00, 798.00, 'images/items/amg_f1_w13.jpg'),
(@col_mercedes, 'Mercedes-AMG GT R', '2022-11-05', 9, 140.00, 1630.00, 'images/items/amg_gt-r.png'),
(@col_mercedes, 'Mercedes-AMG GT3', '2021-08-30', 8, 130.00, 1285.00, 'images/items/amg_gt3.png');

INSERT INTO items (collection_id, name, acquisition_date, importance, price, weight, image_path) VALUES 
(@col_bmw, 'BMW M1 Procar', '2019-12-25', 10, 500.00, 1300.00, 'images/items/m1.jfif'),
(@col_bmw, 'BMW M4 GT3', '2023-02-28', 9, 200.00, 1250.00, 'images/items/m4_gt3.jfif'),
(@col_bmw, 'BMW M4 GTS', '2020-07-14', 8, 120.00, 1510.00, 'images/items/m4_gts.png'),
(@col_bmw, 'BMW M2 CS', '2023-06-15', 9, 110.00, 1550.00, 'images/items/m2-cs.jpg'),
(@col_bmw, 'BMW M3 E30', '2018-09-20', 10, 300.00, 1200.00, 'images/items/m3-e30.jpg'),
(@col_bmw, 'BMW M3 F80', '2021-04-10', 8, 85.00, 1595.00, 'images/items/m3-f80.jpeg'),
(@col_bmw, 'BMW M3 G80', '2023-01-05', 9, 120.00, 1730.00, 'images/items/m3-g80.png'),
(@col_bmw, 'BMW M5 Competition', '2022-11-12', 9, 135.00, 1895.00, 'images/items/m5-competition.jpg'),
(@col_bmw, 'BMW M5 E39', '2019-03-30', 10, 250.00, 1795.00, 'images/items/m5-e39.jpg'),
(@col_bmw, 'BMW M5 E60', '2020-08-18', 9, 180.00, 1830.00, 'images/items/m5-e60.jpg'),
(@col_bmw, 'BMW M6 GT3', '2021-12-01', 8, 140.00, 1300.00, 'images/items/m6-gt3.jpg'),
(@col_bmw, 'BMW M8 Competition', '2023-02-22', 9, 160.00, 1960.00, 'images/items/m8-competition.png');

INSERT INTO items (collection_id, name, acquisition_date, importance, price, weight, image_path) VALUES 
(@col_pokemon, 'Charizard Base Set', '2015-01-01', 10, 999.00, 2.00, 'images/items/pokemon1.jpg'),
(@col_pokemon, 'Pikachu', '2016-06-01', 7, 50.00, 2.00, 'images/items/pokemon2.png'),
(@col_pokemon, 'Dragapult VMAX', '2023-10-01', 6, 20.00, 2.00, 'images/items/pokemon3.jpg');

INSERT INTO items (collection_id, name, acquisition_date, importance, price, weight, image_path) VALUES 
(@col_yugioh, 'Exodia the Forbidden One', '2014-09-01', 10, 450.00, 2.00, 'images/items/1.png'),
(@col_yugioh, 'Number 83: Galaxy Queen', '2018-03-20', 7, 30.00, 2.00, 'images/items/2.png'),
(@col_yugioh, 'Morphing Jar', '2017-07-12', 8, 45.00, 2.00, 'images/items/3.png');


-- EVENTOS ------------------------------------------------------------
-- Futuros
INSERT INTO events (creator_id, name, location, event_date, start_time, price, description, is_present, rating) 
VALUES 
(@user_id, 'Salão Automóvel 2026', 'FIL, Lisboa', '2026-03-10', '10:00:00', 25.00, 'Para expor coleções de diversas marcas.', NULL, NULL);
SET @evt_auto = LAST_INSERT_ID();
INSERT INTO event_collections (event_id, collection_id) VALUES (@evt_auto, @col_ferrari), (@evt_auto, @col_bmw), (@evt_auto, @col_mercedes);

-- Passados
INSERT INTO events (creator_id, name, location, event_date, start_time, price, description, is_present, rating) 
VALUES 
(@user_id, 'Torneio Nacional Cartas Colecionáveis', 'Porto', '2025-09-20', '14:30:00', 5.00, 'Para mostrar o meu deck de Yu-Gi-Oh e trocar algumas cartas Pokémon.', 1, 4);
SET @evt_tcg = LAST_INSERT_ID();
INSERT INTO event_collections (event_id, collection_id) VALUES (@evt_tcg, @col_yugioh), (@evt_tcg, @col_pokemon);

INSERT INTO events (creator_id, name, location, event_date, start_time, price, description, is_present, rating) 
VALUES 
(@user_id, 'Encontro de Clássicos Mercedes-Benz', 'Estoril Garden', '2025-11-15', '09:00:00', 0.00, 'Um encontro fantástico para entusiastas da marca Mercedes-Benz.', NULL, NULL);
SET @evt_mercedes = LAST_INSERT_ID();
INSERT INTO event_collections (event_id, collection_id) VALUES (@evt_mercedes, @col_mercedes);