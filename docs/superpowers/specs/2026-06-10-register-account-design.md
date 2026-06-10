# Register Account — Design Spec

## Overview

Add user self-registration to the Pterodactyl Panel. Registration is admin-togglable (off by default). Users register with email, username, first name, and last name — then receive an email to set their password. The account remains inactive until the password is set, which also serves as email verification.

## Architecture

### Flow

1. Admin enables registration via a toggle in the admin settings panel
2. User visits `/auth/register`, fills in email, username, first name, last name
3. Backend creates a user record with no password and `email_verified_at = null`
4. System sends a "set your password" email using the existing password reset token infrastructure
5. User clicks link, sets password → `email_verified_at` is set, account becomes usable
6. Unverified accounts cannot log in

### New Components

| Component | Type | Purpose |
|-----------|------|---------|
| `RegisterController` | PHP Controller | Handles form submission, creates user, triggers email |
| `RegisterContainer.tsx` | React Component | Registration form UI |
| `register.ts` | API function | Calls the registration endpoint |
| Migration | Database | Adds `email_verified_at` column to users table |
| `WelcomeSetPasswordNotification` | Laravel Notification | Email with set-password link |
| Admin setting | Config | `pterodactyl:auth:registration_enabled` in settings table |

### Reused Components

- Password reset token table (`password_resets`) and `CanResetPassword` trait
- Existing email infrastructure (Laravel notifications)
- reCAPTCHA middleware (same as login)
- Throttling middleware
- Existing password reset page (`/auth/password/reset/{token}`)

## Backend

### Routes (in `routes/auth.php`)

- `GET /auth/register` — renders the auth Blade wrapper (React takes over)
- `POST /auth/register` — `RegisterController@register` (throttled, reCAPTCHA)

### RegisterController Logic

1. Check if registration is enabled (`settings.pterodactyl:auth:registration_enabled`), return 403 if not
2. Validate input: email, username, name_first, name_last (same rules as User model)
3. Create user with `password = null`, `email_verified_at = null`
4. Generate a password reset token (reuse `Password::broker()->createToken()`)
5. Send a `WelcomeSetPasswordNotification` email with the token link
6. Return success response (generic message to prevent email enumeration)

### Login Gate

- Modify `LoginController@login` to reject users where `email_verified_at` is null
- Return error: "Please verify your email address before logging in."

### Password Reset Completion Hook

- After a user successfully sets their password via the reset token, if `email_verified_at` is null, set it to `now()`
- This activates the account in the same action

### Throttling

- Reuse the existing `RecaptchaMiddleware`
- Rate limit: 3 registration attempts per IP per 10 minutes

## Frontend

### RegisterContainer.tsx

- Location: same directory as `LoginContainer.tsx`
- Fields: email, username, first name, last name
- Formik + Yup validation (same patterns as login form)
- reCAPTCHA (reuse existing `Reaptcha` integration)
- On success: display message "Check your email to set your password and activate your account"
- Link to login page: "Already have an account?"

### Route Addition (AuthenticationRouter.tsx)

- `/auth/register` → `RegisterContainer`

### LoginContainer Update

- Add "Don't have an account? Register" link
- Conditionally shown when registration is enabled
- Enabled/disabled state from `SiteConfiguration` DTO (same pattern reCAPTCHA uses)

### Styling

- Match existing auth page aesthetic (twin.macro + Tailwind, same card layout)
- Same `bg-neutral-900` body, centered card

## Database

### Migration: Add `email_verified_at` to users

```sql
ALTER TABLE users ADD COLUMN email_verified_at TIMESTAMP NULL DEFAULT NULL;
```

- Existing users: backfill `email_verified_at` with their `created_at` value (prevents lockout)
- New registrations: start with `null`

### Admin Setting

- Key: `pterodactyl:auth:registration_enabled`
- Value: `false` (default — registration disabled)
- Stored in the existing `settings` table

## Admin UI

- Simple toggle in the existing admin settings page (Blade template)
- Label: "Allow Registration"
- Description: "When enabled, users can create accounts from the login page. New accounts must verify their email before logging in."

## Email

### WelcomeSetPasswordNotification

- Extends Laravel `Notification`
- Uses token from `password_resets` table
- Links to `/auth/password/reset/{token}?email={email}` (reuses existing reset password page)
- Subject: "Welcome — Set Your Password"
- Body: brief welcome message + button/link to set password and activate account

### Password Reset Page Copy

- When a user lands on `/auth/password/reset/{token}` and their `email_verified_at` is null, the page heading and button should read "Set Your Password" instead of "Reset Your Password"
- Determined via a flag in the server-rendered config or by checking user state on token validation

## Security

| Concern | Mitigation |
|---------|-----------|
| Email/username enumeration | Generic success response always returned |
| Bot abuse | reCAPTCHA + rate limiting (3 per IP per 10 min) |
| Token security | Same expiry as password reset (60 minutes) |
| Unverified access | Login gate rejects users with null `email_verified_at` |
| Feature disabled bypass | Server-side 403 check, not just UI hiding |
| No stored password until set | User record created with `password = null` |

## Edge Cases

| Case | Behavior |
|------|----------|
| Duplicate email/username (verified account exists) | Validation error returned |
| Duplicate username (unverified account exists) | Validation error returned (unverified accounts claim their username) |
| User registers but never verifies | Account sits inactive; admin can delete manually. No automatic purge (prevents username/email recycling attacks). |
| Forgot password on unverified account | Works normally; completing it also verifies the account |
| Registration disabled mid-form | POST returns 403; user sees error |
| Token expires before use | User re-submits the registration form; backend detects the existing unverified account, regenerates the token, and resends the email |
| Re-registration with same email (unverified) | Resend verification email with a fresh token (no duplicate error) |
| Re-registration with same email (verified) | Return validation error (duplicate email) |
