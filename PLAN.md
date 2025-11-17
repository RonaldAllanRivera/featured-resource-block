# Featured Resource Block – Technical Plan

## 1. Goals & Constraints

- **Goal**  
  Build a standalone WordPress plugin that adds a `Resources` custom post type, an Elementor widget called `Featured Resource Block`, and a scheduled mock API sync – with senior-level code structure, error handling, and documentation.

- **Constraints**  
  - **Dependencies**: WordPress core + Elementor only.  
  - **Tech**: Pure PHP for backend, vanilla JS only if needed on frontend.  
  - **Plugin scope**: Self-contained plugin, no ACF or extra libraries.  
  - **Quality**: Follow WordPress coding standards, security best practices, and clean architecture.

---

## 2. Features Overview

- **Custom Post Type: Resources**
  - **Post type key**: `mist_resource` (UI label: “Resources”, slug: `/resources/`).
  - **Supports**: `title`, `excerpt`, `thumbnail`.
  - **Meta**: `mist_resource_url` (Resource URL), `mist_remote_id` (ID from API).

- **Elementor Widget: Featured Resource Block**
  - **Widget name**: `Featured Resource Block`.
  - **Controls**:
    - Selected Resource (dropdown from CPT).
    - Layout Style (Card / Minimal).
    - Button Text.
    - Gradient Background (on/off).
    - Image Size (Elementor image-size control).
  - **Output**: Semantic, responsive HTML with BEM-like classes and minimal vanilla JS (if any).

- **Settings: Resource Sync**
  - **Menu**: `Settings → Resource Sync`.
  - **Options**:
    - API Key (text).
    - API Endpoint (text; defaults to the assignment mock URL, can point to a local mock endpoint).
    - Enable Sync (boolean).

- **Mock API Sync**
  - **Endpoint**: `https://mocki.io/v1/0c7b33d3-2996-4d7f-a009-4ef34a27c7e9`.
  - **Schedule**: WP-Cron every 15 minutes.
  - **Behavior**:
    - Fetches remote resources.
    - Creates or updates `Resources` posts.
    - Caches response for 5 minutes via transients.
    - Handles failures silently for end users, logs for developers.

---

## 3. Architecture & Folder Structure

- **Main plugin file**
  - `featured-resource-block.php`
  - **Responsibilities**:
    - Plugin header & text domain.
    - Safety checks (e.g. bail if accessed directly).
    - Elementor presence check (degrade gracefully if Elementor inactive).
    - Register simple autoloader.
    - Bootstrap service classes (CPT, settings, sync, widget registration).

- **Suggested structure**

- **Root**
  - `featured-resource-block.php` – main bootstrap.
  - `readme.txt` (WordPress.org format, optional).
  - `PLAN.md`, `README.md`.

- **/includes**
  - `class-plugin.php` – central orchestrator; registers hooks, initializes subsystems.
  - `class-post-type-resources.php` – CPT `mist_resource` registration.
  - `class-resource-meta.php` – Resource URL meta box + saving.
  - `class-settings-page.php` – Settings → Resource Sync page, option registration.
  - `class-sync-service.php` – API client, mapping, create/update logic.
  - `class-cron-manager.php` – schedules/unschedules cron, cron callback.
  - `class-elementor-integration.php` – Elementor checks & widget registration.
  - `class-widget-featured-resource.php` – Elementor widget implementation.
  - `class-logger.php` (simple) – wrapper for `error_log` + optional admin debug flag.

- **/admin**
  - `views/settings-page.php` – markup for settings form.
  - `css/admin.css` – minimal admin styling if needed.

- **/assets**
  - `css/frontend.css` – card/minimal layouts and responsive rules.
  - `js/frontend.js` – optional vanilla JS enhancements (e.g. basic click tracking).

- **/languages**
  - `.pot` file for localization (nice to have).

---

## 4. Data Model: Resources CPT & Meta

- **Post type**: `mist_resource`
  - **Labels**: “Resources”, “Resource”.
  - **Supports**: `title`, `editor` (for future), `excerpt`, `thumbnail`.
  - **Visibility**: Public, has archive, REST enabled.
  - **Rewrite**: Slug `resources`.

- **Meta fields**
  - **`mist_resource_url`**
    - Stored via `add_meta_box` and `update_post_meta`.
    - Sanitized with `esc_url_raw`.
    - Displayed in widgets with `esc_url`.
  - **`mist_remote_id`**
    - Maps each post to remote API item.
    - Used to decide create vs update on sync.

- **Best practices**
  - Register meta via `register_post_meta` for type safety (`type => 'string'`, `show_in_rest => true`).
  - Capability checks on saving meta (`current_user_can( 'edit_post', $post_id )`).
  - Nonce protection in meta box form.

---

## 5. Elementor Widget Design

