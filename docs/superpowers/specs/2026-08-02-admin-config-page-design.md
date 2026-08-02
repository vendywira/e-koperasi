# Admin Config Page — Design Spec

**Date:** 2026-08-02

## Goal

Separate operational flags/parameters from content CMS. CMS (`/admin/cms`) stays editor-accessible and content-only. New **Config** page — admin-only, menu entry in admin sidebar — holds parameter flags (`provision_mode`, future flags) as a generic key-value store with a typed editor.

## Background / Why

- `provision_mode` (auto | manual) currently lives in the CMS `billing` section → editable by editor. It is an operational flag, not content.
- Pricing already comes from `Plan.pricing_config`, not CMS — the CMS `billing` section is misleading (only `provision_mode` matters; `price_per_unit`/`available_units` are dead).
- Admin needs a dedicated home for parameter/variable flags, admin-only.

## Decisions

| Decision | Value |
|---|---|
| Storage | Reuse `site_contents` table, section `config` (no migration) |
| Access | Route `role:admin` only; menu entry `v-if user.role === 'admin'` |
| CMS billing section | Removed from schemas + labels + DB; `provision_mode` no longer in CMS |
| Config key read | `SiteConfig::get('config.provision_mode', 'manual')` |
| Config key write | `ConfigController` → `SiteContent::saveSection('config', [...])` + `SiteConfig::clearCache()` |
| Frontend | Inertia page `Admin/Config.vue`, generic typed form (select for enum, input for scalar) |
| Read-site | `TenantRequestController::store()` reads `config.provision_mode` |

## Architecture

### Storage
- `site_contents` table already has `section` + `value` (json). Section `config` stores a flat object of parameters, e.g.:
  ```json
  { "provision_mode": "manual" }
  ```
- `SiteConfig::all()` already merges `config/site.php` defaults with DB — `config` section keys are read as `config.<key>` automatically.

### Config defaults (config/site.php)
```php
'config' => [
    'provision_mode' => 'manual', // auto | manual
],
```
(Replaces the `billing` block added earlier this session. `price_per_unit` is dropped — pricing comes from Plan.)

### Backend
- `app/Http/Controllers/Admin/ConfigController.php`:
  - `index(): Response` — if no `config` section in DB, seed from `config('site.config')` default; render `Admin/Config` with current values + defaults.
  - `update(Request): RedirectResponse` — validate known keys, `SiteContent::saveSection('config', $values)`, `SiteConfig::clearCache()`, redirect back with success.
- `routes/cms.php`:
  - CMS route group stays as-is (editor + admin), but the `billing` section is removed from schemas, so editors no longer see it.
  - New group (admin-only):
    ```php
    Route::middleware('role:admin')->prefix('config')->name('config.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ConfigController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\Admin\ConfigController::class, 'update'])->name('update');
    });
    ```

### Frontend
- `resources/js/Pages/Admin/Config.vue`:
  - Receives `config: Record<string, any>` and `defaults: Record<string, any>`.
  - Renders each key as a typed field: enum values → `<select>`, others → text input. `provision_mode` → select auto/manual.
  - PUT to `admin.config.update` with all values, toast + reload on success.
- `resources/js/Layouts/AdminLayout.vue`: new menu section **Pengaturan**, item **Config** (`href="/admin/config"`, `v-if user?.role === 'admin'`, active state on `/admin/config`). Both desktop + mobile sidebar blocks.

### CMS cleanup
- Remove `billing` section from `resources/js/Components/CmsEditor/schemas/index.ts` (fields `unit`, `unit_label`, `price_per_unit`, `provision_mode`, `currency`, `available_units`, `notes`).
- `resources/js/Pages/Admin/Cms/Index.vue` needs no edit — section labels/icons render from the schema; no hardcoded `billing` entry exists there.
- Delete DB row: `DELETE FROM site_contents WHERE section = 'billing'` (one-off; new installs never seed it).

### Read-site
- `app/Http/Controllers/Client/TenantRequestController.php` line ~153:
  ```php
  $provisionMode = SiteConfig::get('config.provision_mode', 'manual');
  ```
- `database/seeders/BillingSeeder.php`: write section `config` instead of `billing`:
  ```php
  SiteContent::updateOrCreate(['section' => 'config'], [
      'value' => ['provision_mode' => 'manual'],
  ]);
  ```
  (Rename concept: seeding operational config, not billing.)

## Behavior

- Admin opens `/admin/config` → sees "Mode Provisioning" select (auto/manual) → saves → `SiteConfig::clearCache()` → next `request-tenant` uses new mode immediately.
- `provision_mode=auto`: `request-tenant` provisions + activates tenant + marks invoice paid (existing flow, unchanged — only the config key path changes).
- `provision_mode=manual`: tenant pending, admin approves (existing flow).
- Editor logs in → no "Config" menu item, no `/admin/config` access; CMS no longer shows a billing section.

## Error Handling

- No `config` section in DB → `index()` seeds from `config('site.config')` before render.
- Update with unexpected keys → ignored (only known keys saved); select values validated against allowed enum.

## Testing

- Manual: admin toggles `provision_mode` auto/manual on Config page → verify `site_contents.config` row updates → `request-tenant` activates vs pending accordingly.
- Manual: editor cannot see/access `/admin/config`.
- Verify CMS no longer lists a Billing section.
- `php -l` on modified PHP; `npm run build` for Vue.

## Rollback

- `git revert` — restores CMS billing section, removes Config page. DB row `site_contents.config` can be deleted; old `billing` row returns if seeder re-run (or re-seed manually).
