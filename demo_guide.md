# Live Demo Guide: Secure Authentication System

This guide outlines the perfect 5-minute demonstration sequence to prove the value and security of your system during your defense.

---

## The Storyline
"Our system doesn't just manage users; it actively protects them. We will demonstrate a live 'brute-force' attack, see how the system automatically defends the account, and how administrators maintain total oversight through the Audit Trail."

---

## STEP 1: The Setup (Registration)
**Goal**: Show the system's intelligent onboarding.
1.  **Open browser** to the Register page.
2.  **Action**: Register a brand new account.
3.  **Speaking Note**: *"Because this is a fresh deployment, our system uses the 'First User is Admin' pattern. This ensures that the very first person to touch the system is granted administrative oversight without needing manual terminal commands."*
4.  **Show**: The Dashboard with the sidebar links: *Security Dashboard, Security Logs, Lockdown Configuration, Locked Accounts.*

---

## STEP 2: The Attack (Failed Logins)
**Goal**: Show automated threat detection.
1.  **Logout** and go to the Login page.
2.  **Action**: Attempt to log in with a known email but the **wrong password** 3 times (or whatever your threshold is).
3.  **Observe**: Watch the error messages change. You will eventually see: *"Multiple failed attempts trigger a security lock..."*
4.  **Speaking Note**: *"Notice that as I fail, the system isn't just saying 'wrong password'. It's warning me that my activity is being recorded. Once I hit the threshold, the account is automatically locked to prevent automated brute-force scripts."*

---

## STEP 3: The User View (Personal Security)
**Goal**: Show user-facing metrics.
1.  **Action**: Log in with your **Admin account** (which is still active).
2.  **Show**: The **Security Dashboard**.
3.  **Speaking Note**: *"Every user gets a personalized Security Dashboard. I can see my 'Successful Logins' vs 'Failed Attempts' at a glance. Hovering over the status icons shows me help tooltips explaining that this data is my personal 'Account Status' based on system records."*
4.  **Show**: **Security Logs** (Personal View).
5.  **Speaking Note**: *"Users can verify their own access history. If I see a login from an IP I don't recognize, I know my account may be compromised."*

---

## STEP 4: The Audit (Admin Oversight)
**Goal**: Show professional system auditing.
1.  **Navigate** to: **Security Administration > Full Security Audit Trail**.
2.  **Show**: The list containing the failed attempts you just made in Step 2.
3.  **Speaking Note**: *"This is where the system shines. As an admin, I see the 'Full Security Audit Trail'. I can see the exact timestamp, the IP address, and whether the attempt was a 'Guest' (non-existent email) or a 'Registered' account. This is essential for forensic investigation after a security incident."*

---

## STEP 5: The Control (Configuration & Restoration)
**Goal**: Show administrative command.
1.  **Navigate** to: **Lockdown Configuration**.
2.  **Action**: Briefly point at the thresholds.
3.  **Navigate** to: **Locked Accounts**.
4.  **Action**: Find the account you locked in Step 2 and click **Restore Access**.
5.  **Speaking Note**: *"Finally, admins have the power to manage these restrictions. I can see who is currently 'Temporalized' (locked) and I can manually restore their access if they've verified their identity, overriding the automated timer."*

---

## Summary for the Teacher
- **Automation**: First User is Admin.
- **Prevention**: Automatic locking after X fails.
- **Auditing**: Full system-wide logs with IP tracking.
- **Management**: Admin UI to config and unblock users.
