# Design Spec: AGENTS.md Guidelines

**Date**: 2026-04-11
**Topic**: AI Agent Guidelines for Local WordPress Project
**Status**: Final (Auto-approved)

## 1. Purpose
To provide a persistent memory and set of operational mandates for any AI agent working in this codebase. This ensures consistency in how the environment is managed and how code is implemented.

## 2. Core Mandates

### 2.1 Environment Management
- **Docker First**: All services MUST be managed via `docker compose`.
- **Environment Variables**: The project is strictly `.env` driven. Agents must verify the existence of required variables before suggesting changes to `compose.yaml`.
- **WP-CLI Service**: Agents MUST use the `cli` service defined in `compose.yaml` for WordPress management task (`docker compose run --rm cli wp ...`).

### 2.2 Project Structure & Volumes
- **Persistence**: Database data lives in `volumes/mysql`, and WordPress files in `volumes/wordpress`.
- **Custom Code**: Any new themes or plugins should be placed in `packages/` if intended for distribution, or directly in `volumes/wordpress/wp-content/` for local development.

### 2.3 Automation & Initialization
- **Init Script**: The `docker/init-wp.sh` script is the source of truth for automated setup. Any changes to default plugins, themes, or admin settings should be reflected there.

## 3. Implementation Plan
- Create `AGENTS.md` in the project root.
- Include a "Project Identity" section.
- Include "Operational Procedures".
- Include "Development Guidelines".
- Include "Memory/Context" for future tasks.
