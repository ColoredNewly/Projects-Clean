-- BizDirectory Database Schema
-- Run this once on your MySQL server

CREATE DATABASE IF NOT EXISTS bizdir CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bizdir;

CREATE TABLE IF NOT EXISTS companies (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(200)  NOT NULL,
    address     VARCHAR(500)  NOT NULL,
    latitude    DOUBLE        DEFAULT 0.0,
    longitude   DOUBLE        DEFAULT 0.0,
    email       VARCHAR(200)  DEFAULT '',
    phone       VARCHAR(50)   DEFAULT '',
    website     VARCHAR(300)  DEFAULT '',
    categories  VARCHAR(200)  NOT NULL COMMENT 'Comma-separated: Services,Entertainment,Industry,Education',
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Sample data (optional)
INSERT INTO companies (name, address, latitude, longitude, email, phone, website, categories) VALUES
('Техно Сервис', 'ул. Македонија 12, Скопје', 41.9981, 21.4254, 'info@tehno.mk', '+389 2 311 1111', 'https://tehno.mk', 'Services'),
('Арт Галерија Мост', 'бул. Партизански Одреди 3, Скопје', 41.9967, 21.4312, 'art@most.mk', '+389 2 322 2222', 'https://most.mk', 'Entertainment'),
('ФИНКИ', 'ул. Руѓер Бошковиќ бб, Скопје', 42.0040, 21.4090, 'contact@finki.ukim.mk', '+389 2 309 3066', 'https://finki.ukim.mk', 'Education'),
('МакСтил', 'Индустриска зона бб, Велес', 41.7122, 21.7764, 'info@maksteel.mk', '+389 43 232 111', 'https://maksteel.mk', 'Industry'),
('Кино Милениум', 'ТЦ Сити Мол, Скопје', 41.9984, 21.4296, 'kino@milenium.mk', '+389 2 300 3000', 'https://kino.mk', 'Entertainment,Services');
