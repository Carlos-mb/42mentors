# Reglas para asistentes de IA en este repositorio

Este fichero es para cualquier IA que trabaje en el proyecto (Claude Code, Cursor, Copilot, Codex, Gemini, ChatGPT…).
Si eres una IA: **lee y cumple estas reglas antes de ejecutar cualquier comando de git o de GitHub.**
Si usas una IA de chat que no ve los ficheros, pega este documento al principio de la conversación.

Contexto del proyecto, decisiones y condiciones de uso de la API de 42: [CLAUDE.md](CLAUDE.md).
Guía de git para personas: sección 1 de [INSTRUCCIONES_EQUIPO.md](INSTRUCCIONES_EQUIPO.md).

## Quién te habla

- Un alumno de 42 que **no tiene experiencia con ramas ni Pull Requests**. Explícale en una frase qué hace cada comando antes de ejecutarlo, en español y sin jerga innecesaria.
- Los commits que hagas salen **a su nombre** y cuentan en la nota del hackathon (la mitad de la evaluación entre equipos es «Git y ramas»).

## Repositorio

- GitHub: https://github.com/Carlos-mb/42mentors
- Ramas fijas, **protegidas** (solo se cambian mediante Pull Request con 1 aprobación):
  - `main`: lo que está desplegado en https://42.2275676.xyz;
  - `develop`: integración de las tareas terminadas.
- Ramas de trabajo, una por tarea, siempre creadas desde `develop`:
  - `feature/nombre-corto` (funcionalidad);
  - `fix/nombre-corto` (arreglo);
  - `docs/nombre-corto` (documentación).
  - Nombre en minúsculas, con guiones, sin tildes ni espacios.

## Reglas de git (obligatorias)

1. **Nunca hagas commit ni push en `main` ni en `develop`.** Antes de cualquier commit, comprueba la rama con `git branch --show-current`. Si es `main` o `develop`, crea primero una rama de trabajo.
2. **Para empezar una tarea:** `git switch develop`, `git pull` y `git switch -c feature/...`.
3. **Antes de cada commit:**
   - comprueba con `git config user.email` que el email está configurado; si no, pídeselo al usuario (debe ser de su cuenta de GitHub);
   - ejecuta `git status` y enséñale al usuario qué ficheros entran;
   - añade los ficheros **por su nombre**; no uses `git add .` ni `git add -A` sin haber revisado la lista.
4. **Nunca añadas al commit:** `.env`, `config.php`, `API-Docs/`, `hosting-tests/`, `deploy/`, `datos.txt`, ficheros con contraseñas, tokens o el `client_secret`, ni datos de otros alumnos (nombres, logins o fotos en capturas o ejemplos). Si ves uno en `git status`, para y avisa.
5. **Commits pequeños**, uno por cada paso con sentido propio. Mensajes en español con el formato `tipo(zona opcional): qué hace`. Tipos: `feat`, `fix`, `docs`, `style`, `refactor`, `chore`. Ejemplo: `feat(mentors): muestra la coalición en la ficha`.
6. **Pull Requests (PR):**
   - siempre con **base `develop`**; solo es `main` cuando el usuario pide expresamente pasar `develop` a producción;
   - pon un título claro y una descripción con qué cambia y cómo probarlo;
   - como revisor, pon a otro miembro del equipo.
7. **Cosas que decide la persona, no tú:**
   - **no fusiones (merge) ninguna PR** ni la apruebes: lo hace una persona del equipo desde GitHub;
   - si el usuario te pide fusionar, usa «merge commit», **nunca squash ni rebase**, para que se conserven los commits y su autor;
   - no cambies la configuración del repositorio, ni las protecciones, colaboradores o visibilidad.
8. **Prohibido:**
   - `git push --force` (y `--force-with-lease`) sobre ramas compartidas;
   - `git reset --hard` sobre `main` o `develop`;
   - reescribir el historial de commits ya subidos;
   - borrar ramas de otros miembros;
   - saltarse protecciones o hooks (`--no-verify`).
9. **Para poner al día una rama**, usa `git merge develop`, no rebase. Si hay conflictos, explícale al usuario cada uno y resuélvelos con él; no descartes cambios de otros.
10. **Si se ha subido un secreto por error:** no intentes ocultarlo reescribiendo el historial. Avisa al usuario de que hay que **regenerarlo en la intra de 42**, avisar al equipo y escribir a security@42.fr.

## Reglas del proyecto que también te afectan

- No leas ni muestres el contenido de `.env`.
- Los datos de la API de 42 solo pueden verse tras el login con 42; no los pongas en páginas públicas, el README ni los ejemplos.
- Stack: PHP 8.1 sin framework + MySQL (PDO). No añadas frameworks, Composer ni Node sin acordarlo con el equipo.
