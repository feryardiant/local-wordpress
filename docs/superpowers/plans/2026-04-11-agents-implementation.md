# Implementation Plan: AGENTS.md Creation

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Create a persistent context file (`AGENTS.md`) in the project root to guide future AI agent interactions.

**Architecture:** A Markdown file containing operational mandates, project-specific context, and guidelines for WordPress development in this specific Docker environment.

**Tech Stack:** Markdown.

---

### Task 1: Create AGENTS.md with Foundational Guidelines

**Files:**
- Create: `AGENTS.md`

- [ ] **Step 1: Write initial AGENTS.md content**

```markdown
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
```

- [ ] **Step 2: Commit AGENTS.md**

```bash
git add AGENTS.md
git commit -m "docs: add AGENTS.md for persistent AI context"
```
