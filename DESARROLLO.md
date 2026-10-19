# Desarrollo y despliegue

Cómo probar el proyecto en local y cómo desplegarlo en el hosting. Contexto, decisiones y condiciones de uso de la API de 42: [CLAUDE.md](CLAUDE.md). Reglas para la IA: [AGENTS.md](AGENTS.md).

## Reglas que no se pueden romper

- **Nunca subir secretos** (`.env`, `client_secret`, contraseñas de la base de datos) a git ni a capturas. Si se filtra uno, se regenera en la intra y se avisa a security@42.fr.
- **No subir `API-Docs/`** al repositorio: es material de 42 tras login con datos de personas. Ya está en `.gitignore`.
- **No poner datos de otros alumnos** en el README ni en las capturas.

---

## Probar en local

1. Instala **XAMPP** (https://www.apachefriends.org), que trae PHP, Apache, MySQL y phpMyAdmin. Arranca Apache y MySQL desde el panel de XAMPP.
2. Coloca el repositorio en `C:\xampp\htdocs\42mentors`, o clónalo directamente ahí.
3. En http://localhost/phpmyadmin, crea una base de datos `mentors42` y ejecuta `sql/schema.sql` en la pestaña SQL.
4. **Registra una app OAuth de desarrollo, distinta de la de producción**, para no usar el secret de producción en local:
   - https://profile.intra.42.fr/oauth/applications/new
   - tipo *42 Community Development*, scope `public`;
   - Redirect URI: `http://localhost/42mentors/public/callback.php`.
5. Copia `.env.example` como `.env` y rellénalo:
   - `FT_CLIENT_ID` y `FT_CLIENT_SECRET` de tu app;
   - `FT_REDIRECT_URI=http://localhost/42mentors/public/callback.php`;
   - `DB_NAME=mentors42`, `DB_USER=root`, `DB_PASS=` (vacío en XAMPP);
   - `APP_DEBUG=1`.
6. Abre http://localhost/42mentors/public/

---

## Desplegar en el hosting

Hosting compartido con cPanel, PHP 8.1, curl y MySQL, con conexión saliente a la API de 42. Producción es https://42mentors.com y preproducción https://42.2275676.xyz; cada dominio tiene su propia instalación y su propio `.env`. En los pasos, sustituye el dominio por el que corresponda.

En los pasos, `~/` es la carpeta personal de la cuenta del hosting, que contiene la carpeta pública del dominio.

1. **cPanel → MySQL Databases**: crear la base de datos y un usuario, y asignarle **todos los privilegios** sobre ella.
2. **cPanel → phpMyAdmin**: ejecutar `sql/schema.sql` sobre esa base de datos.
3. Subir el proyecto, sin `.env` local, `API-Docs/` ni `hosting-tests/`, a `~/42mentors/`. Se puede hacer con `git clone` si hay SSH, o con el File Manager o FTP.
4. **cPanel → Domains**: cambiar el **Document Root** del dominio a `~/42mentors/public`. Así solo `public/` es accesible desde la web.
   - Si cPanel no deja cambiarlo: sube el **contenido** de `public/` a la carpeta pública del dominio, y `src/`, `sql/`, `cache/` y `.env` a `~/`. Las páginas buscan `../src/`, así que sigue funcionando.
5. Crear el `.env` **directamente en el servidor**, nunca en git, con:
   - la app OAuth de **producción**, cuya Redirect URI es `https://<dominio>/callback.php`;
   - los datos de MySQL del paso 1;
   - `APP_DEBUG=0`.
6. Comprobaciones:
   - la página principal del dominio carga;
   - `/.env` y `/src/bootstrap.php` del dominio devuelven **404**. Si se ven o se descargan, hay ficheros privados en la carpeta pública: corrígelo antes de seguir.

### Actualizar la instalación que ya funciona

Cada dominio tiene su instalación (en producción, `~/42mentors.com/42mentors/`), con el `.env` dentro de esa carpeta. En los pasos, `42mentors` es esa carpeta.

1. **Base de datos.** En phpMyAdmin, pestaña SQL, ejecutar **una sola vez** cada fichero nuevo de `sql/migrations/`, en orden. Cada fichero indica en su cabecera si se aplica **antes** de subir el código (añade columnas y no rompe la versión anterior) o **después** (quita columnas que el código anterior aún usa).
   La migración 004 (mentores oficiales) y la 005 (versión del consentimiento) se aplican antes de subir el código. Además, para la 004 hay que añadir `ADMIN_LOGINS=login1,login2` al `.env` del servidor (mira `.env.example`) para que esos logins vean `admin.php`.
2. Generar el paquete en local: `powershell -ExecutionPolicy Bypass -File tools\build-deploy.ps1` crea `deploy/42mentors.zip`, que contiene `public/`, `src/`, `sql/`, `cache/.htaccess` y `.env.example`, sin secretos. En macOS o Linux: `zip -r` de una carpeta `42mentors/` con esos mismos ficheros.
3. En File Manager:
   - subir el ZIP a `~/`;
   - **renombrar** `42mentors` → `42mentors-old`, que queda como copia de seguridad;
   - **extraer** el ZIP, que crea un `42mentors/` limpio;
   - **mover** `.env` de `42mentors-old/` a `42mentors/` (activa *Settings → Show Hidden Files* para verlo).
4. Probar la web. Si algo falla:
   - pon `APP_DEBUG=1` en el `.env` un momento para ver el error, o mira el fichero `error_log` de `42mentors/public/`;
   - para volver atrás, renombra las carpetas al revés.
5. Si todo va bien, borrar `42mentors-old` y el ZIP.

---
---

## Mapa del código

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
| `public/available.php` | **Disponibles ahora**: mentores oficiales disponibles; los oficiales anuncian aquí su disponibilidad. |
| `public/admin.php`, `src/roster.php` | **Administración** de mentores oficiales y manguitos (`ADMIN_LOGINS` en `.env`). |
| `sql/schema.sql`, `sql/migrations/` | Esquema completo para instalaciones nuevas y cambios para bases de datos ya creadas. |

Documentación de la API: carpeta `API-Docs/` (solo en local; empieza por `API-Docs/INDEX_ENDPOINTS.md`).