- **Registration**
  - Hook into Elementor’s widget registration (`elementor/widgets/register` or compatible hook).
  - Only register widget if Elementor active and version compatible.

- **Controls**
  - **Resource select**: `SELECT` control populated via `get_posts` on `mist_resource`.
  - **Layout**: `SELECT` (`card`, `minimal`).
  - **Button text**: `TEXT` control with default (e.g. “View resource”).
  - **Gradient background**: `SWITCHER` control -> adds class `frb-has-gradient`.
  - **Image size**: Elementor’s image size group control; uses featured image.

- **Rendering**
  - Fetch selected `mist_resource`.
  - Prepare vars:
    - Title, excerpt, resource URL.
    - Featured image (with chosen image size).
  - Sanitization:
    - `esc_html` for text.
    - `esc_url` for links.
    - `wp_kses_post` for excerpt if needed.
  - Layout:
    - Card: image, title, excerpt, button, background, padding.
    - Minimal: compact layout, maybe text + inline link.
  - Responsiveness:
    - CSS grid/flexbox.
    - Mobile-first with breakpoint adjustments.

---

## 6. Settings Page & Options

- **Menu**
  - `add_options_page` under `Settings → Resource Sync`.

- **Settings API**
  - Option group: `frb_resource_sync`.
  - Fields:
    - `api_key` – text input; sanitized via `sanitize_text_field`.
    - `enable_sync` – checkbox; sanitized to `0/1`.

- **UX**
  - Display current sync status and last run time.
  - If last sync error exists (stored as `frb_last_sync_error`), show admin-only notice.
  - Nonce protection on form submit.

---

## 7. Mock API Sync & WP-Cron

- **Scheduling**
  - When `enable_sync` is turned ON:
    - If event not scheduled, register `frb_resource_sync_cron` every 15 minutes.
  - When turned OFF:
    - Unschedule all `frb_resource_sync_cron` events.

- **Cron callback flow**
  1. Check if sync enabled; bail if not.
  2. Attempt to read transient `frb_resource_sync_cache`.
  3. If transient empty:
     - Call `wp_remote_get` on API with appropriate headers/args (include API key).
     - Handle `WP_Error` and non-200 responses.
     - Decode JSON (`json_decode` with checks).
     - Cache decoded data for 5 minutes via `set_transient`.
  4. Iterate over remote items:
     - Find local post by `mist_remote_id` meta (or fallback slug).
     - Build post array and insert/update with `wp_insert_post`/`wp_update_post`.
     - Update `mist_resource_url` and `mist_remote_id` meta.
     - (Optional) If image URL provided, media sideload and set featured image.
  5. Store last sync time & error summary as options.

- **Error handling**
  - No notices on frontend.
  - Log errors via `error_log` (only if `WP_DEBUG` or plugin debug flag).
  - Admin settings page shows human-friendly status without exposing raw errors.

---

## 8. Security, Performance & Coding Standards

- **Security**
  - Escape everything on output (`esc_html`, `esc_attr`, `esc_url`).
  - Sanitize everything on input (settings & meta).
  - Capability checks for:
    - Accessing settings page (`manage_options`).
    - Saving post meta (`edit_post`).
  - Nonces on all admin forms.
  - No direct file access (use `ABSPATH` guard).

- **Performance**
  - Use transients for API results (5 minutes).
  - Avoid heavy queries in Elementor controls:
    - Limit posts, order by date, maybe lazy-load if many resources.
  - Only load front-end CSS/JS when widget present (e.g. `has_block`-like detection via Elementor hooks / render callbacks).

- **Coding standards**
  - Use namespaced classes or class prefixes (`FRB_`).
  - Follow WordPress PHP coding standards (spacing, naming, escaping).
  - Short, focused methods with docblocks.

---

## 9. Accessibility & Front-End UX

- **Accessibility**
  - Use semantic HTML (`article`, `h2`, `p`, `a`).
  - Ensure button/link has clear label (e.g. `View resource: {Title}` with `aria-label`).
  - Sufficient color contrast, especially for gradients.

- **UX**
  - Card hover states and focus styles.
  - Graceful fallback if resource missing or incomplete (no PHP notices).

---

## 10. Testing & QA Plan

- **Manual testing**
  - CPT creation/editing; meta box save.
  - Elementor widget:
    - Different layouts, button texts, gradient toggle, image sizes.
  - Settings page:
    - Toggling sync, saving API key.
  - Cron:
    - Manually trigger `frb_resource_sync_cron` via `wp cron event run` or temporary button.

- **Edge cases**
  - API down / invalid JSON / empty response.
  - Duplicate resources.
  - Removing items from API: decide whether to leave local copies (document behavior).

---

## 11. Video Walkthrough Plan (for later script)

Approx. 3–5 minutes:

