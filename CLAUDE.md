# 42mentors — Web de mentoría entre alumnos de 42

> Mantener este fichero actualizado con las conclusiones y decisiones que se vayan tomando.

**Reglas para la IA:** @AGENTS.md

## Proyecto
Web sencilla para que los alumnos de 42 Madrid:
1. **Login** con OAuth2 de la API de 42.
2. **Mentor:** ve sus proyectos terminados, marca/desmarca los que puede mentorizar y guarda.
3. **Estudiante:** ve los proyectos que puede hacer; al pulsar uno ve los mentores (nombre, login, foto, ubicación en el cluster si está logueado), cada uno enlazado a su ficha de la intra.

Nació en el Hackathon 42442 (octubre de 2026), con Carlos (`cmelero-`, GitHub `Carlos-mb`), Florentin (`fcamasa`) y Lucas (`lucaroma`). Desde entonces lo mantiene Carlos en solitario. Repo: https://github.com/Carlos-mb/42mentors (**público**; su URL no se cambia). Detalle original en [proyecto.txt](proyecto.txt); estado y documentación de cara al exterior en [README.md](README.md); desarrollo en local y despliegue en [DESARROLLO.md](DESARROLLO.md).

## Documentación de la API (copia local)
- La web `https://api.intra.42.fr/apidoc` requiere login y se anunció que dejaría de estar disponible. **Se descargó completa el 2026-10-02** en [API-Docs/](API-Docs/README.md):
  - 545 páginas en `md/` y `html/`;
  - índice de endpoints en `API-Docs/INDEX_ENDPOINTS.md`.
- Consultar siempre `API-Docs/` en lugar de la web.
- **Aviso de 42:** la API v2 está llegando a su fin de vida (EOL) y se dejará de dar soporte en el futuro. De momento funciona.

## Conclusiones técnicas sobre la API
- **OAuth2:** Authorization Code flow en `https://api.intra.42.fr/oauth/authorize` y `/oauth/token`; ver `API-Docs/md/guides/web_application_flow.md`. La app se registra en https://profile.intra.42.fr/oauth/applications. El `client_secret` va solo en `.env`.
- **Usuario actual:** `GET /v2/me`. Incluye `login`, `image`, `location` (puesto en el cluster o null), `projects_users` y `campus`.
- **Proyectos terminados (mentor):** `GET /v2/users/:user_id/projects_users`, filtrando por `status == "finished"` y `validated? == true`.
- **Proyectos que puede hacer (estudiante):** `GET /v2/users/:user_id/projects_users/registration`; ver `md/users/allowed_registration_projects.md`. Alternativa: los proyectos del cursus que aún no ha validado.
- **Ubicación en el cluster:** campo `location` del usuario, o `GET /v2/campus/:campus_id/locations` con `filter[active]=true`.
- **Paginación y límites:** ver `md/guides/specification.md`. Respuestas paginadas: por defecto 30, y `page[size]` o `per_page` hasta 100 en casi todos los endpoints. Límite por app de **2 peticiones/segundo y 1200/hora**, así que **hay que cachear**.
- La API no guarda qué proyectos mentoriza cada alumno, así que **hay BD propia** (user_id/login ↔ project_id).
- Ficha de la intra de un usuario: `https://profile.intra.42.fr/users/<login>`.
- **Fotos (`cdn.intra.42.fr`), 2026-10-19:** el CDN devolvió 500 y luego 403 a las fotos incrustadas en la web, aunque la URL abierta directamente en el navegador sí cargaba. También da 403 a `curl` y al servidor del hosting con cualquier cabecera, así que un proxy en `photo.php` no sirvió y se retiró. Quitar el `Referer` (`referrerpolicy`) tampoco lo arregló. Mientras no se aclare con 42, `public/assets/avatars.js` sustituye la foto rota por un círculo con la inicial (`data-initial`).

