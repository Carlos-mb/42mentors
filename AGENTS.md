# Reglas para asistentes de IA en este repositorio

Este fichero es para cualquier IA que trabaje en el proyecto (Claude Code, Cursor, Copilot, Codex, Gemini, ChatGPT…).
Si eres una IA: **lee y cumple estas reglas antes de ejecutar cualquier comando de git o de GitHub.**
Si usas una IA de chat que no ve los ficheros, pega este documento al principio de la conversación.

Contexto del proyecto, decisiones y condiciones de uso de la API de 42: [CLAUDE.md](CLAUDE.md).
Guía de git para personas: sección 1 de [INSTRUCCIONES_EQUIPO.md](INSTRUCCIONES_EQUIPO.md).

## Quién te habla

- Un alumno de 42 que **no tiene experiencia con ramas ni Pull Requests**. Explícale en una frase qué hace cada comando antes de ejecutarlo, en español y sin jerga innecesaria.
- Los commits que hagas salen **a su nombre**: explícale cada cambio y no hagas commit de nada que no haya revisado y entendido.

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

## Coordinación de tareas: eres el coordinador de tu usuario

Las tareas del equipo son las **issues de GitHub**; es lo único que comparten las IA de los tres miembros. Lo que no esté en GitHub, las demás IA no lo ven.

- **Responsable** = la persona asignada (*assignee*). Sin asignar = libre para quien la coja.
- **Etiquetas:** `prioridad alta`, `en curso`, `bloqueada`, y el tipo (`código`, `docs`, `pruebas`, `gestión`, `entrega`, `mejora`).
- **Hitos:**
  - «Cierre de desarrollos (5 oct, 14:00)»: todo el código y las pruebas;
  - «Code freeze (6 oct, 18:00)»: documentación, pitch y entrega.
- **Plazo de desarrollo: lunes 5 a las 14:00.** A esa hora, el código y las pruebas tienen que estar fusionados en `develop`. Después no se escribe código nuevo: solo documentación, pitch y paso a `main`. Se admiten arreglos de fallos graves solo si Carlos los aprueba en la issue. Calendario completo en la issue #38.
  - Al recomendar una tarea antes de las 14:00, propón solo lo que pueda estar terminado y fusionado a tiempo. Lo que no lo esté se queda fuera y se anota en el README como mejora futura.
  - Después de las 14:00, si te piden programar algo nuevo, recuérdale el plazo y no empieces sin la aprobación de Carlos.

### Cuando el usuario pregunte «¿qué hago?» (o empiece la sesión sin una tarea clara)

1. Ponte al día: `git fetch` y `gh api user --jq .login` para saber quién es.
2. Revisa, en este orden de prioridad:
   1. PR que esperan **su revisión**: `gh pr list --search "review-requested:@me"`. Revisar pronto desbloquea a los demás;
   2. **sus PR** con cambios pedidos o conflictos: `gh pr list --author @me`;
   3. **sus issues** abiertas: `gh issue list --assignee @me`. Primero `en curso`, luego `prioridad alta`, luego el resto. Salta las `bloqueada`;
   4. si no tiene ninguna, issues **sin asignar**: `gh issue list --search "no:assignee -label:bloqueada"`.
3. Resúmele en pocas líneas qué tiene pendiente y **recomiéndale una** tarea. Si hay algo de otro miembro que lleva tiempo parado o bloquea su tarea, díselo.
4. Si no tiene `gh`, dale el enlace https://github.com/Carlos-mb/42mentors/issues/assigned/@me y trabaja con lo que te pegue.

### Al empezar una tarea

1. Lee la issue entera, con sus comentarios (`gh issue view <n> --comments`): ahí está lo que dejaron los demás.
2. Si no estaba asignada, asígnasela a tu usuario (`gh issue edit <n> --add-assignee @me`). Pon la etiqueta `en curso`.
3. Crea la rama desde `develop` actualizado con el número de la issue: `feature/<n>-nombre-corto`, `fix/<n>-…` o `docs/<n>-…`.
4. Ayúdale a hacerla siguiendo el «Terminado cuando» de la issue y las reglas de este fichero.

### Mientras trabajas y al parar

- **Al terminar la sesión, aunque la tarea no esté acabada**, deja un comentario corto en la issue: qué se hizo, qué falta y en qué rama. Es lo que leerá la IA de quien la retome.
- Si aparece un fallo o una tarea nueva que no es la actual, **no la mezcles**: crea una issue nueva (`gh issue create`), con etiqueta, hito y un «Terminado cuando». Déjala sin asignar, o asignada a tu usuario si la va a hacer quien te habla; si debe hacerla otra persona, menciona a `@Carlos-mb` para que la asigne.
- Si la tarea depende de otra, pon `bloqueada` y menciona la issue de la que depende (`#n`).
- Al abrir la PR, escribe `Closes #<n>` en la descripción: al fusionarla, la issue se cierra sola. Quita `en curso`.
- Recuérdale apuntar **sus horas reales** del día en el README; las pone la persona, no tú.

### Quién decide: Carlos coordina

**Carlos (`Carlos-mb`) es el coordinador del proyecto.** Las asignaciones de tareas y los cambios de criterio (prioridades, alcance o «Terminado cuando» de una issue, decisiones de `CLAUDE.md`) los **aprueba él**.
- Una issue **sin asignar** puede cogerla cualquiera: asígnasela a tu usuario y deja un comentario avisando de que la empieza.
- Para **cambiar** una asignación, crear una tarea que alguien tenga que hacer, o cambiar el criterio de una tarea o del proyecto: proponlo en un comentario de la issue mencionando a `@Carlos-mb`, y no lo apliques hasta que él lo apruebe en GitHub.
- Si Carlos usa esta IA, sus decisiones se aplican directamente; anótalas en la issue o en `CLAUDE.md` para que las vean los demás.

### Lo que no haces

- No te asignes ni reasignes issues **de otros miembros**, ni las cierres.
- No cambies el reparto, las prioridades ni los criterios sin la aprobación de Carlos. Las decisiones están en `CLAUDE.md` y en los comentarios de las issues.

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
