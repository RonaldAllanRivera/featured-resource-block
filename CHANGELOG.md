# Changelog

All notable changes to this project will be documented in this file.


## [0.1.0] - 2025-11-15

### Added
- Initial project documentation (`PLAN.md`, `README.md`) describing architecture, roadmap, and usage.
- Plugin bootstrap file `featured-resource-block.php` with core constants, autoloader, and main loader hook.
- Core orchestrator class `FRB_Plugin` to centralize hook registration and future subsystem initialization, including activation and deactivation lifecycle entry points.
 - Optional `FRB_DEBUG` flag and basic "plugin loaded" log emitted on `init` when debugging is enabled.
 - `mist_resource` custom post type via `FRB_Post_Type_Resources` with archive, REST API support, and featured image/excerpt support.
 - Resource meta (`mist_resource_url`, `mist_remote_id`) and Resource URL meta box via `FRB_Resource_Meta`.

### Changed
- Clarified implementation status for Phase 1 and Phase 2 in `PLAN.md` and `README.md`.
