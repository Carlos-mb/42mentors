-- Migración 006 (2026-10-19): mentores oficiales y manguitos se marcan ellos mismos (mentor-manguito.php, soy-manguito.php).
-- Ejecutar UNA sola vez en phpMyAdmin (pestaña SQL), ANTES de subir el código (solo añade y copia; el código anterior la ignora).
--
-- users.manguito_role: marca que se pone la propia persona tras aceptar el consentimiento.
-- users.manguito_blocked: el administrador le ha quitado la marca y no puede volver a ponérsela.
-- Quien ya estaba en roster y ya había aceptado (tiene fila en users) conserva su rol; el resto tendrá que marcarse con el enlace.

ALTER TABLE users
    ADD COLUMN manguito_role ENUM('mentor', 'manguito') NULL AFTER available_until,
    ADD COLUMN manguito_blocked TINYINT(1) NOT NULL DEFAULT 0 AFTER manguito_role;

UPDATE users u
  JOIN roster r ON r.login = u.login
   SET u.manguito_role = r.role;
