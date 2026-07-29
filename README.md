# IDI Seafood

## Architecture

- `frontend/`: ReactJS + Vite SPA
- `backend/`: Laravel REST API

The two applications run independently. Laravel does not render React; the
frontend calls the backend over HTTP.

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
php artisan serve
```

Backend URL: <http://localhost:8000>

Health endpoint: <http://localhost:8000/api/health>

No product migrations or other business database schema are included in this
phase. Configure MySQL in `backend/.env`, but do not run migrations yet.

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

Terminal 2:

```bash
cd frontend
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

The current phase contains only the API foundation and health check. It does not
include business CRUD, authentication, permissions, or multilingual backend
features.
