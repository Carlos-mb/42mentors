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

> **Por qué importa:** la mitad de la nota de la evaluación entre equipos es **«Git y ramas»**. Los evaluadores miran que haya ramas, Pull Requests y commits **de los tres**, repartidos en el tiempo. Cada uno sube su trabajo **desde su propia cuenta**.

### 1.1 Preparación (una sola vez, cada uno en su equipo)

1. **Aceptar la invitación** al repositorio: llega por email, o en https://github.com/Carlos-mb/42mentors/invitations
2. **Instalar git**:
   - Windows: `winget install --id Git.Git -e`. Después, cerrar y abrir la terminal.
   - macOS o Linux: suele venir instalado (`git --version`).
3. **Poner vuestra identidad.** El email debe ser uno de vuestra cuenta de GitHub; si no, los commits no salen a vuestro nombre. Recomendado: el email *noreply* que aparece en GitHub → Settings → Emails, para no publicar el vuestro.
   ```bash
   git config --global user.name "Tu Nombre"
   git config --global user.email "ID+usuario@users.noreply.github.com"
   ```
4. **Clonar el repositorio.** Para probar con XAMPP, clonadlo directamente en `C:\xampp\htdocs` (ver sección 2):
   ```bash
   git clone https://github.com/Carlos-mb/42mentors.git
   cd 42mentors
   git switch develop
   ```
   La primera vez que hagáis `push`, git abrirá el navegador para iniciar sesión en GitHub.

### 1.2 Las ramas

| Rama | Para qué | Quién escribe en ella |
|---|---|---|
| `main` | Lo que está **desplegado** en https://42.2275676.xyz. Siempre funciona. | Nadie directamente: **solo mediante PR desde `develop`**, con 1 aprobación (GitHub lo impide de otra forma). |
| `develop` | Integración: aquí se juntan las tareas terminadas. | Nadie directamente: solo mediante PR desde las ramas de tarea, con 1 aprobación (también protegida). |
| `feature/…` | Una funcionalidad nueva. Ej.: `feature/boton-me-ayudo` | Quien hace la tarea. |
| `fix/…` | Arreglar un error. Ej.: `fix/foto-mentor-vacia` | Quien lo arregla. |
| `docs/…` | README, registro de horas, instrucciones. Ej.: `docs/registro-horas` | Quien lo escribe. |

Reglas:
- **Una rama por tarea**, con nombre corto en minúsculas y guiones, sin tildes ni espacios.
- Las ramas de tarea **siempre salen de `develop`** y **vuelven a `develop`** mediante PR.
- Ramas cortas: mejor varias PR pequeñas en un día que una enorme el lunes.

### 1.3 Flujo de cada tarea, paso a paso

**1. Partir de `develop` actualizado y crear la rama:**
```bash
git switch develop
git pull
git switch -c feature/nombre-de-la-tarea
```

**2. Trabajar y hacer commits pequeños.** Un commit por cada paso que tenga sentido por sí mismo:
```bash
git status
git add public/mentor.php src/mentors.php
git commit -m "feat(mentors): muestra la coalición en la ficha"
```
Mirad siempre `git status` antes de `git add`. **Nunca** debe aparecer `.env`, `API-Docs/` ni ningún fichero con contraseñas. Evitad `git add .` si no habéis revisado qué entra.

**3. Subir la rama.** Se puede, y conviene, hacer varias veces al día, aunque la tarea no esté terminada:
```bash
git push -u origin feature/nombre-de-la-tarea
```
Después del primer `push -u`, basta con `git push`.

**4. Abrir la Pull Request.** En GitHub aparece el botón **«Compare & pull request»**:
- **base: `develop`** ← compare: vuestra rama. **Comprobad la base**: GitHub propone `main` por defecto.
- Título: qué hace. En la descripción: qué cambia, cómo probarlo y capturas si hay cambios visuales (sin datos de otros alumnos).
- **Reviewers**: uno de los otros dos miembros.

**5. Revisar** (quien recibe la PR):
- leer los cambios en la pestaña *Files changed*;
- si se puede, probarlo en local: `git fetch`, `git switch feature/nombre-de-la-tarea`;
- **Approve** o **Request changes** con comentarios. Si pide cambios, el autor hace más commits en la misma rama y `git push`: la PR se actualiza sola.

**6. Fusionar** cuando esté aprobada:
- botón **«Create a merge commit»** (no *Squash*: así se conservan todos los commits y su autor, que es lo que se evalúa);
- después, **«Delete branch»** en GitHub, y en local:
  ```bash
  git switch develop
  git pull
  git branch -d feature/nombre-de-la-tarea
  ```

### 1.4 Mantener vuestra rama al día

Si `develop` ha cambiado mientras trabajabais (otro ha fusionado una PR), traed esos cambios a vuestra rama **antes de abrir la PR**:
```bash
git switch develop
git pull
git switch feature/nombre-de-la-tarea
git merge develop
```
**Si hay conflictos**, git marca los ficheros afectados:
1. abridlos y buscad los bloques `<<<<<<<`, `=======` y `>>>>>>>`;
2. dejad el código como debe quedar y borrad las marcas;
3. guardad los cambios y terminad el merge:
   ```bash
   git add <fichero>
   git commit
   ```
Si no lo veis claro, `git merge --abort` deja todo como estaba. Después, preguntad al equipo.

### 1.5 Pasar a producción (`develop` → `main`)

