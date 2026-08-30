{{--
    Employer account recovery — gigamit sa staff Employers page ug sa admin
    Manage Users page. Parehas nga porma, lahi ra ang route.

    Gamit:
        @include('partials.employer-recovery-modal', [
            'employer' => $userRow,                       // App\Models\User (role=company)
            'action'   => route('staff.employers.transfer', $userRow->users_id),
        ])

    Ang bag-ong HR nitawag kay wala na siyay maabot nga inbox — mao nga naay
    duha ka paagi dinhi:
      · Reset code  — kung naa siyay bag-ong email nga iyaha gyud. Walay tawo
                      sa opisina nga makahibalo sa password.
      · Temporary   — kung wala gyud. Basahon sa telepono, ug pugson ang
                      pag-ilis pag-login.
--}}
@php
    $nsrp = $employer->employerNsrp;
    $modalId = 'recoverModal' . $employer->users_id;

    // Ang Account Status dropdown para sa staff nga nag-atiman sa maong
    // employer; ang admin nga include wala nagpasa niini, mao nga parehas ra
    // gihapon ang iyang modal.
    $showStatus  = $canToggleStatus ?? false;
    // Ang pag-reset sa password nga walay giusab nga contact. Ang admin ra ang
    // nagpasa niini karon; ang staff nga porma nagpabilin nga sama sa kaniadto.
    $showReset   = $canResetPassword ?? false;
    // Usa ra ang kahimtang: 'dormant'. Ang sweep ug ang staff parehas og
    // gibutang, mao nga usa ra pud ka switch ang mo-abli pag-usab.
    $isInactive  = $employer->status === 'dormant' || (bool) ($nsrp->dormant_at ?? null);
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <form method="POST" action="{{ $action }}">
                @csrf
                {{-- Kaniadto "Transfer / Recover Account", unya "Change
                     Authorized Contact". Karon usa na ka porma para sa tibuok
                     account: kinsa ang mo-login, ug makahimo ba siya og login.
                     Usa ra gihapon ka kompanya sa tanang higayon — ang tawo ug
                     ang switch ra ang mausab. --}}
                <div class="modal-header" style="background:var(--g-600);border-radius:16px 16px 0 0;">
                    <h6 class="modal-title text-white fw-bold">
                        <i class="ph ph-pencil-simple me-2"></i>Update Employer Account
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="p-3 mb-3 rounded-3" style="background:var(--n-50);font-size:12px;color:var(--n-700);line-height:1.6;">
                        <strong style="color:var(--g-700);">{{ $nsrp->company_name ?? $employer->name }}</strong><br>
                        Current contact: {{ $nsrp->contact_person ?? 'None' }} ({{ $employer->email }})
                    </div>

                    {{-- Ang HR nga naghawid ug sobra sa usa ka kompanya: ang
                         handover mahitungod sa TAWO, ug ang email nagpuyo sa
                         account, mao nga ang tanan niyang kompanya mo-uban. Kung
                         dili ni isulti, ang staff mag-akong usa ra ang naapektuhan. --}}
                    @php $recoverCompanies = $employer->employerCompanies; @endphp
                    @if($recoverCompanies->count() > 1)
                    <div class="p-3 mb-3 rounded-3" style="background:var(--warn-bg);border:1px solid var(--warn-br);font-size:12px;color:var(--n-700);line-height:1.6;">
                        <i class="ph-fill ph-warning-circle me-1" style="color:var(--warn);"></i>
                        <strong style="color:var(--warn);">This account holds {{ $recoverCompanies->count() }} companies.</strong><br>
                        {{ $recoverCompanies->pluck('company_name')->implode(', ') }}.
                        One person signs in for all of them, so a new contact person and a new
                        e-mail here take over every one — not just the company you opened this from.
                    </div>
                    @endif

                    <div style="font-size:11.5px;color:var(--n-500);" class="mb-3">
                        Edit the authorized contact for this employer, switch the account on or
                        off, or both. The company, its registration and its postings stay exactly
                        as they are — only who signs in, and whether they can, changes.
                    </div>

                    {{-- ── ANG CONTACT NAKA-LOCK HANGTOD MO-RESET ──
                         PESO, 2026-08-30. Kasagaran ang staff moabli niini para
                         sa status ra — buhion o patyon ang account. Ang upat ka
                         field sa ubos wala niya labti, apan bukas sila, ug ang
                         usa ka aksidenteng pag-type sa Login Email mao ang
                         pag-ilis sa tawo nga makasulod sa account.

                         Mao nga naka-lock sila. Ang Reset ang mo-abli, ug kana
                         usa ka tinuyo nga lakang. Ang status dropdown sa ubos
                         WALA giapil — kana mao ang kasagarang katuyoan sa pag-abli
                         niini nga window, ug ang pagpapindot pa ug Reset una
                         mahimong sagabal nga walay gipanalipdan. --}}
                    <div class="d-flex justify-content-between align-items-center p-2 mb-3 rounded-3"
                         style="background:var(--n-50);border:1px solid var(--n-200);">
                        <span style="font-size:11.5px;color:var(--n-700);line-height:1.5;">
                            <strong style="color:var(--g-700);">Contact details are locked.</strong><br>
                            Press Reset to edit them. Switching the account on or off does not need it.
                        </span>
                        <button type="button" class="btn btn-sm fw-semibold js-recover-unlock flex-shrink-0 ms-2"
                            style="background:#fff;color:var(--g-700);border:1px solid var(--g-600);
                                   border-radius:8px;font-size:11.5px;padding:5px 14px;white-space:nowrap;">
                            <i class="ph ph-arrow-counter-clockwise me-1"></i>Reset
                        </button>
                    </div>

                    {{-- Pun-on dayon sa naa karon: kasagaran, ang staff moabli
                         niini para sa status ra, ug ang pag-type pag-usab sa
                         parehas nga contact usa ka paagi sa pagkasayop. --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:12px;color:var(--g-700);">Contact Person *</label>
                        <input type="text" name="new_contact_person" readonly class="form-control js-recover-lockable js-recover-contact" required
                            value="{{ old('new_contact_person', $nsrp->contact_person) }}"
                            data-original="{{ $nsrp->contact_person }}"
                            style="font-size:13px;border-radius:8px;border-color:var(--n-200);">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:12px;color:var(--g-700);">Position Title</label>
                        <input type="text" name="new_position_title" readonly class="form-control js-recover-lockable"
                            data-original="{{ $nsrp->position_title }}"
                            value="{{ old('new_position_title', $nsrp->position_title) }}"
                            style="font-size:13px;border-radius:8px;border-color:var(--n-200);">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:12px;color:var(--g-700);">Login Email *</label>
                        <input type="email" name="new_email" readonly class="form-control js-recover-lockable js-recover-contact" required
                            value="{{ old('new_email', $employer->email) }}"
                            data-original="{{ $employer->email }}"
                            style="font-size:13px;border-radius:8px;border-color:var(--n-200);">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:12px;color:var(--g-700);">Mobile Number</label>
                        <input type="text" name="new_mobile_number" readonly class="form-control js-recover-lockable"
                            data-original="{{ $nsrp->mobile_number }}"
                            inputmode="numeric" maxlength="11" placeholder="09171234567"
                            value="{{ old('new_mobile_number', $nsrp->mobile_number) }}"
                            style="font-size:13px;border-radius:8px;border-color:var(--n-200);">
                    </div>

                    @if($showReset)
                    {{-- Ang password nga walay giusab nga tawo.
                         Parehas nga tawo, dili makasulod — nakalimot sa password,
                         o wala moabot ang reset e-mail. Kung ang handover ang
                         gamiton para niini, isulat pag-usab sa rekord ang contact
                         ug ang email ngadto sa mga bili nga naa na sila, ug
                         mag-file ug audit nga nagsulti nga nabalhin ang account
                         nga walay nabalhin. --}}
                    <label class="d-flex align-items-start gap-2 p-2 rounded-3 mb-3"
                           style="border:1px solid var(--n-200);cursor:pointer;background:var(--n-50);">
                        <input class="form-check-input mt-1 flex-shrink-0 js-recover-reset" type="checkbox"
                               name="reset_password" value="1">
                        <span style="font-size:12px;color:var(--n-700);line-height:1.5;">
                            <strong style="color:var(--g-700);">Reset their password too</strong><br>
                            Tick this when the same person simply cannot get in. Leave it alone
                            when you are only editing the details above.
                        </span>
                    </label>
                    @endif

                    {{-- ── UNSAON SILA MAKASULOD ──
                         Motungha ra kini kung nailisan gyud ang tawo o ang
                         email. Kung ang status ra ang giusab, walay bag-ong
                         password nga kinahanglan — ug ang server mao gihapon
                         ang mo-hukom niini, dili kining pagtago. --}}
                    <div class="mb-3 js-recover-method" style="display:{{ $errors->any() ? 'block' : 'none' }};">
                        <label class="form-label fw-semibold" style="font-size:12px;color:var(--g-700);">How will they get in? *</label>

                        <label class="d-flex align-items-start gap-2 p-2 rounded-3 mb-2"
                               style="border:1px solid var(--n-200);cursor:pointer;">
                            <input class="form-check-input mt-1 flex-shrink-0" type="radio"
                                   name="method" value="reset_code" checked>
                            <span style="font-size:12px;color:var(--n-700);line-height:1.5;">
                                <strong style="color:var(--g-700);">Email them a code</strong><br>
                                They set their own password. Nobody here ever knows it.
                                Use this whenever the new email above works.
                            </span>
                        </label>

                        <label class="d-flex align-items-start gap-2 p-2 rounded-3"
                               style="border:1px solid var(--n-200);cursor:pointer;">
                            <input class="form-check-input mt-1 flex-shrink-0" type="radio"
                                   name="method" value="temp_password">
                            <span style="font-size:12px;color:var(--n-700);line-height:1.5;">
                                <strong style="color:var(--g-700);">Temporary password over the phone</strong><br>
                                For a caller with no working inbox. Shown to you once, read it out,
                                and they must change it the moment they log in.
                            </span>
                        </label>
                    </div>

                    @if($showStatus)
                    {{-- ── ANG SWITCH ──
                         Parehas ra og resulta bisan ang sweep o ang staff ang
                         nagpatay: ang account moadto sa Inactive Employers tab,
                         ug ang employer makasulod gihapon aron mo-sulat sa iyang
                         rason. Mao nga usa ra pud ka switch ang mo-abli. --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:12px;color:var(--g-700);">Account Status *</label>
                        <select name="account_status" class="form-select"
                            style="font-size:13px;border-radius:8px;border-color:var(--n-200);">
                            <option value="active"   {{ $isInactive ? '' : 'selected' }}>Active</option>
                            <option value="inactive" {{ $isInactive ? 'selected' : '' }}>Inactive</option>
                        </select>
                        <small style="font-size:11px;color:var(--n-500);">
                            @if($isInactive)
                                This account is inactive and its vacancies are hidden. Set it to Active
                                to bring the postings back — the same as Enable on the Inactive
                                Employers tab.
                            @else
                                Inactive hides their vacancies from jobseekers and sends them to a
                                single screen asking why they went quiet. Nothing is deleted.
                            @endif
                        </small>
                    </div>
                    @endif

                    <div class="mb-0">
                        <label class="form-label fw-semibold" style="font-size:12px;color:var(--g-700);">Reason *</label>
                        <textarea name="reason" class="form-control" rows="2" required
                            placeholder="e.g. Previous HR resigned; caller verified by phone with company ID."
                            style="font-size:13px;border-radius:8px;border-color:var(--n-200);"></textarea>
                        <small style="font-size:11px;color:var(--n-500);">
                            Kept on record with your name — the new contact can see this employer's applicants.
                        </small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm fw-semibold" data-bs-dismiss="modal"
                        style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-sm fw-semibold"
                        style="background:var(--g-600);color:#fff;border:none;border-radius:8px;">
                        <i class="ph ph-check me-1"></i>Confirm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('{{ $modalId }}');
    if (!modal) return;

    const methodBlock = modal.querySelector('.js-recover-method');
    const watched     = modal.querySelectorAll('.js-recover-contact');
    const resetBox    = modal.querySelector('.js-recover-reset');
    if (!methodBlock || !watched.length) return;

    // Ang tawo o ang email ra ang magbuot: ang position ug mobile masulat ra
    // sa rekord, walay password nga nag-agad kanila. Ang checkbox mao ang
    // laing agianan: parehas nga tawo, bag-o ra nga password.
    const contactEdited = () =>
        Array.from(watched).some(input => input.value.trim() !== (input.dataset.original || '').trim());

    const sync = () => {
        const needed = contactEdited() || (resetBox && resetBox.checked);
        methodBlock.style.display = needed ? 'block' : 'none';
    };

    watched.forEach(input => input.addEventListener('input', sync));
    if (resetBox) resetBox.addEventListener('change', sync);

    // ── Ang Reset ang mo-abli sa upat ka contact field ──
    //
    // Naka-readonly sila hangtod dinhi. Ang readonly, dili ang disabled: ang
    // disabled nga field dili ipadala sa porma, ug ang server nagkinahanglan
    // gihapon sa contact person ug sa email bisan ang status ra ang giusab.
    const unlock   = modal.querySelector('.js-recover-unlock');
    const lockable = modal.querySelectorAll('.js-recover-lockable');

    const setLocked = (locked) => {
        lockable.forEach(input => {
            input.readOnly = locked;
            input.style.background = locked ? 'var(--n-50)' : '';
        });
        if (unlock) {
            unlock.disabled = !locked;
            unlock.style.opacity = locked ? '1' : '0.5';
        }
    };

    if (unlock) unlock.addEventListener('click', () => {
        setLocked(false);
        const firstField = lockable[0];
        if (firstField) firstField.focus();
    });

    // Ang pagsira mag-uli sa lock ug sa mga bili: ang katunga nga giusab nga
    // pangalan magpabilin unta sa porma alang sa sunod nga employer nga ablihan.
    modal.addEventListener('hidden.bs.modal', () => {
        lockable.forEach(input => {
            if (input.dataset.original !== undefined) input.value = input.dataset.original;
        });
        setLocked(true);
        if (resetBox) resetBox.checked = false;
        sync();
    });

    // Kung gibalik sa server ang porma nga naay sayop, ang giusab nga bili naa
    // pa — dili kini angay ma-lock, kay dili na siya ma-ayo sa tawo.
    setLocked({{ $errors->any() ? 'false' : 'true' }});

    modal.addEventListener('shown.bs.modal', sync);
    sync();
})();
</script>
