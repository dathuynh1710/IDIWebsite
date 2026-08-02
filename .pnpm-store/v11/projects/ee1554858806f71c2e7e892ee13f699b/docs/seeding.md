# Sample database seed

The sample seed is intended for local development and demonstrations only.
Run it after migrations:

```bash
php artisan db:seed
```

To rebuild a confirmed local database from scratch:

```bash
php artisan migrate:fresh --seed
```

Never run `migrate:fresh` against production or an environment containing data
that must be preserved.

## Local administrator

- Username: `admin`
- Password: `idi686868`
- Role: `super-admin`

Change this password before using the account outside local development.

## Seeded modules

- Locale configuration: `vi`, `en`, and `zh`, with `vi` as default.
- Access control: super-admin/editor roles and ten CMS permissions.
- Media: folders and metadata-only sample assets for branding, products,
  news, documents, and banners.
- CMS modules and representative settings.
- Product catalog: two categories, two products, three attributes, documents,
  attribute values, and seven days of view statistics.
- Editorial content: one category, two posts, three tags, two pages, and four
  page sections.
- Recipe content: one recipe with ingredients, steps, and a linked product.
- Investor relations: one annual-report category, document, and file.
- Recruitment/contact: one job position and two office locations.
- Presentation/settings: homepage slider, responsive media assignments, site
  settings, and social links.
- SEO routing: published routes for all three locales and the seeded routable
  content.

The seed deliberately leaves operational tables empty: activity logs, content
revisions, redirects, job applications, contact messages, sitemap logs, queue
tables, sessions, and failed jobs. Those rows should be produced by real
application behavior.

All seeders are idempotent. Running `php artisan db:seed` again updates the
known sample records without duplicating them.
