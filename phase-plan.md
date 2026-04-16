# Secure Login Monitoring System - Step-by-Step Phase Plan

## Project Goal
Build a cybersecurity-focused web application that secures user authentication through:
- failed login detection
- temporary account locking
- login activity logging
- a visible security dashboard

The system must be simple, functional, and easy to explain.

---

## Phase 1 - Stabilize the Existing Starter Kit

### Objective
Make sure the current Laravel Livewire Starter Kit is running correctly before adding more features.

### Tasks
1. confirm the project runs with `php artisan serve`
2. confirm Vite runs with `npm run dev`
3. confirm database connection works
4. confirm register, login, logout, and settings pages load
5. confirm migrations are applied successfully

### Deliverable
A stable working base project with no setup errors.

### Success Check
- login page loads
- registration works
- dashboard loads after login
- no migration or runtime errors

---

## Phase 2 - Confirm Core Cybersecurity Logic

### Objective
Verify that the project’s main security behavior is implemented and working.

### Tasks
1. inspect `app/Providers/FortifyServiceProvider.php`
2. inspect `app/Listeners/RecordFailedLogin.php`
3. inspect `app/Listeners/RecordSuccessfulLogin.php`
4. inspect `app/Models/LoginLog.php`
5. inspect user migration changes for:
   - `failed_attempts`
   - `is_locked`
   - `locked_until`
6. test the failed login flow
7. test the successful login reset flow
8. test the account lock behavior

### Deliverable
Confirmed core security logic.

### Success Check
- failed attempts increment correctly
- successful login resets attempts
- account locks after threshold
- locked user is blocked from login
- login log records are created

---

## Phase 3 - Define the Visible App Purpose and Identity

### Objective
Transform the project from a generic Laravel starter kit into a clearly branded **Secure Login Monitoring System**.

### Why This Phase Matters
At this stage, the backend security logic may already exist, but the system can still look like a default template. This phase ensures that the app’s purpose is immediately visible to the user, the professor, and the panel.

### Tasks
1. review all visible headings, labels, descriptions, and navigation text
2. replace generic wording with cybersecurity-focused language
3. update the application identity from a default starter kit into a custom secure login monitoring system
4. define the main system terminology consistently across the app:
   - Secure Login Monitoring System
   - Security Dashboard
   - Login Activity Logs
   - Account Status
   - Failed Login Attempts
5. update the login page title, subtitle, and helper text
6. update the dashboard heading and introductory text
7. review the settings/profile pages and remove wording that feels too generic
8. ensure the project branding is simple, professional, and aligned with cybersecurity
9. decide on a consistent visual tone:
   - dark modern interface
   - minimal and professional
   - subtle security visual style
   - no overly flashy effects

### Files to Review
- `resources/views/pages/auth/login.blade.php`
- `resources/views/dashboard.blade.php`
- settings-related view files
- navigation layout files
- app name/config if needed

### Deliverable
A project that clearly communicates its purpose as a cybersecurity-focused system rather than a starter kit.

### Success Check
- the app name and page labels feel custom
- a user can immediately tell the system is about secure login and monitoring
- the interface language is consistent across pages
- no major page still feels generic or placeholder-like

---

## Phase 4 - Redesign the Login Page

### Objective
Replace the default login page with a polished, cybersecurity-themed login interface that reflects the system’s purpose without breaking the authentication flow.

### Why This Phase Matters
The login page is the first impression of the system. Since the core specialization of the project is secure authentication, this page must feel intentional, professional, and clearly security-focused.

### Tasks
1. inspect the current login page structure and identify reusable components
2. keep the existing login functionality intact
3. redesign the layout to feel more polished and modern
4. replace the generic heading with a security-focused heading such as:
   - Secure Account Access
   - Sign In to the Secure Login Monitoring System
5. add a short, clear security message such as:
   - Multiple failed attempts may temporarily lock your account
   - Login activity is monitored for account protection
6. improve:
   - spacing
   - typography
   - card layout
   - button styling
   - input field hierarchy
7. add subtle security-themed visual cues:
   - shield or lock icon
   - soft glow or panel emphasis
   - dark modern styling
8. make locked-account messages more visible and professional
9. ensure the design remains responsive and clean
10. review the register page and make it visually consistent with the new login page

### Files to Review
- `resources/views/pages/auth/login.blade.php`
- `resources/views/pages/auth/register.blade.php`
- shared auth layout files if applicable

### Deliverable
A custom login page that no longer feels like the default starter kit and clearly represents the project’s cybersecurity focus.

### Success Check
- the login page looks custom and polished
- the login page communicates the security purpose clearly
- the page remains responsive and usable
- the authentication flow still works correctly
- the register page visually matches the updated login design

---

## Phase 5 - Build the Security Dashboard

### Objective
Replace the default dashboard placeholder with a meaningful **Security Dashboard** that displays real security-related information from the system.

### Why This Phase Matters
A strong specialization project must show visible evidence of its unique function. The dashboard should prove that the app is not just a login form, but a monitoring system that tracks and presents security activity.

### Tasks
1. inspect the current dashboard layout and remove placeholder content
2. redesign the page into a dedicated Security Dashboard
3. add a dashboard heading and short description explaining the purpose of the page
4. create summary cards for key metrics such as:
   - successful logins
   - failed login attempts
   - locked accounts
   - total login log entries
5. add a recent login activity section
6. display a table or list showing recent login records with:
   - email or user
   - IP address
   - status
   - date/time
7. add a simple account security status area showing:
   - current account lock status
   - failed attempt count
   - lock expiration if applicable
