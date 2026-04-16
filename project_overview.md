# Laravel Livewire Secure Login Starter Kit - Technical Walkthrough

Welcome to your project! This guide is designed to help you understand exactly how your application works, layer by layer. We will walk through the structure, the login flow, the database, and the security systems currently built into this codebase.

## 1. Project Overview

This project is a modern **Laravel Livewire Starter Kit** that has been extended to include a **Custom Secure Login System**. 

The core technologies working together in this repository are:
- **Laravel (v11/13)**: The foundational PHP backend framework. It handles the database connections, security flow, and routing.
- **Livewire (v4)**: A framework that allows you to write interactive, dynamic interfaces without writing custom JavaScript. It communicates directly with the Laravel backend.
- **Flux UI (livewire/flux)**: A library of sophisticated, pre-built interactive Blade components (like styled inputs, buttons, and dropdowns) that give the app a polished look out of the box.
- **Laravel Fortify**: A "headless" authentication backend. It handles all the secure logic for logging in and registering, leaving you free to design the UI pages yourself.
- **Tailwind CSS (v4) & Vite**: Tailwind is used to rapidly style the HTML, while Vite is the fast build tool that compiles and serves those styles instantly during development.
- **SQLite**: The current lightweight, file-based database. It requires no installation, making it perfect for rapid development.

## 2. Setup Guide

