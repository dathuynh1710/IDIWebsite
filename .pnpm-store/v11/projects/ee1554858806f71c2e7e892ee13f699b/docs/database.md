# IDI Seafood database architecture

## 1. Overview

The backend uses MySQL 8, InnoDB, and `utf8mb4`. The schema is organized into
identity/audit, locale and media, routing and revision history, product catalog,
editorial content, recipes, investor relations, recruitment, contact, banners,
and site configuration modules.

Business migrations intentionally do not call Eloquent models or depend on seed
data. Laravel's default cache, queue, session, and password-reset tables remain
unchanged.

## 2. Tables by module

- Identity and audit: `users`, Spatie permission tables, `activity_log`.
- Localization and media: `locales`, `media_folders`, `media`,
  `media_variants`, `mediables`.
- CMS infrastructure: `modules`, `module_settings`, `localized_routes`,
  `redirects`, `content_revisions`.
- Products: `product_categories`, `attributes`, `products`,
  `product_attributes`, `product_documents`, `product_view_statistics`.
- Editorial: `post_categories`, `posts`, `tags`, `post_tag`, `pages`,
  `page_sections`.
- Recipes: `recipes`, `recipe_ingredients`, `recipe_steps`, `product_recipe`.
- Investor relations: `document_categories`, `investor_documents`,
  `investor_document_files`.
- Recruitment and contact: `job_positions`, `job_applications`,
  `office_locations`, `contact_messages`.
- Presentation and settings: `sliders`, `slider_items`,
  `slider_item_media`, `site_settings`, `social_links`, `sitemap_logs`.

## 3. Main relationships

Media, creator, updater, author, reviewer, assignee, featured-media, and
self-parent references are optional and normally use `SET NULL`. Owned child
rows and pivots use `CASCADE`: product attributes/statistics, recipe
ingredients/steps, page sections, slider media, settings, variants, and
many-to-many pivots. Media attached to official product/investor documents or
localized slider slots uses `RESTRICT` to prevent accidental hard deletion.

Product deletion cascades to product attributes, product documents, view
statistics, and product-recipe pivots. Category deletion sets the product or
post category reference to null, preserving the content record.

## 4. Multilingual JSON fields

Only translatable values use JSON. A typical value is:

```json
{"vi":"Nội dung tiếng Việt","en":"English content","zh":"中文内容"}
```

Shared identifiers, flags, dates, file metadata, numeric values, and foreign
keys remain typed relational columns. JSON columns have no database defaults;
nullable fields remain `NULL` until the application writes them. JSON `slug`
columns are not indexed; localized URL uniqueness is enforced by
`localized_routes`.

JSON columns by table:

- Media/configuration: `media.title`, `media.alt_text`, `media.caption`;
  `modules.page_title`, `description`, `seo_title`, `meta_description`,
  `og_title`, `og_description`; `module_settings.setting_value`;
  `site_settings.value`; `social_links.label`.
- Product catalog: `product_categories.name`, `slug`, `description`,
  `seo_title`, `meta_description`, `translation_status`,
  `locale_published_at`; `attributes.name`, `unit`, `options`;
  `products.title`, `slug`, `short_description`, `description`, `content`,
  `seo_title`, `meta_description`, `og_title`, `og_description`,
  `schema_extra`, `translation_status`, `locale_published_at`;
  `product_attributes.value`; `product_documents.title`.
- Editorial: the equivalent multilingual fields on `post_categories`,
  `posts`, and `pages`; `tags.name`, `tags.slug`; `page_sections.title`,
  `content`, `payload`.
- Recipes: multilingual content/SEO/status fields on `recipes`;
  `recipe_ingredients.name`, `unit`, `note`; `recipe_steps.instruction`.
- Investor/recruitment/contact: multilingual content on
  `document_categories`, `investor_documents`, `investor_document_files`,
  `job_positions`, and `office_locations`.
- Sliders: `sliders.description`; `slider_items.title`, `subtitle`,
  `button_label`, `link`.
- Audit: `activity_log.attribute_changes`, `activity_log.properties`,
  `content_revisions.snapshot`, and `content_revisions.changed_fields`.

