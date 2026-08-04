# Plan: Add Confirm Password Field to Add Staff Account Modal

## Summary
Add a "Confirm Password" input field to the Add Staff Account modal in the admin Manage Users page, with both client-side and server-side validation to ensure passwords match.

---

## Files to Modify

### 1. `resources/views/admin/users/manage.blade.php` (View)

**Changes:**
- Add a new "Confirm Password" input field after the existing Password field (after line 356)
- Add client-side validation (JavaScript) to check passwords match on form submit
- Update the `toggleStaffPw()` function to also toggle the confirm password field visibility

**Location in file:** Lines 339-357 (password section), add new field after it.

**New field structure (matching existing style):**
```html
<div class="mb-4">
    <label class="form-label fw-semibold small" style="color:#2d7a5f;">
        Confirm Password <span class="text-danger">*</span>
    </label>
    <div class="position-relative">
        <input type="password" name="password_confirmation" id="staffPasswordConfirm"
            class="form-control"
            style="border:1.5px solid #a8e6cf;border-radius:10px;
                   font-size:13px;padding-right:40px;"
            placeholder="Re-enter password" required>
        <button type="button"
            style="position:absolute;right:12px;top:50%;
                   transform:translateY(-50%);background:none;
                   border:none;color:#888;cursor:pointer;padding:0;"
            onclick="toggleStaffPwConfirm()">
            <i class="bi bi-eye" id="staffPwConfirmIcon"></i>
        </button>
    </div>
</div>
```

**JavaScript additions:**
- Add `toggleStaffPwConfirm()` function (similar to existing `toggleStaffPw()`)
- Add form submit validation to check passwords match before submission

### 2. `app/Http/Controllers/AdminController.php` (Backend)

**Changes:**
- Add `password_confirmation` field to validation rules in `storeUser()` method
- Use Laravel's `confirmed` validation rule

**Location:** Line 97 (password validation rule)

**Updated validation:**
```php
$request->validate([
    'name'       => 'required|string|max:255',
    'email'      => 'required|email|unique:users,email',
    'phone'      => 'nullable|string|max:20',
    'password'   => 'required|string|min:6|confirmed',
    'staff_role' => 'required|in:sra,lra,job_fair,job_vacancy',
]);
```

---

## What Won't Break

- **Update User modal**: No changes needed - it's optional password change, no confirm needed
- **Admin profile password change**: Already has confirm password (`new_password_confirmation`)
- **Admin registration**: Already has confirm password
- **Other modals (View Requirements)**: Unrelated, no changes
- **Tab filtering/pagination**: JavaScript logic untouched
- **Staff role selection**: Unaffected
- **Existing form fields**: All stay the same

---

## Verification Steps

1. Open Admin > Manage Users page
2. Click "Add Staff Account" button
3. Verify the Confirm Password field appears below the Password field
4. Test eye icon toggle works on both password fields
5. Try submitting with mismatched passwords - should show validation error
6. Try submitting with matching passwords - should create account successfully
7. Verify existing modals (Update, View Requirements) still work
8. Verify tab filtering and pagination still work