Here is the exact, beginner-friendly setup guide to get this project running from zero on Windows using VS Code. (Note: You do not need XAMPP because we are using SQLite and Laravel's built-in server).

### Requirements:
- **PHP 8.3** or higher installed on Windows.
- **Composer** (PHP's package manager).
- **Node.js & npm** (for building frontend styles).

### Step-by-Step Instructions:

1. **Open the project in VS Code**:
   Drag the project folder into VS Code and open a new Terminal (`Ctrl` + `` ` ``).

2. **Install PHP Dependencies**:
   ```bash
   composer install
   ```

3. **Setup Environment File**:
   Copy the example environment file to create your local `.env`.
   ```bash
   copy .env.example .env
   ```

4. **Generate Application Key**:
   This secures your user sessions and passwords.
   ```bash
   php artisan key:generate
   ```

5. **Configure the Database**:
   Open the `.env` file and ensure DB_CONNECTION is set to `sqlite`. Delete any other `DB_*` keys like `DB_HOST` or `DB_PASSWORD`.

6. **Run Migrations (Create Tables)**:
   This creates the database file (if it doesn't exist) and runs the SQL instructions to create your tables.
   ```bash
   php artisan migrate
   ```

7. **Install Frontend Assets**:
   Download the Tailwind and JS dependencies.
   ```bash
   npm install
   ```

8. **Start the Development Servers**:
   You need two terminals running simultaneously.
   - Terminal 1 (PHP Server): 
     ```bash
     php artisan serve
     ```
   - Terminal 2 (Vite Frontend Builder):
     ```bash
     npm run dev
     ```
     *(If PowerShell blocks this, run `cmd /c npm run dev`)*

9. **Verify**:
   Open a web browser and go to `http://127.0.0.1:8000`. You should see the welcome page!

## 3. Folder & File Breakdown

When you need to make changes, these are the most important files and folders:

### Key Folders Structure
- `app/`: The brain of the application. Your core PHP logic, models, and listeners live here.
- `app/Livewire/Actions/`: Contains functional Livewire action classes like `Logout.php`.
- `bootstrap/app.php`: The master configuration file where middleware and routing are initialized in modern Laravel.
- `database/migrations/`: PHP files that serve as blueprints to create database tables (e.g., `create_users_table.php`).
- `public/`: The public-facing directory where compiled assets and the main `index.php` entrypoint reside.
- `resources/views/`: The visual layer. All Blade templates and Livewire/Volt pages live here.
- `routes/`: Files controlling the web addresses of your app.

### Important Individual Files
- `package.json` & `composer.json`: Manage your Node and PHP dependencies.
- `vite.config.js`: Configuration for the Vite build tool.
- `.env`: **Extremely important**. Stores secret keys and local configurations like database connection details. Never commit this to GitHub.
- `artisan`: The Laravel command-line tool file.
- `routes/web.php`: Defines the guest and protected URLs (e.g., `/` or `/dashboard`). Edit this to add new non-auth pages.
- `routes/settings.php`: Holds the routing definitions for profile and security settings pages.

## 4. Authentication Flow Analysis

Because the app uses Laravel Fortify, the heavy backend logic is handled safely behind the scenes. Here is how the flow actually occurs:

- **Registration Flow**:
  1. User navigates to `/register`. The view `resources/views/pages/auth/register.blade.php` is displayed.
  2. The form posts to Fortify's internal register route.
  3. Fortify hashes the password and saves the user into the `users` database table.

- **Login Flow**:
  1. User navigates to `/login`. The view `resources/views/pages/auth/login.blade.php` is displayed.
  2. The form posts data to `{{ route('login.store') }}` (Fortify).
  3. Fortify intercepts the login through `App\Providers\FortifyServiceProvider`. Here, custom logic checks if the user's `is_locked` boolean is true.
  4. If locked, an error is immediately returned. If not, the password hash is verified.
  5. **Hooks**: On failure, an Event Listener (`RecordFailedLogin`) fires to increment failed attempts. On success, `RecordSuccessfulLogin` resets attempts.
  6. Upon success, the user's active session is tracked in the `sessions` table, and they are redirected to `/dashboard`.

- **Logout Flow**:
  1. Handled by the Livewire action `app/Livewire/Actions/Logout.php`. It clears session data, regenerates the token to prevent hijacking, and redirects to `/`.

## 5. Livewire & Component Analysis

This starter kit utilizes **Flux UI** alongside standard Livewire/Volt structure.

- **Logout Component**
  - **Path**: `app/Livewire/Actions/Logout.php`
  - **What it does**: Handles the user logout behavior securely.
  - **Where it is rendered**: It is called dynamically inside the user profile dropdown component (`desktop-user-menu.blade.php`).

- **Settings/Profile Components**
  - Found inside `resources/views/pages/settings`. These handle changing passwords, deleting accounts, and setting up Two-Factor Authentication.

## 6. Route and Page Flow

Here is the exact journey a user takes:

1. **Guest Pages**: A new user visits `http://127.0.0.1:8000/`. This hits `routes/web.php` resulting in `welcome.blade.php`.
2. **Auth Pages**: They click "Log in" and are routed to `/login`.
3. **Dashboard (Post-Login)**: Once successful, the flow redirects them to `/dashboard` (defined in `routes/web.php`).
4. **Middleware Protection**: The `/dashboard` route is protected by `['auth', 'verified']` middleware. Any unauthenticated user attempting to access it is instantly booted back to `/login`.

## 7. Database Analysis

Based on the `database/migrations` directory, here is the state of your database:

- `users`: The main table. It stores basic profile data. **Security additions**: newly added `failed_attempts` (integer), `is_locked` (boolean), and `locked_until` (timestamp) columns are already set up.
- `login_logs`: Tracks every single login attempt perfectly. **Key Columns**: `user_id`, `ip_address`, and `status`.
- `sessions`: Automatically manages user session state, tracking what device they are on and their last active time.
- `password_reset_tokens`: Stores temporary tokens for password recovery flow.

*(Note: These tables are already perfectly ready for your custom security project.)*

## 8. Security Analysis

### Built-in Security Features:
- **Password Hashing**: Passwords are mathematically scrambled via Bcrypt.
- **CSRF Protection**: Native Blade directives (`@csrf`) ensure malicious third-party sites cannot submit forms on behalf of the user.
- **Session Hijacking Defense**: The logout process explicitly regenerates the session token.
- **Rate Limiting**: Fortify manages request throttling inherently.

### Security Modifications Implemented:
Your repository contains advanced architectural enhancements for security:
- **Failed Login Tracking**: A dedicated `login_logs` table logs every success and failure via events.
- **Account Locking**: Built directly into the database. After exceeding a threshold, `is_locked` triggers and denies user processing altogether.
- **Interceptor**: Customized via `FortifyServiceProvider` to intercept the request at the earliest possible stage, checking the locked state before processing password verification.

## 9. How to Adapt it to Your Project

Your goal was to build a **"Secure Login System with Failed Attempt Detection and Account Locking"**.

**Good News:** The core foundation for this, including database tables and event interception, has largely already been implemented!

To finish adapting this, you should:
1. **Understand what's there**: Review the `FortifyServiceProvider` and `RecordFailedLogin` listener logic to grasp how the lock occurs.
2. **Visual Dashboard**: The next priority is to give admins a way to see what is happening. You need to create a new page in `resources/views/pages/` that queries the `LoginLog::class` model to show a visual HTML table of recent suspicious login activity.
3. **UI Enhancements**: Update `resources/views/pages/auth/login.blade.php` to display more informative alerts when an account is locked due to security measures.

## 10. Priority Action Plan

✅ **1. Setup Checklist**: Install Composer/NPM dependencies, clone `.env`, set to SQLite, run migrations.
✅ **2. "Learn this Codebase" Checklist**: Study Fortify's `FortifyServiceProvider`, the `login_logs` migration, and the Event Listeners.
✅ **3. "First Edits" Checklist**: The database definitions and core backend interceptors are complete.
🚀 **4. "Features to Add" Checklist (Next Steps)**:
   - Create a Livewire Component to visually display `login_logs` histories on the dashboard.
   - Refine the error messages pushed to the frontend during an account lockout scenario.

## 11. Most Important Files to Study First

To truly master this custom implementation, you must explore these three areas:

1. `app/Providers/FortifyServiceProvider.php`
   *(Crucial: Analyze how it implements custom authentication closures to intercept a login if `is_locked` is true)*.
2. `app/Listeners/RecordFailedLogin.php`
   *(Crucial: See exactly how the application increments the `failed_attempts` column and calculates the `locked_until` timestamp)*.
3. `routes/web.php`
   *(Crucial: When you are ready to build an admin review screen for security events, you map the new URL here)*.