1. **Intro (20–30s)**
   - Who you are and goal of the plugin.

2. **Folder Structure (40–60s)**
   - Walk through main file and `/includes`, `/assets`, `/admin`.
   - Emphasize separation of concerns.

3. **Bootstrap & Architecture (40–60s)**
   - Show main plugin class, autoloader, hook registration.
   - Explain why this structure scales for larger plugins.

4. **Resources CPT & Meta (40–60s)**
   - Show CPT registration & `Resource URL` meta handling.
   - Mention sanitization, capabilities, and REST friendliness.

5. **Elementor Widget (40–60s)**
   - Show widget class, controls, and `render()` method.
   - Explain how data flows from CPT → Elementor controls → front-end HTML/CSS.

6. **Settings, Sync & Cron (60–90s)**
   - Show settings page code, options registration.
   - Walk through sync service, transient caching, cron scheduling, and error handling.

7. **Wrap-up (20–30s)**
   - Mention key engineering decisions, trade-offs, and what you’d improve with more time.

Later, I’ll help you turn this outline into a full spoken script.

---

## 12. Implementation Phases

### Phase 0 – Environment & Repository Setup (Completed)
- **Objectives**
  - Have a clean, reproducible local environment and a well-structured Git repository.
  - Capture initial decisions so reviewers can see your thought process.

- **Key tasks**
  - Set up local WordPress with a minimal theme and only two plugins: Elementor and this plugin.
  - Initialize Git repository, add `.gitignore` (vendor, node_modules, logs, IDE files).
  - Commit `PLAN.md` and `README.md` as the first commit (`chore: add project docs`).
  - Create a lightweight branching strategy (e.g. `main` + feature branches per phase).

- **Deliverables**
  - Clean repo with only relevant files tracked.
  - Documented environment assumptions in `README.md` (already covered at a high level).

### Phase 1 – Plugin Skeleton & Bootstrap (Completed)

- **Status**
  - Completed in version `0.1.0` (bootstrap file, `FRB_Plugin` core class, activation/deactivation hooks).

- **Objectives**
  - Create a minimal but production-ready plugin skeleton that cleanly boots all components.

- **Key tasks**
  - Create `featured-resource-block.php` with:
    - Plugin header, `ABSPATH` guard, text domain, and basic constants (e.g. version, paths).
    - Simple PSR-4–style autoloader or `spl_autoload_register` with `includes` namespace/prefix.
  - Implement `FRB_Plugin` (or similar) in `class-plugin.php` to:
    - Register activation/deactivation hooks (for cron scheduling cleanup only).
    - Register core hooks for init, admin, Elementor integration, and sync.
  - Wire up bootstrap so only one global entry point is exposed (no scattered globals).

- **Deliverables**
  - Plugin that activates without errors and logs a basic “loaded” message (if debug flag enabled).
  - First implementation commit focused only on structure (`feat: add plugin bootstrap skeleton`).

### Phase 2 – Data Model: CPT & Meta (Completed)

- **Status**
  - Completed in version `0.1.0` (CPT `mist_resource`, REST-registered meta, Resource URL meta box).

- **Objectives**
  - Implement the `mist_resource` CPT and meta in a way that is robust, REST-ready, and secure.

- **Key tasks**
  - Implement `FRB_Post_Type_Resources` in `class-post-type-resources.php`:
    - Register CPT with proper labels, rewrite rules, supports, and REST settings.
  - Implement `FRB_Resource_Meta` in `class-resource-meta.php`:
    - Register `mist_resource_url` and `mist_remote_id` via `register_post_meta`.
    - Add meta box for `Resource URL` with nonce and capability checks.
    - Implement `save_post` handler to sanitize and persist meta.
  - Add basic unit of manual testing: create a few resources and confirm meta persistence.

- **Deliverables**
  - `Resources` menu visible in admin with working add/edit UI.
  - Meta box present and correctly saving/validating `Resource URL`.
  - Commit (`feat: add Resources CPT and meta`) with concise explanation.

### Phase 3 – Elementor Integration & Widget (Completed)

- **Status**
  - Completed in version `0.1.0` (Elementor integration, Featured Resource Block widget, and frontend CSS).

- **Objectives**
  - Provide a well-structured Elementor widget that demonstrates familiarity with Elementor APIs and WordPress data.

- **Key tasks**
  - Implement `FRB_Elementor_Integration` in `class-elementor-integration.php`:
    - Check Elementor activation and version.
    - Register the widget class on the correct Elementor hook.
  - Implement `FRB_Widget_Featured_Resource` in `class-widget-featured-resource.php`:
    - Define widget metadata (name, title, icon, categories).
    - Register controls for resource selection, layout, button text, gradient toggle, image size.
    - Implement `render()` with clean, escaped HTML and minimal logic.
  - Create `assets/css/frontend.css` to support `card` and `minimal` layouts with BEM-like classes.
  - Manually test rendering with multiple resources and control combinations in Elementor.

