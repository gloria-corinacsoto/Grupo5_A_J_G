DROP DATABASE IF EXISTS feria_lsbm;

CREATE DATABASE IF NOT EXISTS feria_lsbm
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE feria_lsbm;

CREATE TABLE IF NOT EXISTS emails_autorizados (
    correo  VARCHAR(150) NOT NULL,
    PRIMARY KEY (correo)
);

CREATE TABLE IF NOT EXISTS usuarios (
    id                INT          NOT NULL AUTO_INCREMENT,
    nombre            VARCHAR(100) NOT NULL,
    tipo              ENUM('empresa','visitante') NOT NULL,
    correo            VARCHAR(150) NOT NULL UNIQUE,
    clave_hash        VARCHAR(255) NOT NULL,
    fecha_alta        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    intentos_fallidos INT          NOT NULL DEFAULT 0,
    bloqueado         TINYINT(1)   NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS stands (
    id                   INT          NOT NULL AUTO_INCREMENT,
    usuario_id           INT          NOT NULL UNIQUE,
    nombre               VARCHAR(100) NOT NULL,
    logo_url             VARCHAR(255) NOT NULL,
    web_url              VARCHAR(255) NOT NULL,
    videoconferencia_url VARCHAR(255) NOT NULL,
    horario              ENUM('manana','tarde') NOT NULL DEFAULT 'manana',
    spot_url             VARCHAR(255),
    PRIMARY KEY (id),
    CONSTRAINT fk_stand_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS votos (
    usuario_id  INT       NOT NULL,
    stand_id    INT       NOT NULL,
    fecha       DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id),
    CONSTRAINT fk_voto_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_voto_stand   FOREIGN KEY (stand_id)
        REFERENCES stands(id)   ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS configuracion (
    id               INT       NOT NULL,
    votacion_inicio  DATETIME  NOT NULL,
    votacion_fin     DATETIME  NOT NULL,
    PRIMARY KEY (id)
);

INSERT INTO configuracion (id, votacion_inicio, votacion_fin)
    VALUES (1, '2027-01-21 09:00:00', '2027-01-21 17:00:00');

INSERT INTO emails_autorizados (correo) VALUES
    ('alumno1@lasalle.es'),
    ('alumno2@lasalle.es'),
    ('demo.empresa@lasalle.es'),
    ('demo.visitante@lasalle.es');

INSERT INTO usuarios (nombre, tipo, correo, clave_hash) VALUES
    ('Empresa Demo',   'empresa',   'demo.empresa@lasalle.es',   '$2y$10$Rxa0n6M1TOF5GKX0N7BF4ew/eTxS7XqKf1GPUIBD4DT0lxqJ.JJi6'),
    ('Visitante Demo', 'visitante', 'demo.visitante@lasalle.es', '$2y$10$Rxa0n6M1TOF5GKX0N7BF4ew/eTxS7XqKf1GPUIBD4DT0lxqJ.JJi6');

INSERT INTO stands (usuario_id, nombre, logo_url, web_url, videoconferencia_url, horario, spot_url)
    VALUES (1, 'Empresa Demo', 'https://example.com/logo.png', 'https://example.com',
            'https://meet.google.com/demo', 'manana', 'https://example.com/spot');

DELIMITER $$

CREATE TRIGGER trg_correo_autorizado
BEFORE INSERT ON usuarios
FOR EACH ROW
BEGIN
    DECLARE v_existe INT;

    SELECT COUNT(*) INTO v_existe
    FROM emails_autorizados WHERE correo = NEW.correo;

    IF v_existe = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Este correo no esta autorizado por la organizacion';
    END IF;
END$$

CREATE TRIGGER trg_bloquear_cuenta
BEFORE UPDATE ON usuarios
FOR EACH ROW
BEGIN
    IF NEW.intentos_fallidos >= 5 THEN
        SET NEW.bloqueado = 1;
    END IF;
END$$

CREATE TRIGGER trg_stand_solo_empresa
BEFORE INSERT ON stands
FOR EACH ROW
BEGIN
    DECLARE v_tipo VARCHAR(20);

    SELECT tipo INTO v_tipo FROM usuarios WHERE id = NEW.usuario_id;

    IF v_tipo <> 'empresa' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Solo las empresas pueden tener un stand';
    END IF;
END$$

CREATE TRIGGER trg_validar_voto
BEFORE INSERT ON votos
FOR EACH ROW
BEGIN
    DECLARE v_abierta INT;
    DECLARE v_spot    VARCHAR(255);
    DECLARE v_dueno   INT;

    SELECT COUNT(*) INTO v_abierta
    FROM configuracion
    WHERE id = 1 AND NOW() BETWEEN votacion_inicio AND votacion_fin;

    IF v_abierta = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La votacion no esta abierta';
    END IF;

    SELECT spot_url, usuario_id INTO v_spot, v_dueno
    FROM stands WHERE id = NEW.stand_id;

    IF v_spot IS NULL OR v_spot = '' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Ese stand no tiene spot para votar';
    END IF;

    IF v_dueno = NEW.usuario_id THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No puedes votar a tu propio stand';
    END IF;
END$$

DELIMITER ;

DROP USER IF EXISTS 'feria'@'localhost';
DROP USER IF EXISTS 'feria_admin'@'localhost';
DROP USER IF EXISTS 'feria_app'@'localhost';

CREATE USER 'feria_admin'@'localhost' IDENTIFIED BY 'admin_pass';
GRANT ALL PRIVILEGES ON feria_lsbm.* TO 'feria_admin'@'localhost';

CREATE USER 'feria_app'@'localhost' IDENTIFIED BY 'app_pass';
GRANT SELECT, INSERT, UPDATE ON feria_lsbm.* TO 'feria_app'@'localhost';

FLUSH PRIVILEGES;
