# ✈️ Travel & Tour App 

A sample application to manage **travel packages and tours**, with role-based access control for **Admin** and **Editor** users.

---

## 🚀 Features
- 🔑 Authentication & API token management 
- 👥 Roles: **Admin** (full access) & **Editor** (limited access)
- ✍️ Admin can add, edit, tours & travels
- 📋 Editor can manage content but with restricted permissions
- 📜 API endpoints to create and list travels/tours
- 🛠️ RESTful architecture (ready for frontend or mobile integration)

---

## 🛠️ Tech Stack
- **Backend:** Laravel 12 (PHP 8.3+)
- **Auth:** Laravel Sanctum
- **Database:** MySQL 
- **API Docs:** Scramble 

---

## 📦 Installation

### 1. Clone the repo
```bash
git clone https://github.com/KellaSoa/travel_api
cd travel_api
```

### 2. Install dependencies
```bash
composer install
npm install && npm run dev
```

### 3. Run migrations & seed roles

```bash
php artisan migrate --seed
```

This will create Admin and Editor roles.

### 4. Start server
```bash
php artisan serve
```
### 5. Adding Users

This project includes a custom artisan command to quickly create users with roles:
```bash 
php artisan users:create
```

## 🔑 Authentication

After creating a user, you can log in via the API to get a token.

### Login request:

```bash
POST /api/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "secret"
}
```
### Login response:

```bash
{
  "token": "your-token"
}
```
Copy the token from the response.

Use it in your headers for all protected requests:
```bash 
Authorization: Bearer <your-token>
```

### ⚡ This makes the step more easier:

- Create a user
- Login → get token
- Use token to create Travels and Tours

## 📚 API Documentation

This project uses Scramble to generate API documentation.

👉 You can view all available endpoints for Travels and Tours here:

Local: http://localhost:8000/docs/api

### The docs include details for:

- Authentication (Login)
- Travels
- Tours

## 🧪 Testing

Run unit & feature tests:
```bash
php artisan test
```


## 🧹 Code Quality & Static Analysis
### 1. Laravel Pint – Code formatting

Laravel Pint automatically formats your PHP code according to Laravel’s coding standards.

Install Pint (if not already installed):

```bash 
composer require --dev laravel/pint
```

Run Pint:

# Check code style without changing files
```bash 
./vendor/bin/pint --test
```

# Automatically fix code style
```bash 
./vendor/bin/pint
```

### 2. Larastan – Static Analysis

Larastan helps catch bugs and type issues in your Laravel project.

# Install Larastan:

```bash 
composer require --dev "larastan/larastan:^3.0"
```
# Then, create a phpstan.neon

```bash 
includes:
    - vendor/larastan/larastan/extension.neon
    - vendor/nesbot/carbon/extension.neon

parameters:

    paths:
        - app/

    # Level 10 is the highest level
    level: 5

#    ignoreErrors:
#        - '#PHPDoc tag @var#'
#
#    excludePaths:
#        - ./*/*/FileToBeExcluded.php
```
# Run Larastan: Analyze code and check for errors

```bash 
./vendor/bin/phpstan analyse
```