# Local Project Setup Guide & Troubleshooting

## What went wrong in the terminal?

You encountered two separate environment errors on your machine during setup:

### Error 1: "could not find driver" (SQLite)
**Why it happened:** 
When you ran `php artisan migrate`, Laravel attempted to talk to a SQLite database. To do this, PHP relies on small plugins called "extensions". The `could not find driver` error means the PHP installed on your computer has the SQLite extensions turned off by default, so PHP literally doesn't know how to speak to a SQLite database.

**Why do we need to fix this?**
Without the database driver, the app cannot create tables, save users, or log activity. Laravel simply cannot function without database access.

**How to fix:**
1. Find your `php.ini` file (if you use XAMPP, it's usually at `C:\xampp\php\php.ini`). **Why?** The `php.ini` file controls all settings and extensions for PHP.
2. Open it in a text editor like Notepad.
3. Search for `;extension=pdo_sqlite` and `;extension=sqlite3`.
4. Remove the semicolon `;` at the beginning of those lines so they look exactly like this:
   ```ini
   extension=pdo_sqlite
   extension=sqlite3
   ```
   **Why remove the semicolon?** In `.ini` files, a semicolon at the start of a line means "ignore this line" (it acts as a comment). Removing it tells PHP to actually load the SQLite driver.
5. Save the file. If you are running Apache via XAMPP, restart it so the new settings take effect.

### Error 2: "npm is not recognized"
**Why it happened:** 
When you ran `npm install`, Windows could not find the `npm` command. This happens because Node.js, which provides the `npm` (Node Package Manager) command, is either not installed on your computer, or hasn't fully registered in your system PATH yet. 

**Why do we need npm?**
Modern Laravel projects use a tool called Vite and Tailwind CSS for designing the frontend. `npm` is required to download all the JavaScript and CSS packages that make the website look polished. Without it, the site will look like broken HTML text.

**How to fix:** 
1. Download and install [Node.js](https://nodejs.org/en/download/). (The LTS version is recommended).
   - **Alternative (Command Line):** If you prefer using the terminal, simply open PowerShell or Command Prompt and run: `winget install OpenJS.NodeJS.LTS`

### IMPORTANT: What to do IMMEDIATELY after installing Node.js?
If you try to run `npm install` right after the installer finishes (like you just saw), **it will still fail** with the exact same error. 

**Why?**
When you open a terminal in VS Code, it captures a snapshot of your computer's "PATH" (the list of folders where commands live) at that exact moment. When you installed Node.js, it added itself to your system's PATH. However, your *currently open terminal* is still using the old snapshot from before the installation!

**Your Next Step:**
You must **close and reopen VS Code entirely** (or click the trash can icon on the terminal to kill it, and open a new one). Restarting forces the terminal to fetch the new PATH snapshot, which will finally allow it to recognize the `npm` command so you can proceed to Step 6!

### Error 3: "npm.ps1 cannot be loaded because running scripts is disabled"
**Why it happened:** 
By default, Windows PowerShell has a strict security protection feature called "Execution Policy" set to "Restricted". It restricts running custom scripts to protect you from malicious software. Because `npm` on Windows uses a PowerShell script file (`npm.ps1`) behind the scenes, Windows blocks it, thinking it is a random dangerous script.

**How to fix:**
You need to tell Windows PowerShell that it is safe to run developer tools.
1. In your VS Code terminal, paste and run this exact security bypass command:
   ```powershell
   Set-ExecutionPolicy -Scope CurrentUser -ExecutionPolicy RemoteSigned
   ```
2. If it asks you to confirm, type `Y` (for Yes) and press Enter.
*(Note: You only ever have to run this command once on your computer!)*

### Error 4: "npm error code ENOENT" / "Could not read package.json"
**Why it happened:** 
Whenever you run an `npm` command (like `npm install` or `npm run dev`), the tool actively searches for a file named `package.json` in your current folder to know what to do. 

In your terminal snippet, you ran `npm run dev` while sitting in your root user folder (`PS C:\Users\AppDev>`). Because the `package.json` file is actually stored securely inside your `AppDev2` project folder, Node.js panicked and threw an "ENOENT" (Error: NO ENTity / No File) error because it legitimately couldn't find the file.

**How to fix:**
You simply need to point your terminal to the correct folder!
1. Check the very last line of your terminal. If it says `PS C:\Users\AppDev>`, you are in the wrong place.
2. Run this "Change Directory" command to step inside your project folder: 
   ```bash
   cd .\AppDev2
   ```
3. Your terminal should now correctly update to say `PS C:\Users\AppDev\AppDev2>`. You can now safely run `npm install` or `npm run dev` and it will work perfectly!

🚨 **CRUCIAL TIP:** Always verify your terminal path before running ANY `npm` or `php artisan` commands. They will only work if your terminal is inside the project folder!

---

## Complete Step-by-Step Setup Guide

Follow these steps carefully to completely set up the project on any new local computer.

### Step 1: Install Required Software
1. **PHP (8.3+)**: Install PHP directly or via XAMPP/Laragon. 
   **Why?** Laravel is a framework written in PHP. Your computer needs PHP installed to run the backend logic.
2. **Composer**: Download and install [Composer](https://getcomposer.org/download/). 
   **Why?** Composer is the package manager for PHP. It downloads all third-party PHP code that Laravel relies on to function.
3. **Node.js**: Download and install [Node.js](https://nodejs.org/). 
   **Why?** Node.js provides `npm`, which is used to download frontend dependencies (JavaScript/CSS libraries).

### Step 2: Clone and Open Project
1. Open your terminal or VS Code.
2. Run: `git clone https://github.com/N4ksu/AppDev2.git`
   **Why?** This command downloads the exact source code repository from GitHub to your local machine.
3. Enter the folder: `cd .\AppDev2`
   **Why?** You must be inside the project folder for all subsequent commands (like `composer` or `php artisan`) to affect your project.

### Step 3: Install Backend Dependencies
```bash
composer install
```
**Why do we need to do this?** 
When you clone a project from GitHub, it does not include the massive `vendor/` folder full of third-party Laravel code (to keep the download fast). Running `composer install` reads the `composer.json` file and downloads all those missing PHP packages required to make Laravel work.

### Step 4: Configure Environment Variables
1. Create your local configuration file:
   ```bash
   copy .env.example .env
   ```
   **Why?** Secret configuration (like database passwords and API keys) is never uploaded to GitHub for security. The `.env.example` file is provided as a safe empty template. Copying it to `.env` creates your own private, local settings file for your computer.

2. Open `.env` and configure the database connection:
   ```env
   DB_CONNECTION=sqlite
   ```
   *(Delete or comment out `DB_HOST`, `DB_PORT`, `DB_DATABASE` lines).*
   **Why?** By default, Laravel might try to connect to a complex MySQL server. We are telling Laravel to use a simple file-based SQLite database instead, making local testing much easier without needing XAMPP's MySQL running.

3. Generate application key:
   ```bash
   php artisan key:generate
   ```
   **Why?** This generates a secure random cryptographic string (e.g., `APP_KEY=base64:...`) in your `.env` file. Laravel uses this key to securely encrypt user sessions, cookies, and passwords. Without it, the app will instantly crash for security reasons.

### Step 5: Setup the Database
```bash
php artisan migrate
```
*(If prompted "Would you like to create it?", type `yes` and hit Enter).*
**Why do we need to do this?** 
Right now, the database is completely empty. Running `migrate` tells Laravel to execute all the blueprint files located in the `database/migrations/` folder. This automatically creates the structured `users`, `sessions`, and `login_logs` tables in your SQLite database so the app can start storing real data.

### Step 6: Initialize Application Data (Seeding)
```bash
php artisan db:seed
```
**Why do we need to do this?** 
Even after creating the tables, the app needs initial data to work. This command runs the `database/seeders/` files which:
1. Creates the **Default Admin Account** so you can log in immediately.
2. Initializes the **Security Settings** (Max attempts, lock duration) in the database.
Without this step, you won't be able to log in or adjust security settings.

### Step 7: Install Frontend Dependencies
```bash
npm install
```
**Why do we need to do this?** 
Just like `composer install` downloads backend PHP packages, `npm install` reads the `package.json` file and downloads all the frontend CSS and JavaScript packages (like Tailwind CSS) into a new `node_modules/` folder.

### Step 8: Run the Application!
You need two terminals running simultaneously.

**Terminal 1 (Backend - PHP):**
```bash
php artisan serve
```
**Why do we do this?** PHP is a server technology. `artisan serve` boots up a tiny, built-in local web server on your computer acting as the backend engine, listening for requests on `http://localhost:8000`.

**Terminal 2 (Frontend - Build & Hot Refresh):**
```bash
npm run dev
```

**Why do we need to run this?** 
This starts **Vite**, which handles your Tailwind CSS and interactive UI components. Whenever you save a file in VS Code, Vite instantly updates the browser so you can see your changes in real-time.

---

### Step 9: Manually Creating an Admin Account (via Tinker)
If you need to create a custom administrator or if you skipped the seeding step, you can create an account directly through the command line.

1. Open your terminal in the project folder.
2. Run the interactive PHP shell:
   ```bash
   php artisan tinker
   ```
3. Paste this exact block of code and press Enter:
   ```php
   $user = \App\Models\User::firstOrCreate(
       ['email' => 'admin@example.com'],
       [
           'name' => 'Administrator',
           'password' => Hash::make('password123'),
       ]
   );
   $user->role = 'admin';
   $user->save();
   ```
4. Type `exit` and hit Enter. You can now log in at `http://localhost:8000/login` with these credentials.

---

## Development Workflow: How to Modify Code
If you want to modify the system or add new features, follow this workflow:

1. **Always keep `npm run dev` running**: This ensures your CSS changes are compiled instantly.
2. **Modify Blade Views**: Most UI changes are in `resources/views/pages/`.
3. **Modify Logic**: Backend security logic is located in:
   - `app/Listeners/RecordFailedLogin.php` (Failed attempts/Locking logic)
   - `app/Http/Middleware/EnsureAccountNotLocked.php` (Lock enforcement)
4. **Database Changes**: If you need new columns, create a new migration (`php artisan make:migration ...`) and run `php artisan migrate`.

The application is now fully running and ready for development. Visit: `http://localhost:8000`
