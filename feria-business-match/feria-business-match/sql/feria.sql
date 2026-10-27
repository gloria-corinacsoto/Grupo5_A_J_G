-- Ejecuta este script en MySQL Workbench (rayo amarillo) para crear todo.

CREATE DATABASE IF NOT EXISTS feria_lsbm
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE feria_lsbm;

-- Una fila por cada persona inscrita
CREATE TABLE IF NOT EXISTS inscripciones (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  nombre            VARCHAR(60)  NOT NULL,
  apellidos         VARCHAR(100) NOT NULL,
  correo            VARCHAR(120) NOT NULL UNIQUE,   -- UNIQUE: no se repite el correo
  tipo              ENUM('empresa', 'visitante') NOT NULL,
  fecha_inscripcion DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Usuario propio para la web (mejor que usar "root")
CREATE USER IF NOT EXISTS 'feria'@'localhost' IDENTIFIED BY 'cambia_esta_clave';
GRANT INSERT, SELECT ON feria_lsbm.* TO 'feria'@'localhost';
FLUSH PRIVILEGES;
