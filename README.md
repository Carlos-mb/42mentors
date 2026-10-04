# 42 Mentors

Web para que los estudiantes de 42 encuentren compañeros que ya han validado el proyecto en el que están atascados, y para que quienes ya lo terminaron se ofrezcan a ayudar.

Proyecto del **Hackathon 42442** (42 Madrid, octubre de 2026).

## Equipo

| Login 42 | Nombre | Responsabilidades |
|---|---|---|
| `cmelero-` | Carlos | _TODO_ |
| `fcamasa` | Florentin | _TODO_ |
| `lucaroma` | Lucas | _TODO_ |

## El problema ("dolor")

La lista de proyectos de 42 es cada vez más larga y compleja. Cuando un estudiante se atasca en un proyecto que nadie de su entorno conoce, no tiene una forma rápida de saber **quién de su campus ya lo ha validado** y está dispuesto a ayudar. La información existe en la intra, pero está dispersa y no indica quién quiere ayudar.

## La solución

1. **Login con 42:** OAuth2 de la API de 42, sin registro aparte.
2. **Mi perfil (mentor):**
   - marca con qué proyectos validados puede ayudar, con una nota opcional por proyecto;
   - escribe una presentación;
   - indica su disponibilidad (*Disponible*, *Ocupado* o *En pausa*, que le oculta de las búsquedas);
   - indica cómo prefiere que le contacten (en el cluster o por Slack) y en qué idiomas.
3. **Proyectos (estudiante):** los proyectos que aún no ha validado y que tienen mentores en su campus, con los que tiene en curso primero. Al pulsar uno, ve los mentores con foto, nota sobre el proyecto y **puesto en el cluster si están conectados**.
4. **Mentores:** directorio del campus con buscador por nombre o login (sin importar las tildes), filtro por proyecto y filtro «conectados ahora».
5. **Ficha del mentor:**
   - datos de la intra: foto, nivel, coalición, puesto actual y nota y fecha de validación de cada proyecto que mentoriza;
   - lo que escribe el mentor: presentación, contacto, idiomas y notas;
   - «mentor desde» y enlace a su perfil de la intra.

   El propio mentor ve además un botón para editarla.

### Uso de la API de 42
| Para qué | Endpoint |
|---|---|
| Login | `/oauth/authorize` + `/oauth/token` (Authorization Code flow) |
| Datos del usuario, campus y proyectos (una sola llamada al entrar) | `GET /v2/me` |
| Nombre y foto de los mentores en las listas (una llamada por cada 100 mentores) | `GET /v2/users?filter[id]=…` |
| Ficha del mentor: puesto, nivel y notas de sus proyectos | `GET /v2/users/:id` |
| Ficha del mentor: coalición | `GET /v2/users/:id/coalitions` |
| Quién está conectado y dónde (una llamada por campus, cacheada 2 min) | `GET /v2/campus/:id/locations?filter[active]=true` |

Límite de la API: 2 peticiones por segundo y 1200 por hora por app. Por eso se usa caché y se reintenta ante un 429.

### Privacidad (condiciones de uso de la API de 42)
- Solo se guarda el mínimo, y **solo tras el consentimiento explícito** del mentor:
  - id, login, campus y proyectos que mentoriza;
  - lo que el propio mentor escribe en su perfil.
- El nombre, la foto, el nivel, la coalición, las notas de la intra y la ubicación se consultan en el momento. El token de acceso vive solo en la sesión.
- Toda la información está detrás del login con 42, con `noindex` y `robots.txt`.
- Página de privacidad con botón **"Borrar mis datos"**.

## Metodología de ideación y prototipado

### Cómo surgió la idea
La idea nació de juntar tres necesidades reales de los miembros del equipo:

- **Florentin** quería conectar a las personas de la comunidad de 42 y hacerla crecer. Además, tenía una necesidad concreta: buscar apoyo para el examen 02 del common core, que lleva más de 10 intentos.
- **Carlos** necesitaba encontrar compañeros para sus proyectos. En su experiencia, Slack no es un canal productivo para eso: los mensajes se pierden y no se sabe quién está dispuesto a ayudar.
- **Lucas** propuso animar a la gente a participar en la comunidad con algún incentivo, de forma que ayudar sirva también para conocer a otros estudiantes.

Al ponerlas en común vimos que las tres tenían una raíz común: **la ayuda existe en el campus, pero no hay forma de encontrarla**. De ahí salió la idea: una web donde quien ya ha validado un proyecto puede ofrecerse a ayudar con él, y quien está atascado puede ver quién se ha ofrecido y dónde está sentado en ese momento.

### Alternativas descartadas
| Alternativa | Por qué se descartó |
|---|---|
| Premios o recompensas por participar (idea de Lucas) | Requiere implicar al staff de 42 y no cabía en el plazo del hackathon. Se deja como evolución futura; como primer paso se planteó el botón «Me ayudó» y un ranking de mentores. |
| Seguir usando Slack | Es el problema de partida: los mensajes se pierden y no indican quién ha validado el proyecto ni quién quiere ayudar. |
| Flask (Python) | El hosting compartido no admite Python. Se cambió a PHP sin framework con MySQL. |
| Mostrar los proyectos a los que el estudiante puede inscribirse (`/projects_users/registration`) | Se priorizaron los proyectos no validados que **ya tienen mentores**, porque un proyecto sin mentores no le sirve al estudiante. Queda como mejora. |

