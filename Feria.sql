CREATE DATABASE IF NOT EXISTS Feria
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE Feria;

CREATE TABLE IF NOT EXISTS inscripciones (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  nombre            VARCHAR(60)  NOT NULL,
  apellidos         VARCHAR(100) NOT NULL,
  correo            VARCHAR(120) NOT NULL UNIQUE,
  tipo              ENUM('empresa', 'visitante') NOT NULL,
  fecha_inscripcion DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE USER IF NOT EXISTS 'feria'@'localhost' IDENTIFIED BY '1234';
GRANT INSERT, SELECT ON Feria.* TO 'feria'@'localhost';
FLUSH PRIVILEGES;