## Condiciones de uso de la API (GTU 08.01.2025 + API User Charter)
Leídas el 2026-10-02. Requisitos que nos afectan:
- **Finalidad:** solo apps que beneficien a la red 42 (la mentoría encaja). Nada comercial: ni anuncios ni venta de datos.
- **Datos solo dentro de la red 42** (art. 3 y 4.1): cualquier dato de la API, solo detrás del login con 42.
  - Nada en páginas o endpoints públicos.
  - Nada en buscadores: `robots.txt` + `noindex`.
  - Nada en el repo, el README ni capturas con datos de otros alumnos.
- **Consentimiento previo y por escrito** para guardar o cachear datos (3.1, 4.1, 4.2).
  - Pantalla de aceptación en el primer login; guardar la fecha en la BD como prueba.
  - No guardar nada antes de aceptar.
  - Solo se muestran los mentores que han aceptado (foto y ubicación incluidas).
- **Política de privacidad** (qué datos, para qué, derechos, cómo retirar el consentimiento) y **botón "borrar mis datos"** (4.2, 4.4).
- **Borrado** cuando los datos ya no hagan falta o se cierre la web (4.5).
- **Secretos:** prohibido compartirlos, también con otros alumnos de 42 (3.2).
  - La app OAuth de desarrollo local es distinta de la de producción.
  - El secret de producción solo está en el `.env` del servidor.
  - El secret rota cada mes: vigilar la fecha de caducidad en la intra.
- **Seguridad (4.3):**
  - HTTPS y secretos fuera de `public_html`;
  - access token solo en la sesión, nunca en la BD;
  - cookies `Secure`/`HttpOnly`/`SameSite`;
  - guardar el mínimo (user_id, login, project_id);
  - backups.
- **No saltarse el rate limit** (por ejemplo, repartiendo la producción entre varias apps).
- **Incidentes:** si se filtra un secret, revocarlo y avisar a security@42.fr (art. 6).
- **`API-Docs/` NO se sube al repo público:** es material de 42 tras login y trae datos de ejemplo con nombres y logins. Están en `.gitignore`: `API-Docs/`, `config.php` y `.env`.

## Stack y entorno
- **PHP 8.1 sin framework + MySQL (PDO)**, con `curl` para la API y `$_SESSION` para la sesión. Hosting compartido (cPanel) con HTTPS, curl saliente hacia la API de 42 y MySQL. Los detalles internos del servidor están en `CLAUDE.local.md`, que no se sube al repo: @CLAUDE.local.md
- Producción: https://42mentors.com. Preproducción: https://42.2275676.xyz (el README solo menciona producción). Cada dominio tiene su instalación y su `.env`.
- Estructura:
  - `public/` es el document root;
  - `src/` contiene la lógica;
  - `sql/schema.sql` (esquema completo) y `sql/migrations/` (cambios para BD ya creadas);
  - `cache/` guarda las ubicaciones y los perfiles;
  - los secretos van en `.env`, en la raíz y fuera de `public/` (hay `.env.example` en el repo).
- CI en GitHub Actions: `php -l` con PHP 8.1 y gitleaks en cada push y PR.
- Desarrollo en local: ver [DESARROLLO.md](DESARROLLO.md) (la web también arranca con `php -S localhost:8000 -t public`).

## Decisiones de producto
- Login: `/v2/me` una sola vez. El usuario y sus proyectos (sin subproyectos, `parent_id`) van a la sesión; el token solo en la sesión.
- **Proyecto terminado** = `validated? == true`.
- «Proyectos que puede hacer» = proyectos con mentores de su campus que el estudiante no ha validado; los «en curso» primero. Se ocultan los de la piscina. `/projects_users/registration` queda como mejora.
- **Mentores solo del mismo campus**; los conectados primero. Orden en todas las listas: conectados, luego disponibles antes que ocupados, luego login.
- **Privacidad (cumple las condiciones de la API):**
  - la BD solo guarda id, login, campus_id, consented_at, last_login_at, los proyectos que mentoriza, lo que escribe el propio mentor y los votos, y solo tras `consent.php`;
  - nombre y foto de los mentores se piden en el momento con `GET /v2/users?filter[id]=…`;
  - la caché de ubicaciones (por campus, 120 s) y la de perfiles solo guardan mentores con consentimiento y proyectos;
  - `privacy.php` incluye «Borrar mis datos» (elimina también los votos emitidos y recibidos);
  - hay `noindex` (meta + cabecera `X-Robots-Tag`) y `robots.txt`.
