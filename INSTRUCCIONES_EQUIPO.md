# Instrucciones para el equipo

Estado a **2 de octubre de 2026**:
- El MVP está **desplegado y funcionando** en https://42.2275676.xyz (probado por Carlos).
- Después se añadieron el **directorio de mentores** y la **ficha del mentor**, **pendientes de probar** en el servidor.
- Repositorio: https://github.com/Carlos-mb/42mentors (privado hasta el code freeze; después, público para la evaluación).

Este documento explica cómo continuar.

Contexto completo del proyecto, decisiones y condiciones de uso de la API: [CLAUDE.md](CLAUDE.md).

---

## 0. Reglas que no se pueden romper

- **Nunca subir secretos** (`.env`, `client_secret`, contraseñas de la base de datos) a git, a Slack ni a capturas. Si se filtra uno, se regenera en la intra y se avisa a security@42.fr.
- **No subir `API-Docs/`** al repositorio: es material de 42 tras login con datos de personas. Ya está en `.gitignore`.
- **No poner datos de otros alumnos** en el README, las capturas ni el pitch. Para la demo, usad vuestras propias cuentas.
- **Nada de un único commit gigante.** Se penaliza. Commits pequeños y frecuentes, en ramas.
- **Code freeze: martes 6 de octubre a las 18:00.** Fecha límite interna: lunes 5 por la noche.

---

## 1. Git y GitHub: cómo trabajamos con ramas

> **Por qué importa:** trabajar en ramas con Pull Requests revisadas permite que tres personas cambien el proyecto a la vez sin pisarse, y deja claro quién hizo cada cambio. Cada uno sube su propio trabajo **desde su propia cuenta**, poco a poco.
>
> **Si nunca habéis usado ramas**, leed 1.1 y 1.2 con calma. El resto es seguir los pasos. **Si vais a usar una IA**, leed también 1.9.

### 1.1 Las ideas básicas en 5 minutos

Pensad en el proyecto como un **documento compartido** en el que trabajamos los tres a la vez sin pisarnos.

