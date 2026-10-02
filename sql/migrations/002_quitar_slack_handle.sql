-- Migración 002 (2026-10-02): el usuario de Slack de 42 es siempre el login de la intra,
-- así que no se pregunta ni se guarda (dato de menos = mejor para las condiciones de uso de la API).
-- Ejecutar UNA sola vez en phpMyAdmin (pestaña SQL), DESPUÉS de subir el código que ya no usa la columna
-- (el código anterior sí la usa y fallaría).

ALTER TABLE users DROP COLUMN slack_handle;
