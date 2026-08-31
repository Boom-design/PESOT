{{--
    Resume — rendered by dompdf, not by a browser.

    dompdf has no flexbox and no grid, so every column here is a table cell and
    every spacing is padding or margin. Keep it that way when editing: a rule it
    cannot read is silently dropped and the page quietly falls apart.

    All of this comes from the NSRP form. Nothing is asked of the jobseeker twice.
--}}
@php
    /** Only print a section when it actually has something in it. */
    $fill = fn($v) => filled($v) ? $v : null;

    $fullName = trim(collect([
        $registration->first_name,
        $registration->middle_name,
        $registration->surname,
        $registration->suffix,
    ])->filter()->implode(' '));

    $presentAddress = collect([
        $registration->house_street,
        $registration->barangay,
        $registration->municipality_city,
        $registration->province,
    ])->filter()->implode(', ');

    $email = $registration->reg_email ?: optional($registration->user)->email;

    // ── Education — printed high to low, the way a resume reads. ──
    $eduOrder = [
        'Graduate Studies/Post-graduate/Masters' => 'Graduate Studies',
        'Tertiary / College'                     => 'Tertiary / College',
        'Senior High School'                     => 'Senior High School',
        'Junior High School'                     => 'Junior High School',
        'Elementary'                             => 'Elementary',
    ];
    $education = collect($eduOrder)
        ->map(function ($label, $key) use ($nsrp) {
            $row = ($nsrp->education ?? [])[$key] ?? null;
            if (!$row || blank($row['school_name'] ?? null)) return null;
            return [
                'label'  => $label,
                'school' => $row['school_name'],
                'course' => $row['course_other'] ?? $row['course'] ?? null,
                'year'   => $row['year_graduated'] ?? $row['level_reached'] ?? $row['year_last_attended'] ?? null,
            ];
        })
        ->filter()
        ->values();

    $work = collect($nsrp->workExperiences ?? [])->filter(fn($w) => filled($w->company_name) || filled($w->position));

    $trainings = collect($nsrp->trainings ?? [])->filter(fn($t) => filled($t['course'] ?? null))->values();

    $eligibilities = collect($nsrp->eligibilities ?? [])->filter(fn($e) => filled($e['name'] ?? null))->values();
    $licenses      = collect($nsrp->licenses ?? [])->filter(fn($l) => filled($l['name'] ?? null))->values();
    $certs         = collect($nsrp->certifications ?? []);

    $skills = collect($nsrp->other_skills ?? [])->filter()->values();
    if (filled($nsrp->other_skills_specify ?? null)) {
        $skills = $skills->push($nsrp->other_skills_specify);
    }

    // ── Languages — the form stores a "1" per ability, so count the ticks. ──
    $languages = collect($nsrp->language_proficiency ?? [])
        ->reject(fn($v, $k) => $k === 'other')
        ->map(function ($abilities, $lang) {
            $can = collect(['read' => 'Read', 'write' => 'Write', 'speak' => 'Speak', 'understand' => 'Understand'])
                ->filter(fn($label, $key) => !empty($abilities[$key]))
                ->values();
            return $can->isEmpty() ? null : ['name' => $lang, 'abilities' => $can->implode(', ')];
        })
        ->filter()
        ->values();

    if (filled($nsrp->other_language ?? null)) {
        $otherRow = collect(($nsrp->language_proficiency ?? [])['other'] ?? [])->first() ?? [];
        $can = collect(['read' => 'Read', 'write' => 'Write', 'speak' => 'Speak', 'understand' => 'Understand'])
            ->filter(fn($label, $key) => !empty($otherRow[$key]))
            ->values();
        $languages = $languages->push([
            'name'      => $nsrp->other_language,
            'abilities' => $can->isEmpty() ? '—' : $can->implode(', '),
        ]);
    }

    $occupations = collect($nsrp->preferred_occupations ?? [])->filter()->values();
    $locLocal    = collect($nsrp->local_locations ?? [])->filter()->values();
    $locOverseas = collect($nsrp->overseas_locations ?? [])->filter()->values();

    /**
     * Dates on the NSRP form are not all real dates. Work experience is typed
     * free-hand and comes back as things like "03/2021", so parsing has to be
     * allowed to fail — print what the jobseeker wrote rather than blowing up
     * the whole resume over one field.
     */
    $safeDate = function ($value, string $format) {
        if (blank($value)) return null;
        try {
            return \Carbon\Carbon::parse($value)->format($format);
        } catch (\Throwable $e) {
            return (string) $value;
        }
    };

    $fmt = fn($d) => $safeDate($d, 'M Y');

    $dob = $safeDate($registration->date_of_birth, 'F d, Y');

