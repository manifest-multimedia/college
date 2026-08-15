# College360 Needle sidecar

This private sidecar lets AI Sensei use [Needle](https://github.com/cactus-compute/needle) for high-confidence, read-only tool selection and structured argument extraction. Laravel remains responsible for authorization and for executing every tool.

## Install on an application server

Use a dedicated Python virtual environment, outside the web root:

```bash
python3 -m venv /opt/college360-needle/venv
/opt/college360-needle/venv/bin/pip install -r /var/www/college.manifestghana.com/services/needle/requirements.txt
/opt/college360-needle/venv/bin/needle fetch
/opt/college360-needle/venv/bin/uvicorn app:app --app-dir /var/www/college.manifestghana.com/services/needle --host 127.0.0.1 --port 8011
```

Run the final command under a process manager such as systemd. Bind only to `127.0.0.1` (or a protected internal network); this service has no authentication because the Laravel application is its sole caller.

Enable it in the Laravel environment only after the health endpoint responds:

```dotenv
NEEDLE_ENABLED=true
NEEDLE_URL=http://127.0.0.1:8011
NEEDLE_TIMEOUT=5
NEEDLE_MINIMUM_CONFIDENCE=0.90
```

Needle is intentionally limited to read-only Sensei tools. It falls back to the existing assistant for uncertain questions, uploads, unsupported requests, and all actions that change college data.
