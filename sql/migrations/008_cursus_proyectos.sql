-- Migración 008 (2026-10-20): a qué cursus pertenece cada proyecto, para que «Proyectos» muestre solo los del
-- camino del alumno. Se aprende de los projects_users de los mentores con consentimiento (no son datos personales).
-- Ejecutar UNA sola vez en phpMyAdmin, ANTES de subir el código nuevo.

CREATE TABLE IF NOT EXISTS project_cursus (
    project_id INT UNSIGNED NOT NULL,
    cursus_id  INT UNSIGNED NOT NULL,
    PRIMARY KEY (project_id, cursus_id),
    CONSTRAINT fk_pc_project FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
