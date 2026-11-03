-- ============================================================
--  BASE DE DATOS: feria_lsbm
--  III Feria Virtual La Salle Business Match 2027
--  Versión final - la web (PHP) usa esta base de datos
--  Tablas: 5
-- ============================================================

-- OJO: esto borra la base de datos anterior (si existía) y la crea de cero
DROP DATABASE IF EXISTS feria_lsbm;

CREATE DATABASE IF NOT EXISTS feria_lsbm
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE feria_lsbm;

-- ------------------------------------------------------------
-- TABLA: emails_autorizados
-- Solo los correos que estén aquí podrán registrarse (RF-01, RF-02)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS emails_autorizados (
    correo  VARCHAR(150) NOT NULL,
    PRIMARY KEY (correo)
);

-- ------------------------------------------------------------
-- TABLA: usuarios
-- Empresas y visitantes. El correo es el usuario (RF-03, RF-04)
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- TABLA: stands
-- Un stand por empresa (usuario_id es UNIQUE) (RF-06)
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- TABLA: votos
-- usuario_id es la clave primaria: cada usuario solo puede votar una vez (RF-15)
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- TABLA: configuracion
-- Una sola fila (id = 1) con las fechas de la votación (RF-16)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS configuracion (
    id               INT       NOT NULL,
    votacion_inicio  DATETIME  NOT NULL,
    votacion_fin     DATETIME  NOT NULL,
    PRIMARY KEY (id)
);

-- ============================================================
-- DATOS INICIALES
-- ============================================================

-- Fechas de la votación (CAMBIAR por las fechas reales de la feria)
INSERT INTO configuracion (id, votacion_inicio, votacion_fin)
    VALUES (1, '2027-01-21 09:00:00', '2027-01-21 17:00:00');

-- Correos autorizados de ejemplo (sustituir por los reales)
INSERT INTO emails_autorizados (correo) VALUES
    ('alumno1@lasalle.es'),
    ('alumno2@lasalle.es'),
    ('demo.empresa@lasalle.es'),
    ('demo.visitante@lasalle.es');

-- Usuarios de prueba (contraseña de los dos: 12345678)
-- Es un hash BCrypt generado igual que lo hace PHP con password_hash()
-- IMPORTANTE: borrar estos dos usuarios antes de la entrega final
INSERT INTO usuarios (nombre, tipo, correo, clave_hash) VALUES
    ('Empresa Demo',   'empresa',   'demo.empresa@lasalle.es',   '$2y$10$Rxa0n6M1TOF5GKX0N7BF4ew/eTxS7XqKf1GPUIBD4DT0lxqJ.JJi6'),
    ('Visitante Demo', 'visitante', 'demo.visitante@lasalle.es', '$2y$10$Rxa0n6M1TOF5GKX0N7BF4ew/eTxS7XqKf1GPUIBD4DT0lxqJ.JJi6');

-- Stand de ejemplo para la empresa demo (con spot, para poder probar el voto)
INSERT INTO stands (usuario_id, nombre, logo_url, web_url, videoconferencia_url, horario, spot_url)
    VALUES (1, 'Empresa Demo', 'https://example.com/logo.png', 'https://example.com',
            'https://meet.google.com/demo', 'manana', 'https://example.com/spot');

-- ============================================================
-- TRIGGERS
-- ============================================================

DELIMITER $$

-- ------------------------------------------------------------
-- TRIGGER 1: Solo se pueden registrar los correos autorizados
-- Se ejecuta ANTES de insertar para poder cancelar la operación
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- TRIGGER 2: Contar los inicios de sesión fallidos y bloquear
--            la cuenta cuando llegue a 5 intentos
-- (la web suma 1 a intentos_fallidos cada vez que falla la contraseña)
-- ------------------------------------------------------------
CREATE TRIGGER trg_bloquear_cuenta
BEFORE UPDATE ON usuarios
FOR EACH ROW
BEGIN
    IF NEW.intentos_fallidos >= 5 THEN
        SET NEW.bloqueado = 1;
    END IF;
END$$

-- ------------------------------------------------------------
-- TRIGGER 3: Solo las empresas pueden tener un stand
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- TRIGGER 4: Validar el voto antes de guardarlo:
--            - La votación debe estar abierta (entre inicio y fin)
--            - El stand debe tener spot
--            - No se puede votar al propio stand
-- (fusionado en un único trigger porque así lo hicimos en la tienda)
-- ------------------------------------------------------------
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

-- ============================================================
-- USUARIOS MySQL
-- ============================================================

-- Borramos el usuario de la versión anterior (si existía)
DROP USER IF EXISTS 'feria'@'localhost';

-- 1. Usuario administrador (desarrollo y mantenimiento)
--    Tiene todos los permisos sobre la BD
CREATE USER IF NOT EXISTS 'feria_admin'@'localhost' IDENTIFIED BY 'admin_pass';
GRANT ALL PRIVILEGES ON feria_lsbm.* TO 'feria_admin'@'localhost';

-- 2. Usuario de la web PHP (solo permisos necesarios)
--    No puede borrar tablas ni borrar datos
CREATE USER IF NOT EXISTS 'feria_app'@'localhost' IDENTIFIED BY 'app_pass';
GRANT SELECT, INSERT, UPDATE ON feria_lsbm.* TO 'feria_app'@'localhost';

-- Aplicar los cambios de permisos
FLUSH PRIVILEGES;

-- ============================================================
-- CONSULTAS ÚTILES PARA LA ORGANIZACIÓN (se ejecutan en Workbench)
-- ============================================================

-- Ganador del concurso de spots (RF-19):
-- SELECT s.nombre, COUNT(*) AS total_votos
-- FROM votos v JOIN stands s ON s.id = v.stand_id
-- GROUP BY s.id, s.nombre
-- ORDER BY total_votos DESC
-- LIMIT 1;

-- Desbloquear una cuenta bloqueada por intentos fallidos:
-- UPDATE usuarios SET intentos_fallidos = 0, bloqueado = 0 WHERE correo = 'correo@lasalle.es';

-- Autorizar un correo nuevo:
-- INSERT INTO emails_autorizados (correo) VALUES ('nuevo@lasalle.es');
