# Public route inventory (before implementation)

All rows support vi / en / zh; CMS URLs exist only for available, published translations. Slugs are existing CMS values, never generated translations.

| Page / content | Source | Current React URL | Target vi | Target en | Target zh |
|---|---|---|---|---|---|
| Home | static | / | /vi | /en | /zh |
| Products | CMS listing | /products | /vi/san-pham | /en/products | /zh/chanpin |
| Product category / detail | CMS, currently filter / modal | /products?category={slug} | /vi/san-pham/{slug} | /en/products/{slug} | /zh/chanpin/{slug} |
| About, history, values, leadership | CMS | /about, /about/story, /about/values and localized paths | /vi/gioi-thieu/{slug} | /en/about/{slug} | /zh/guanyu/{slug} |
| Quality | static | /quality | /vi/chat-luong | /en/quality | /zh/zhiliang |
| Sustainability | static frontend; existing seeded page route | /sustainability | /vi/phat-trien-ben-vung | /en/sustainability | /zh/kechixu-fazhan |
| Investors | static shell, CMS documents | /investors | /vi/quan-he-co-dong | /en/investors | /zh/touzizhe |
| Announcements | CMS document listing | /investors/announcements | /vi/quan-he-co-dong/cong-bo-thong-tin | /en/investors/announcements | /zh/touzizhe/gonggao |
| Financials | CMS document listing | /investors/financials | /vi/quan-he-co-dong/bao-cao-tai-chinh | /en/investors/financials | /zh/touzizhe/caiwu-baogao |
| Annual reports | CMS document listing | /investors/annual-reports | /vi/quan-he-co-dong/bao-cao-thuong-nien | /en/investors/annual-reports | /zh/touzizhe/niandu-baogao |
| AGM | CMS document listing | /investors/agm | /vi/quan-he-co-dong/dai-hoi-co-dong | /en/investors/agm | /zh/touzizhe/gudong-dahui |
| Green bond | static / CMS documents | /investors/green-bond | /vi/quan-he-co-dong/trai-phieu-xanh | /en/investors/green-bond | /zh/touzizhe/lvse-zhaiquan |
| News listing | CMS | /news | /vi/tin-tuc | /en/news | /zh/xinwen |
| News category / article | CMS | local filter, /news/{slug} | /vi/tin-tuc/{slug} | /en/news/{slug} | /zh/xinwen/{slug} |
| Recipes listing / detail | CMS | /recipes, /recipes/{slug} | /vi/cong-thuc[/{slug}] | /en/recipes[/{slug}] | /zh/shipu[/{slug}] |
| Careers listing / position | CMS, detail modal | /careers | /vi/tuyen-dung[/{slug}] | /en/careers[/{slug}] | /zh/zhaopin[/{slug}] |
| Contact | static shell / CMS contact data | /contact | /vi/lien-he | /en/contact | /zh/lianxi |

Preserve existing shared category/detail namespaces; reject collisions across content types. Investor file downloads remain backend download URLs, not fictional detail pages. Existing localized_routes entries for unsupported document detail pages must not enter sitemap.

Deployment currently uses Vite / optional GitHub Pages. Actual HTTP 301 requires a server entry point; SPA navigation alone cannot return HTTP 301. Production must route public document requests through Laravel's public-site handler and serve built assets from frontend/dist, or implement equivalent reverse-proxy rules.

## Implementation and deployment

