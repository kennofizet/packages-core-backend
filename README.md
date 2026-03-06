# packages-core/backend

This package is the **core base package** for the Core ecosystem. It provides the foundational backend logic and data models that other packages depend on.

## Purpose

This package handles the base settings and management for:

- **Zone** — Core zone model and configuration
- **Zone Management** — Zone CRUD operations, assignment, and lifecycle management
- **Server** — Server registration, settings, and connection management
- **Token** — Token definitions, issuance rules, and base token logic

Other packages (e.g. `********-backend`) extend or consume the entities provided here. This package should be installed first as a dependency before any feature-level packages.

## Installation

```bash
composer require kennofizet/packages-core-backend
```
