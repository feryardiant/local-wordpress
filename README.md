# 📦 WordPress Evaluation Environment

A zero-config, Docker-based local environment designed for rapid evaluation of WordPress themes and plugins. Spin up a fully installed site with your choice of themes and plugins in seconds, bypassing the WordPress setup wizard entirely.

## 🚀 Quick Evaluation Workflow

1.  **Configure**: Copy `.env.example` to `.env` in the root directory (or use the template below).
2.  **Start**: Run `docker compose up -d`.
3.  **Evaluate**: Access your site at [http://localhost:8080](http://localhost:8080) (or your configured port).

## 🛠 Environment Setup (optional)

Everything is controlled via environment variables. Copy this template to your `.env` file and adjust as needed:

```bash
# Docker / PHP Versions
PHP_VERSION=8.4
FORWARD_WEB_PORT=8080
FORWARD_MAILPIT_PORT=8025

# Database Credentials
DB_USER=wordpress
DB_PASS=secret
DB_NAME=wordpress

# Site Configuration (Automatic Installation)
SITE_URL=http://localhost:8080
SITE_TITLE="WP Evaluation Site"
SITE_TIMEZONE="Asia/Jakarta"
SITE_DEBUG=0 # Set to 1 to enable WordPress debug mode
SITE_ADMIN_USER=admin
SITE_ADMIN_PASS=password
SITE_ADMIN_EMAIL=admin@example.com

# Auto-Initialization (comma-separated lists)
SITE_PLUGINS=akismet,hello-dolly
SITE_THEMES=twentytwentyfive
SITE_DEFAULT_THEME=twentytwentyfive

# Network / Multisite
MULTISITE_ENABLED=0 # Set to 1 for automated multisite conversion

# WooCommerce (Automatic Configuration)
WC_STORE_ADDRESS="Jl. Example No. 123"
WC_STORE_CITY="Batang"
WC_DEFAULT_COUNTRY="ID:JT"
WC_CURRENCY="IDR"
WC_STORE_POSTCODE="12345"
WC_WEIGHT_UNIT="kg"
WC_DIMENSION_UNIT="cm"
WC_PRICE_THOUSAND_SEP="."
WC_PRICE_DECIMAL_SEP=","
WC_PRICE_DECIMAL_NUM=0
```

## 🌐 Multisite Support

This environment supports automated conversion to a **WordPress Multisite Network** (subfolder type).

- **Enable**: Set `MULTISITE_ENABLED=1` in your `.env` file.
- **How it works**: The `cli` service will automatically convert the site and apply the necessary `.htaccess.multisite` configuration from the `public/` directory.

## 🛍 WooCommerce Integration

If `woocommerce` is present in `SITE_PLUGINS`, the environment automatically:
- Configures store location and currency settings based on `WC_*` environment variables.
- Sets units for weight and dimensions.
- Configures price formatting (separators and decimals).
- Skips the onboarding profile and marks the setup task list as complete for a "Ready-to-Evaluate" experience.

## 📧 Email Testing (Mailpit)

All outgoing emails are automatically captured for testing using **Mailpit**.

- **Dashboard**: [http://localhost:8025](http://localhost:8025) (or your configured `FORWARD_MAILPIT_PORT`).
- **How it works**: The `custom-theme` theme contains an automated PHP Mailer configuration (`phpmailer_init` hook) that routes all emails to the internal `mail` service container on port 1025.

## 🔌 Evaluating Themes & Plugins

- **Official Market (Repo)**: Add the slugs to `SITE_PLUGINS` or `SITE_THEMES` in your `.env` and restart the containers.
- **Custom / Local**: Place your theme or plugin folder in the `packages/` directory (e.g., `packages/cf7-entry-manager`).

## 🛠 Development Tools

This project uses modern tools for maintenance and consistency:

- **Linting & Formatting**:
    - **Biome**: Used for `JS`, `TS`, `JSON`, and `CSS` files. Run `bun lint` or `bun lint:fix`.
    - **PHPCS / PHPCBF**: Enforces WordPress Coding Standards. Run `composer lint` or `composer lint:fix`.
- **Dependency Management**:
    - **Bun**: Manages root development tools and package-specific JS dependencies via workspaces.
    - **Composer**: Manages PHP dependencies, utilizing `wikimedia/composer-merge-plugin` to discover and merge `composer.json` files from the `packages/` directory.
- **Internationalization (i18n)**:
    - **POT Generation**: Use `scripts/make-pot.sh` to automatically generate `.pot` files for all local packages using `wp-cli i18n`.
- **Distribution**:
    - **Archive Generation**: Use `scripts/make-dist.sh` to create distribution-ready ZIP archives for themes and plugins, automatically excluding development files based on `.distignore`.

## 📦 Monorepo Structure

The project is organized as a monorepo to simplify development of multiple WordPress assets:

- **Workspaces**: Configured in `package.json` to allow centralized management of node modules.
- **Shared Dependencies**: Common development tools (linters, pre-commit hooks) are shared across all packages to ensure a unified standard.

## ⚖️ Licensing

This project uses a hybrid licensing model:

- **Environment & Tools**: [MIT License](LICENSE#development-environment-mit) (Found in root directory).
- **WordPress Packages**: [GPLv3 or later](LICENSE#wordpress-packages-gpl-30-or-later) (Found in `packages/` directory).

This split allows the evaluation environment to be used freely for various projects while ensuring the distributable plugins and themes are compliant with the WordPress ecosystem and protected by modern licensing standards.

## 🛠 Lifecycle Commands

- **Start Services**: `docker compose up -d`
- **Stop Services**: `docker compose down`
- **Reset Environment**: `docker compose down -v` (Removes all data for a fresh start)
- **View Setup Logs**: `docker compose logs -f cli` (Monitor the auto-installation process)

## 📁 Project Structure

- `docker/init-wp.sh`: The "Zero-Config" engine—automatically handles installation, options, and branding.
- `packages/`: Local themes and plugins (includes `custom-theme` and `cf7-entry-manager`).
- `public/`: Static assets (favicon) and server configurations (.htaccess).
- `scripts/`: Development scripts (e.g., `make-pot.sh`).
- `volumes/`: Persisted data for WordPress files, MySQL, and Mailpit.
- `compose.yaml`: Docker services orchestration.
