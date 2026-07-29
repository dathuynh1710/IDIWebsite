# IDI Seafood

## Architecture

- `frontend/`: ReactJS + Vite SPA
- `backend/`: Laravel REST API + Blade Admin CMS

The two applications run independently. Laravel does not render the public
React app; the frontend calls the backend over HTTP. The private `/admin`
interface uses Laravel Blade and Alpine.js.

## Requirements

Frontend:

- Node.js 20+ (Node.js 22 is used by CI)
- npm

Backend:

- PHP 8.2+
- Composer
- MySQL 8+

## Frontend setup

```bash
cd frontend
cp .env.example .env
npm install
npm run dev
```

Frontend URL: <http://localhost:5173>

## Backend setup

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan storage:link
npm install
php artisan serve
```

Backend URL: <http://localhost:8000>

Health endpoint: <http://localhost:8000/api/health>

Admin URL: <http://localhost:8000/admin>

## Environment

Frontend:

```dotenv
VITE_API_URL=http://localhost:8000/api
```

Backend:

```dotenv
FRONTEND_URL=http://localhost:5173
```

Every `VITE_*` value is compiled into the frontend bundle and can be visible to
users. Never put passwords, Laravel application keys, private API keys, or
private tokens in a `VITE_*` variable.

## Development

Terminal 1:

```bash
cd backend
php artisan serve
```

Terminal 2 (public website):

```bash
cd frontend
npm run dev
```

Terminal 3 (admin assets):

```bash
cd backend
npm run dev
```

## Build frontend

```bash
cd frontend
npm run build
```

## Tests backend

```bash
cd backend
php artisan test
```

Admin architecture and extension guidance are documented in
`backend/docs/admin-blade-dashboard.md`.