- **Menú:** Proyectos (`projects.php`), Mentores (`mentors.php`, directorio), Mi perfil (`profile.php`, edición), Mis valoraciones (`ratings.php`, si `VOTES_ENABLED`) y Manguito (`available.php`, «Disponibles ahora»; solo para quien `can_see_available()`). La home tiene una caja por cada una de Proyectos, Mentores, Mi perfil y, con el mismo permiso, Manguito.
- **Ficha pública** en `mentor.php?login=…`: igual para todos; el propio mentor ve además «Editar mi perfil». Solo mentores del mismo campus (o uno mismo); un login desconocido muestra «Esta persona no es mentor» y enlaza a la intra.
  - Datos que vienen de la intra (en el momento, **no se guardan**): `GET /v2/users/:id` (nombre, foto, puesto, nivel, nota/fecha de validación de los proyectos; si la API no devuelve `marked_at`, la fecha no aparece) y `GET /v2/users/:id/coalitions`.
  - Datos que escribe el mentor (BD): `bio` (160), `availability` (available, busy o paused), `contact_pref` (cluster, slack o both), `languages` y `mentor_projects.note` (160). «Mentor desde» = `consented_at`.
  - «En pausa» oculta al mentor de Proyectos y Mentores, pero su ficha sigue accesible. El usuario de Slack de 42 es siempre el login de la intra: no se guarda.
- **Directorio:** se carga la lista entera con nombres y fotos (`ft_users_by_ids`, una petición por cada 100) y se filtra en el navegador (`assets/mentors.js`) por texto (sin tildes), proyecto y «conectados ahora». Incluye al propio usuario («· tú»).
- **Mentores inactivos:** se ocultan de Proyectos y Mentores tras 30 días sin `last_login_at` (`MENTOR_INACTIVE_DAYS`); la ficha por enlace sigue accesible. **Pendiente:** borrado definitivo de los inactivos (art. 4.5 de las condiciones de la API).
- **«Me ayudó»:**
  - **interruptor `VOTES_ENABLED`** en el `.env` (por defecto activo; `False`, `0`, `no` u `off` lo apagan, `votes_enabled()` en `src/votes.php`): apagado, se oculta todo (botones, puntos de las fichas, «Mis valoraciones»), `vote.php` y `ratings.php` redirigen con un aviso y los votos guardados se conservan en `votes`;
  - un voto de **1 a 3** por cada (estudiante, mentor, proyecto), que tiene que ser uno de los que mentoriza; nadie se vota a sí mismo y solo se vota a mentores del mismo campus;
  - se vota desde la ficha del mentor («Un poco», «Bastante», «Mucho»); `vote.php` guarda y `ratings.php` muestra «Mis valoraciones»;
  - el voto se puede cambiar, pero **no quitar** (si se quitara y se volviera a votar, cambiaría `created_at` y se haría trampa en el ranking). Solo «Borrar mis datos» los elimina;
  - para los demás los votos son **anónimos**: solo totales, nunca quién votó ni las fechas;
  - tabla `votes (voter_id, mentor_id, project_id, value, created_at, updated_at)`, con clave primaria en las tres primeras columnas, `value` entre 1 y 3 y claves foráneas `ON DELETE CASCADE` a `users`;
  - guardar quién vota exige el **consentimiento del estudiante** (si no lo ha dado, `consent.php?next=…` y vuelve a la ficha). Un votante sin proyectos de mentor no sale en ningún sitio.
