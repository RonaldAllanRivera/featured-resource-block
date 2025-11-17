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
- Elementor integration via `FRB_Elementor_Integration` and the `Featured Resource Block` widget (`FRB_Widget_Featured_Resource`) with frontend styles in `assets/css/frontend.css`.
- Resource Sync settings page and options via `FRB_Settings_Page` and `admin/views/settings-page.php`.

### Changed
- Clarified implementation status for Phases 1–4 in `PLAN.md` and `README.md`.
