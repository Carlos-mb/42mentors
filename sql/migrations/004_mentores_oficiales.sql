-- Migración 004 (2026-10-19): mentores oficiales, manguitos y «Disponibles ahora».
-- Ejecutar UNA sola vez en phpMyAdmin (pestaña SQL), ANTES de subir el código (solo añade; el código anterior la ignora).
--
-- roster: lista que gestiona el administrador (admin.php). Solo login y rol; es lo único que existe antes del consentimiento
-- y no se muestra a nadie hasta que la persona acepta. «manguito» solo lo ven la propia persona y el administrador.
-- users.available_until: hasta cuándo ha dicho un mentor oficial que está disponible (caduca sola).

CREATE TABLE IF NOT EXISTS roster (
    login    VARCHAR(64) NOT NULL PRIMARY KEY,
    role     ENUM('mentor', 'manguito') NOT NULL,
    added_at DATETIME    NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE users
    ADD COLUMN available_until DATETIME NULL AFTER languages;
