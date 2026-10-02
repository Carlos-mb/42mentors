# Hackathon 42442 — Web de mentoría entre alumnos de 42

> Mantener este fichero actualizado con las conclusiones y decisiones que se vayan tomando.

**Reglas de git y de trabajo para la IA (obligatorias):** @AGENTS.md

## Proyecto
Web sencilla para que los alumnos de 42 Madrid:
1. **Login** con OAuth2 de la API de 42.
2. **Mentor:** ve sus proyectos terminados, marca/desmarca los que puede mentorizar y guarda.
3. **Estudiante:** ve los proyectos que puede hacer; al pulsar uno ve los mentores (nombre, login, foto, ubicación en el cluster si está logueado), cada uno enlazado a su ficha de la intra.

Detalle original en [proyecto.txt](proyecto.txt). Equipo en [datos.txt](datos.txt):
- Carlos: `cmelero-`
- Florentin: `Fcamasa`
- Lucas: `lucaroma`

## Bases del hackathon (resumen de [Bases Hackathon 42442 (2026).pdf](Bases%20Hackathon%2042442%20(2026).pdf))
- **Code freeze:** 6 oct 2026, 18:00. **Peer evaluation:** 6 oct 18:01 → 7 oct 13:00 (repo público). **Demo Day:** 7 oct 16:00–18:00 (pitch 4 min + 2 min Q&A).
- Obligatorio:
  - usar la API de 42;
  - repo en GitHub con ramas y commits continuos (se penaliza un único commit);
  - **nunca subir secretos**, incluir `.env.example`.
- `README.md` obligatorio con:
  - logins del equipo;
  - responsabilidades por miembro y cómo se gestiona el proyecto;
  - **registro de horas por participante** (necesario para los bonus days);
  - el "dolor" que se ataca, la solución y la metodología de ideación/prototipado;
  - guía para levantar el proyecto;
  - registro de problemas técnicos y soluciones.
- Evaluación:
  - 50% peer evaluation, con 0/1 por criterio: Git y ramas (50%), ideación (25%), gestión (25%);
  - 25% funcionalidad e impacto (desempate);
  - 25% pitch.

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
- La API no guarda qué proyectos mentoriza cada alumno, así que **necesitamos BD propia** (user_id/login ↔ project_id).
- Ficha de la intra de un usuario: `https://profile.intra.42.fr/users/<login>`.

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
  - Cada miembro registra su propia app OAuth para desarrollo local.
  - El secret de producción solo está en `config.php` del servidor.
  - El secret rota cada mes: vigilar la fecha de caducidad en la intra.
- **Seguridad (4.3):**
  - HTTPS y secretos fuera de `public_html`;
  - access token solo en la sesión, nunca en la BD;
  - cookies `Secure`/`HttpOnly`/`SameSite`;
  - guardar el mínimo (user_id, login, project_id);
  - backups.
- **No saltarse el rate limit** (por ejemplo, repartiendo la producción entre varias apps).
- **Incidentes:** si se filtra un secret, revocarlo y avisar a security@42.fr (art. 6).
- **`API-Docs/` NO se sube al repo público:** es material de 42 tras login y trae datos de ejemplo con nombres y logins. Al crear el repo, meter en `.gitignore`: `API-Docs/`, `config.php` y `.env`.

## Estado / decisiones
- 2026-10-02: leído el enunciado y las bases; descargada la documentación de la API. Aún no hay código ni repo git.
- Pendiente: elegir stack, crear el repo en GitHub con estructura de ramas, `.env.example`, esqueleto del README.
- 2026-10-02: app OAuth en la intra → tipo "42 Community Development", scope `public`.
  - Redirect URI de producción: `https://42.2275676.xyz/callback.php`.
  - La de `localhost:5000` era del plan con Flask, ya descartado.