8. make the dashboard visually consistent with the cybersecurity theme
9. keep the layout readable, responsive, and not overloaded
10. ensure all displayed data comes from actual system records

### Files to Review
- `resources/views/dashboard.blade.php`
- `app/Models/LoginLog.php`
- controller, Livewire, or view data source used for dashboard metrics
- `routes/web.php` if route/data adjustments are needed

### Deliverable
A fully customized Security Dashboard showing meaningful, project-related information.

### Success Check
- the dashboard no longer contains default placeholder content
- the dashboard shows real login/security information
- the page visually supports the cybersecurity project identity
- the dashboard helps explain the project during demo or defense


---

## Phase 6 - Add Security Logs Page

### Objective
Create a dedicated page for viewing login activity.

### Tasks
1. add a route for `/security-logs`
2. create a Blade or Livewire page for logs
3. fetch records from `login_logs`
4. display:
   - user/email
   - IP address
   - status
   - timestamp
5. optionally add simple filters later

### Deliverable
A working login activity logs page.

### Success Check
Recent login attempts can be viewed clearly in the UI.

---

## Phase 7 - Improve Navigation and Page Structure

### Objective
Make the app easier to explore and more complete as a system.

### Tasks
1. update sidebar or navigation labels
2. add navigation entry for Security Logs
3. ensure dashboard, settings, and logs are easy to access
4. keep route protection correct with auth middleware

### Deliverable
A cleaner and more usable app flow.

### Success Check
Users can navigate between core pages without confusion.

---

## Phase 8 - Implement Forgot Password Properly

### Objective
Complete the required full authentication cycle by making the forgot password and reset password flow functional, stable, and demo-ready.

### Why This Phase Matters
The project must support a full auth cycle, so forgot password cannot remain broken or hidden. This phase ensures that users can request a password reset, receive a valid reset link, and successfully create a new password.

### Tasks
1. inspect the current forgot password and reset password flow in the existing Laravel Fortify setup
2. confirm that the required routes, views, and actions already exist
3. verify that `.env` mail settings are correctly configured for Mailtrap
4. test whether a password reset token is generated correctly
5. test whether the reset email is sent successfully to Mailtrap
6. open the reset link from Mailtrap and verify the token works
7. confirm that the user can submit and save a new password
8. verify that the user can log in successfully using the new password
9. fix any validation, redirect, or mail-related issue without rewriting the authentication system
10. keep the reset flow clean, stable, and easy to demonstrate

### Files to Review
- `app/Providers/FortifyServiceProvider.php`
- forgot password and reset password views
- `routes/web.php` if relevant
- Fortify-related auth files
- `.env`
- mail configuration files if needed

### Deliverable
A working forgot password feature that completes the system’s full authentication cycle.

### Success Check
- the forgot password link works
- reset email is sent successfully through Mailtrap
- reset token is valid
- user can set a new password
- user can log in with the new password
- no broken password reset flow appears in the final demo

---

## Phase 9 - Improve Settings and Security Messaging

### Objective
Refine the settings area so it feels like part of the Secure Login Monitoring System instead of a leftover default account page.

### Why This Phase Matters
Even if the core login and dashboard are improved, the project can still feel unfinished if the settings area remains generic. This phase makes the system more complete and consistent.

### Tasks
1. inspect the current settings/profile pages
2. update page titles, descriptions, and labels to better match the project identity
3. improve the visual styling so settings pages align with the login page and dashboard
4. review the profile section and keep only what is useful and relevant
5. add or improve a simple security information section showing:
   - account status
   - failed attempt count
   - current lock status
   - lock expiration if locked
6. review password-related settings and present them more clearly
7. keep all sensitive actions functional and stable
8. make the settings area easier to understand for demo purposes
9. ensure the language stays professional, simple, and cybersecurity-focused

### Files to Review
- settings/profile view files
- password/settings views
- shared layout/navigation files
- any data provider used to show user/account status

### Deliverable
A cleaner, more relevant settings area that feels fully integrated into the Secure Login Monitoring System.

### Success Check
- settings pages visually match the rest of the app
- security-related information is visible and useful
- the page no longer feels like a default starter-kit leftover
- profile and password features remain stable and understandable

---

## MVP Definition

### Must Have
- login
- registration
- failed login detection
- temporary account locking
- login logs
- security dashboard
- custom login UI

### Should Have
- security logs page
- better navigation
- improved settings theme

### Optional
- forgot password fully working
- account unlock tools
- advanced filters for logs
- suspicious login alerts

---

## Suggested Working Order

### Immediate Next Steps
1. confirm core lock logic works
2. redesign login page
3. redesign dashboard
4. add security logs page
5. fix navigation
6. decide forgot password scope
7. polish and test

---

## Key Files by Phase

### Core Logic
- `app/Providers/FortifyServiceProvider.php`
- `app/Listeners/RecordFailedLogin.php`
- `app/Listeners/RecordSuccessfulLogin.php`
- `app/Models/LoginLog.php`

### Database
- user migration with lock fields
- login logs migration

### UI
- `resources/views/pages/auth/login.blade.php`
- `resources/views/dashboard.blade.php`
- settings views
- logs page view

### Routing
- `routes/web.php`

---

## Final Outcome
At the end of this phase plan, the project should no longer feel like a default starter kit.

It should feel like a complete **Secure Login Monitoring System** with:
- a custom cybersecurity-themed interface
- visible security monitoring features
- stable account protection logic
- a clear specialization for defense and presentation