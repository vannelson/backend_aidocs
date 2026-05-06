# GoodDocs Backend

Laravel API for the GoodDocs assignment.

## Stack

- Laravel 12
- Sanctum token authentication
- MySQL database: `gooddocs`
- Controller / Service / Repository / Interface structure

## Features Implemented

- Login, register, logout, and current-user endpoint
- Create, open, rename, edit, and delete documents
- Rich-text content persistence as HTML
- Import `.txt` and `.md` files into new editable documents
- Share documents with seeded or registered users
- Owner-managed sharing with `viewer` and `editor` roles
- List current shares and update collaborator roles

## Supported File Types

File import is intentionally limited to:

- `.txt`
- `.md`

`.docx` is not supported in this build.

## Demo Accounts

Seeded users:

- `ava@gooddocs.test` / `password123`
- `ben@gooddocs.test` / `password123`
- `cara@gooddocs.test` / `password123`

## API Base URL

Local API base:

```txt
http://127.0.0.1:8000/api/v1
```

## Setup

1. Open a terminal in `backend_aidocs`.
2. Install dependencies:

```powershell
composer install
```

3. If `.env` does not exist, create it:

```powershell
copy .env.example .env
```

4. Confirm database settings in `.env`.
   Required database name:

```env
DB_DATABASE=gooddocs
```

5. Generate the Laravel app key:

```powershell
php artisan key:generate
```

6. Run migrations and seed demo users:

```powershell
php artisan migrate --seed
```

7. Start the backend server:

```powershell
php artisan serve
```

The API will run on:

```txt
http://127.0.0.1:8000
```

## Useful Commands

Run tests:

```powershell
php artisan test
```

Reseed demo users:

```powershell
php artisan db:seed
```

Reset backend tables and reseed:

```powershell
php artisan migrate:fresh --seed
```

Use `migrate:fresh` only if you want to wipe existing backend data.

## Main Endpoints

Auth:

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `GET /api/v1/auth/me`
- `POST /api/v1/auth/logout`

Documents:

- `GET /api/v1/documents`
- `POST /api/v1/documents`
- `GET /api/v1/documents/{id}`
- `PUT /api/v1/documents/{id}`
- `DELETE /api/v1/documents/{id}`
- `POST /api/v1/documents/import`

Sharing:

- `GET /api/v1/users/shareable`
- `GET /api/v1/documents/{id}/share`
- `POST /api/v1/documents/{id}/share`
- `PUT /api/v1/documents/{id}/share/{shareId}`

## Sharing Rules

- Owner can read, edit, delete, and share the document.
- Editor can read and edit the document.
- Viewer can read the document only.
- Only the owner can manage shared access.

## Notes

- The backend expects the frontend to authenticate with Sanctum bearer tokens.
- Shared and owned documents are returned separately so the frontend can render clear sections.
