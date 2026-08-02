# IDI Seafood Backend

Laravel backend cung cấp REST API và Admin CMS server-rendered bằng Blade.

## Development

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan storage:link
php artisan serve
```

Trong terminal khác:

```bash
npm run dev
```

Admin URL: <http://localhost:8000/admin>

Tài liệu kiến trúc: [docs/admin-blade-dashboard.md](docs/admin-blade-dashboard.md)

## Build và test

```bash
npm run build
php artisan optimize:clear
php artisan test
```

Public React website nằm trong `../frontend/`; admin không dùng React/Vue và
không thay đổi source public frontend.