- 2026-10-02: **el producto final irá en un dominio propio sobre hosting compartido**. Probablemente tenga PHP y MySQL; probablemente no permita Python.
  - Propuesta: **PHP sin framework + MySQL (PDO)**, con `curl` para la API y `$_SESSION` para la sesión. Flask queda descartado.
  - Desarrollo en local con XAMPP (aún no está instalado; tampoco git).
  - Secretos en un `config.php` fuera de `public_html` y fuera de git; en el repo, `config.example.php` y `.env.example`.
  - Harán falta 2 Redirect URI en la app de la intra: la de local y la de `https://<dominio>/...`.
  - Las peticiones HTTPS salientes son **imprescindibles**. El único flujo documentado es el `authorization_code`, que necesita el `client_secret` en un servidor; no se mencionan ni el flujo implícito ni PKCE. Prueba: un PHP con curl a `https://api.intra.42.fr/oauth/token/info`, donde 401 = OK y 0 = bloqueado.
    - Si está bloqueado, plan B: un subdominio del dominio propio apuntado por DNS a una plataforma gratuita (Render, Railway, Vercel…).
    - Otras opciones: Cloudflare Worker como intermediario, o cambiar de hosting.
  - **Hosting verificado el 2026-10-02 con phpinfo:**
    - dominio `https://42.2275676.xyz` en A2 Hosting (CloudLinux, LiteSpeed), con HTTPS que funciona;
    - PHP 8.1.34, curl activo (OpenSSL 1.1.1w), pdo_mysql, mysqli y sqlite;
    - sin `disable_functions` ni `open_basedir`;
    - carpeta pública en `/home/mmmkofti/42.2275676.xyz`, así que los secretos pueden ir fuera de ella, en `/home/mmmkofti/`.
    - ✅ curl saliente hacia api.intra.42.fr funciona (prueba `hosting-tests/test42.php` → HTTP 401). **El hosting sirve: el plan PHP + MySQL queda viable.**
    - `info.php` ya está borrado del servidor.
    - Pendiente: borrar `test42.php` del servidor y saber si hay acceso SSH.
  - **Por verificar en el hosting:** versión de PHP (≥ 8.1), extensión `curl`, peticiones HTTPS salientes permitidas, HTTPS en el dominio, versión de MySQL, poder guardar ficheros fuera de `public_html`, si es carpeta o subdominio, y si hay acceso SSH.

- 2026-10-02: **MVP escrito en PHP 8.1 sin framework + MySQL** (stack decidido).
  - ✅ **Desplegado en https://42.2275676.xyz y funcionando** (confirmado por Carlos el 2026-10-02). Instalado en `/home/mmmkofti/42mentors/`, con document root en `42mentors/public` y `.env` en `42mentors/.env`.
  - En el PC de Carlos no hay PHP: las pruebas se hacen en el servidor.
  - Aún no hay repo git. Instrucciones para el equipo (git, XAMPP, despliegue, tareas) en [INSTRUCCIONES_EQUIPO.md](INSTRUCCIONES_EQUIPO.md); README obligatorio con TODOs en [README.md](README.md).
  - Estructura:
    - `public/` es el document root;
    - `src/` contiene la lógica;
    - `sql/schema.sql` (esquema completo) y `sql/migrations/` (cambios para BD ya creadas);
    - `cache/` guarda las ubicaciones;
    - los secretos van en `.env`, en la raíz y fuera de `public/`. Se usa **`.env` en vez de `config.php`** porque las bases exigen `.env.example`.
  - Login: `/v2/me` una sola vez. El usuario y sus proyectos (sin subproyectos, `parent_id`) van a la sesión; el token solo en la sesión.
  - Privacidad (cumple las condiciones de la API):
    - **la BD solo guarda id, login, campus_id, consented_at, last_login_at** y los proyectos que mentoriza, y solo tras `consent.php`. Desde la ficha de mentor, también lo que escribe el propio mentor (ver más abajo);
    - nombre y foto de los mentores se piden en el momento con `GET /v2/users?filter[id]=…`;
    - la caché de ubicaciones (por campus, 120 s) solo guarda mentores con consentimiento;
    - `privacy.php` incluye «Borrar mis datos»;
    - hay `noindex` (meta + cabecera `X-Robots-Tag`) y `robots.txt`.
  - «Proyectos que puede hacer» (decisión 12, de momento) = proyectos con mentores de su campus que el estudiante no ha validado; los «en curso» primero. `/projects_users/registration` queda como mejora.
  - Mentores solo del mismo campus (decisión 13); los conectados primero (14).

