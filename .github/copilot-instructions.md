# Copilot Instructions

## Architecture
- Root PHP pages (index.php, citas.php, etc.) render views directly and rely on includes/head.php, header.php, menu-drawer.php, and footer.php for layout; always load includes/check-session.php before restricted content.
- includes/db.php bootstraps both a PDO instance ($pdo/$db) and a mysqli fallback ($conn); new DB code must keep working when either interface is used.
- includes/load_env.php hydrates environment variables from .env during local dev; prefer getenv to hardcoded secrets.
- assets/css and assets/js hold the only front-end bundle; no build step or package manager is involved.
- api/ exposes JSON endpoints consumed by a React-based medical dashboard; responses should stay compatible with sendJsonResponse in api/config/headers.php.

## Data sync and integrations
- guardar_cita.php writes to the local citas table and triggers remote sync through includes/remote_api.php when REMOTE_API_URL is set.
- If the remote HTTP call fails, enqueue_sync in includes/sync_queue.php logs the payload for retries; PHP CLI workers/processors live in workers/.
- Run php workers/sync_worker.php to drain pending jobs and php workers/list_pending.php for visibility during debugging.
- includes/remote_api.php circles between REST calls and DB fallbacks; reuse resolve_remote_medico_id and normalize_remote_specialty helpers from guardar_cita.php when mapping specialties.
- scripts/test_remote_sync.php verifies the REMOTE_DB_* path; keep both the API and DB fallbacks healthy because production deployments toggle between them.

## Auth and roles
- Sessions are hardened in includes/check-session.php (strict cookies, 30-minute idle timeout); reuse it for any new authenticated page.
- login.php handles registration, guest mode, and a missing rol column gracefully; mirror that defensive pattern in new auth-related code.
- api/config/auth.php enforces bearer tokens for write endpoints; call check_api_token early and return 401 in the same style as api/list_users.php.
- migrations/migrate_add_rol.php is a token-protected one-off to add usuarios.rol; do not assume the column exists unless the migration ran.

## Database expectations
- Table structures live in crear_tablas.sql plus scripts/create_sync_queue.sql; check them before altering schemas.
- Favor parameterized PDO statements; when adding mysqli fallbacks match the escaping pattern found in api/create_cita.php.
- Some code (api/pacientes.php, api/reportes.php) aggregates counts and stats; keep result shapes stable for the React consumer.

## Operational notes
- Set API_SHARED_TOKEN, DB_*, REMOTE_API_URL, REMOTE_API_TOKEN, and REMOTE_DB_* via environment variables or .env (for local only).
- Debug traces are written to api/request_debug.log, api/debug_raw.txt, and worker_debug.log; clean up or rotate when adding new logging.
- Desktop visitors are redirected offsite by assets/js/app.js; respect that behavior unless requirements change.
- No automated tests exist; use php -l <file> or a browser hit for smoke checks.
- Keep responses JSON-encoded UTF-8 and include success/data/timestamp/status_code fields when using sendJsonResponse.
- api/config/headers.php currently contains duplicated header logic after an incomplete merge; avoid reintroducing divergence and consolidate if editing.

## Getting started quickly
- Copy .env.example to .env, adjust credentials, restart Apache (or WAMP), then load http://localhost/asosiacionMexicanaDeDiabetes/.
- Seed a user via crear_admin.php or the registration form, log in, and create a cita to exercise the sync queue and remote API path.
- For API consumers, hit api/pacientes.php?endpoint=pacientes or api/reportes.php?endpoint=dashboard with the Authorization header to validate authentication.
