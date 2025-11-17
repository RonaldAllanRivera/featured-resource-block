# Featured Resource Block

A standalone WordPress plugin that adds a `Resources` custom post type and an Elementor widget called `Featured Resource Block`. It also supports scheduled syncing from a mock API, with caching and robust error handling.

---

## Requirements

- **WordPress**: 6.0+
- **PHP**: 7.4+ (tested with PHP 8.x)
- **Elementor**: 3.x+

No other plugins are required.

---

## Version & Development Status

- **Current version**: `0.1.0`
- **Implemented**:
  - Core plugin bootstrap file and autoloader.
  - `FRB_Plugin` orchestrator with activation/deactivation hooks.
  - Optional `FRB_DEBUG` flag that logs a basic "plugin loaded" message to the PHP error log when enabled.
  - Project documentation (`PLAN.md`, `README.md`, `CHANGELOG.md`).
- **Planned (next phases)**:
  - `Resources` custom post type and meta.
  - Elementor `Featured Resource Block` widget.
  - Resource Sync settings page and mock API sync service.

---

## Installation

1. **Download or clone** this repository into  
   `wp-content/plugins/featured-resource-block`.
2. In the WordPress admin, go to  
   **Plugins → Installed Plugins**.
3. Activate **Featured Resource Block**.
4. Ensure **Elementor** is installed and activated.

---

## Usage

### 1. Create Resources

1. In the admin menu, go to **Resources → Add New**.
2. Fill in:
   - **Title** – name of the resource.
   - **Excerpt** – short description.
   - **Featured Image** – thumbnail used in the widget.
   - **Resource URL** – custom field in the meta box (link to the resource).
3. Publish the resource.

### 2. Add the Elementor Widget

1. Edit a page with **Elementor**.
2. In the widget panel, search for **“Featured Resource Block”**.
3. Drag the widget into your layout.
4. Configure the widget controls:

- **Selected Resource**  
  Pick which `Resource` post to display.

- **Layout Style**  
  - **Card** – full card with image, title, excerpt, and button.  
  - **Minimal** – compact, text-focused layout.

- **Button Text**  
  Custom label for the call-to-action button (e.g. “View resource”).

- **Gradient Background**  
  Toggle to enable/disable a gradient background behind the card.

- **Image Size**  
  Choose which image size to use for the featured image (uses WordPress/Elementor image sizes).

5. Save and preview the page. The block will render using clean, responsive HTML and CSS.

---

## Resource Sync

### Settings Page

1. Go to **Settings → Resource Sync**.
2. Configure:
   - **API Key** – your API key (mocked for this assignment, but handled as real config).
   - **Enable Sync** – turn scheduled syncing on or off.
3. Click **Save Changes**.

### How Sync Works

- When **Enable Sync** is ON:
  - The plugin schedules a WP-Cron event every **15 minutes**.
  - On each run, it:
    - Fetches data from  
      `https://mocki.io/v1/0c7b33d3-2996-4d7f-a009-4ef34a27c7e9`.
    - Caches the raw response using a **transient** for 5 minutes to avoid API overuse.
    - Decodes the JSON and maps each item to a local `Resource` post.
    - **Creates** new posts if a resource does not exist yet.
    - **Updates** existing posts if a matching `mist_remote_id` is found.
  - Any errors (network, JSON, mapping) are handled gracefully:
    - No errors are shown to site visitors.
    - A short status/error summary is stored in an option and can be surfaced on the settings page.

- When **Enable Sync** is OFF:
  - The scheduled cron event is unscheduled to avoid unnecessary processing.

> Note: WP-Cron runs when your site receives traffic. On local environments, you may trigger cron manually or via WP-CLI during development.

---

## Known Limitations

- **API schema** is assumed based on the mock endpoint and may require adjustment if the payload changes.
- **Image handling** from the remote API is basic by design:
  - If remote images are available, they may be imported / mapped in a simplified way.
  - There is no full media-library management UI for imported images.
- **Deletion behavior** is conservative:
  - Resources removed from the API are not automatically trashed locally (to prevent accidental data loss).
- **Cron scheduling** relies on WP-Cron:
  - On very low-traffic sites, the 15-minute interval may not be exact.

These trade-offs are intentional for a small assignment and can be improved if needed.

---

## What I’d Improve with More Time

- **Richer sync management**
  - Manual “Sync Now” button on the settings page.
  - Detailed sync logs (separate log table or CPT).
  - Configurable sync interval.

- **Advanced media handling**
  - Robust image import with deduplication and better error reporting.
  - Progress indicators for large imports.

- **Stronger UX for large datasets**
  - Paginated resource selector in the Elementor control.
  - Searchable multi-select for resources.

- **Internationalization & RTL**
  - Full translation coverage (`.pot` file + examples).
  - RTL-specific styles for the widget.

- **Testing & CI**
  - PHPUnit tests for the sync service and CPT registration.
  - Integration tests for Elementor widget output.
  - Basic GitHub Actions workflow for linting and tests.

- **Future Elementor enhancements**
  - More layout variations and typography controls.
  - Per-device (desktop/tablet/mobile) control of spacing and alignment.

---

## Development Notes

- **Code style**  
  Follows WordPress PHP coding standards (naming, spacing, escaping) and Elementor widget best practices.

- **Security**  
  All user input (settings & meta) is sanitized before saving; all output is escaped before rendering. Nonces and capability checks guard admin actions.

- **Performance**  
  API results are cached with transients, and front-end assets are only loaded when needed (when the widget is present on a page).
  - When the `FRB_DEBUG` flag is enabled, the plugin logs a basic "plugin loaded" message to the PHP error log for debugging purposes.