- `shared/public-routes.json` is the single static path map consumed by Laravel and React. Keep `shared/` beside `backend/` when deploying.
- Dynamic paths retain their existing prefixes. `PublicRoutes` extends the existing helpers and localized_routes/redirects tables; model save/delete/restore events and product bulk visibility actions synchronize routes. No new translation text or slug columns are introduced.
- Missing or unpublished translations are omitted from the route API and language choices refuse navigation with an explanation. Existing About records without translation_status preserve the previous active + translated-title behavior; an explicit draft/hidden status is honored.
- Unknown paths return 404. Missing route API produces a retry state, not invented slug substitutions. `/admin`, `/api`, auth and document download paths retain their own routing.
- Legacy paths default to Vietnamese where the record is identifiable. Slug changes record 301 aliases; chains flatten and reverting a slug removes its redirect source. The server preserves the query string. Fragments are never sent to HTTP servers: browsers retain them across a redirect without a fragment, and SPA navigation explicitly preserves them.
- Product/category and news/category share the existing namespace. Cross-type slug collisions raise the existing `slug.{locale}` validation error instead of renaming content silently. The unique locale/path database index remains the final guard.
- Product and job detail dialogs are URL-addressable and reopen on reload; closing returns to the localized listing. Investor documents still link to their existing files/download handlers.
- PageHead emits canonical and available hreflang alternatives. `/sitemap.xml` emits only live routes, excludes missing titles/slugs, drafts, future publication, expired positions, inactive content/categories/modules and deleted records, and honors stored sitemap/noindex flags. Unsupported seeded investor document detail routes are not advertised.

Apply after deploying code (no schema migration required for this change):

```powershell
cd frontend
npm.cmd ci
# Set VITE_API_URL to the deployed /api endpoint and VITE_SITE_URL to the public origin before building.
npm.cmd run build
cd ../backend
php artisan routes:sync-public
```

The backfill is repeatable and transactional: a collision rolls back the entire run with a validation error. It updates route metadata only, never content or translation text. Resolve actual duplicate CMS slugs through the existing locale slug forms, then rerun. The command was run successfully against the current local database.

Set backend `FRONTEND_URL` and frontend build `VITE_SITE_URL` to the same public origin. Serve `frontend/dist` assets and send public document requests (including `/`, legacy paths, localized paths and `/sitemap.xml`) to Laravel. Do not put a blanket `try_files ... /index.html` ahead of Laravel for these requests, because that suppresses HTTP 301 and 404 responses.

Example Nginx locations inside the existing PHP server block (adapt the checkout path; keep existing PHP/admin/API locations):

```nginx
root /srv/idi/backend/public;
location / { try_files $uri @laravel; }
location ~ ^/(assets|images|videos)/ {
    root /srv/idi/frontend/dist;
    try_files $uri @backend_asset;
}
location @backend_asset {
    root /srv/idi/backend/public;
    try_files $uri =404;
}
location @laravel { rewrite ^ /index.php last; }
```

GitHub Pages cannot issue these HTTP redirects or serve Laravel. Its existing static preview may remain a preview, but production 301/reload behavior requires the PHP/reverse-proxy deployment above. No production server configuration was changed by this task.

## Validation

Backend coverage includes all seven dynamic model types, three locales, static aliases, changed/reverted slugs, query retention, missing translations, future dates, sitemap flags, category collision, repeatable backfill, HTTP direct entry/reload and unknown URL 404. Frontend coverage includes all static localized URLs, same-record language switching for News/Recipes/Products/Careers, missing translation behavior, menu/category links, query/hash retention, canonical/hreflang, old slug navigation, and actual product/job dialog loading on direct entry and remount in three languages, alongside existing About/menu tests.

PHP 8.5 reports an existing deprecation in `backend/config/database.php` for `PDO::MYSQL_ATTR_SSL_CA`; it is outside this change. PowerShell blocks npm.ps1 in this environment, so validation uses `npm.cmd`.

Final validation commands/results:

- `php artisan test --compact --filter='PublicRoutesTest|About|News|Recipe|Recruitment|ProductsApi|ProductCategory|Menu|InvestorRelationsApi'`: 88 tests, 905 assertions, no failures; existing PHP 8.5 deprecations noted above.
- `npm.cmd test`: 3 Node utility tests + 28 Vitest integration tests passed.
- `npm.cmd run build`: passed.
- `php vendor/bin/pint --test` on PublicRoutes, the four existing route helpers, PublicRoutesController, SyncPublicRoutes and PublicRoutesTest: passed.
- `git diff --check`: passed (Git emits line-ending notices).
- `php artisan routes:sync-public`: passed on local data.
