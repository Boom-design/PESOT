{{-- Data Privacy Notice (Republic Act No. 10173), shown before anyone registers.
     Wording from PESO CDO's own applicant registration form, 2026-09-17.
     Expects: $audience — 'jobseeker' or 'employer'. --}}
@php $isEmployer = ($audience ?? 'jobseeker') === 'employer'; @endphp
<div class="cert-box mb-3" id="privacyNotice" style="border:1px solid var(--n-200);border-radius:12px;padding:13px;transition:border-color .2s, box-shadow .2s;">
    <div class="section-heading" style="margin-bottom:6px;">
        <i class="ph ph-shield-check me-2"></i>Data Privacy Notice *
    </div>
    <div style="font-size:11.5px;color:rgba(255,255,255,0.85);line-height:1.6;">
        <p class="mb-2">
            In compliance with the <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong>, the Public
            Employment Service Office of Cagayan de Oro City (PESO CDO) collects personal information through this
            {{ $isEmployer ? 'Employer' : 'Applicant' }} Online Registration Form for PESO Employment Information System
            registration, job matching, employment facilitation, coordination with
            {{ $isEmployer ? 'jobseekers' : 'employers' }}, and related PESO services.
        </p>
        <p class="mb-2">
            The information collected will be accessed only by authorized PESO and DOLE personnel and duly authorized
            partner agencies and will not be disclosed to unauthorized parties, except when required for verification,
            or as provided by law.
        </p>
        <p class="mb-2">
            By submitting this form, you acknowledge and consent to the collection, processing, and use of your
            personal data for the purposes stated above. For data privacy concerns, you may coordinate with the City
            Government of Cagayan de Oro City's Data Privacy Officer.
        </p>
    </div>
    <div class="form-check mt-1">
        <input class="form-check-input" type="checkbox" name="privacy_agreed" value="1" id="privacyAgree"
            {{ old('privacy_agreed') ? 'checked' : '' }} required>
        <label class="form-check-label" for="privacyAgree" style="font-size:11.5px;font-weight:600;">
            I agree to the collection and processing of my personal data as stated above. *
        </label>
    </div>
    <div id="privacyNeedCheck" style="display:none;font-size:11.5px;font-weight:600;color:var(--danger);margin-top:4px;">
        <i class="ph-fill ph-warning-circle me-1"></i>Please check the box above first before filling in the form.
    </div>
    @error('privacy_agreed')
        <div style="font-size:11px;color:var(--danger);margin-top:4px;">{{ $message }}</div>
    @enderror
</div>

<style>
    /* The rest of the form waits until the notice is agreed to. */
    form.privacy-locked .peso-input { opacity: .55; }
    #privacyNotice.privacy-alert {
        border-color: var(--danger) !important;
        box-shadow: 0 0 0 3px rgba(198, 40, 40, .18);
        animation: privacyShake .35s;
    }
    @keyframes privacyShake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
</style>
<script>
    // Nothing else in the form can be typed in, picked or uploaded until the
    // Data Privacy Notice is checked. Trying to shows where to agree first.
    (function () {
        const box    = document.getElementById('privacyAgree');
        const notice = document.getElementById('privacyNotice');
        const note   = document.getElementById('privacyNeedCheck');
        const form   = box ? box.closest('form') : null;
        if (!form) return;

        function sync() {
            form.classList.toggle('privacy-locked', !box.checked);
            if (box.checked) {
                notice.classList.remove('privacy-alert');
                note.style.display = 'none';
            }
        }

        function warn() {
            note.style.display = 'block';
            notice.classList.remove('privacy-alert');
            void notice.offsetWidth; // restart the shake
            notice.classList.add('privacy-alert');
            notice.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // A field outside the notice that takes input. Buttons stay usable.
        function isLockedField(el) {
            if (!el || notice.contains(el)) return false;
            return !!el.closest('input:not([type=hidden]):not([type=button]):not([type=submit]), select, textarea, label, .custom-dropdown-list, .custom-dropdown-item');
        }

        form.addEventListener('focusin', function (e) {
            if (!box.checked && isLockedField(e.target)) {
                e.target.blur();
                warn();
            }
        });
        ['mousedown', 'click', 'keydown', 'paste', 'drop'].forEach(function (type) {
            form.addEventListener(type, function (e) {
                if (!box.checked && isLockedField(e.target)) {
                    e.preventDefault();
                    if (type !== 'click') warn();
                }
            }, true);
        });

        box.addEventListener('change', sync);
        sync();
    })();
</script>
