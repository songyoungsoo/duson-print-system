# Member (Person) System Test Plan

> **Created**: 2026-03-04
> **Project**: Duson Planning Print System
> **Agent**: playwright-test-planner

---

## 1. Authentication & Session Management
**Seed**: `tests/seed.spec.ts`

### 1.1 Secure Login Flow
**Steps**:
1. Navigate to `/member/login.php`
2. Enter valid test credentials.
3. Click the "Login" button.
4. **Expected Outcome**: Page redirects to home or dashboard, and a session cookie is set.

### 1.2 Failed Login (Negative Case)
**Steps**:
1. Navigate to `/member/login.php`
2. Enter an unregistered ID or incorrect password.
3. Click the "Login" button.
4. **Expected Outcome**: An alert or error message says "ID/Password mismatch".

---

## 2. Registration & Identity
**Seed**: `tests/seed.spec.ts`

### 2.1 Registration Validation & Duplication Check
**Steps**:
1. Navigate to `/member/join.php`
2. Input a pre-existing user ID and click "ID Check" (아이디 중복확인).
3. Verify that the system indicates the ID is unavailable.
4. Fill in the form but leave "Name" (이름) empty.
5. Click "Register" (회원가입).
6. **Expected Outcome**: A validation error highlights the mandatory Name field.

### 2.2 Password Reset Request
**Steps**:
1. Navigate to `/member/password_reset_request.php`
2. Enter a valid registered email address.
3. Click "Send Reset Link".
4. **Expected Outcome**: A success message confirms the email has been sent.

---

## 3. Profile Management (Post-Auth)
**Seed**: `tests/seed.spec.ts`

### 3.1 Edit User Information
**Steps**:
1. Authenticate as a test user.
2. Navigate to `/member/form.php` (Profile page).
3. Change the "Phone Number" or "Address".
4. Click "Update Info".
5. **Expected Outcome**: Success message appears, and the new data persists on page reload.