- **Deliverables**
  - Working `Featured Resource Block` widget visible in Elementor and rendering real `Resource` data.
  - CSS that is responsive and scoped to the widget.
  - Commit (`feat: add Elementor Featured Resource Block widget`).

### Phase 4 – Settings Page & Options (Completed)

- **Status**
  - Completed in version `0.1.0` (Resource Sync settings page, options, sync status, and debug stored options).

- **Objectives**
  - Expose sync configuration in a clean, WordPress-native settings page under `Settings → Resource Sync`.

- **Key tasks**
  - Implement `FRB_Settings_Page` in `class-settings-page.php`:
    - Register settings, sections, and fields (`api_key`, `api_endpoint`, `enable_sync`).
    - Render a settings form view from `admin/views/settings-page.php`.
    - Add sanitization callbacks and capability checks.
  - Display helpful contextual text:
    - Explain what the sync does, approximate interval, and that this is a mock API.
  - Surface last sync time and last error (if any).

- **Deliverables**
  - Fully functional settings page with clear labels and descriptions.
  - Commit (`feat: add Resource Sync settings page`).

### Phase 5 – Sync Service, Transients & WP-Cron

- **Status**
  - Completed in version `0.1.0` (FRB_Logger, FRB_Cron_Manager, FRB_Sync_Service, configurable API endpoint, local FRB_Mock_Api REST endpoint, cron wiring, and manual dev sync docs).

- **Objectives**
  - Implement a maintainable sync pipeline that demonstrates solid API integration, caching, and cron scheduling.

- **Key tasks**
  - Implement `FRB_Cron_Manager` in `class-cron-manager.php`:
    - Schedule/unschedule `frb_resource_sync_cron` on option changes and activation/deactivation.
  - Implement `FRB_Sync_Service` in `class-sync-service.php`:
    - Read configuration (API key, API endpoint, enable flag) from options.
    - Handle HTTP requests via `wp_remote_get` with robust error handling.
    - Use transients to cache successful responses for 5 minutes.
    - Map API data to `mist_resource` posts (create or update based on `mist_remote_id`).
  - Implement a small logger (`FRB_Logger`) to centralize error logging and optional debug output.
  - Implement a local mock API class (`FRB_Mock_Api`) in `class-mock-api.php` to expose a REST endpoint for demo resources.
  - Provide a way to manually trigger sync in development (e.g. temporary admin action or WP-CLI guidance in `README.md`).

- **Deliverables**
  - Reliable cron-based sync that can recover from transient errors without breaking the site.
  - Commit (`feat: add API sync service and cron scheduling`).

### Phase 6 – Front-End Polish, Accessibility & Performance

- **Objectives**
  - Ensure the widget output is not just functional but production quality in UX, accessibility, and performance.

- **Key tasks**
  - Refine `frontend.css` for:
    - Mobile-first responsive grid/flex layouts.
    - Clear hover/focus states and button styling.
    - Optional gradient background class with sufficient contrast.
  - Add accessibility improvements:
    - Semantic HTML structure (`article`, headings, lists when appropriate).
    - Clear `aria-label` / `title` attributes on links when needed.
  - Verify assets only load when necessary:
    - Enqueue CSS/JS conditionally based on Elementor widget usage (using Elementor hooks or render detection).

- **Deliverables**
  - Polished front-end experience across viewport sizes.
  - Commit (`chore: refine frontend styles and accessibility`).

### Phase 7 – Hardening, QA & Final Documentation

- **Objectives**
  - Validate behavior under edge cases and present the plugin as production-ready.

- **Key tasks**
  - Run through the Testing & QA plan (Section 10) methodically and fix issues found.
  - Double-check security practices (escaping, sanitization, nonces, capabilities).
  - Review code organization and naming against WordPress standards.
  - Finalize `README.md` with any updates discovered during testing (limitations, improvements).
  - Prepare a short summary of approach for the submission email and repo `README` top section.

- **Deliverables**
  - Stable plugin with known behaviors documented.
  - Final commit (`chore: QA fixes and documentation updates`).

### Phase 8 – Video Walkthrough Preparation

- **Objectives**
  - Translate the existing video outline into a concise, confident walkthrough.

- **Key tasks**
  - Use Section 11 as the backbone for a script (later we will refine wording together).
  - Prepare a short demo flow:
    - Open repo → show structure → show key classes (plugin, CPT, widget, sync) → show settings → show a resource displayed via Elementor.
  - Rehearse explanations focusing on engineering decisions and trade-offs.

- **Deliverables**
  - Clear mental (or written) script ready for a 3–5 minute recording.
  - Final tag or release in Git marking the version demonstrated in the video.
