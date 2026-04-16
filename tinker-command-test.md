# Tinker Guide: Secure Login Monitoring System

Use this guide to verify the system or reset data for your demo.

### 1. Accessing Tinker
In your terminal, run:
```bash
php artisan tinker
```

---

### 2. User Management & Unlocking
**Check status of a specific user:**
```php
App\Models\User::where('email', 'doy@gmail.com')->first(['id', 'email', 'failed_attempts', 'is_locked', 'locked_until']);
```

**Immediately UNLOCK a specific user:**
```php
App\Models\User::where('email', 'doy@gmail.com')->update([
    'is_locked'       => false,
    'failed_attempts' => 0,
    'locked_until'    => null,
]);
```

**Unlock ALL users (Standard reset for demo):**
```php
App\Models\User::where('is_locked', true)->update([
    'is_locked'       => false,
    'failed_attempts' => 0,
    'locked_until'    => null,
]);
```

---

### 3. Monitoring Login Logs
**View the 5 most recent logs:**
```php
App\Models\LoginLog::with('user')->latest()->take(5)->get(['id', 'user_id', 'status', 'ip_address', 'created_at']);
```

**View logs for a specific email:**
```php
App\Models\LoginLog::whereHas('user', fn($q) => $q->where('email', 'doy@gmail.com'))->latest()->get();
```

---

### 4. Data Cleanup (Demo Ready)
**Wipe all login activity for a clean demo start:**
```php
App\Models\LoginLog::truncate();
```

**Remove only orphan logs (failed attempts with no user account):**
```php
App\Models\LoginLog::whereNull('user_id')->delete();
```

---

### 5. Timezone Verification
**Check what time the server currently thinks it is:**
```php
now();                    // Should show Asia/Manila time
now()->timezoneName;      // Should return "Asia/Manila"
```

### 6. Quick Exit
To close tinker, type:
```php
exit
```
or press `Ctrl + C`.
