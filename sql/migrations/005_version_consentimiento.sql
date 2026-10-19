-- Migración 005 (2026-10-19): versión de la política que aceptó cada persona.
-- Ejecutar UNA sola vez en phpMyAdmin (pestaña SQL), ANTES de subir el código (solo añade; el código anterior la ignora).
--
-- users.consent_version: versión de la política de privacidad y condiciones de uso aceptada en consent.php.
-- Quien ya había aceptado queda en la versión 1 (el texto anterior). El código nuevo guarda la 2 a partir de ahora
-- y pide aceptar de nuevo solo si la versión guardada es menor que CONSENT_MIN_VERSION (src/auth.php).

ALTER TABLE users
    ADD COLUMN consent_version SMALLINT UNSIGNED NOT NULL DEFAULT 1 AFTER consented_at;