@endphp

@php
    // ── The circle beside the name ──
    //
    // The photo is the one already on My Account Profile. The NSRP form does
    // not ask for a second one: the jobseeker has uploaded a picture of
    // themselves once, and asking again for the same face is a form doing the
    // office's filing for it.
    //
    // dompdf reads a file from disk, not a URL, so it is embedded as a data
    // URI. A jobseeker with no photo gets their initials — a blank grey hole
    // on a resume reads as a mistake rather than as a choice.
    $photoFile = optional($registration->user)->profile_photo;

    $photoData = null;
    if (filled($photoFile)) {
        $photoPath = storage_path('app/public/' . $photoFile);
        if (is_file($photoPath)) {
            $mime = @mime_content_type($photoPath) ?: 'image/jpeg';
            $photoData = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($photoPath));
        }
    }

    $initials = collect([$registration->first_name, $registration->surname])
        ->filter()
        ->map(fn($n) => mb_strtoupper(mb_substr(trim($n), 0, 1)))
        ->implode('');

    // The line under the name. The occupation the jobseeker is after says more
    // than any title they could invent for themselves.
    $headline = $occupations->first() ?: 'Jobseeker';
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        /*
            dompdf has no flexbox and no grid. Every column below is a table
            cell and every gap is padding. A rule it cannot read is dropped in
            silence, so the page falls apart without saying why — keep to
            tables, floats, padding and margins when editing this.
        */
        @page { margin: 0; }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 8.6px;
            color: #2b2b2b;
            line-height: 1.55;
            margin: 0;
        }

        /* ── HEADER ── */
        .header { background: #ececec; width: 100%; }
        .header-pad { padding: 26px 30px 20px 30px; }

        .name-tag {
            background: #3d3d3d;
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 3px;
            padding: 4px 12px;
            display: inline-block;
            margin-bottom: 8px;
        }

        .name {
            font-size: 27px;
            font-weight: bold;
            color: #2b2b2b;
            letter-spacing: 2.5px;
            margin: 0;
            line-height: 1.1;
        }

        .headline-bar {
            background: #cfe3d4;
            display: inline-block;
            padding: 3px 14px 3px 10px;
            margin-top: 7px;
        }
        .headline {
            font-size: 9px;
            color: #4a4a4a;
            letter-spacing: 2.6px;
            text-transform: uppercase;
        }

        /* The photo sits on a mint block, the way the layout has it. */
        .photo-block {
            background: #cfe3d4;
            width: 118px;
            height: 118px;
            text-align: center;
        }
        .photo {
            width: 96px;
            height: 96px;
            border-radius: 48px;
            margin-top: 11px;
        }
        .photo-fallback {
            width: 96px;
            height: 96px;
            border-radius: 48px;
            background: #6f6f6f;
            color: #ffffff;
            font-size: 30px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 11px;
            display: inline-block;
        }
        .photo-fallback-inner { padding-top: 28px; }

        /* ── COLUMNS ── */
        .body-pad { padding: 20px 30px 26px 30px; }
        .col-left  { width: 33%; vertical-align: top; padding-right: 20px; }
        .col-right { width: 67%; vertical-align: top; }

        .sec {
            font-size: 10.5px;
            font-weight: bold;
            letter-spacing: 2.4px;
            color: #2b2b2b;
            text-transform: uppercase;
            border-bottom: 1.6px solid #2b2b2b;
            padding-bottom: 3px;
            margin: 0 0 9px 0;
        }
        .sec-gap { margin-top: 17px; }

        .item { margin-bottom: 9px; }
        .item-title { font-weight: bold; color: #2b2b2b; font-size: 9px; }
        .item-sub   { color: #6a6a6a; font-size: 8.4px; }
        .muted      { color: #6a6a6a; }

        /* ── CONTACT ── */
        .contact-row { margin-bottom: 7px; }
        .contact-ico {
            width: 17px;
            height: 17px;
            border-radius: 9px;
            background: #cfe3d4;
            color: #3d3d3d;
            font-size: 8px;
            text-align: center;
            vertical-align: middle;
        }
        .contact-ico-inner { padding-top: 3px; }
        .contact-text {
            padding-left: 7px;
            border-bottom: 1px solid #d6d6d6;
            padding-bottom: 3px;
            vertical-align: middle;
            font-size: 8.4px;
            word-wrap: break-word;
        }

        /* ── DATE CHIP beside each job ── */
        .chip {
            background: #cfe3d4;
            color: #3d3d3d;
            font-size: 7.6px;
            font-weight: bold;
            padding: 3px 5px;
            text-align: center;
            width: 54px;
            line-height: 1.25;
        }
        .job-rule {
            border-left: 1.4px solid #cfcfcf;
            padding-left: 11px;
        }
        .bullet { margin: 0 0 1px 0; }

        /* ── SKILLS ── */
        .skill-name { font-size: 8.4px; padding-bottom: 4px; }
        .pip   { color: #3d3d3d; letter-spacing: 1.5px; font-size: 8px; }
        .pip-off { color: #cfcfcf; letter-spacing: 1.5px; font-size: 8px; }

        table { border-collapse: collapse; width: 100%; }
        td { padding: 0; }
    </style>
</head>
<body>

{{-- ══ HEADER ══ --}}
<table class="header">
    <tr>
        <td class="header-pad" style="vertical-align: middle;">
            <div class="name-tag">{{ $initials ?: 'PESO' }}</div>
            <div class="name">{{ mb_strtoupper($fullName) }}</div>
            <div class="headline-bar"><span class="headline">{{ $headline }}</span></div>
        </td>
        <td class="photo-block" style="vertical-align: middle;">
            @if($photoData)
                <img class="photo" src="{{ $photoData }}" alt="">
            @else
                <div class="photo-fallback">
                    <div class="photo-fallback-inner">{{ $initials ?: '—' }}</div>
                </div>
            @endif
        </td>
    </tr>
</table>

{{-- ══ TWO COLUMNS ══ --}}
<table class="body-pad">
    <tr>
        {{-- ────────── LEFT ────────── --}}
        <td class="col-left">

            <div class="sec">Contact</div>
            @foreach([
                ['✆', $registration->contact_number],
                ['✉', $email],
                ['⌂', $presentAddress],
            ] as [$icon, $value])
                @if(filled($value))
                <table class="contact-row">
                    <tr>
                        <td class="contact-ico" style="width:17px;">
                            <div class="contact-ico-inner">{{ $icon }}</div>
                        </td>
                        <td class="contact-text">{{ $value }}</td>
                    </tr>
                </table>
                @endif
            @endforeach

            @if($education->isNotEmpty())
            <div class="sec sec-gap">Education</div>
            @foreach($education as $e)
                <div class="item">
                    @if($e['year'])<div class="item-sub">{{ $e['year'] }}</div>@endif
                    <div class="item-title">{{ mb_strtoupper($e['course'] ?: $e['label']) }}</div>
                    <div class="item-sub">{{ $e['school'] }}</div>
                </div>
            @endforeach
            @endif

            @if($skills->isNotEmpty())
            <div class="sec sec-gap">Skills</div>
            {{-- The pips are decoration, not a rating: nobody asked the
                 jobseeker to score themselves, so every skill reads the same.
                 Inventing a level here would be putting words in their mouth. --}}
            <table>
                @foreach($skills as $skill)
                <tr>
                    <td class="skill-name">{{ $skill }}</td>
                    <td class="skill-name" style="text-align:right;width:66px;">
                        <span class="pip">■ ■ ■ ■</span><span class="pip-off"> ■</span>
                    </td>
                </tr>
                @endforeach
            </table>
            @endif

            @if($languages->isNotEmpty())
            <div class="sec sec-gap">Languages</div>
            @foreach($languages as $lang)
                <div class="item">
                    <div class="item-title">{{ $lang['name'] }}</div>
                    <div class="item-sub">{{ $lang['abilities'] }}</div>
                </div>
            @endforeach
            @endif

            @if($eligibilities->isNotEmpty() || $licenses->isNotEmpty())
            <div class="sec sec-gap">Eligibility</div>
            @foreach($eligibilities as $e)
                <div class="item">
                    <div class="item-title">{{ $e['name'] }}</div>
                    @if(filled($e['date_taken'] ?? null))
                        <div class="item-sub">{{ $fmt($e['date_taken']) }}</div>
                    @endif
                </div>
            @endforeach
            @foreach($licenses as $l)
                <div class="item">
                    <div class="item-title">{{ $l['name'] }}</div>
                    @if(filled($l['valid_until'] ?? null))
                        <div class="item-sub">Valid until {{ $fmt($l['valid_until']) }}</div>
                    @endif
                </div>
            @endforeach
            @endif

        </td>

        {{-- ────────── RIGHT ────────── --}}
        <td class="col-right">

            @php
                // Gitukod sa PHP, dili sa nagsunod-sunod nga @if sa usa ka linya:
                // ang Blade dili mo-compile sa direktiba nga midikit dayon sa
                // @endif, ug ang tibuok pahina mahugno tungod sa usa ka koma.
                $who = collect([
                    $fullName,
                    $registration->age ? $registration->age . ' years old' : null,
                    $registration->sex,
                    $registration->civil_status,
                ])->filter()->implode(', ');

                $profileLines = collect([
                    $who ? $who . '.' : null,
                    $presentAddress ? 'Currently in ' . $presentAddress . '.' : null,
                    $occupations->isNotEmpty() ? 'Looking for work as ' . $occupations->implode(', ') . '.' : null,
                    $locLocal->isNotEmpty() ? 'Willing to work in ' . $locLocal->implode(', ') . '.' : null,
                    $locOverseas->isNotEmpty() ? 'Open to overseas work in ' . $locOverseas->implode(', ') . '.' : null,
                ])->filter();
            @endphp
            <div class="sec">Profile</div>
            <div style="margin-bottom:4px;">{{ $profileLines->implode(' ') }}</div>

            @if($work->isNotEmpty())
            <div class="sec sec-gap">Job Experience</div>
            @foreach($work as $w)
                <table style="margin-bottom:11px;">
                    <tr>
                        <td style="width:54px;vertical-align:top;">
                            <div class="chip">
                                {{ $fmt($w->date_from) ?: 'None' }}<br>
                                {{ $w->is_current ? 'Present' : ($fmt($w->date_to) ?: 'None') }}
                            </div>
                        </td>
                        <td style="vertical-align:top;padding-left:11px;">
                            <div class="item-title" style="font-size:9.4px;">{{ mb_strtoupper($w->position ?: 'Position not stated') }}</div>
                            <div class="item-sub" style="margin-bottom:3px;">
                                {{ $w->company_name ?: 'Company not stated' }}
                                @if($w->date_from)
                                    / {{ $fmt($w->date_from) }} –
                                    {{ $w->is_current ? 'Present' : ($fmt($w->date_to) ?: 'None') }}
                                @endif
                            </div>
                            <div class="job-rule">
                                @if(filled($w->industry))<div class="bullet">Industry: {{ $w->industry }}</div>@endif
                                @if(filled($w->employment_status))<div class="bullet">Status: {{ $w->employment_status }}</div>@endif
                                @if(blank($w->industry) && blank($w->employment_status))
                                    <div class="bullet muted">No further details were given for this role.</div>
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            @endforeach
            @endif

            @if($trainings->isNotEmpty())
            <div class="sec sec-gap">Training</div>
            @foreach($trainings as $t)
                <table style="margin-bottom:9px;">
                    <tr>
                        <td style="width:54px;vertical-align:top;">
                            <div class="chip">
                                {{ $fmt($t['date_from'] ?? null) ?: 'None' }}<br>
                                {{ $fmt($t['date_to'] ?? null) ?: 'None' }}
                            </div>
                        </td>
                        <td style="vertical-align:top;padding-left:11px;">
                            <div class="item-title">{{ $t['course'] }}</div>
                            <div class="item-sub">
                                {{ $t['institution'] ?? 'Institution not stated' }}
                                @if(filled($t['hours'] ?? null)) / {{ $t['hours'] }} hours @endif
                            </div>
                        </td>
                    </tr>
                </table>
            @endforeach
            @endif

            @if($certs->isNotEmpty())
            <div class="sec sec-gap">Certifications</div>
            @foreach($certs as $c)
                <div class="item">
                    <div class="item-title">{{ $c->title ?? $c->name ?? 'Certificate' }}</div>
                    <div class="item-sub">
                        {{ $c->issuer ?? $c->institution ?? '' }}
                        @if(filled($c->date_taken ?? null)) / {{ $fmt($c->date_taken) }} @endif
                    </div>
                </div>
            @endforeach
            @endif

            @if($work->isEmpty() && $trainings->isEmpty() && $certs->isEmpty())
                <div class="muted" style="margin-top:12px;">
                    No work experience, training or certification has been recorded on the
                    NSRP form yet. Adding them on My Profile will fill this side of the page.
                </div>
            @endif

        </td>
    </tr>
</table>

</body>
</html>
