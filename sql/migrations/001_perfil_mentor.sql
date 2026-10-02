-- Migración 001 (2026-10-02): perfil de mentor.
-- Para BD creadas con la primera versión de schema.sql. Ejecutar UNA sola vez en phpMyAdmin (pestaña SQL).
-- Es compatible con el código anterior: se puede aplicar antes de subir la nueva versión.

ALTER TABLE users
    ADD COLUMN bio          VARCHAR(160) NULL AFTER campus_id,
    ADD COLUMN availability ENUM('available', 'busy', 'paused') NOT NULL DEFAULT 'available' AFTER bio,
    ADD COLUMN contact_pref ENUM('cluster', 'slack', 'both')    NOT NULL DEFAULT 'cluster' AFTER availability,
    ADD COLUMN slack_handle VARCHAR(80)  NULL AFTER contact_pref,
    ADD COLUMN languages    VARCHAR(64)  NULL AFTER slack_handle;

ALTER TABLE mentor_projects
    ADD COLUMN note VARCHAR(160) NULL AFTER project_id;
