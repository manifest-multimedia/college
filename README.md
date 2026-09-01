# College360

College360 is a Laravel-based college management system for student administration, academic records and multi-year results, examinations, billing and fees, communications, elections, reporting, and student self-service.

The application is deployed as separate institutional instances so each institution can retain its own configuration, data, customisations, and release path.

## Contents

- [Technology and requirements](#technology-and-requirements)
- [Local development](#local-development)
- [Configuration](#configuration)
- [Useful commands](#useful-commands)
- [AI Sensei and Needle](#ai-sensei-and-needle)
- [Deployment](#deployment)
- [Security and operational notes](#security-and-operational-notes)

## Technology and requirements

| Area | Requirement |
| --- | --- |
| Application | PHP 8.2 or later (PHP 8.4 is used by deployment workflows) |
| Framework | Laravel 12, Livewire 3 |
| PHP dependencies | Composer 2 |
| Front-end build | Node.js 22 and npm |
| Database | MySQL/MariaDB in production; SQLite can be used locally |
| Runtime services | A web server with PHP-FPM, a queue worker where queues are enabled, and scheduled tasks where configured |
| Optional local AI | Python 3.10+ with `venv` for the private Needle sidecar |

## Local development

1. Clone the repository and create an environment file.

   ```bash
   git clone https://github.com/manifest-multimedia/college.git
   cd college
   cp .env.example .env
   ```

2. Configure `DB_*` values in `.env`. For a quick local SQLite setup, set `DB_CONNECTION=sqlite` and create the configured SQLite database file.

3. Install dependencies and initialise the application.

   ```bash
   composer install
   npm ci
   php artisan key:generate
   php artisan migrate
   npm run build
   ```

4. Start the application.

   ```bash
   php artisan serve
   ```

   For the normal full development stack, use:

   ```bash
   composer run dev
   ```

   This runs the Laravel server, queue listener, logs, and Vite development server together.

## Configuration

Use `.env.example` as the baseline. Never commit `.env`, private keys, deployment exports, or database credentials.

Important settings include:

| Setting group | Purpose |
| --- | --- |
| `APP_*` | Application name, environment, debug mode, URL, and encryption key |
| `DB_*` | Database connection and credentials |
| `SESSION_*`, `CACHE_*`, `QUEUE_*` | Laravel runtime storage and workers |
| `AUTH_*`, `AUTHCENTRAL_*` | Institution-specific authentication behaviour |
| `OPENAI_*` | Existing external AI Sensei assistant integration and file processing |
| `NEEDLE_*` | Optional local-first AI Sensei tool routing; disabled by default |
| `RESULTS_SMS_*` | Secure results-message upload limits, provider rate limit, retention, and optional ClamAV scanning |

After changing configuration in production, refresh Laravel's cached configuration:

```bash
php artisan optimize:clear
php artisan config:cache
```

### Results SMS file uploads

Authorized academic administrators can use **Communication → Results SMS File Upload** to validate `.xlsx` or `.csv` files with `Student ID`, `SMS Message`, and optional `Status` columns. Student IDs must be formatted as text in Excel to retain leading zeroes. Only `Ready` rows are eligible when `Status` is included.

The upload and row-level message data are encrypted at rest. Validation and sending require a running Laravel queue worker, for example:

```bash
php artisan queue:work --queue=default --tries=3 --timeout=900
php artisan schedule:work
```

For production malware scanning, install ClamAV, set `RESULTS_SMS_CLAMAV_BINARY` to the `clamscan` executable, and set `RESULTS_SMS_REQUIRE_MALWARE_SCAN=true`. The scheduled `results-sms:purge-expired` command removes protected uploads and logs according to `RESULTS_SMS_RETENTION_DAYS`.

## Useful commands

```bash
# Run the test suite
php artisan test

# Run a focused test
php artisan test tests/Unit/Services/NeedleToolRouterTest.php

# Format PHP code changed in the worktree
vendor/bin/pint --dirty

# Apply pending database migrations in production
php artisan migrate --force

# Inspect registered routes
php artisan route:list
```

## AI Sensei and Needle

AI Sensei continues to use the established MCP integration: application code, permissions, and business rules are the only authority for reading or changing College360 data.

[Needle](https://github.com/cactus-compute/needle) is an optional small local model used before the external assistant for straightforward, high-confidence tool selection and structured argument extraction. The model does **not** receive direct database access and it does **not** execute application actions.

### What stays local

When enabled, Needle can select only these read-only Sensei tools:

- List courses, question sets, exams, and cohorts
- View question-set and exam details
- View or parse student-ID configuration and statistics
- Get a cohort student count

Laravel validates the model output, applies the existing MCP permission checks, and executes the permitted tool. The response is stored in the normal AI Sensei conversation history.

### When the external assistant is used

The request is passed to the existing AI assistant when Needle is unavailable, below the confidence threshold, cannot match a tool, a file is attached, the request needs broader reasoning, or the request could change data. This includes creating exams or question sets, bulk imports, student-ID reassignment, and deletion.

### Install the Needle sidecar

Needle runs as a private Python HTTP service alongside Laravel. It must not be exposed to the public internet: Laravel is the only intended caller.

The following example assumes Ubuntu/Debian and the demo path. Substitute the institution's actual release path for other deployments.

1. Install Python prerequisites and create a durable virtual environment outside the web root.

   ```bash
   sudo apt update
   sudo apt install -y python3 python3-venv
   sudo install -d -o www-data -g www-data /opt/college360-needle
   sudo -u www-data python3 -m venv /opt/college360-needle/venv
   sudo -u www-data /opt/college360-needle/venv/bin/pip install --upgrade pip
   sudo -u www-data /opt/college360-needle/venv/bin/pip install -r /var/www/college.manifestghana.com/current/services/needle/requirements.txt
   ```

2. Download Needle's inference engine once, using the same service user that will run it.

   ```bash
   sudo -u www-data HOME=/opt/college360-needle /opt/college360-needle/venv/bin/needle fetch
   ```

3. Create `/etc/systemd/system/college360-needle.service`.

   ```ini
   [Unit]
   Description=College360 Needle sidecar
   After=network-online.target
   Wants=network-online.target

   [Service]
   Type=simple
   User=www-data
   Group=www-data
   WorkingDirectory=/var/www/college.manifestghana.com/current/services/needle
   Environment=HOME=/opt/college360-needle
   ExecStart=/opt/college360-needle/venv/bin/uvicorn app:app --host 127.0.0.1 --port 8011
   Restart=always
   RestartSec=5
   NoNewPrivileges=true

   [Install]
   WantedBy=multi-user.target
   ```

4. Enable and verify the service.

   ```bash
   sudo systemctl daemon-reload
   sudo systemctl enable --now college360-needle
   sudo systemctl status college360-needle
   curl --fail http://127.0.0.1:8011/health
   ```

   A healthy response is `{"ok":true}`. Troubleshoot with `sudo journalctl -u college360-needle -f`.

5. Enable the Laravel integration in the institution's `.env` only after the health check succeeds.

   ```dotenv
   NEEDLE_ENABLED=true
   NEEDLE_URL=http://127.0.0.1:8011
   NEEDLE_TIMEOUT=5
   NEEDLE_MINIMUM_CONFIDENCE=0.90
   ```

6. Refresh Laravel configuration.

   ```bash
   cd /var/www/college.manifestghana.com/current
   php artisan optimize:clear
   php artisan config:cache
   ```

### Needle operations

- Keep the sidecar bound to `127.0.0.1` unless it is behind a protected internal network and authenticated transport.
- Do not put credentials or database connection strings in the sidecar configuration.
- The first model download is intentional; normal inference is local after the engine is cached.
- The deployment workflows upload the sidecar source but do not create the Python environment or systemd service. Provision each server once, then restart the service after changes to `services/needle` or its requirements:

  ```bash
  sudo -u www-data /opt/college360-needle/venv/bin/pip install -r /var/www/college.manifestghana.com/current/services/needle/requirements.txt
  sudo systemctl restart college360-needle
  ```

The lightweight sidecar source is in [services/needle](services/needle/README.md).

## Deployment

Pushing to `master` starts the institutional deployment workflows independently and in parallel:

| Workflow | Target | GitHub environment |
| --- | --- | --- |
| `deploy-mhtia.yml` | MHTIA | `mhtia-production` |
| `deploy-pnmtc.yml` | PNMTC | `pnmtc-production` |
| `deploy-demo.yml` | `college.manifestghana.com` demo | `demo-production` |

Each environment owns its deployment credentials and application configuration. Use GitHub Environment secrets; do not place server credentials in the repository.

The demo workflow provisions its application database, publishes to `/var/www/college.manifestghana.com/current`, installs production Composer dependencies, builds front-end assets, runs migrations, caches Laravel configuration, and configures the Nginx virtual host. It requires these `demo-production` secrets:

```text
DEPLOY_HOST
DEPLOY_USER
SSH_KEY
DB_CONNECTION
DB_DATABASE
DB_USERNAME
DB_PASSWORD
```

MHTIA and PNMTC use their existing Deployer configuration supplied by their respective GitHub environments. A workflow can also be started manually through GitHub Actions.

### Production checklist

- Confirm the target environment secrets are present and correct.
- Verify `.env` has production-safe `APP_ENV`, `APP_DEBUG=false`, `APP_URL`, database, mail, queue, and institution authentication settings.
- Back up the database before data-affecting migrations.
- Confirm queue workers and scheduled tasks are running where the institution uses them.
- Run `php artisan migrate --force`, cache configuration, and verify a login and a critical student/admin workflow.
- If enabling Needle, complete its health check before setting `NEEDLE_ENABLED=true`.

## Security and operational notes

- Student access is restricted to active students by the `student.active` middleware.
- Sensitive information belongs in GitHub Environment secrets or server `.env` files, never source control or logs.
- Treat AI Sensei write requests as privileged actions. Existing role and MCP permission checks remain mandatory regardless of the AI provider.
- Take database backups before migrations, bulk changes, or institutional data imports.
- Check `storage/logs/laravel.log`, web-server logs, queue-worker logs, GitHub Actions logs, and `journalctl` for production issues.

## License

College360 is proprietary software. All rights reserved unless a separate written license states otherwise.
