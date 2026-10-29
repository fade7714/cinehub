-- =======================================================
-- Baza Danych: cinehub_db (Kwalifikacja INF.03)
-- Projekt: CineHub - Baza Filmów
-- Autor: Backend Developer & Data Analyst
-- Silnik: InnoDB | Kodowanie: utf8mb4_unicode_ci
-- =======================================================

CREATE DATABASE IF NOT EXISTS `cinehub_db`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `cinehub_db`;

-- -------------------------------------------------------
-- Wyłączenie sprawdzania kluczy obcych na czas tworzenia
-- -------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `recenzje`;
DROP TABLE IF EXISTS `obsada`;
DROP TABLE IF EXISTS `aktorzy`;
DROP TABLE IF EXISTS `filmy`;

SET FOREIGN_KEY_CHECKS = 1;

-- -------------------------------------------------------
-- 1. Tabela: filmy
-- -------------------------------------------------------
CREATE TABLE `filmy` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tytul` VARCHAR(255) NOT NULL,
    `rezyser` VARCHAR(150) NOT NULL,
    `gatunek` VARCHAR(100) NOT NULL,
    `rok_premiery` INT NOT NULL,
    `czas_trwania` INT NOT NULL COMMENT 'Czas trwania w minutach',
    `opis` TEXT NOT NULL,
    `plakat` VARCHAR(255) DEFAULT NULL COMMENT 'Ścieżka do pliku lub URL plakatu',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_filmy_gatunek` (`gatunek`),
    INDEX `idx_filmy_rok` (`rok_premiery`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 2. Tabela: aktorzy
-- -------------------------------------------------------
CREATE TABLE `aktorzy` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `imie` VARCHAR(100) NOT NULL,
    `nazwisko` VARCHAR(100) NOT NULL,
    `data_urodzenia` DATE DEFAULT NULL,
    `zdjecie` VARCHAR(255) DEFAULT NULL COMMENT 'Ścieżka lub URL zdjęcia aktora',
    `biografia` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_aktorzy_nazwisko` (`nazwisko`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 3. Tabela: obsada (Relacja wiele-do-wielu filmy <-> aktorzy)
-- -------------------------------------------------------
CREATE TABLE `obsada` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `film_id` INT NOT NULL,
    `aktor_id` INT NOT NULL,
    `rola` VARCHAR(150) NOT NULL COMMENT 'Postać odgrywana przez aktora w filmie',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_obsada_film` 
        FOREIGN KEY (`film_id`) REFERENCES `filmy` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_obsada_aktor` 
        FOREIGN KEY (`aktor_id`) REFERENCES `aktorzy` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `uq_film_aktor_rola` (`film_id`, `aktor_id`, `rola`),
    INDEX `idx_obsada_film` (`film_id`),
    INDEX `idx_obsada_aktor` (`aktor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 4. Tabela: recenzje
-- -------------------------------------------------------
CREATE TABLE `recenzje` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `film_id` INT NOT NULL,
    `autor` VARCHAR(100) NOT NULL,
    `ocena` TINYINT NOT NULL CHECK (`ocena` BETWEEN 1 AND 10),
    `tresc` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_recenzje_film` 
        FOREIGN KEY (`film_id`) REFERENCES `filmy` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_recenzje_film` (`film_id`),
    INDEX `idx_recenzje_ocena` (`ocena`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