- 2026-10-02: **ficha de mentor y directorio** (decididos con Carlos; **pendiente de probar en el servidor**).
  - Menú: **Proyectos** (`projects.php`, antes `student.php`), **Mentores** (`mentors.php`, directorio) y **Mi perfil** (`profile.php`, edición; antes `mentor.php`).
  - **Ficha pública** en `mentor.php?login=…`: igual para todos; el propio mentor ve además «Editar mi perfil». Solo mentores del mismo campus (o uno mismo); un login desconocido muestra «Esta persona no es mentor» y enlaza a la intra.
  - Datos de la ficha que vienen de la intra (API, en el momento, **no se guardan**):
    - `GET /v2/users/:id`: nombre, foto, puesto (`location`), nivel (42cursus o el más alto) y nota/fecha de validación de los proyectos que mentoriza;
    - `GET /v2/users/:id/coalitions`: coalición.
    - Ojo: los ejemplos de la documentación no muestran `marked_at`. Si la API no lo devuelve, la fecha simplemente no aparece.
  - Datos que escribe el mentor (BD, tras el consentimiento):
    - `bio` (160);
    - `availability`: available, busy o paused. «En pausa» oculta al mentor de Proyectos y Mentores, pero su ficha sigue accesible;
    - `contact_pref` (cluster, slack o both). El usuario de Slack de 42 **es siempre el login de la intra**: no se pregunta ni se guarda, y la ficha muestra `@login` (columna `slack_handle` eliminada con la migración 002);
    - `languages`;
    - `mentor_projects.note` (160).
    - «Mentor desde» = `consented_at`.
  - **Directorio, opción A:** se carga la lista entera con nombres y fotos (`ft_users_by_ids`, una petición por cada 100) y se filtra en el navegador (`assets/mentors.js`) por texto (sin tildes), proyecto y «conectados ahora». Incluye al propio usuario («· tú»).
  - Orden de mentores en todas las listas: conectados, luego disponibles antes que ocupados, luego login.
  - Migraciones para la BD existente:
    - `001_perfil_mentor.sql`, que se aplica **antes** de subir el código;
    - `002_quitar_slack_handle.sql`, que se aplica **después**.
    - `schema.sql` contiene el esquema completo para instalaciones nuevas.
  - Al guardar «Mi perfil» aparece una ventana «Cambios guardados» con «Ver mi ficha pública» y «Seguir editando». También hay un aviso si se sale con cambios sin guardar.
  - El texto del consentimiento se amplió con los datos nuevos. Las cuentas que aceptaron antes son solo de prueba del equipo; con usuarios reales habría que pedirles que aceptaran de nuevo.
  - Queda para más adelante: botón «Me ayudó» con contador y número de correcciones del proyecto (`scale_teams`, varias llamadas).

- 2026-10-02: **repo git creado**: https://github.com/Carlos-mb/42mentors (privado de momento; hay que hacerlo **público** antes del code freeze para la peer evaluation).
  - git y gh instalados en el PC de Carlos; `gh` autenticado como `Carlos-mb`. Identidad del repo local: `Carlos` / email noreply de GitHub.
  - Ramas: `main` y `develop`, **ambas protegidas** (solo vía PR con 1 aprobación, sin force push; los admins pueden saltárselo). Trabajo en `feature/*`, `fix/*`, `docs/*` → PR a `develop` (merge commit, no squash) → PR a `main`. Guía para el equipo en la sección 1 de `INSTRUCCIONES_EQUIPO.md`; pensada para gente que nunca ha usado ramas ni PR.
  - Florentin y Lucas usarán IA para git (Florentin, Claude; Lucas, otra aún por saber). Las reglas para cualquier IA están en `AGENTS.md`, que `CLAUDE.md` importa; para IA de chat, se pega su contenido.
  - Colaboradores: `Floren87` (Florentin). **Falta Lucas** (pendiente su usuario de GitHub).
  - Fuera del repo (`.gitignore`): `API-Docs/`, `.env`, `config.php`, `hosting-tests/`, `deploy/`, `datos.txt`, PDF de las bases.

### Decisiones de equipo pendientes (propuesta inicial entre paréntesis)
Urgentes (bloquean el arranque):
1. ~~Stack~~ → decidido: PHP sin framework + MySQL.
2. Reparto de roles.
3. Dueño del repo GitHub.
4. Dueño de la app OAuth.
5. Nombre del proyecto.

Forma de trabajar:
6. Modelo de ramas (`main` protegida, `develop`, `feature/*` con PR revisada).
7. Commits (Conventional Commits).
8. Tablero de tareas (GitHub Projects).
9. Registro de horas (tabla diaria, obligatorio).
10. Daily de 10 min.

Producto:
11. Proyecto terminado = `validated? == true`.
12. Proyectos que puede hacer el estudiante (`/projects_users/registration`, si no los del cursus no validados).
13. Solo mentores de 42 Madrid.
14. Conectados primero.
15. Ubicaciones cacheadas unos minutos.
16. Extras solo tras el MVP.

Entrega:
17. Demo en el dominio propio; tener la versión local como plan B.
18. Quién hace el pitch.
19. Reparto de la peer evaluation.
20. Fecha límite interna: lunes 5 por la noche.
