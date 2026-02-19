# Zoho CRM Integration — Laravel + Vue.js

Web form for creating Deals and Accounts in Zoho CRM with automatic token refresh.


## Stack

- **Backend:** Laravel 12, PHP 8.2
- **Frontend:** Vue.js 3, Vite, Tailwind CSS
- **Database:** MySQL


## Requirements
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/kapolizei/zoho-crm-integration
cd zoho-crm-integration
```

### 2. Backend setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Fill in your `.env`:

```env
DB_DATABASE=crmoz
DB_USERNAME=root
DB_PASSWORD=your_password

ZOHO_CLIENT_ID=your_client_id
ZOHO_CLIENT_SECRET=your_client_secret
ZOHO_BASE_URL=https://www.zohoapis.eu/crm/v2
ZOHO_AUTH_URL=https://accounts.zoho.eu/oauth/v2/token
```

Run migrations:

```bash
php artisan migrate
```

Start the server:

```bash
php artisan serve
```

### 3. Frontend setup

```bash
cd frontend
npm install
npm run dev
```

## Zoho CRM Authorization

> This step is required once before using the form.

### Step 1 — Create Zoho Application

1. Go to [https://api-console.zoho.com](https://api-console.zoho.com)
2. Create a new **Server-based Application**
3. Set **Authorized Redirect URI** to: http://localhost:8000/zoho/callback
4. Copy **Client ID** and **Client Secret** to your `.env`

### Step 2 — Authorize

Open in browser:

```
http://localhost:5173
```
- Click *Connect Zoho CRM*
- Grant access to CRM
- You will be redirected back to the form automatically

Tokens are saved to the database and will refresh automatically every hour.


## Usage

1. Open [http://localhost:5173](http://localhost:5173)
2. Fill in the Account details (Name, Website, Phone)
3. Fill in the Deal details (Name, Stage)
4. Click **Create Record in Zoho CRM**
5. The Account and Deal will be created and linked in Zoho CRM

---

## Project Structure

```
├── backend/
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   ├── CrmController.php        # Form submission handler
│   │   │   └── ZohoAuthController.php   # OAuth authorization
│   │   ├── Models/
│   │   │   └── ZohoToken.php            # Token model
│   │   └── Services/
│   │       └── ZohoService.php          # Zoho API integration
│   └── routes/
│       ├── api.php                      # API routes
│       └── web.php                      # OAuth routes
│
└── frontend/
    └── src/
        └── components/
            └── ZohoCRMForm.vue          # Main form component
```

---

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/zoho/auth` | Redirect to Zoho OAuth |
| `GET` | `/zoho/callback` | Handle OAuth callback |
| `POST` | `/api/crm/submit` | Create Deal + Account |
| `GET` | `/api/crm/status` | Check authorization status |

---

## Token Refresh

Access tokens expire after **1 hour**. The backend automatically refreshes the token using the stored refresh token before each API request — no manual action required.