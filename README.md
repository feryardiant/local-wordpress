# 📦 WordPress Evaluation Environment

A zero-config, Docker-based local environment designed for rapid evaluation of WordPress themes and plugins. Spin up a fully installed site with your choice of themes and plugins in seconds, bypassing the WordPress setup wizard entirely.

## 🚀 Quick Evaluation Workflow

1.  **Configure**: Create a `.env` file in the root directory (use the template below).
2.  **Start**: Run `docker compose up -d`.
3.  **Evaluate**: Access your site at [http://localhost:8080](http://localhost:8080) (or your configured port).

## 🛠 Environment Setup (optional)

Everything is controlled via environment variables. Copy this template to your `.env` file and adjust as needed:

```bash
# Docker / PHP Versions
PHP_VERSION=8.4
FORWARD_WEB_PORT=8080

# Database Credentials
DB_USER=wordpress
DB_PASS=secret
DB_NAME=wordpress

# Site Configuration (Automatic Installation)
SITE_URL=http://localhost:8080
SITE_TITLE="WP Evaluation Site"
SITE_TIMEZONE="Asia/Jakarta"
SITE_ADMIN_USER=admin
SITE_ADMIN_PASS=password
SITE_ADMIN_EMAIL=admin@example.com

# Auto-Initialization (comma-separated lists)
SITE_PLUGINS=akismet,hello-dolly
SITE_THEMES=twentytwentyfive
SITE_DEFAULT_THEME=twentytwentyfive
```

## 🔌 Evaluating Themes & Plugins

- **Official Market (Repo)**: Add the slugs to `SITE_PLUGINS` or `SITE_THEMES` in your `.env` and restart the containers.
- **Custom / Local**: Place your theme or plugin folder in the `packages/` directory.

## 🛠 Lifecycle Commands

- **Start Services**: `docker compose up -d`
- **Stop Services**: `docker compose down`
- **Reset Environment**: `docker compose down -v` (Removes all data for a fresh start)
- **View Setup Logs**: `docker compose logs -f cli` (Monitor the auto-installation process)

## 📁 Project Structure

- `docker/init-wp.sh`: The "Zero-Config" engine—automatically handles installation, options, and branding.
- `packages/`: Local themes and plugins (currently includes `custom-theme`).
- `volumes/`: Persisted data for WordPress files and MySQL.
- `compose.yaml`: Docker services orchestration.
