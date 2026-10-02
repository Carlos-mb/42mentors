# Instrucciones para el equipo

Estado a **2 de octubre de 2026**:
- El MVP está **desplegado y funcionando** en https://42.2275676.xyz (probado por Carlos).
- Después se añadieron el **directorio de mentores** y la **ficha del mentor**, **pendientes de probar** en el servidor.
- **Aún no hay repositorio git.**

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

## 1. Git y GitHub (lo primero)

### 1.1 Instalar git (cada uno, en su equipo)
- Windows: `winget install --id Git.Git -e`. Después, cerrar y abrir la terminal.
- macOS o Linux: suele venir instalado (`git --version`).

```bash
git config --global user.name "Tu Nombre"
git config --global user.email "tu-email-de-github"
```

### 1.2 Crear el repositorio (solo la persona dueña del repo)
1. En GitHub: **New repository**, con el nombre `42-mentors`, **público** (lo exigen las bases para la evaluación entre equipos) y **sin** README ni .gitignore, porque ya existen.
2. **Settings → Collaborators**: añadir a los otros dos miembros.
3. En la carpeta del proyecto, crear la historia inicial **en varios commits por partes**, no en uno solo:

```bash
git init -b main
git status
```
Antes de seguir, comprobad que en `git status` **no aparecen** `.env`, `API-Docs/`, `hosting-tests/` ni `deploy/`. Si aparecen, revisad `.gitignore`.

```bash
git add .gitignore .env.example CLAUDE.md proyecto.txt datos.txt "Bases Hackathon 42442 (2026).pdf"
git commit -m "chore: configuración inicial, contexto y bases del hackathon"
git add sql/
git commit -m "feat(db): esquema mínimo de usuarios, proyectos y mentorías, y migraciones"
git add src/ public/assets/style.css public/assets/filter.js public/robots.txt cache/.htaccess
git commit -m "feat: base de la app (configuración, BD, cliente de la API de 42, sesión y vistas)"
git add public/index.php public/login.php public/callback.php public/logout.php
git commit -m "feat(auth): login con OAuth2 de la API de 42"
git add public/consent.php public/privacy.php
git commit -m "feat(privacy): consentimiento, política de privacidad y borrado de datos"
git add public/profile.php public/assets/profile.js
git commit -m "feat(profile): perfil de mentor editable (proyectos, presentación, disponibilidad, contacto)"
git add public/projects.php public/project.php
git commit -m "feat(projects): buscar mentores por proyecto con ubicación en el cluster"
git add public/mentors.php public/mentor.php public/assets/mentors.js
git commit -m "feat(mentors): directorio con buscador y ficha pública del mentor"
git add tools/
git commit -m "chore: script para generar el paquete de despliegue"
git add README.md INSTRUCCIONES_EQUIPO.md
git commit -m "docs: README e instrucciones para el equipo"
git status
```
Al terminar, `git status` debe decir que no queda nada pendiente.

```bash
git remote add origin https://github.com/<usuario>/42-mentors.git
git push -u origin main
git checkout -b develop
git push -u origin develop
```

4. En GitHub, **Settings → Branches**: proteger `main` y `develop`, exigiendo Pull Request con 1 aprobación.
5. **Projects → New project → Board**: crear el tablero con columnas To do, In progress y Done, y pasar a issues las tareas de la sección 4.

### 1.3 Los demás miembros
```bash
git clone https://github.com/<usuario>/42-mentors.git
cd 42-mentors
git checkout develop
```

### 1.4 Flujo diario (todos)
```bash
git checkout develop
git pull
git checkout -b feature/nombre-corto-de-la-tarea
```
Trabajad y haced commits pequeños:
```bash
git add <ficheros>
git commit -m "feat: descripción corta"
git push -u origin feature/nombre-corto-de-la-tarea
```
- En GitHub, abrid una **Pull Request hacia `develop`** y pedid revisión a otro miembro.
- Cuando `develop` funcione en el hosting: PR de `develop` a `main`.
- Prefijos de commit: `feat:` (funcionalidad), `fix:` (arreglo), `docs:`, `style:`, `refactor:`, `chore:`.

---

## 2. Probar en local (cada uno)

1. Instalad **XAMPP** (https://www.apachefriends.org), que trae PHP, Apache, MySQL y phpMyAdmin. Arrancad Apache y MySQL desde el panel de XAMPP.
2. Colocad el repositorio en `C:\xampp\htdocs\42-mentors`, o clonadlo directamente ahí.
3. En http://localhost/phpmyadmin, cread una base de datos `mentors42` y ejecutad `sql/schema.sql` en la pestaña SQL.
4. **Cada miembro registra su propia app OAuth** para desarrollo, porque las condiciones de 42 prohíben compartir secretos entre alumnos:
   - https://profile.intra.42.fr/oauth/applications/new
   - tipo *42 Community Development*, scope `public`;
   - Redirect URI: `http://localhost/42-mentors/public/callback.php`.
5. Copiad `.env.example` como `.env` y rellenadlo:
   - `FT_CLIENT_ID` y `FT_CLIENT_SECRET` de vuestra app;
   - `FT_REDIRECT_URI=http://localhost/42-mentors/public/callback.php`;
   - `DB_NAME=mentors42`, `DB_USER=root`, `DB_PASS=` (vacío en XAMPP);
   - `APP_DEBUG=1`.
6. Abrid http://localhost/42-mentors/public/

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
