Secure Login Monitoring System (SLMS) — System Overview
1. Project Overview

The Secure Login Monitoring System (SLMS) is a cybersecurity-focused web application designed to strengthen user authentication by monitoring login activity, detecting repeated failed login attempts, and protecting accounts through temporary account locking. Unlike a basic login system that only checks whether credentials are correct, SLMS adds an active security layer that records authentication events, applies lock policies, and provides visibility into suspicious access behavior.

The system is intended for websites, portals, and database-backed applications that require safer account access and better login monitoring. It addresses the common problem of weak authentication oversight by giving both users and administrators clear visibility into login activity, failed attempts, and account restriction events. In this way, the project demonstrates cybersecurity specialization through a practical, working web application rather than a simple login form alone.

2. System Purpose

The main purpose of SLMS is to protect accounts from repeated suspicious login attempts while giving administrators a clear audit trail of authentication activity. It is designed to make login security more visible, more manageable, and more responsive within the system itself.

3. Problem Addressed

Many web applications rely on standard login forms that only validate credentials without actively monitoring suspicious behavior. This creates a security gap where repeated failed login attempts, brute-force behavior, and non-existent account targeting may go unnoticed or be handled only at a very basic level. SLMS addresses this problem by tracking failed login attempts, temporarily locking accounts after a configurable threshold, recording login activity, and allowing administrators to review and manage security events from within the application.

4. Core Features
Secure user registration, login, logout, and password reset
Failed login attempt detection
Temporary account locking after a configurable number of failed attempts
Automatic unlock after the configured lock duration
Manual admin unlock for restricted accounts
User-scoped personal security activity logs
Admin-only full security audit trail
Configurable security settings, including maximum failed attempts and lock duration
Security dashboard for monitoring login activity and account restriction events
5. User Roles
A. Standard User

A standard user can:

register an account
log in and log out
reset their password
view their own security activity
monitor their own failed login attempts and account protection events
B. System Administrator

A system administrator can:

view the full security audit trail
monitor locked accounts
manually unlock restricted accounts
configure lock policies such as maximum failed attempts and lock duration
review suspicious login activity across the system
6. Authentication and Security Flow
A user submits credentials through the login form.
The system checks whether the account is currently locked.
If the login attempt fails, the system records the event in the audit log and increments the failed attempt counter for the account.
If the failed attempts reach the configured threshold, the account is temporarily locked until the specified lock duration expires.
If the login succeeds, the system resets the failed attempt counter and records a successful login event.
If the lock period has already expired, the account is automatically treated as unlocked.
Administrators may also manually unlock restricted accounts through the admin interface.
All relevant authentication activity is stored in the security logs for monitoring and review.
7. System Architecture

SLMS follows a simple layered web architecture:

Presentation Layer

This layer contains the user interface of the system, including the login page, dashboards, logs pages, and administrative controls. It is built using Blade views, Flux UI components, and Livewire-based interactivity.

Application Logic Layer

This layer handles the main system behavior, including authentication, failed login tracking, lock enforcement, automatic unlock logic, admin unlock actions, and security settings management. Laravel Fortify provides the authentication foundation, while custom listeners and middleware enforce the security logic.

Data Layer

This layer stores users, login activity logs, security settings, password reset data, and session-related information in the database. It ensures that account status and audit records are persistent and queryable.

8. Tech Stack
Backend Framework: Laravel
Frontend / UI: Livewire, Blade, Flux UI, Tailwind CSS
Authentication: Laravel Fortify
Database: SQLite
ORM: Eloquent
Build Tool: Vite
9. Database Overview

The main database structures used in the system include:

users – stores account information and security-related fields such as failed attempts, lock status, lock expiration, and role
login_logs – stores authentication events such as successful logins, failed attempts, and lock-related activity
security_settings – stores configurable security policy values such as maximum failed attempts and lock duration
password_reset_tokens – supports the password reset process
sessions – tracks authenticated sessions when applicable
10. Admin Security Management

The administrator serves as the monitoring and control role of the system. Through the admin side, the administrator can:

review the complete audit trail
identify suspicious login behavior
view all currently locked accounts
manually unlock accounts when necessary
configure system-wide security rules without changing the code

This makes the system more realistic and manageable compared to a purely hardcoded authentication prototype.

11. Why This Project Fits Cybersecurity Specialization

This project fits the cybersecurity specialization because security is central to the system’s purpose, not just an added feature. SLMS focuses on authentication protection, suspicious login monitoring, account restriction, audit logging, and administrator-controlled security policies. It goes beyond a standard login form by actively enforcing account protection rules and making security events visible through dashboards and logs. This directly aligns with the project brief’s requirement for a focused system that clearly demonstrates a technical specialization.

12. Short Defense Version

The Secure Login Monitoring System is a cybersecurity-focused web application that protects user accounts by tracking failed login attempts, temporarily locking suspicious accounts, recording login activity, and allowing administrators to manage locked accounts and security settings. I built it to show that authentication can be made more secure, more visible, and more manageable through a practical web-based system.

13. Documentation-Ready Final Version

The Secure Login Monitoring System (SLMS) is a cybersecurity-focused web application designed to improve authentication security by monitoring login activity, detecting repeated failed login attempts, and temporarily locking accounts when suspicious behavior is detected. Unlike a basic login system that only checks credentials, SLMS adds an active security layer through audit logging, lock enforcement, automatic unlock handling, and administrator-managed security policies. The system supports both standard users and administrators, with normal users limited to their own security activity and administrators given access to system-wide monitoring, locked account management, and configurable security rules. By combining authentication, monitoring, account protection, and audit visibility in one focused application, the project demonstrates cybersecurity specialization through a practical and working web-based system.