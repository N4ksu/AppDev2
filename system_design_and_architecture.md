1. System purpose

Build a web-based cybersecurity application that does more than normal login:

authenticates users securely
tracks every login attempt
detects repeated failed logins
temporarily locks the account after too many failures
shows login activity in a security dashboard

That makes the app a Secure Login Monitoring System, not just a login form.

2. Recommended system name

Secure Login Monitoring System

That name matches what your code is doing better than leaving it as a generic starter kit.

3. High-level architecture

Use a simple layered architecture:

Presentation Layer

This is what the user sees.

login page
register page
forgot password page
dashboard
security logs page
settings/profile page

These are mostly in resources/views/ and auth views like resources/views/pages/auth/login.blade.php.

Application Layer

This is where your main logic happens.

Fortify handles registration, login, password reset, and auth flow
FortifyServiceProvider.php checks login rules and can intercept locked users
event listeners handle failed and successful login events
Livewire logout action handles secure logout

The analysis already points to app/Providers/FortifyServiceProvider.php, app/Listeners/RecordFailedLogin.php, app/Listeners/RecordSuccessfulLogin.php, and app/Livewire/Actions/Logout.php as key files.

Data Layer

This stores users, sessions, and security logs.

users
login_logs
sessions
password_reset_tokens

Your updated overview already says the users table now includes failed_attempts, is_locked, and locked_until, while login_logs stores login attempts.

4. Architecture flow
User Browser
   ↓
Laravel Routes (web.php)
   ↓
Auth Pages / Dashboard Views
   ↓
Laravel Fortify Authentication
   ↓
FortifyServiceProvider custom login check
   ↓
If login fails → RecordFailedLogin listener
   ↓
Update users.failed_attempts + insert login_logs row
   ↓
If attempts >= 3 → set is_locked = true, locked_until = now + 15 mins

If login succeeds → RecordSuccessfulLogin listener
   ↓
Reset failed_attempts to 0 + insert success log
   ↓
Create session → redirect to dashboard

This fits your current code direction and the custom security logic you already described.

5. Core modules

Build the app around these modules:

A. Authentication Module

Purpose:

user registration
user login
user logout
forgot password

Handled mainly by Fortify and auth views. Password reset is supported by the starter kit through password_reset_tokens, but email sending still needs proper mail setup.

B. Login Monitoring Module

Purpose:

record all login attempts
store IP address
label attempt as success or failed

Main table:

login_logs
C. Account Locking Module

Purpose:

count failed attempts
lock account after 3 failures
store unlock time

Main fields in users:

failed_attempts
is_locked
locked_until
D. Security Dashboard Module

Purpose:

show total login attempts
show failed attempts
show successful logins
show locked accounts
show recent login logs

This is the module that will make your project stop looking like a plain starter kit.

E. Profile & Settings Module

Purpose:

update profile
future password change
future security preferences
6. Suggested page structure

Use this page structure:

Guest pages
/
/login
/register
/forgot-password
Protected pages
/dashboard
/security-logs
/settings/profile
/settings/security

Your current flow already uses /login and /dashboard, with dashboard protected by auth middleware.

7. Database design

Keep it small and focused.

users

Core user table.

Fields:

id
name
email
password
failed_attempts
is_locked
locked_until
created_at
updated_at
login_logs

Stores every login attempt.

Fields:

id
user_id nullable
email_attempted optional but useful
ip_address
status (success, failed, locked)
created_at
updated_at
sessions

Used by Laravel session system.

password_reset_tokens

Used for forgot password flow.

8. Recommended route-to-feature mapping
routes/web.php

Use it for:

welcome page
dashboard page
security logs page
settings page
app/Providers/FortifyServiceProvider.php

Use it for:

custom login validation
checking if account is locked before allowing login
app/Listeners/RecordFailedLogin.php

Use it for:

increment failed attempts
insert failed log
lock account after threshold
app/Listeners/RecordSuccessfulLogin.php

Use it for:

reset failed attempts
insert success log
resources/views/pages/auth/login.blade.php

Use it for:

redesigning the login page
showing account locked message
showing security notice

These file directions match your current analysis and updated overview.

9. Best UI concept for the app

Do not keep the generic starter kit look.

Use this visual direction:

Theme
dark modern interface
security-focused branding
lock/shield icon
clean typography
fewer empty placeholders
Login page

Change it into:

title: Secure Account Access
subtitle: Multiple failed attempts may temporarily lock your account
optional small note: Login attempts are monitored for account protection
Dashboard

Replace the default blocks with:

successful logins card
failed attempts card
locked accounts card
recent security activity table
Security logs page

Show:

time
email/user
IP address
status
remarks

This is the part that will make the project identity visible.

10. Recommended implementation order

Follow this order:

Phase 1 — stabilize backend
confirm users fields work
confirm login_logs inserts correctly
confirm lock logic works after 3 failed attempts
confirm successful login resets attempts
Phase 2 — make the UI match the project
redesign login page
replace dashboard placeholders
add security logs page
Phase 3 — optional features
fix forgot password using mail config
add password change page
add unlock button for admin or timed auto-unlock message

This order is close to the “safest order” already identified in your project analysis: database, listeners, lock logic, then UI.

11. MVP scope

Keep your MVP simple.

Must-have
register
login
failed login detection
temporary account lock
login logs
dashboard with security summary
Nice-to-have
forgot password
custom email alerts
suspicious IP warning
admin unlock action
12. Final architecture summary you can use in documentation

The system follows a layered web architecture composed of a presentation layer, application layer, and data layer. The presentation layer provides the login, registration, dashboard, and security pages through Laravel Blade and Livewire-based UI components. The application layer uses Laravel Fortify and custom event listeners to process authentication, record login attempts, and enforce temporary account locking. The data layer stores user accounts, login logs, sessions, and password reset records in the database, enabling secure authentication and login activity monitoring.

13. Start implementing from these files first

Open these first, in this order:

app/Providers/FortifyServiceProvider.php
app/Listeners/RecordFailedLogin.php
app/Listeners/RecordSuccessfulLogin.php
routes/web.php
resources/views/pages/auth/login.blade.php
resources/views/dashboard.blade.php