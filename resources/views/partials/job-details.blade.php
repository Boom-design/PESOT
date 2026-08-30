{{--
    Full details of one job posting: the posting itself plus every
    qualification requirement that was set on it.

    Expects $job (App\Models\Job). Shared by the employer and staff
    Reports pages so a closed posting reads the same to both.
--}}
@php
    $scheduleLabel = match($job->schedule_type) {
        'job_fair' => ['Job Fair', 'is-success'],
        'inhouse'  => ['In-house', 'is-warn'],
        default    => ['Company Interview', 'is-info'],
    };

    // A requirement is only shown when the employer actually set it —
    // a page of "None" rows tells the reader nothing.
    $requirements = [];

    if ($job->education_required)  $requirements[] = ['ph-graduation-cap', 'Education', $job->education_required];
    if ($job->experience_required) $requirements[] = ['ph-briefcase', 'Experience', ($job->experience_years ? $job->experience_years . ' year/s' : null) ?: ($job->experience_months ? $job->experience_months . ' month/s' : 'Required')];
    if ($job->age_required)        $requirements[] = ['ph-identification-badge', 'Age', $job->age_min . ' – ' . $job->age_max . ' years old'];
    if ($job->height_required)     $requirements[] = ['ph-ruler', 'Height', 'At least ' . $job->height_minimum];
    if ($job->sex_preference && $job->sex_preference !== 'Any') $requirements[] = ['ph-gender-intersex', 'Sex', $job->sex_preference];
    if ($job->course_major)        $requirements[] = ['ph-book-open', 'Course / Major', $job->course_major];
    if ($job->license)             $requirements[] = ['ph-identification-card', 'License', $job->license];
    if ($job->eligibility)         $requirements[] = ['ph-seal-check', 'Eligibility', $job->eligibility];
    if ($job->certification)       $requirements[] = ['ph-certificate', 'Certification', $job->certification];
    if ($job->language)            $requirements[] = ['ph-translate', 'Language', $job->language];
    if ($job->civil_status)        $requirements[] = ['ph-heart', 'Civil Status', $job->civil_status];
    if ($job->religion)            $requirements[] = ['ph-hands-praying', 'Religion', $job->religion];
    if ($job->preferred_residence) $requirements[] = ['ph-house-line', 'Preferred Residence', $job->preferred_residence];
    if ($job->acceptsPwd())        $requirements[] = ['ph-wheelchair', 'Accepts PWD', 'Yes'];

    $skills = is_array($job->skills_required) ? $job->skills_required : json_decode($job->skills_required ?? '[]', true);
    $disabilities = is_array($job->disability_types) ? $job->disability_types : json_decode($job->disability_types ?? '[]', true);
    $programs = is_array($job->accepts_programs) ? $job->accepts_programs : json_decode($job->accepts_programs ?? '[]', true);
@endphp

<div class="peso-card p-3 p-md-4 mb-3">
    <h6 class="fw-bold mb-3" style="font-size:13px;color:var(--g-700);">
        <i class="ph ph-briefcase me-2" style="color:var(--g-600);"></i>Job Posting Details
    </h6>

    <div class="row g-2" style="font-size:12px;">
        @foreach ([
            ['Position',      $job->title],
            ['Location',      $job->location],
            ['Employment Type', $job->type ? ucwords(str_replace(['_', '-'], ' ', $job->type)) : null],
            ['Industry Group', $job->industry_group],
            ['Salary',        $job->salary],
            ['Slots',         $job->slots],
            ['Deadline',      $job->deadline?->format('F j, Y')],
            ['Date Posted',   $job->created_at?->format('F j, Y')],
        ] as [$label, $value])
        <div class="col-md-6">
            <div class="p-2 rounded-2" style="background:var(--n-50);border:1px solid var(--n-200);">
                <div style="color:var(--n-500);font-size:11px;">{{ $label }}</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $value !== null && $value !== '' ? $value : 'None' }}</div>
            </div>
        </div>
        @endforeach

        <div class="col-md-6">
            <div class="p-2 rounded-2" style="background:var(--n-50);border:1px solid var(--n-200);">
                <div style="color:var(--n-500);font-size:11px;">Schedule Type</div>
                <span class="badge-peso {{ $scheduleLabel[1] }}">{{ $scheduleLabel[0] }}</span>
            </div>
        </div>

        @if($job->venue_type || $job->venue_address)
        <div class="col-md-6">
            <div class="p-2 rounded-2" style="background:var(--n-50);border:1px solid var(--n-200);">
                <div style="color:var(--n-500);font-size:11px;">Venue</div>
                <div style="color:var(--g-700);font-weight:600;">{{ $job->venue_address ?: $job->venue_type }}</div>
            </div>
        </div>
        @endif
    </div>

    @if($job->description)
    <div class="mt-3">
        <div style="color:var(--n-500);font-size:11px;">Job Description</div>
        @include('partials.job-description', ['description' => $job->description, 'descriptionSize' => '12px'])
    </div>
    @endif
</div>

<div class="peso-card p-3 p-md-4 mb-3">
    <h6 class="fw-bold mb-3" style="font-size:13px;color:var(--g-700);">
        <i class="ph ph-list-checks me-2" style="color:var(--g-600);"></i>Qualification Requirements
    </h6>

    @if(empty($requirements) && empty($skills) && empty($disabilities) && empty($programs) && !$job->other_qualifications)
        <p class="mb-0" style="font-size:12px;color:var(--n-500);">
            No qualification requirements were set on this posting.
        </p>
    @else
        <div class="row g-2" style="font-size:12px;">
            @foreach($requirements as [$icon, $label, $value])
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background:var(--n-50);border:1px solid var(--n-200);">
                    <i class="ph {{ $icon }}" style="color:var(--g-600);"></i>
                    <span style="color:var(--g-700);"><strong>{{ $label }}:</strong> {{ $value }}</span>
                </div>
            </div>
            @endforeach
        </div>

        @foreach ([
            ['Skills Required', $skills],
            ['Disability Types Accepted', $disabilities],
            ['Programs Accepted', $programs],
        ] as [$label, $list])
            @if(!empty($list))
            <div class="mt-3">
                <div style="color:var(--n-500);font-size:11px;margin-bottom:6px;">{{ $label }}</div>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($list as $item)
                        <span class="badge-peso is-info">{{ is_array($item) ? implode(' — ', $item) : $item }}@if(!$loop->last),@endif</span>
                    @endforeach
                </div>
            </div>
            @endif
        @endforeach

        @if($job->other_qualifications)
        <div class="mt-3">
            <div style="color:var(--n-500);font-size:11px;">Other Qualifications</div>
            <div style="color:var(--g-700);font-size:12px;white-space:pre-line;">{{ $job->other_qualifications }}</div>
        </div>
        @endif
    @endif
</div>
