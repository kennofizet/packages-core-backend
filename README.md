# packages-core/backend

This package is the **core base package** for the Core ecosystem. It provides the foundational backend logic and data models that other packages depend on.

## Purpose

This package handles the base settings and management for:

- **Zone** — Core zone model and configuration
- **Zone Management** — Zone CRUD operations, assignment, and lifecycle management
- **Server** — Server registration, settings, and connection management
- **Token** — Token definitions, issuance rules, and base token logic
- **Season** — Shared season master data scoped by zone (used by feature packages like workpoint)

Other packages (e.g. `********-backend`) extend or consume the entities provided here. This package should be installed first as a dependency before any feature-level packages.

## Installation

```bash
composer require kennofizet/packages-core-backend
```

## Season Context

- Middleware resolves current season from the active season of current zone.
- The resolved value is exposed as request attribute `knf_core_user_season_id_current`.
- `BaseModelActions::currentUserSeasonId()` provides the value in services/controllers.
- `BaseModel` now auto-applies season global scope for models that have `season_id`.

## Season Hooks

- Event class: `packages-core.season_event_class` (default `SeasonCreated`)
- Post-create listeners: `packages-core.after_season_created_listeners`
- Listener contract: `Kennofizet\PackagesCore\Contracts\AfterSeasonCreatedListener`
