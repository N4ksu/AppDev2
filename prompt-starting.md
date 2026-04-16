Analyze this entire codebase as a Laravel Livewire Starter Kit project and produce a complete beginner-friendly technical walkthrough of the system.

Your task:
1. Inspect the whole repository deeply.
2. Understand the app structure, dependencies, authentication flow, database usage, Livewire components, routing, middleware, layout structure, assets, and configuration.
3. Do not assume anything. Base everything on the actual files in this codebase.
4. Explain everything in a clear, humanized, step-by-step way, as if teaching a student who did not originally write the code.

What I need from you:

A. PROJECT OVERVIEW
- Explain what this project is
- State the main framework, libraries, and architecture used
- Identify whether it uses Laravel, Livewire, Blade, Tailwind, Alpine, Vite, MySQL, etc.
- Explain the role of each major technology found in the repo

B. FOLDER AND FILE BREAKDOWN
Give a structured explanation of the important folders and files, such as:
- app/
- app/Livewire or app/Http
- bootstrap/
- config/
- database/
- public/
- resources/
- resources/views/
- routes/
- storage/
- tests/
- composer.json
- package.json
- vite.config.*
- .env.example
- artisan
For each important file/folder, explain:
- what it does
- why it matters
- when I need to edit it

C. HOW TO SET UP AND RUN THE PROJECT
Provide the exact step-by-step setup guide for this codebase from zero on Windows using VS Code/XAMPP if applicable.
Include:
- required software
- PHP version needed
- Composer requirement
- Node.js / npm requirement
- database requirement
- exact commands to run
- how to copy .env
- how to generate app key
- how to configure database
- how to run migrations
- how to install frontend assets
- how to start the Laravel server
- how to start Vite if needed
- how to verify the project is working
- common setup errors and how to fix them

D. AUTHENTICATION FLOW ANALYSIS
Trace the full authentication system in this codebase.
Explain:
- where registration is handled
- where login is handled
- where logout is handled
- where password reset is handled
- what middleware protects routes
- what files control auth behavior
- how the user session works
- what happens after successful login
- what database tables are used for users/auth
Show the actual file paths involved.

E. LIVEWIRE ANALYSIS
Identify all Livewire components in this repo and explain:
- component name
- file path
- what it does
- where it is rendered
- what state/actions it handles
- whether it is important for authentication or dashboard flow

F. ROUTES AND PAGE FLOW
Map the user navigation flow:
- guest pages
- auth pages
- dashboard or home after login
- protected pages
- middleware used
Explain clearly what page loads first and what happens next.

G. DATABASE ANALYSIS
Inspect migrations and models and explain:
- existing tables
- table purpose
- key columns
- relationships if any
- what tables are already ready for use
- what table I may need to add for my project idea

H. SECURITY ANALYSIS
Review the codebase and explain the built-in security features already present, such as:
- password hashing
- CSRF protection
- validation
- middleware
- session handling
- email verification if present
- rate limiting / throttling if present
Then identify what is missing if I want to turn this into a cybersecurity-focused project with:
- failed login attempt tracking
- account locking after repeated failed attempts
- login activity logs
- suspicious login monitoring

I. HOW TO EXTEND THIS FOR MY PROJECT
My planned project is:
“Secure Login System with Failed Attempt Detection and Account Locking”

Based on the current codebase, explain:
- what parts are already useful
- what new database tables/columns I should add
- what files I will most likely edit first
- what backend logic I need to add
- what UI pages/components I may need to update
- what the safest implementation order is

J. PRIORITY ACTION PLAN
After analyzing everything, give me:
1. a beginner-friendly setup checklist
2. a “learn this codebase first” checklist
3. a “first edits to make” checklist
4. a “features to add for my project” checklist

K. IMPORTANT RULES
- Do not give vague summaries
- Do not skip file paths
- Do not only explain theory
- Use the actual repository structure
- Be specific and practical
- Tell me exactly where to look
- Mention actual filenames, classes, routes, and components
- Explain in a humanized and easy-to-understand way
- Separate your answer by headings
- At the end, provide a concise summary of the most important files I should study first

Final output format:
1. Project overview
2. Setup guide
3. Folder/file explanation
4. Auth flow
5. Livewire/component analysis
6. Route/page flow
7. Database analysis
8. Security analysis
9. How to adapt it to my project
10. Priority action plan
11. Most important files to study first




STEP 2 

Now turn your analysis into a practical implementation guide for me.

Based on the codebase you just analyzed, give me:
- the exact first 10 tasks I should do in order
- the exact files I should open first
- what database changes I should make for failed login attempt detection and account locking
- what logic already exists that I can reuse
- what code should be added carefully without breaking the starter kit auth flow

Make the answer simple, step-by-step, and beginner-friendly.
Do not rewrite the whole app.
Focus only on the safest path to convert this starter kit into:
“Secure Login System with Failed Attempt Detection and Account Locking.”


STEP 3 
And use this third prompt if you want Antigravity to inspect security gaps only:

Audit this Laravel Livewire Starter Kit codebase specifically for authentication and login security.

Check:
- login flow
- registration flow
- validation
- session handling
- CSRF protection
- password hashing
- middleware
- throttling / rate limiting
- password reset flow
- route protection
- redirect behavior after login/logout

Then tell me:
1. what security is already good
2. what is weak or missing
3. what I need to add for failed login attempt detection
4. how to implement account locking in the safest Laravel way for this codebase
5. what files and database changes are needed

Use actual file paths and code references from the repo.
Explain simply and clearly.