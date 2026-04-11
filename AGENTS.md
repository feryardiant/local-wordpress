# Agent Guidelines & Project Context

This file serves as a persistent context for AI agents working in this project. All agents MUST read and adhere to these guidelines to ensure consistency and prevent environment breakage.

## 🏗 Project Identity

A Dockerized WordPress local development environment using Apache, MySQL 8.0, and automated initialization via WP-CLI.

## 🛠 Operational Mandates

1.  **Environment Variables**: The project is strictly dependent on a `.env` file. NEVER hardcode values in `compose.yaml`. Verify required variables (see `README.md`) before proposing changes.
2.  **Service Lifecycle**: Always use `docker compose` for starting/stopping services.
3.  **WP-CLI Management**: Use the dedicated `cli` service for all WordPress commands.
    *   Command pattern: `docker compose run --rm cli wp <command>`
4.  **Volumes & Persistence**: Database data is in `volumes/mysql`, and site files are in `volumes/wordpress`. Modification of site files MUST be done with awareness of file permissions (the environment uses user `33` / `www-data`).

## 📁 Development Guidelines

1.  **Themes/Plugins**: Prefer creating custom themes/plugins in `packages/` and symlinking them or mounting them into `volumes/wordpress/wp-content/` if intended for portability.
2.  **Initialization**: Changes to site titles, admin users, or pre-installed plugins should be implemented in `docker/init-wp.sh`.
3.  **Security**: NEVER commit the `.env` file or any other secrets to version control.

## 📝 Persistent Memory (Context)

- **Date**: 2026-04-11
- **Status**: The local environment is set up with automated installation and a comprehensive `README.md`.
- **Next Steps**: (Add future project goals here as needed).
