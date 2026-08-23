# Database table catalog

| Table | Purpose |
|---|---|
| `users` | Administrative users, activation, avatar, authentication, and soft deletion. |
| `password_reset_tokens` | Laravel password reset tokens. |
| `sessions` | Laravel database-compatible session storage. |
| `cache`, `cache_locks` | Laravel cache storage and locks. |
| `jobs`, `job_batches`, `failed_jobs` | Laravel queue infrastructure. |
| `permissions`, `roles` | Spatie permission and role definitions. |
| `model_has_permissions`, `model_has_roles`, `role_has_permissions` | Spatie polymorphic access-control pivots. |
| `activity_log` | Spatie audit events with polymorphic subject and causer. |
| `locales` | Available locale codes, display names, direction, and ordering. |
| `media_folders` | Hierarchical media library folders. |
| `media` | File metadata and multilingual title, alt text, and caption. |
| `media_variants` | Derived media rendition paths by preset. |
| `mediables` | Ordered, role-based polymorphic media attachments. |
| `modules` | CMS module identity, display metadata, and SEO defaults. |
| `module_settings` | Typed JSON configuration entries per module. |
| `localized_routes` | Shared multilingual URL registry for all routable entities. |
| `redirects` | Internal or external path redirects and hit counters. |
| `content_revisions` | Immutable polymorphic JSON content snapshots. |
| `product_categories` | Hierarchical multilingual product taxonomy. |
| `attributes` | Reusable typed product attribute definitions. |
| `products` | Multilingual product content and shared commercial metadata. |
| `product_attributes` | Ordered typed attribute values for products. |
| `product_documents` | Locale-aware media documents attached to products. |
| `product_view_statistics` | Daily product view aggregates by locale. |
| `post_categories` | Hierarchical multilingual post taxonomy. |
| `posts` | Multilingual editorial/news content. |
| `tags` | Multilingual editorial tags. |
| `post_tag` | Post-to-tag pivot. |
| `pages` | Hierarchical multilingual CMS pages. |
| `page_sections` | Ordered structured sections owned by pages. |
| `recipes` | Multilingual recipe summary and two-column rich content. |
| `product_recipe` | Product-to-recipe pivot. |
| `document_categories` | Hierarchical investor-document taxonomy. |
| `investor_documents` | Investor publication metadata and multilingual title. |
| `investor_document_files` | Locale-aware official media files for publications. |
| `job_positions` | Multilingual job listings. |
| `job_applications` | Candidate submissions and review workflow. |
| `office_locations` | Multilingual office/contact locations. |
| `contact_messages` | Website contact submissions and assignment workflow. |
| `sliders` | Named, reusable banner collections. |
| `slider_items` | Scheduled multilingual slides. |
| `slider_item_media` | Required locale/device media slots for slides. |
| `site_settings` | Typed global settings, optionally translatable. |
| `social_links` | Ordered public social links. |
| `sitemap_logs` | Sitemap generation attempts, counts, and errors. |
