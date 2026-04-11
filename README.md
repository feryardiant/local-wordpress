# Local WordPress Development

A Docker-based local WordPress environment using Apache, MySQL 8.0, and WP-CLI for automated initialization.

## 🛠 Environment Setup

This project relies on environment variables defined in a `.env` file. Copy the template below to get started:

### `.env` Template

```bash
# Docker / PHP Versions
PHP_VERSION=8.4
FORWARD_WEB_PORT=8080

# Database Credentials
DB_USER=wordpress
DB_PASS=secret
DB_NAME=wordpress

# Site Configuration
SITE_URL=http://localhost:8080
SITE_TITLE="WordPress Local"
SITE_ADMIN_USER=admin
SITE_ADMIN_PASS=password
SITE_ADMIN_EMAIL=admin@example.com

# Initialization (comma-separated lists)
SITE_PLUGINS=akismet,hello-dolly
SITE_THEMES=twentytwentyfive
SITE_DEFAULT_THEME=twentytwentyfive
```

## 🚀 Usage

### Lifecycle Commands

- **Start Services**: `docker compose up -d`
- **Stop Services**: `docker compose down`
- **View Logs**: `docker compose logs -f`
- **Check Status**: `docker compose ps`

### Access Information

- **Local Site**: [http://localhost:8080](http://localhost:8080) (Adjust port if `FORWARD_WEB_PORT` is changed)
- **Admin Dashboard**: [http://localhost:8080/wp-admin](http://localhost:8080/wp-admin)
- **Default Credentials**: Defined in your `.env` file (e.g., `admin` / `password`).

## 🛠 Management & WP-CLI

This project includes a dedicated `cli` service to run commands without manually installing WP-CLI on your host machine.

- **Run WP-CLI command**: `docker compose run --rm cli wp <command>`
- **Example (list users)**: `docker compose run --rm cli wp user list`
- **Example (update plugins)**: `docker compose run --rm cli wp plugin update --all`

## 📁 Project Structure

- `compose.yaml`: Docker services orchestration.
- `docker/init-wp.sh`: Script used by the `cli` service to install and configure WordPress.
- `volumes/wordpress`: Persisted WordPress site files.
- `volumes/mysql`: Persisted database data.
- `packages/`: (Empty) Place for custom plugins or themes.
