# Design Spec: Local WordPress README

**Date**: 2026-04-11
**Topic**: Project README for personal reference
**Status**: Draft

## 1. Purpose & Goals
The goal is to create a `README.md` file for the current WordPress Docker project. It is intended for personal reference, focusing on:
- Quick start/stop commands.
- Environment variable configuration (critical for the `compose.yaml`).
- Site management via WP-CLI.
- Connection details (URLs, credentials).

## 2. Target Audience
- Primarily the project owner (personal reference).

## 3. Architecture & Structure
The README will be structured into the following sections:
1. **Title & Description**: Brief overview of the tech stack (WP 6.9, PHP 8.4, MySQL 8.0).
2. **Environment Setup**: Template for the `.env` file with descriptions of key variables.
3. **Usage (Common Commands)**: Docker Compose commands for lifecycle management.
4. **Access Information**: Admin URL, default credentials, and local site URL.
5. **Advanced Management**: Examples of using the `cli` service (WP-CLI).
6. **Project Structure**: Brief map of the directory (volumes, docker scripts).

## 4. Content Details

### 4.1 Environment Template
A comprehensive template of the `.env` file required to run the `compose.yaml`. This ensures that all necessary variables (DB credentials, site settings, versions) are documented.

### 4.2 Lifecycle Commands
- `docker compose up -d`
- `docker compose down`
- `docker compose logs -f`
- `docker compose ps`

### 4.3 WP-CLI Usage
Instruction on how to execute WP-CLI commands via the dedicated `cli` service container:
`docker compose run --rm cli wp <command>`

## 5. Success Criteria
- The README contains all information necessary to start the environment from scratch.
- The `.env` template correctly reflects the variables used in `compose.yaml`.
- The instructions for WP-CLI are accurate for the project's Docker setup.

## 6. Constraints
- Must align with the existing `compose.yaml` and `init-wp.sh` script.
- Should remain concise for quick reading.
