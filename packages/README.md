# 📦 Packages

This directory is the dedicated location for custom WordPress themes and plugins.

## 🛠 Contribution Guidelines

To ensure the local development environment remains organized and functional, all additions to this directory MUST follow these rules:

1.  **Flat Directory Structure**: Every theme or plugin must be placed directly under the `packages/` directory.
    - ✅ `packages/my-custom-theme/`
    - ❌ `packages/themes/my-custom-theme/`
2.  **WordPress Conventions**: Follow standard WordPress metadata requirements for themes (`style.css` header) and plugins (primary PHP file header).
3.  **Manual Mounts**: Any new package must be manually added as a volume mount in `compose.yaml` to be visible within the WordPress container.

## 🤖 Automation Features

- **Favicon Synchronization**: During initialization, the `cli` service automatically copies the `favicon.ico` from the `public/` directory to the WordPress site root. This ensures your custom branding is applied consistently across the local environment.

## 📁 Current Packages

### Themes
- **custom-theme**: A starter theme for local development. Includes automated PHP Mailer configuration to route WordPress emails to the project's Mailpit service.
