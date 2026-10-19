-- Migración 007 (2026-10-19): elimina la tabla roster, que ya no se usa (la sustituye users.manguito_role, migración 006).
-- Ejecutar UNA sola vez en phpMyAdmin, DESPUÉS de subir el código nuevo y comprobar que «Manguito» funciona
-- (el código anterior todavía lee roster).

DROP TABLE IF EXISTS roster;
