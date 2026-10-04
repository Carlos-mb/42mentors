-- Migración 003 (2026-10-04): «Me ayudó», valoraciones de 1 a 3 de un estudiante a un mentor por un proyecto.
-- Ejecutar UNA sola vez en phpMyAdmin (pestaña SQL), ANTES de subir el código (el código anterior no usa la tabla).
--
-- Un voto por (estudiante, mentor, proyecto). Los votos son anónimos: fuera de «Mis valoraciones»
-- solo se muestran totales. created_at no cambia al modificar el voto (el ranking cuenta por esa fecha).
-- Al borrar a un usuario se borran los votos que emitió y los que recibió.

CREATE TABLE IF NOT EXISTS votes (
    voter_id   INT UNSIGNED     NOT NULL,
    mentor_id  INT UNSIGNED     NOT NULL,
    project_id INT UNSIGNED     NOT NULL,
    value      TINYINT UNSIGNED NOT NULL,
    created_at DATETIME         NOT NULL,
    updated_at DATETIME         NOT NULL,
    PRIMARY KEY (voter_id, mentor_id, project_id),
    KEY idx_mentor (mentor_id, created_at),
    KEY idx_project (project_id),
    CONSTRAINT chk_votes_value CHECK (value BETWEEN 1 AND 3),
    CONSTRAINT fk_votes_voter   FOREIGN KEY (voter_id)   REFERENCES users (id)    ON DELETE CASCADE,
    CONSTRAINT fk_votes_mentor  FOREIGN KEY (mentor_id)  REFERENCES users (id)    ON DELETE CASCADE,
    CONSTRAINT fk_votes_project FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