- **Pendiente (ranking):** mentores del campus ordenados por la suma de puntos de los votos con `created_at` en la franja (semana, mes, 3 meses, año y siempre). Empates: más votos y, después, login. Solo mentores con algún voto en la franja. Con pocos votos en una franja corta se podría deducir quién votó; es aceptable porque solo se muestran totales.
- **Zonas horarias (la web se abre a más campus):** PHP y MySQL trabajan en UTC (`bootstrap.php`, y `SET time_zone` en `db.php`), y las fechas guardadas son UTC. La zona del campus del usuario viene en `/v2/me` (`campus[].time_zone`), va a la sesión (`$user['time_zone']`, Madrid si falta) y solo se usa para escribir y mostrar horas (`user_timezone()`, `local_time()` en `src/mentors.php`). Quien ve «Disponibles ahora» es del mismo campus que los oficiales que ve, así que comparten zona.
- **Mentores oficiales y manguitos (42 Madrid), 2026-10-19:**
  - los gestiona el administrador en `admin.php` (logins en `ADMIN_LOGINS` del `.env`), en la tabla `roster (login, role, added_at)` con `role` = `mentor` o `manguito`. Es lo único que se guarda antes del consentimiento (solo login y rol); no se muestra a nadie hasta que la persona acepta;
  - el rol se lee al iniciar sesión y va a la sesión (`$user['role']`): los cambios del admin se aplican en el siguiente login;
  - un mentor oficial anuncia que está disponible **hasta la hora que escribe** (máximo `AVAILABLE_MAX_HOURS` = 8 h desde ahora; si la hora ya pasó hoy, cuenta como mañana) desde `available.php` (`users.available_until`, que caduca sola). La página «Disponibles ahora» la ven manguitos, oficiales y admin: lista a los oficiales del mismo campus con consentimiento y `available_until > NOW()`, con su puesto en el cluster. Ver la página no exige consentimiento; anunciar la disponibilidad, sí;
  - un oficial puede no mentorizar ningún proyecto: `active_locations()` y `mentor.php` también lo admiten (`sql_is_official()`). No aparece en Proyectos ni en Mentores sin proyectos, y no hay insignia de «oficial» en lo existente;
  - **«manguito» solo lo ven la propia persona y el admin**: no sale en fichas ni listas, tampoco para los mentores;
  - **`MANGUITOS_ALL=True` (provisional, hasta tener el permiso de 42 Madrid):** todo el mundo cuenta como manguito y ve «Disponibles ahora», esté o no en `roster` (un oficial sigue siendo oficial). Se define en el `.env` de cada entorno; por defecto, desactivado;
  - «Borrar mis datos» borra todo, también la entrada de `roster` (la administración tendría que volver a añadir a la persona).
- Queda para más adelante: número de correcciones del proyecto (`scale_teams`, varias llamadas) y otras mejoras listadas en la sección «Mejoras futuras» del README.

- **Versión del código:** `APP_VERSION` en `src/bootstrap.php` (fecha y contador del día) se ve en el pie de página y como `?v=` en los CSS/JS propios (evita la caché de 7 días del servidor). **Subir el número en cada despliegue**, para saber qué código usa cada entorno.

## Repositorio y flujo de trabajo
- Se trabaja en solitario y **directamente en `main`**, sin ramas ni PR obligatorias. `main` es lo que se despliega en producción, que puede llevar cambios hechos solo en el servidor (ver `CLAUDE.local.md`). La rama `develop` queda como histórico del hackathon.
- La protección de ramas de GitHub no hace falta.
- Fuera del repo (`.gitignore`): `API-Docs/`, `.env`, `config.php`, `hosting-tests/`, `deploy/`, `datos.txt`, PDF de las bases del hackathon y `CLAUDE.local.md`.
