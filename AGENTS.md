# Reglas para asistentes de IA en este repositorio

Para cualquier IA que trabaje en el proyecto (Claude Code, Cursor, Copilot, Codex, Gemini…).

Contexto del proyecto, decisiones y condiciones de uso de la API de 42: [CLAUDE.md](CLAUDE.md).
Desarrollo en local y despliegue: [DESARROLLO.md](DESARROLLO.md).

Carlos (`Carlos-mb`) es la única persona que trabaja en el proyecto. Se trabaja directamente en `main`, sin ramas ni Pull Requests obligatorias.

## Git
- Commits pequeños, con mensajes en español y el formato `tipo(zona opcional): qué hace`. Tipos: `feat`, `fix`, `docs`, `style`, `refactor`, `chore`.
- Añade los ficheros por su nombre y revisa `git status` antes de cada commit.
- **Nunca añadas al commit:** `.env`, `config.php`, `API-Docs/`, `hosting-tests/`, `deploy/`, `datos.txt`, `CLAUDE.local.md`, ficheros con contraseñas, tokens o el `client_secret`, ni datos de otros alumnos (nombres, logins o fotos en capturas o ejemplos).
- **La URL del repo** (https://github.com/Carlos-mb/42mentors) no se cambia: ni renombrar, ni transferir, ni borrar, ni hacerlo privado.
- Si se sube un secreto por error: no reescribas el historial. Hay que **regenerarlo en la intra de 42** y escribir a security@42.fr.

## Reglas del proyecto
- No leas ni muestres el contenido de `.env`.
- Los datos de la API de 42 solo pueden verse tras el login con 42; no los pongas en páginas públicas, el README ni los ejemplos.
- Stack: PHP 8.1 sin framework + MySQL (PDO). No añadas frameworks, Composer ni Node sin consultarlo.
- Producción es **https://42mentors.com** y puede llevar cambios hechos solo en el servidor (ver `CLAUDE.local.md`). No despliegues ni modifiques el servidor sin que Carlos lo pida.
