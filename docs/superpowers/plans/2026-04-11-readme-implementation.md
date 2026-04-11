# Local WordPress README Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Create a comprehensive `README.md` for personal reference on starting and managing the local WordPress environment.

**Architecture:** A structured Markdown file following Approach B from the design spec, emphasizing environment variable configuration and WP-CLI usage.

**Tech Stack:** Markdown, Docker Compose, WP-CLI.

---

### Task 1: Create README.md with Basic Info and Environment Template

**Files:**
- Create: `README.md`

- [ ] **Step 1: Write initial README structure**

```markdown
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
\```
```

- [ ] **Step 2: Commit initial structure**

```bash
git add README.md
git commit -m "docs: initialize README with basic info and .env template"
```

---

### Task 2: Add Usage and Access Information

**Files:**
- Modify: `README.md`

- [ ] **Step 1: Add Lifecycle and Access sections**

```markdown
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
```

- [ ] **Step 2: Commit usage and access info**

```bash
git add README.md
git commit -m "docs: add usage and access information to README"
```

---

### Task 3: Add Management (WP-CLI) and Project Structure

**Files:**
- Modify: `README.md`

- [ ] **Step 1: Add Advanced Management and Structure sections**

```markdown
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
```

- [ ] **Step 2: Commit final README sections**

```bash
git add README.md
git commit -m "docs: add WP-CLI management and project structure to README"
```