### Prototipado
1. **Análisis (1-2 oct):** lectura del enunciado, las bases y las condiciones de uso de la API de 42, que marcaron el diseño de privacidad (consentimiento, datos mínimos, borrado).
2. **MVP desplegado el 2 oct** en el dominio del proyecto: login con 42, «Mi perfil» para marcar proyectos y búsqueda de mentores por proyecto.
3. **Iteración sobre el MVP:** directorio de mentores con buscador, ficha del mentor con datos de la intra, y caché para no superar el límite de la API.

### Validación
Antes de desarrollarla, contamos la idea a **más de 20 estudiantes de 42 Madrid**, y a todos les pareció muy buena idea. Confirmaron lo que habíamos visto en el equipo: cuando alguien se atasca en un proyecto, no tiene una forma rápida de saber quién del campus lo ha validado y quiere ayudar.

## Gestión del proyecto

_TODO: tablero (GitHub Projects), reparto de tareas, dailies, modelo de ramas y Pull Requests._

- Tareas: [issues de GitHub](https://github.com/Carlos-mb/42mentors/issues), cada una con responsable y criterio de terminado. La IA de cada miembro las consulta para proponerle qué hacer y deja en ellas el avance (ver [AGENTS.md](AGENTS.md)).
- Modelo de ramas: `main` (lo desplegado) ← `develop` ← `feature/<tarea>`, integrando siempre con Pull Request revisada por otro miembro.
- Commits: [Conventional Commits](https://www.conventionalcommits.org/es/) (`feat:`, `fix:`, `docs:`…).

### Uso de IA
Usamos asistentes de IA (Claude Code, entre otros) para programar, redactar documentación y trabajar con git.
- **Lo decide el equipo:** qué se construye, el diseño de cada funcionalidad, el reparto de tareas y qué se fusiona.
- **Lo hace la IA:** propone y escribe código y textos, y ejecuta comandos de git siguiendo las reglas de [AGENTS.md](AGENTS.md). Entre esas reglas: no subir secretos, no tocar `main` ni `develop` directamente y no fusionar PR.
- **Cómo se controla:** cada cambio va en su rama y con su Pull Request, y otro miembro lo revisa y lo prueba antes de aprobarlo. Los commits en los que ha participado la IA lo indican con `Co-Authored-By`.

## Registro de horas

| Fecha | cmelero- | fcamasa | lucaroma | Qué se hizo |
|---|---|---|---|---|
| 2026-10-01 | | | | Formación de equipos |
| 2026-10-02 | _TODO_ | 1 | _TODO_ | Análisis, documentación de la API, verificación del hosting, MVP inicial |
| 2026-10-03 | 1 | 1 | | fcamasa: app de la API creada desde mi perfil de la intra y entorno de desarrollo preparado en localhost. cmelero- (con fcamasa): configuración de la IA como coordinadora mediante issues de GitHub; repaso de cómo es una issue y de cómo se aprueban y fusionan las PR |
| 2026-10-04 | | | | |
| 2026-10-05 | | | | |
| 2026-10-06 | | | | |
| **Total** | | | | |

## Cómo levantar el proyecto

### Requisitos
- PHP 8.1 o superior con las extensiones `curl` y `pdo_mysql`.
- MySQL o MariaDB.
- Una aplicación OAuth en la intra: https://profile.intra.42.fr/oauth/applications/new, con tipo *42 Community Development*, scope `public` y la Redirect URI apuntando a `callback.php`.

### Pasos
1. Crear una base de datos y ejecutar [`sql/schema.sql`](sql/schema.sql), por ejemplo desde phpMyAdmin. Si la base de datos ya existe de una versión anterior, aplicar en orden los ficheros de [`sql/migrations/`](sql/migrations/).
2. Copiar [`.env.example`](.env.example) como `.env` y rellenarlo con las credenciales de la app OAuth y de la base de datos.
3. Hacer que el servidor web sirva **solo la carpeta `public/`**. `src/`, `sql/`, `cache/` y `.env` deben quedar fuera de la parte pública.
4. Comprobar que `cache/` tiene permisos de escritura para PHP.

Para desarrollo en local con XAMPP, despliegue en el hosting y flujo de git, ver [INSTRUCCIONES_EQUIPO.md](INSTRUCCIONES_EQUIPO.md).

### Estructura
```
public/          Lo único accesible por web (páginas, CSS, JS, robots.txt)
src/             Lógica: configuración, BD, cliente de la API, sesión, vistas
sql/schema.sql   Esquema completo de la base de datos (instalaciones nuevas)
sql/migrations/  Cambios para bases de datos ya creadas
cache/           Caché temporal de ubicaciones (no se versiona)
.env.example     Plantilla de configuración (el .env real nunca se sube)
```

## Problemas técnicos y soluciones

| Problema | Solución |
|---|---|
| La documentación de la API (`/apidoc`) requiere login y se anunció su retirada. | Se descargó entera para uso interno del equipo. No se publica porque es material de 42 con datos de ejemplo de personas. |
| El hosting compartido no admite Python. | Se eligió PHP sin framework con MySQL. |
| Duda de si el hosting permitía peticiones HTTPS salientes, imprescindibles para el OAuth. | Prueba con `curl` hacia `api.intra.42.fr`: respondió 401, así que la conexión funciona. |
| Límite de 1200 peticiones por hora. | Una sola llamada a `/v2/me` al entrar. Ubicaciones pedidas una vez por campus y cacheadas. Perfiles de mentores en una sola llamada. |
| Las condiciones de la API exigen consentimiento y datos mínimos. | Pantalla de consentimiento, esquema mínimo, borrado de datos y `noindex`. |
| _TODO: añadir los que vayan surgiendo_ | |
