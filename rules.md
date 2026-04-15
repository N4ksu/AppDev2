# Antigravity Project Rules

## Project Identity
This project is a **Secure Login Monitoring System** built from a Laravel Livewire Starter Kit and customized into a cybersecurity-focused web application.

Its purpose is not just to provide login and registration. Its purpose is to:
- detect repeated failed login attempts
- temporarily lock accounts after a threshold
- record login activity
- present a security-focused dashboard and logs

Always preserve that purpose in every code, UI, and architectural decision.

---

## Primary Stack
Use and respect the current stack already present in the codebase:

- Laravel
- Livewire
- Fortify
- Blade
- Flux UI
- Tailwind CSS
- Vite
- SQLite for current development unless explicitly changed

Do not introduce a new framework, package, or architecture unless clearly necessary and justified.

---

## Core Development Principles

### 1. Preserve the Existing Auth Foundation
Do not rewrite the starter kit authentication from scratch.

Always extend the existing Laravel Fortify authentication flow instead of replacing it.

Keep the project stable by building on:
- `app/Providers/FortifyServiceProvider.php`
- Fortify actions
- Livewire actions
- existing auth views
- Laravel auth events and listeners

### 2. Keep the Project Simple
This is a student project with a focused cybersecurity specialization.

Prefer the simplest stable implementation that is:
- easy to understand
- easy to explain in defense
- realistic to finish on time
- aligned with the current codebase

Avoid overengineering.

### 3. Do Not Break Existing Features
Before making major changes:
- inspect related files first
- understand the current flow
- preserve working login, registration, logout, and settings behavior

Do not remove or break working features unless explicitly instructed.

### 4. Follow the Existing Architecture
Respect the current Laravel structure.

Use:
- routes in `routes/web.php`
- business/auth logic in providers, actions, listeners, or services
- database changes through migrations
- data access through Eloquent models
- UI changes in Blade/Livewire views

Do not place logic in random files.

### 5. Keep Security Logic in the Backend
Security-related features must be enforced in backend logic, not only in the UI.

Examples:
- failed attempt counting
- account locking
- unlock timing
- login logging
- validation rules

The UI should only display status and feedback.

### 6. Use Small, Safe Changes
Work in small increments.

For every task:
1. inspect the related files
2. explain what will be changed
3. implement the smallest safe version
4. verify it works
5. summarize the exact changes made

Do not do broad uncontrolled refactors.

### 7. Use Clear Naming
Use simple and descriptive names for:
- models
- listeners
- migrations
- routes
- page titles
- dashboard cards
- variables and methods

Names should match the project identity.

Good examples:
- `LoginLog`
- `RecordFailedLogin`
- `RecordSuccessfulLogin`
- `Security Dashboard`
- `Failed Login Attempts`

### 8. Maintain UI Consistency
The UI should no longer feel like a generic starter kit.

Every design change should move the app toward a:
- modern
- dark
- clean
- professional
- cybersecurity-focused look

Do not use random colors, layouts, or unrelated styles.

### 9. Prioritize the Visible Project Identity
Every page should support the project purpose.

The app should clearly feel like a **Secure Login Monitoring System**, not a default Laravel demo.

Prioritize these visible improvements:
- security-focused login page
- meaningful dashboard
- login activity logs page
- security status messages
- clear labels and headings

### 10. Always Explain Before and After
For every requested implementation:
- first identify the exact files involved
- explain why each file matters
- then implement
- then summarize what changed and why

Do not give vague answers.

---

## File-Level Rules

### Routes
Use `routes/web.php` for page routing.
Do not add unnecessary route clutter.

### Database
All schema changes must go through migrations.
Never manually edit the database as the final solution.

### Models
Use Eloquent models for database interaction where appropriate.
Keep model responsibilities clear.

### Views
Edit Blade views cleanly.
Do not mix heavy backend logic into the templates.

### Listeners / Events
Use Laravel auth events and listeners for login attempt tracking and reset logic whenever possible.

### Providers
Use `FortifyServiceProvider.php` carefully for authentication checks such as locked-account interception.

---

## UI/UX Rules

### Login Page
The login page must:
- clearly communicate secure access
- display a security-focused heading
- mention monitoring or account protection
- keep the form clean and easy to use

### Dashboard
The dashboard must not remain generic.

It should become a **Security Dashboard** with meaningful content such as:
- successful logins
- failed login attempts
- locked accounts
- recent login activity

### Settings
Settings pages should remain functional and visually consistent with the rest of the app.

---

## Coding Rules

- Do not rewrite entire files unless necessary
- Do not duplicate logic
- Reuse existing Laravel/Fortify features first
- Keep code readable and beginner-friendly
- Prefer maintainable solutions over clever solutions
- Add comments only where they truly help understanding
- Do not leave placeholder logic without clearly marking it
- Do not claim a feature works unless it has been verified

---

## Testing and Verification Rules
After implementing a feature, verify it through realistic flows.

Examples:
- successful login resets failed attempts
- failed login increments attempts
- lock triggers after threshold
- locked user cannot log in
- log records are inserted properly
- dashboard reflects actual values

Always mention what was tested and what still needs testing.

---

## Priority Features
The most important features of this project are:

1. secure authentication
2. failed login detection
3. temporary account locking
4. login activity logging
5. security-focused dashboard
6. improved cybersecurity-themed UI

Lower-priority features should not delay the core system.

---

## Features to Avoid Unless Requested
Do not add these unless explicitly approved:
- complex admin role systems
- advanced OTP flows
- third-party security packages not needed by the project
- unnecessary API integrations
- heavy frontend rewrites
- unrelated dashboard widgets
- broad architecture rewrites

---

## Response Format for Every Task
For every implementation request, follow this format:

1. Goal
2. Files to inspect/edit
3. Planned change
4. Step-by-step implementation
5. Risks or things to preserve
6. Summary of completed changes

---

## Final Rule
Always optimize for:
- clarity
- stability
- cybersecurity relevance
- fast progress
- easy explanation in a student defense