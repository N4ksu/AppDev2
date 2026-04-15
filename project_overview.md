1. Project Overview
This repository contains a modern Laravel Livewire Starter Kit. It gives you a clean starting point with a full authentication system and standard frontend layout out-of-the-box, which has now been upgraded into a custom Secure Login System.

Core Technologies in this Project:

Laravel (v11/12+): The main PHP backend framework handling routing, databases, and the request lifecycle.
Livewire (v3/4): The framework used for interactive components without writing raw JavaScript.
Flux UI (livewire/flux): A sophisticated toolkit of interactive Blade components used for the beautiful UI (buttons, inputs, dropdowns).
Laravel Fortify: The headless backend authentication engine that controls user registration and login securely.
Tailwind CSS (v4): Used for styling everything easily.
Vite: The incredibly fast frontend build tool used to serve your Tailwind and JavaScript assets.
SQLite: The default, lightweight file-based database we are currently using.
2. Setup Guide
Here is how you start this project from zero on Windows using VS Code:

Requirements:

PHP 8.3 or higher.
Composer (PHP dependency manager).
Node.js & npm.
Step-by-Step Commands in VS Code Terminal:

Install PHP Dependencies: composer install
Environment File: copy .env.example .env
Application Key: php artisan key:generate
Database Setup: Open 

.env
 and ensure DB_CONNECTION=sqlite. You do not need XAMPP for this!
Run Migrations: php artisan migrate
Install Node Modules: npm install
Start the PHP Server: php artisan serve
Start Vite (Open a 2nd terminal): npm run dev (or cmd /c npm run dev if PowerShell blocks it).
Verify: Open http://127.0.0.1:8000 in your browser.
3. Folder/File Explanation
app/: Where the custom Logic lives.
Models/: Contains User.php and 

LoginLog.php
 for database interactions.
Listeners/: Contains 

RecordFailedLogin.php
 and 

RecordSuccessfulLogin.php
 for tracking attempts.

Providers/FortifyServiceProvider.php
: Registers your authentication logic and intercepts locked users.
database/migrations/: Blueprint files like 

create_login_logs_table.php
 that generate your database tables.
resources/views/: Where all UI HTML is. Look inside pages/auth/ for the login designs.

routes/web.php
: Where you define URLs (e.g., / and /dashboard). Edit this to add new pages.
database/database.sqlite: Your actual, physical database file.

.env
: The secret configuration file where database and mail settings live.
4. Authentication Flow Analysis
Because this uses Laravel Fortify, the routing and logic are hidden in the backend.

Registration: Handled by App\Actions\Fortify\CreateNewUser. It saves directly to the users table. View: resources/views/pages/auth/register.blade.php.
Login: User submits 

login.blade.php
. Fortify invokes authenticateUsing() in 

FortifyServiceProvider
. It checks if the user is locked (based on the is_locked column). If not, it compares the password hash.
Events Triggers: If login fails, the 

RecordFailedLogin
 listener fires, adding a row to login_logs and incrementing failed_attempts. If it succeeds, 

RecordSuccessfulLogin
 fires, resetting attempts to 0.
Logout: Uses Livewire component action 

app/Livewire/Actions/Logout.php
 to destroy the session.
5. Livewire & Component Analysis
This starter kit heavily leans on normal Blade components powered by Flux UI.

Component: 

Logout.php
File path: 

app/Livewire/Actions/Logout.php
What it does: Explicitly handles HTTP session invalidation and logs out the user securely.
Where it is rendered: Called dynamically from the user profile dropdown on the dashboard.
6. Route and Page Flow
Guest Flow: Navigates to / -> Hits 

welcome.blade.php
.
Auth Flow: Clicks "Log In" -> Hits /login -> Displays 

login.blade.php
.
Protected Flow: If successful, user is redirected to /dashboard. Any access to /dashboard while logged out is instantly blocked by the auth middleware.
7. Database Analysis
Inspecting database/migrations/ reveals the following tables perfectly ready for use:

users: Purpose is to hold user profiles. Key additional columns: failed_attempts, is_locked, locked_until.
login_logs: Tracks all login attempts perfectly. Key Columns: user_id, ip_address, status, created_at.
sessions: Active user sessions are tracked here automatically.
8. Security Analysis
Built-In Security Present:

Password Hashing: Enabled out-of-the-box (Bcrypt).
CSRF Protection: Livewire and Blade (@csrf) protect against Cross-Site Request Forgery naturally.
Session hijacking defense: 

Logout.php
 intentionally calls Session::regenerateToken().
Custom Security Implemented By Us:

Failed login tracking: Every failed/successful login is permanently audited in the login_logs table via Event Listeners.
Account locking: After 3 failed attempts, 

RecordFailedLogin
 switches the is_locked boolean to true and sets a 15-minute locked_until timestamp.
Interceptor: 

FortifyServiceProvider
 actively intercepts locked users before checking passwords and throws a validation UI error.
9. How to Adapt It to Your Project
(We have fundamentally completed this step!)

What was useful: We kept Fortify and didn't rewrite the wheel.
What we added: We added the login_logs table and modified the users table via php artisan make:migration.
Backend logic added: We used Illuminate\Auth\Events\Failed to wire up our new logic seamlessly without breaking the Starter Kit core.
10. Priority Action Plan
(What we accomplished):

Beginner Setup Checklist: COMPLETED (Using SQLite and Vite).
Learn This Codebase Checklist: COMPLETED (Understanding Fortify logic).
First Edits Made: COMPLETED (Migrations generated and updated to fix foreign key constraints).
Features Added for Project: COMPLETED (Models, Listeners, and Fortify logic completely integrated!).
If you want to keep extending it, your new priority is to build a new view in resources/views/ (like a Dashboard Table) to visually display the login_logs!

11. Most Important Files to Study First
To truly master this custom codebase moving forward, focus closely on:


app/Providers/FortifyServiceProvider.php
 - Look at the authenticateUsing method we added to see how the lock works!

app/Listeners/RecordFailedLogin.php
 - Look at how we increment the failed_attempts column and trigger the time lock.

routes/web.php
 - Your map. Look here when you are ready to create a new page!