| Palabra | Qué es | Comparación |
|---|---|---|
| **Repositorio** (repo) | La carpeta del proyecto con todo su historial de cambios. Hay una copia en GitHub (https://github.com/Carlos-mb/42mentors) y otra en el ordenador de cada uno. | La carpeta compartida. |
| **Commit** | Una «foto» guardada de los cambios, con un mensaje que explica qué se hizo. Se queda en vuestro ordenador hasta que hacéis *push*. | Pulsar «Guardar versión» con una nota. |
| **Rama** (*branch*) | Una línea de trabajo paralela. Copia el proyecto en ese momento y, mientras trabajáis en ella, **no afectáis a lo de los demás**. | Una copia de borrador del documento. |
| **Push** | Subir vuestros commits a GitHub. | Sincronizar con la nube. |
| **Pull** | Bajar de GitHub lo que han subido los demás. | Descargar la última versión. |
| **Pull Request (PR)** | Una **petición** en GitHub: «he terminado esta tarea en mi rama; revisadla y, si está bien, juntadla con `develop`». Muestra los cambios línea a línea y permite comentar. | Mandar el borrador a un compañero para que lo revise antes de pasarlo al documento final. |
| **Revisión / aprobación** (*review*) | Otro miembro mira la PR y pulsa *Approve* (aprobar) o pide cambios. | El visto bueno del compañero. |
| **Merge** (fusionar) | Juntar los cambios de una rama con otra. Se hace con un botón de la PR, cuando está aprobada. | Pegar el borrador revisado en el documento final. |
| **Conflicto** | Dos personas cambiaron **las mismas líneas** de un fichero, y git no sabe con cuál quedarse. Hay que elegirlo a mano (ver 1.6). | Dos ediciones del mismo párrafo. |

**Cómo se ve con nuestras ramas** (cada `●` es un commit):

```
main      ●───────────────────────────────●        ← lo publicado en la web
           \                             /
develop     ●───────────●───────────●───●          ← donde se juntan las tareas terminadas
             \         /  \        /
feature/a     ●───●───●    \      /                ← tarea de Florentin (PR 1)
                            \    /
fix/b                        ●──●                  ← tarea de Lucas (PR 2)
```

Es decir: cada tarea sale de `develop`, se trabaja en su rama y vuelve a `develop` mediante una PR revisada. De vez en cuando, `develop` pasa a `main` con otra PR, y eso es lo que se publica.

### 1.2 Nuestras ramas

| Rama | Para qué | ¿Se puede escribir directamente? |
|---|---|---|
| `main` | Lo que está **publicado** en https://42.2275676.xyz. Siempre debe funcionar. | **No.** GitHub lo bloquea. Solo cambia mediante una PR desde `develop`, aprobada por otra persona. |
| `develop` | Donde se juntan las tareas terminadas. | **No.** GitHub lo bloquea. Solo cambia mediante una PR desde una rama de tarea, aprobada por otra persona. |
| `feature/…` | Una funcionalidad nueva. Ej.: `feature/boton-me-ayudo` | Sí: es **vuestra** rama de trabajo. |
| `fix/…` | Arreglar un error. Ej.: `fix/foto-mentor-vacia` | Sí. |
| `docs/…` | README, registro de horas, instrucciones. Ej.: `docs/horas-florentin-3-oct` | Sí. |

Reglas:
- **Una rama por tarea**, con nombre corto en minúsculas y guiones, sin tildes ni espacios.
- Las ramas de tarea **siempre salen de `develop`** y **vuelven a `develop`** mediante una PR.
- **Ramas cortas:** mejor varias PR pequeñas al día que una enorme el lunes. Así hay menos conflictos y se ve trabajo continuo.

### 1.3 Preparación (una sola vez, cada uno en su ordenador)

1. **Aceptar la invitación** al repositorio: llega por email, o en https://github.com/Carlos-mb/42mentors/invitations
2. **Instalar git**:
   - Windows: `winget install --id Git.Git -e`. Después, cerrar y abrir la terminal.
   - macOS o Linux: suele venir instalado (`git --version`).
3. **Decirle a git quiénes sois.** Si el email no es de vuestra cuenta de GitHub, los commits **no cuentan como vuestros**. Recomendado: el email *noreply* de GitHub → Settings → Emails, que acaba en `@users.noreply.github.com`, para no publicar el vuestro.
   ```bash
   git config --global user.name "Tu Nombre"
   git config --global user.email "ID+usuario@users.noreply.github.com"
   ```
4. **Descargar el repositorio** (*clonar*). Para probar con XAMPP, hacedlo dentro de `C:\xampp\htdocs` (ver sección 2):
   ```bash
   git clone https://github.com/Carlos-mb/42mentors.git
   cd 42mentors
   git switch develop
   ```
   La primera vez que hagáis `push`, se abrirá el navegador para iniciar sesión en GitHub.
5. **Opcional:** instalar **GitHub CLI** (`gh`) para abrir PR desde la terminal o que lo haga vuestra IA. En Windows: `winget install --id GitHub.cli -e`, y después `gh auth login`.

### 1.4 Una tarea de principio a fin

**Paso 1. Ponerse al día y crear la rama de la tarea.**
```bash
git switch develop                  # ir a develop
git pull                            # bajar lo último que hayan subido los demás
git switch -c feature/mi-tarea      # crear vuestra rama y entrar en ella
```
Desde aquí, todo lo que cambiéis queda en `feature/mi-tarea` y no afecta a nadie.

**Paso 2. Trabajar y guardar commits pequeños.** Cada vez que terminéis un paso que tenga sentido por sí mismo:
```bash
git status                                          # qué ficheros habéis cambiado
git add public/mentor.php src/mentors.php           # elegir qué entra en la foto
git commit -m "feat(mentors): muestra la coalición en la ficha"
```
Mirad **siempre** `git status` antes de `git add`. **Nunca** debe aparecer `.env`, `API-Docs/` ni nada con contraseñas. No uséis `git add .` sin haber revisado la lista.

**Paso 3. Subir la rama a GitHub.** Conviene hacerlo varias veces al día, aunque la tarea no esté terminada: es vuestra copia de seguridad.
```bash
git push -u origin feature/mi-tarea     # la primera vez
git push                                # las siguientes
```

**Paso 4. Abrir la Pull Request (PR) cuando la tarea esté terminada.**
1. Entrad en https://github.com/Carlos-mb/42mentors. Aparece un aviso amarillo con el botón **«Compare & pull request»**. Si no sale: pestaña **Pull requests** → **New pull request**.
2. Arriba hay dos desplegables: **base** (a dónde va) y **compare** (de dónde viene). Dejadlos así: **base: `develop`** ← **compare: `feature/mi-tarea`**.
   ⚠️ GitHub suele proponer `main` como base. **Cambiadlo a `develop`.**
3. **Título:** qué hace, por ejemplo «Muestra la coalición en la ficha del mentor».
4. **Descripción:** qué cambia, cómo probarlo y capturas si se ve algo nuevo (sin datos de otros alumnos).
5. A la derecha, en **Reviewers**, elegid a uno de los otros dos miembros.
6. Botón **Create pull request**. Avisad en el chat del equipo.

**Paso 5. Si os piden cambios:** hacedlos **en la misma rama** con nuevos commits y `git push`. La PR se actualiza sola; no hay que abrir otra.

**Paso 6. Fusionar (merge) cuando esté aprobada.** Lo puede hacer el autor o quien revisó:
1. En la PR, abajo, la flecha junto al botón verde: elegid **«Create a merge commit»** y confirmad. **No uséis *Squash* ni *Rebase***: juntan los commits en uno y se pierde quién hizo qué, que es justo lo que se evalúa.
2. Pulsad **«Delete branch»**: la rama ya no hace falta en GitHub.
3. En vuestro ordenador:
   ```bash
   git switch develop
   git pull
   git branch -d feature/mi-tarea
   ```
¡Listo! Para la siguiente tarea, volved al paso 1.

### 1.5 Revisar la PR de un compañero

GitHub os avisa por email, o la veis en la pestaña **Pull requests**.
1. Pestaña **Files changed**: en verde lo añadido y en rojo lo quitado. Pinchando en una línea podéis dejar un comentario.
2. Si se puede, probadla en local:
   ```bash
   git fetch                            # enterarse de las ramas nuevas
   git switch feature/su-tarea          # ponerse en su rama
   ```
   Al terminar, volved a la vuestra con `git switch feature/mi-tarea`.
3. Botón **Review changes** (arriba a la derecha) y elegid:
   - **Approve**: todo bien;
   - **Request changes**: hay que corregir algo; explicad qué en el comentario;
   - **Comment**: solo dudas o sugerencias.

Revisad pronto: una PR esperando bloquea al compañero.

### 1.6 Traer a vuestra rama lo que han hecho los demás

Si alguien ha fusionado una PR en `develop` mientras trabajabais, traed esos cambios a vuestra rama **antes de abrir la PR**. GitHub también os avisa con *«This branch is out-of-date»*.
```bash
git switch develop
git pull
git switch feature/mi-tarea
git merge develop
```
**Si sale un conflicto** (*CONFLICT*), git os dice en qué ficheros está. Dentro de cada fichero veréis:
```
<<<<<<< HEAD
vuestra versión
=======
la versión de develop
>>>>>>> develop
```
1. Dejad el código como debe quedar (una de las dos versiones, o una mezcla) y **borrad las tres líneas de marcas**.
2. Guardad los cambios y terminad el merge:
   ```bash
   git add <fichero>
   git commit
   ```
Si os liais, `git merge --abort` lo deja todo como estaba antes del merge. Después, preguntad al equipo.

### 1.7 Publicar: pasar `develop` a `main`

Se hace pocas veces, cuando `develop` tiene cosas nuevas que funcionan:
1. Probar `develop` en local con la lista de la sección 4.
2. Abrir una PR con **base: `main`** ← **compare: `develop`**, con un título tipo «Versión 3 oct: directorio y ficha de mentor».
3. Otro miembro la aprueba y se fusiona con **«Create a merge commit»**. **No borréis la rama `develop`.**
4. Desplegar `main` en el hosting (sección 3.1).

### 1.8 Cómo escribir los mensajes de commit

Formato `tipo(zona opcional): qué hace`, en español y en presente:

| Tipo | Cuándo | Ejemplo |
|---|---|---|
| `feat` | Funcionalidad nueva | `feat(projects): ordena los mentores por disponibilidad` |
| `fix` | Arreglo de un error | `fix(auth): vuelve al login si el token ha caducado` |
| `docs` | Documentación | `docs: añade horas del 3 de octubre` |
| `style` | Solo aspecto o CSS | `style: mejora la tarjeta de mentor en móvil` |
| `refactor` | Reorganizar código sin cambiar lo que hace | `refactor: extrae la paginación de la API` |
| `chore` | Configuración y herramientas | `chore: actualiza .gitignore` |

Nada de mensajes como «cambios», «arreglos» o «asdf».

### 1.9 Si usáis una IA para trabajar con git

Podéis pedirle a vuestra IA que haga los comandos de git por vosotros. Para que no rompa nada, **las reglas que debe cumplir están en [AGENTS.md](AGENTS.md)**: no tocar `main` ni `develop`, no subir secretos, no fusionar PR sin una persona, no forzar `push`, etc.

**Según la IA que uséis:**

| IA | Qué hacer |
|---|---|
| **Claude Code** (app de escritorio o terminal) | Abridlo **en la carpeta del repositorio**. Lee `CLAUDE.md` automáticamente, y este carga `AGENTS.md`. No hay que hacer nada más. Para que pueda abrir PR, instalad `gh` (1.3, paso 5). |
| **Cursor, GitHub Copilot, Codex, Gemini CLI, Windsurf** u otras que trabajan sobre la carpeta | La mayoría lee `AGENTS.md` sola. Para asegurarse, empezad la conversación con: *«Lee AGENTS.md y CLAUDE.md y cumple sus reglas»*. |
| **IA de chat en el navegador** (ChatGPT, Gemini, Claude.ai…) que no ve vuestros ficheros | Pegad el contenido de `AGENTS.md` al principio del chat. Ella os dirá los comandos y **vosotros los ejecutáis**: copiad y pegad la salida si algo falla. |

**Ejemplos de lo que le podéis pedir:**
- «Empiezo la tarea de añadir el botón "Me ayudó". Crea la rama desde develop actualizado.»
- «Haz commit de lo que he cambiado, con un mensaje según las reglas, y súbelo.»
- «Abre una Pull Request hacia develop con una descripción de los cambios y pon a Floren87 como revisor.»
- «Trae a mi rama lo último de develop y ayúdame con los conflictos.»
- «Explícame qué cambia la PR número 3 para revisarla.»

**Lo que hacéis siempre vosotros, no la IA:**
- **Aprobar** las PR de los compañeros: la revisión es vuestra; la IA puede ayudaros a entender el código.
- **Fusionar** una PR, sobre todo hacia `main`.
- Mirar lo que va a entrar en cada commit antes de subirlo.
- **No darle nunca a la IA el contenido de `.env`**, el `client_secret` ni contraseñas.
- Saber que **los commits salen a vuestro nombre**: sois responsables de lo que suba la IA.

Si la IA propone algo que contradice `AGENTS.md` (`push --force`, `reset --hard`, commit en `develop`…), **decidle que no** y preguntad en el chat del equipo.

### 1.10 Problemas frecuentes

| Problema | Solución |
|---|---|
| El `push` falla con *protected branch* | Estáis en `main` o en `develop`. Es lo esperado. Mirad el caso siguiente. |
| He hecho commits en `develop` o `main` sin querer (aún sin subirlos) | Pasadlos a una rama nueva y dejad `develop` como está en GitHub: `git switch -c feature/lo-que-sea` y después `git branch -f develop origin/develop`. Vuestros commits siguen en `feature/lo-que-sea`. Si fue en `main`, cambiad `develop` por `main` en el segundo comando. |
| *Your branch is behind* | Hay cambios nuevos en GitHub: `git pull`. |
| No sé en qué rama estoy | `git branch --show-current` |
| Tengo cambios sin guardar y quiero cambiar de rama | Haced commit primero; o `git stash` para apartarlos y `git stash pop` para recuperarlos. |
| He subido un secreto por error | **Avisad en el momento.** Borrarlo del repo no basta, porque queda en el historial: hay que **regenerarlo en la intra** y escribir a security@42.fr. |

**Nunca:** `git push --force`, `git reset --hard` sobre `main` o `develop`, ni borrar ramas de otros.

### 1.11 Chuleta

```bash
git switch develop                      # ir a develop
git pull                                # bajar lo último
git switch -c feature/tarea             # crear una rama de tarea y entrar en ella
git branch --show-current               # en qué rama estoy
git status                              # qué he cambiado
git add <ficheros>                      # elegir qué entra en el commit
git commit -m "feat: …"                 # guardar el commit
git push -u origin feature/tarea        # subir la rama (después basta con git push)
git merge develop                       # traer lo último de develop a mi rama
git log --oneline --graph --all         # ver las ramas y los commits
```

### 1.12 Tablero de tareas

En GitHub: **Projects → New project → Board**, con las columnas To do, In progress y Done. Pasad a *issues* (tareas de GitHub) las de la sección 4. Si en la descripción de una PR escribís `Closes #12`, al fusionarla se cierra la tarea 12.

---

## 2. Probar en local (cada uno)

1. Instalad **XAMPP** (https://www.apachefriends.org), que trae PHP, Apache, MySQL y phpMyAdmin. Arrancad Apache y MySQL desde el panel de XAMPP.
2. Colocad el repositorio en `C:\xampp\htdocs\42mentors`, o clonadlo directamente ahí.
3. En http://localhost/phpmyadmin, cread una base de datos `mentors42` y ejecutad `sql/schema.sql` en la pestaña SQL.
4. **Cada miembro registra su propia app OAuth** para desarrollo, porque las condiciones de 42 prohíben compartir secretos entre alumnos:
   - https://profile.intra.42.fr/oauth/applications/new
   - tipo *42 Community Development*, scope `public`;
   - Redirect URI: `http://localhost/42mentors/public/callback.php`.
5. Copiad `.env.example` como `.env` y rellenadlo:
   - `FT_CLIENT_ID` y `FT_CLIENT_SECRET` de vuestra app;
   - `FT_REDIRECT_URI=http://localhost/42mentors/public/callback.php`;
   - `DB_NAME=mentors42`, `DB_USER=root`, `DB_PASS=` (vacío en XAMPP);
   - `APP_DEBUG=1`.
6. Abrid http://localhost/42mentors/public/

---

## 3. Desplegar en el hosting (https://42.2275676.xyz)

Hosting comprobado: hosting compartido con cPanel, PHP 8.1, curl y MySQL, con conexión saliente a la API de 42.

En los pasos, `~/` es la carpeta personal de la cuenta del hosting, que contiene la carpeta pública del dominio.

1. **cPanel → MySQL Databases**: crear la base de datos y un usuario, y asignarle **todos los privilegios** sobre ella.
2. **cPanel → phpMyAdmin**: ejecutar `sql/schema.sql` sobre esa base de datos.
3. Subir el proyecto, sin `.env` local, `API-Docs/` ni `hosting-tests/`, a `~/42mentors/`. Se puede hacer con `git clone` si hay SSH, o con el File Manager o FTP.
4. **cPanel → Domains**: cambiar el **Document Root** de `42.2275676.xyz` a `~/42mentors/public`. Así solo `public/` es accesible desde la web.
   - Si cPanel no deja cambiarlo: subid el **contenido** de `public/` a la carpeta pública del dominio, y `src/`, `sql/`, `cache/` y `.env` a `~/`. Las páginas buscan `../src/`, así que sigue funcionando.
5. Crear el `.env` **directamente en el servidor**, nunca en git, con:
   - la app OAuth de **producción**, cuya Redirect URI es `https://42.2275676.xyz/callback.php`;
   - los datos de MySQL del paso 1;
   - `APP_DEBUG=0`.
6. Comprobaciones:
   - https://42.2275676.xyz/ carga;
   - https://42.2275676.xyz/.env y https://42.2275676.xyz/src/bootstrap.php devuelven **404**. Si se ven o se descargan, hay ficheros privados en la carpeta pública: corregidlo antes de seguir.

### 3.1 Actualizar la instalación que ya funciona

La instalación actual está en `~/42mentors/`, con el `.env` dentro de esa carpeta.

1. **Base de datos.** En phpMyAdmin, pestaña SQL, ejecutar **una sola vez** cada fichero nuevo de `sql/migrations/`, en orden. Cada fichero indica en su cabecera si se aplica **antes** de subir el código (añade columnas y no rompe la versión anterior) o **después** (quita columnas que el código anterior aún usa).
2. Generar el paquete en local: `powershell -ExecutionPolicy Bypass -File tools\build-deploy.ps1` crea `deploy/42mentors.zip`, que contiene `public/`, `src/`, `sql/`, `cache/.htaccess` y `.env.example`, sin secretos. En macOS o Linux: `zip -r` de una carpeta `42mentors/` con esos mismos ficheros.
3. En File Manager:
   - subir el ZIP a `~/`;
   - **renombrar** `42mentors` → `42mentors-old`, que queda como copia de seguridad;
   - **extraer** el ZIP, que crea un `42mentors/` limpio;
   - **mover** `.env` de `42mentors-old/` a `42mentors/` (activad *Settings → Show Hidden Files* para verlo).
4. Probar la web. Si algo falla:
   - poned `APP_DEBUG=1` en el `.env` un momento para ver el error, o mirad el fichero `error_log` de `42mentors/public/`;
   - para volver atrás, renombrad las carpetas al revés.
5. Si todo va bien, borrar `42mentors-old` y el ZIP.

---

## 4. Tareas pendientes (pasarlas al tablero y repartirlas)

### Bloqueantes
- [ ] Decidir el reparto de roles y quién es dueño del repositorio y de la app de producción (ver «Decisiones pendientes» en CLAUDE.md).
- [ ] Repositorio, ramas, protección y tablero (sección 1).
- [x] Primera ejecución real del MVP y despliegue en el hosting (2 oct, Carlos).
- [ ] Actualizar el servidor con la ficha del mentor y el directorio (sección 3.1) y probarlos.

### Pruebas de punta a punta (con cuentas reales del equipo)
- [ ] Login, logout y vuelta al login cuando caduca el token (unas 2 horas).
- [ ] Rechazar el consentimiento: no se guarda nada en `users`.
- [ ] Aceptar el consentimiento. En «Mi perfil», rellenar presentación, disponibilidad, contacto e idiomas, marcar proyectos con nota, guardar y recargar: se conserva todo.
- [ ] Con otra cuenta: «Proyectos» muestra esos proyectos. Al entrar en uno, el mentor aparece con foto, su nota y su puesto si está en el cluster, y la tarjeta lleva a su ficha.
- [ ] «Mentores»:
  - buscar por nombre sin tildes (p. ej. «alvaro» encuentra «Álvaro») y por login;
  - filtrar por proyecto y por «Solo conectados ahora».
- [ ] Ficha del mentor:
  - se ven nivel, coalición, puesto y la nota y fecha de validación de sus proyectos;
  - «Editar mi perfil» aparece solo en la ficha propia;
  - `mentor.php?login=noexiste` muestra «Esta persona no es mentor».
  - Si no aparece la fecha de validación, la API no devuelve `marked_at`: anotarlo en problemas técnicos.
- [ ] «En pausa»: el mentor desaparece de «Proyectos» y «Mentores», pero su ficha sigue accesible por enlace.
- [ ] «Borrar mis datos» en la página de privacidad: el mentor desaparece.
- [ ] Vista en móvil.
- [ ] Ver qué pasa con muchos proyectos (más de 30) y con la piscina, y decidir si se filtran por cursus.

### Mejoras (solo si el MVP está terminado)
- [ ] Probar `GET /v2/users/:id/projects_users/registration` para mostrar también proyectos sin mentores.
- [ ] Botón «Me ayudó» en la ficha del mentor, con contador de ayudas (tabla nueva y control de abusos).
- [ ] Número de veces que el mentor ha corregido cada proyecto (`scale_teams`; cuesta varias llamadas a la API).

### Entrega (obligatorio, cuenta para la nota)
- [ ] README: responsabilidades, metodología de ideación, gestión, **registro de horas diario** y problemas técnicos.
- [ ] Capturas para el README **sin datos de otros alumnos**.
- [ ] Pitch de 4 minutos (problema, demo, impacto) más 2 de preguntas. Ensayar el día 7 por la mañana.
- [ ] Evaluación entre equipos (del 6 a las 18:01 al 7 a las 13:00): repartir qué equipos revisa cada uno.

---

## 5. Mapa del código

| Fichero | Qué hace |
|---|---|
| `src/bootstrap.php` | Carga `.env`, configura la sesión y la cabecera `noindex`, e incluye el resto. Todas las páginas empiezan con él. |
| `src/ft_api.php` | Cliente de la API de 42: OAuth, `ft_get`, `ft_get_all` paginado, `ft_users_by_ids`, reintento ante 429. |
| `src/auth.php` | Sesión, `require_login`, `require_consent`, CSRF. |
| `src/locations.php` | Ubicaciones del cluster por campus, cacheadas y solo de mentores. |
| `src/mentors.php` | Campos del perfil (disponibilidad, contacto, idiomas), `enrich_mentors` (nombre, foto y puesto desde la API), orden y formatos. |
| `src/view.php`, `src/views/` | `e()` para escapar HTML, cabecera, pie, errores y la tarjeta de mentor (`mentor_card.php`). |
| `public/login.php`, `public/callback.php` | Flujo OAuth. Al entrar se llama una vez a `/v2/me` y se guardan en la sesión el usuario y sus proyectos. |
| `public/consent.php`, `public/privacy.php` | Consentimiento, política de privacidad y borrado de datos. |
| `public/profile.php` | **Mi perfil**: proyectos que mentorizo (con nota), presentación, disponibilidad, contacto e idiomas. |
| `public/projects.php`, `public/project.php` | **Proyectos**: proyectos con mentores y mentores de cada proyecto. |
| `public/mentors.php` + `assets/mentors.js` | **Mentores**: directorio con buscador que filtra en el navegador. |
| `public/mentor.php` | **Ficha pública** del mentor (`?login=`). |
| `sql/schema.sql`, `sql/migrations/` | Esquema completo para instalaciones nuevas y cambios para bases de datos ya creadas. |

Documentación de la API: carpeta `API-Docs/` (solo en local; empezad por `API-Docs/INDEX_ENDPOINTS.md`).