## 5. Locale convention

Supported application locale codes are `vi`, `en`, and `zh`; `vi` is the
application default. The `locales` table is not seeded by these migrations.
Rows that represent one locale (`localized_routes`, localized media slots,
statistics, and optional localized files/messages/logs) use a foreign key to
`locales.code`. JSON-bearing content tables do not repeat locale foreign keys.

## 6. Polymorphic localized routes

`localized_routes` maps any routeable entity by `routeable_type` and
`routeable_id`. It stores a normalized domain-free path and guarantees
`(locale, full_path)` uniqueness. This replaces module-specific route tables.
The polymorphic target cannot have a database foreign key, so removal and
integrity checks belong to application services.

## 7. Polymorphic content revisions

`content_revisions` stores a JSON snapshot and optional changed-field metadata
for any revisionable entity. Its nullable locale supports both language-specific
changes and shared-field changes. It records only `created_at`; revisions are
immutable and therefore have no `updated_at`.

## 8. Soft delete rules

CMS entities and user/media records use `deleted_at`: users, media folders,
media, product/post/document categories, products, posts, tags, pages, recipes,
investor documents, job positions, office locations, sliders, and slider items.
Operational events, pivots, settings, statistics, submissions, and logs are not
soft deleted. A soft delete does not fire database foreign-key actions.

## 9. Foreign-key rules

- `CASCADE`: true owned children and pivots with no independent meaning.
- `SET NULL`: optional parents, featured media, audit users, and assignments.
- `RESTRICT`: locale rows and media that represents an important document or
  required slider asset.
- Polymorphic targets have no foreign key by design.

Hard deletion of a locale is restricted while locale-specific rows exist.

## 10. Index rules

Foreign keys are indexed by InnoDB. Additional indexes cover active/sort-order
queries, status/date filtering, polymorphic type/id lookup, locale publication,
period lookup, email search, and route path uniqueness. JSON and long-text
columns are not indexed.

Important unique constraints include SKU, module/slider/setting codes,
localized full paths, permission identities, pivot identities, product view
daily aggregates, media variants, and slider locale/device slots.

The requested unique key on `(disk, directory, file_name)` was not created:
with `directory(500)` and `utf8mb4`, its worst-case key length exceeds MySQL's
3072-byte InnoDB limit. A safe `(disk, file_name)` lookup index is present;
path-level uniqueness must be enforced in the upload service. The `mediables`
identity key is retained, but MySQL permits repeated keys when nullable
`locale` is `NULL`; the service must prevent duplicate global attachments.

## 11. Migration and rollback

Run against an explicitly local or testing MySQL database:

```bash
php artisan optimize:clear
php artisan migrate
php artisan migrate:status
php artisan migrate:rollback
php artisan migrate
php artisan test
```

Do not run `migrate:fresh` against a database whose environment and ownership
have not been verified.

Local sample data, seeded modules, and the development administrator credentials
are documented in `docs/seeding.md`.

## 12. Spatie tables

`spatie/laravel-permission` provides `permissions`, `roles`,
`model_has_permissions`, `model_has_roles`, and `role_has_permissions`.
The configured default guard is `web`. `spatie/laravel-activitylog` provides
`activity_log`, including subject/causer polymorphic indexes plus indexes for
`log_name`, `event`, and `created_at`. `spatie/laravel-translatable` provides
model behavior and has no database migration.

## 13. Assumptions

- The repository is a new, local Laravel installation: only the three default
  migrations existed and no production migration history was present.
- Hard deletes are exceptional because primary CMS entities use soft deletes.
- Product/category relations use `SET NULL` to preserve a product after a rare
  hard category deletion.
- Official document media and slider slot media use `RESTRICT`.
- Investor quarter remains nullable `TINYINT UNSIGNED`; the 1-4 rule will be
  enforced by request validation rather than a database check for portability.
- Locale rows will be populated later by a small deployment seeder or
  administration workflow; this task intentionally adds no business seed data.
- Enums in migrations remain literal and self-contained for reliable rollback;
  matching PHP backed enums under `app/Enums` are application conveniences, not
  migration dependencies.