1. Probar `develop` en el servidor o en local con la lista de la sección 4.
2. En GitHub: PR con **base: `main`** ← compare: `develop`, con un título tipo «Versión 3 oct: directorio y ficha de mentor».
3. Otro miembro la aprueba y se fusiona.
4. Desplegar `main` en el hosting (sección 3.1).

### 1.6 Mensajes de commit

Formato `tipo(zona opcional): qué hace`, en presente y en español:

| Tipo | Cuándo | Ejemplo |
|---|---|---|
| `feat` | Funcionalidad nueva | `feat(projects): ordena los mentores por disponibilidad` |
| `fix` | Arreglo de un error | `fix(auth): vuelve al login si el token ha caducado` |
| `docs` | Documentación | `docs: añade horas del 3 de octubre` |
| `style` | Solo aspecto o CSS | `style: mejora la tarjeta de mentor en móvil` |
| `refactor` | Reorganizar código sin cambiar lo que hace | `refactor: extrae la paginación de la API` |
| `chore` | Configuración y herramientas | `chore: actualiza .gitignore` |

Nada de mensajes como «cambios», «arreglos» o «asdf».

### 1.7 Problemas frecuentes

- **«He hecho commits en `develop` o `main` sin querer, aún sin `push`»**: movedlos a una rama nueva y devolved `develop` a como está en GitHub:
  ```bash
  git switch -c feature/lo-que-sea
  git branch -f develop origin/develop
  ```
  Los commits siguen en `feature/lo-que-sea`. Si el error fue en `main`, cambiad `develop` por `main` en la segunda línea.
- **El `push` a `main` o `develop` falla con «protected branch»**: está bien, es lo esperado. Usad una rama y una PR.
- **«Your branch is behind»**: haced `git pull`.
- **Nunca uséis `git push --force`** sobre `main` ni `develop`.
- **He subido un secreto por error**: avisad en el momento. Hay que **regenerarlo en la intra** (borrarlo del repo no basta, porque queda en el historial) y avisar a security@42.fr.

### 1.8 Chuleta

```bash
git switch develop && git pull                 # ponerse al día
git switch -c feature/tarea                    # nueva rama de tarea
git status                                     # qué ha cambiado
git add <ficheros> && git commit -m "feat: …"  # guardar un paso
git push -u origin feature/tarea               # subir la rama (luego basta con git push)
git merge develop                              # traer lo último de develop a mi rama
git log --oneline --graph --all                # ver las ramas y los commits
```
En PowerShell de Windows antiguo, `&&` no funciona: ejecutad los comandos por separado.

### 1.9 Tablero de tareas

**Projects → New project → Board**, con las columnas To do, In progress y Done. Pasad a issues las tareas de la sección 4; cada PR puede cerrar su issue si escribís `Closes #12` en la descripción.

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

Hosting comprobado: A2 Hosting, PHP 8.1, curl y MySQL, con conexión saliente a la API de 42. Detalles en [CLAUDE.md](CLAUDE.md).

1. **cPanel → MySQL Databases**: crear la base de datos y un usuario, y asignarle **todos los privilegios** sobre ella.
2. **cPanel → phpMyAdmin**: ejecutar `sql/schema.sql` sobre esa base de datos.
3. Subir el proyecto, sin `.env` local, `API-Docs/` ni `hosting-tests/`, a `/home/mmmkofti/42mentors/`. Se puede hacer con `git clone` si hay SSH, o con el File Manager o FTP.
4. **cPanel → Domains**: cambiar el **Document Root** de `42.2275676.xyz` a `/home/mmmkofti/42mentors/public`. Así solo `public/` es accesible desde la web.
   - Si cPanel no deja cambiarlo: subid el **contenido** de `public/` a `/home/mmmkofti/42.2275676.xyz/`, y `src/`, `sql/`, `cache/` y `.env` a `/home/mmmkofti/`. Las páginas buscan `../src/`, así que sigue funcionando.
5. Crear el `.env` **directamente en el servidor**, nunca en git, con:
   - la app OAuth de **producción**, cuya Redirect URI es `https://42.2275676.xyz/callback.php`;
   - los datos de MySQL del paso 1;
   - `APP_DEBUG=0`.
6. **Borrar `test42.php`** del servidor si sigue ahí.
7. Comprobaciones:
   - https://42.2275676.xyz/ carga;
   - https://42.2275676.xyz/.env y https://42.2275676.xyz/src/bootstrap.php devuelven **404**. Si se ven o se descargan, hay ficheros privados en la carpeta pública: corregidlo antes de seguir.

### 3.1 Actualizar la instalación que ya funciona

La instalación actual está en `/home/mmmkofti/42mentors/`, con el `.env` dentro de esa carpeta.

1. **Base de datos.** En phpMyAdmin, pestaña SQL, ejecutar **una sola vez** cada fichero nuevo de `sql/migrations/`, en orden. Cada fichero indica en su cabecera si se aplica **antes** de subir el código (añade columnas y no rompe la versión anterior) o **después** (quita columnas que el código anterior aún usa).
2. Generar el paquete en local: `powershell -ExecutionPolicy Bypass -File tools\build-deploy.ps1` crea `deploy/42mentors.zip`, que contiene `public/`, `src/`, `sql/`, `cache/.htaccess` y `.env.example`, sin secretos. En macOS o Linux: `zip -r` de una carpeta `42mentors/` con esos mismos ficheros.
3. En File Manager:
   - subir el ZIP a `/home/mmmkofti/`;
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
