# 42 Mentors

Web para que los estudiantes de 42 encuentren a compañeros de su campus que ya han validado el proyecto en el que están atascados, y para que quienes ya lo terminaron se ofrezcan a ayudar.

Desplegado en **https://42mentors.com**. Por las condiciones de uso de la API de 42, todo el contenido está detrás del login con 42.

Hecho con **vibe coding** por un equipo de tres personas, cada una con su IA, coordinadas a través de GitHub ([cómo trabajamos](#vibe-coding-coordinado-por-github)).

## Origen

Nació en el **Hackathon 42442** (42 Madrid, octubre de 2026). Hoy lo mantiene Carlos.

| Login 42 | GitHub | Nombre |
|---|---|---|
| `cmelero-` | [`Carlos-mb`](https://github.com/Carlos-mb) | Carlos |
| `fcamasa` | [`Floren87`](https://github.com/Floren87) | Florentin |
| `lucaroma` | [`lucas-rcv`](https://github.com/lucas-rcv) | Lucas |

Algunos commits aparecen con otro autor en git (`ThisWeekapp` es Lucas y `Florentin Vasilica Camasa` es Florentin).

## Vibe coding coordinado por GitHub

El proyecto se hizo con **vibe coding**: las personas decidían qué construir y revisaban el resultado, y la IA escribía el código, los textos y los comandos de git. Carlos coordinaba las tareas con su IA (Claude Code); Florentin y Lucas usaban las suyas (Lucas, Cursor) para descargarse sus tareas de GitHub y avanzar de forma sincronizada.

GitHub era lo único que compartían las tres IA: lo que no estuviera allí, las demás no lo veían.

1. **Planificar.** Cada tarea era una [issue](https://github.com/Carlos-mb/42mentors/issues?q=is%3Aissue) con etiquetas (`prioridad alta`, `en curso`, `bloqueada` y el tipo), responsable y un criterio «Terminado cuando». Carlos aprobaba el reparto y los cambios de criterio.
2. **Coger tarea.** Cada persona preguntaba a su IA «¿qué hago ahora?». Con `gh`, la IA miraba las PR por revisar y las issues asignadas, y recomendaba una.
3. **Avanzar.** Una rama por issue (`feature/17-me-ayudo`) y, al parar, un comentario con lo hecho, lo que faltaba y la rama, para que otra IA pudiera retomarlo ([ejemplo](https://github.com/Carlos-mb/42mentors/issues/17)).
4. **Integrar.** Pull Request a `develop`, que tenía que aprobar otra persona ([ejemplo](https://github.com/Carlos-mb/42mentors/pull/34)).

**Reglas comunes.** Un solo fichero, [`AGENTS.md`](https://github.com/Carlos-mb/42mentors/blob/d4d3a852bd3269280d5a7e2e19185adfdc48bfeb/AGENTS.md) (versión del hackathon), valía para cualquier herramienta: cómo coordinarse, reglas de git y lo que la IA tenía prohibido, como subir secretos, escribir directamente en `main` o `develop`, o aprobar y fusionar PR. Además, [GitHub Actions](.github/workflows/ci.yml) comprueba la sintaxis PHP y busca secretos con gitleaks.

**Resultado.** 30 issues con 57 comentarios, 32 Pull Requests (todas aprobadas por otra persona antes de fusionarse) y 61 commits que acreditan a la IA con `Co-Authored-By`.

**Después del hackathon**, Carlos mantiene el proyecto con Claude Code, directamente en `main`. [`CLAUDE.md`](CLAUDE.md) guarda las decisiones para que la IA las tenga en cada sesión, y [`AGENTS.md`](AGENTS.md), sus reglas actuales.

## Qué hace

1. **Login con 42** (OAuth2), sin registro aparte.
2. **Mi perfil:** el mentor marca con qué proyectos validados puede ayudar (no los de la piscina), con una nota por proyecto, y escribe una presentación, su disponibilidad (*Disponible*, *Ocupado* o *En pausa*), cómo prefiere que le contacten (cluster o Slack) y sus idiomas.
3. **Proyectos:** los que el estudiante aún no ha validado y tienen mentores en su campus, primero los que tiene en curso. Cada uno muestra sus mentores, con su **puesto en el cluster si están conectados**.
4. **Mentores:** directorio del campus con buscador y filtros por proyecto y «conectados ahora».
5. **Ficha del mentor:** foto, nivel, coalición, puesto, nota de cada proyecto que mentoriza, lo que ha escrito el mentor y enlace a su perfil de la intra.
6. **Manguito:** los mentores oficiales avisan de que están disponibles hasta una hora (como mucho 8 h) y los manguitos ven quién lo está. Cada uno se marca con un enlace privado; el administrador solo puede quitar la marca.
7. **Listas útiles:** no salen los mentores en pausa ni los que llevan más de 30 días sin entrar. Primero los conectados, luego los disponibles.
8. **En español e inglés**, según el navegador o el selector ES · EN.

## Uso de la API de 42

| Para qué | Endpoint |
|---|---|
| Login | `/oauth/authorize` + `/oauth/token` |
| Usuario, campus, cursus y proyectos (una vez al entrar) | `GET /v2/me` |
| Nombre y foto de los mentores (una llamada por cada 100) | `GET /v2/users?filter[id]=…` |
| Ficha del mentor | `GET /v2/users/:id` y `GET /v2/users/:id/coalitions` |
| Quién está conectado y dónde (por campus, caché de 2 min) | `GET /v2/campus/:id/locations?filter[active]=true` |

Límite de la API: 2 peticiones/segundo y 1200/hora por app, así que se cachea y se reintenta ante un 429.

## Privacidad

- No se guarda nada **sin consentimiento explícito**. Tras él, solo id, login, campus, proyectos que mentoriza y lo que escribe el propio mentor.
- Nombre, foto, nivel, coalición y ubicación se piden a la API y solo se cachean temporalmente en el servidor. El token de acceso vive solo en la sesión.
- Todo está detrás del login con 42, con `noindex` y `robots.txt`.
- La página de privacidad tiene **«Descargar mis datos»** y **«Borrar mis datos»**. Los datos de quien lleva 12 meses sin entrar se borran solos.

## Cómo levantar el proyecto

**Requisitos:** PHP 8.1+ con `curl` y `pdo_mysql`, MySQL o MariaDB y una aplicación OAuth en https://profile.intra.42.fr/oauth/applications/new (scope `public`, Redirect URI a `callback.php`).

1. Crear la base de datos con [`sql/schema.sql`](sql/schema.sql). Si ya existe de una versión anterior, aplicar en orden [`sql/migrations/`](sql/migrations/).
2. Copiar [`.env.example`](.env.example) como `.env` y rellenarlo.
3. Servir **solo `public/`**; `src/`, `sql/`, `cache/` y `.env` quedan fuera. En local: `php -S localhost:8000 -t public` (Redirect URI `http://localhost:8000/callback.php`).
4. Dar permisos de escritura a `cache/`.

Desarrollo en local y despliegue: [DESARROLLO.md](DESARROLLO.md).

```
public/          Lo único accesible por web
src/             Lógica, idiomas (src/lang/) y vistas
sql/             Esquema y migraciones
cache/           Caché temporal (no se versiona)
tools/           Script del paquete de despliegue
.github/         CI: sintaxis PHP y búsqueda de secretos
```

## Mejoras futuras

- Proyectos a los que el estudiante puede inscribirse (`/v2/users/:id/projects_users/registration`).
- Dejar más claro dónde se activa el perfil de mentor.
- Menú «hamburguesa» en el móvil.
- Número de correcciones del mentor en cada proyecto (`scale_teams`).
