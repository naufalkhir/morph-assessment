# Morph Digital — Technical Assessment

### Full-Stack Data Entry & Listing Application

> **Stack:** Vue.js 3 + CSS · Laravel 12 · MySQL 8  
> **Candidate:** Muhammad Naufal  
> **Deadline:** 12 March 2026, 5:00 PM

---

## Project Structure

```
morph-assessment/
├── backend/    # Laravel 12 REST API
├── frontend/   # Vue 3 + Vite SPA
└── README.md
```

---

## Quick Start

### Prerequisites

| Tool     | Version |
| -------- | ------- |
| PHP      | 8.4.18  |
| Composer | 2.9.5   |
| Node.js  | 22.16.0 |
| npm      | 10.9.2  |
| MySQL    | 8.0.30  |

---

### Step 1 — Clone

```bash
git clone https://github.com/naufalkhir/morph-assessment.git
cd morph-assessment
```

---

### Step 2 — Backend

```bash
cd backend
composer install
cp .env.example .env
```

Edit `backend/.env`:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=morph_assessment
DB_USERNAME=root
DB_PASSWORD=

APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173
```

```bash
php artisan key:generate
mysql -u root -p -e "CREATE DATABASE morph_assessment CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate
php artisan db:seed
php artisan serve
```

> ✅ Backend running at **http://localhost:8000**

---

### Step 3 — Frontend

Open a new terminal:

```bash
cd frontend
npm install
cp .env.example .env
```

Edit `frontend/.env`:

```env
VITE_API_BASE_URL=http://localhost:8000/api
```

```bash
npm run dev
```

> ✅ Frontend running at **http://localhost:5173**

---

## API Endpoints

Base URL: `http://localhost:8000/api`

| Method   | Endpoint        | Description        |
| -------- | --------------- | ------------------ |
| `GET`    | `/entries`      | List all entries   |
| `POST`   | `/entries`      | Create a new entry |
| `GET`    | `/entries/{id}` | Get single entry   |
| `PUT`    | `/entries/{id}` | Update an entry    |
| `DELETE` | `/entries/{id}` | Delete an entry    |

---

## Features

### Core

- Entry form with description, amount, currency, date
- Client-side and server-side validation
- Listing table with currency and date formatting

### Bonus

- Pagination + page size selector
- Column sorting
- Search + date range filter
- Edit record modal
- Delete with confirmation
- CSV export
- Dark mode toggle

---

## Troubleshooting

**CORS errors**

```bash
FRONTEND_URL=http://localhost:5173
php artisan config:clear
```

**MySQL connection refused**

```bash
sudo service mysql start
```

**Node modules issues**

```bash
rm -rf node_modules package-lock.json
npm install
```

---

_Built by Muhammad Naufal for Morph Digital Sdn. Bhd. Technical Assessment_
