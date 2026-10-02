-- Esquema COMPLETO de la base de datos (MySQL / MariaDB), para instalaciones nuevas.
-- Ejecutar una vez desde phpMyAdmin (pestaña SQL) sobre la base de datos creada en cPanel.
-- Si la BD ya existe de una versión anterior, NO ejecutes esto: aplica los ficheros de sql/migrations/.
--
-- Condiciones de uso de la API de 42: se guarda el MÍNIMO y solo tras el consentimiento
-- del usuario (consent.php). Nombre, foto, nivel, coalición y notas de 42 NO se guardan:
-- se piden a la API al mostrarlos. Lo que sí se guarda lo escribe el propio mentor.

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED NOT NULL PRIMARY KEY,    -- id de usuario en la API de 42
    login         VARCHAR(64)  NOT NULL UNIQUE,
    campus_id     INT UNSIGNED NULL,                    -- campus principal (para mostrar solo mentores del mismo campus)
    bio           VARCHAR(160) NULL,                    -- presentación escrita por el mentor
    availability  ENUM('available', 'busy', 'paused') NOT NULL DEFAULT 'available',  -- paused = oculto en las búsquedas
    contact_pref  ENUM('cluster', 'slack', 'both')    NOT NULL DEFAULT 'cluster',  -- en Slack, el usuario es el login
    languages     VARCHAR(64)  NULL,                    -- códigos separados por comas, p. ej. "es,en"
    consented_at  DATETIME     NOT NULL,                -- prueba del consentimiento («mentor desde»)
    last_login_at DATETIME     NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Catálogo de proyectos de 42 (no son datos personales)
CREATE TABLE IF NOT EXISTS projects (
    id   INT UNSIGNED NOT NULL PRIMARY KEY,             -- id de proyecto en la API de 42
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Qué proyectos se ofrece a mentorizar cada usuario
CREATE TABLE IF NOT EXISTS mentor_projects (
    user_id    INT UNSIGNED NOT NULL,
    project_id INT UNSIGNED NOT NULL,
    note       VARCHAR(160) NULL,                       -- nota opcional del mentor sobre ese proyecto
    PRIMARY KEY (user_id, project_id),
    KEY idx_project (project_id),
    CONSTRAINT fk_mp_user    FOREIGN KEY (user_id)    REFERENCES users (id)    ON DELETE CASCADE,
    CONSTRAINT fk_mp_project FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
