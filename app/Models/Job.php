<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Job extends Model
{
    use HasFactory;

    protected $table = 'job_qualifications';

    protected $primaryKey = 'job_qualifications_id';

    protected $fillable = [
    'company_id',
    'posting_group_id',
    'title',
    'description',
    'location',
    'type',
    'industry_group',
    'slots',
    'external_hires',       // ← gi-hire gawas sa PESO, gi-report sa employer
    'status',
    // Gisirado sa sweep sa employer nga hunong na, dili sa employer mismo.
    // Kini ra ang buksan pag-balik pag-enable sa account.
    'dormant_closed_at',
    'posting_status',
    'posting_type',
    'remarks',
    'salary',
    'deadline',            // ← bug fix: gi-pasa na sa controller pero wala diri sauna
    'accepts_programs',    // ← bug fix: gi-pasa na sa controller pero wala diri sauna
    'religion',
    'civil_status',
    'other_qualifications',
    'accepts_disability',
    'disability_types',
    'course_major',
    'license',
    'eligibility',
    'certification',
    'language',
    'preferred_residence',
    'experience_months',
    'experience_required',  // ← para sa Smart Matching (age/height/exp/skills criteria)
    'experience_years',
    'age_required',
    'age_min',
    'age_max',
    'height_required',
    'height_minimum',
    'skills_required',
    'sex_preference',
    'education_required',
    'schedule_type',
    'requested_job_fair_id',
    'preferred_date',
    // In-house is a window, not a day: the employer offers a span and LRA/SRA
    // pick one date inside it. A single-day request has the end equal to the
    // start.
    'preferred_date_end',
    'confirmed_date',
    'preferred_time',
    'poster_image',
    'venue_type',
    'venue_address',
];
    
    protected $casts = [
        'disability_types'  => 'array',
        'accepts_programs'  => 'array',
        'skills_required'   => 'array',
        'deadline'          => 'date',
        'preferred_date'    => 'date',
        'preferred_date_end' => 'date',
        'confirmed_date'    => 'date',
        'dormant_closed_at' => 'datetime',
    ];

    /**
     * Does this vacancy accept PWD applicants?
     *
     * accepts_disability is the word "yes" or "no", so the column cannot be
     * read as a truth value on its own: "no" is a non-empty string, and every
     * `if ($job->accepts_disability)` around the system was reading a refusal
     * as a yes. Ask through here instead.
     */
    public function acceptsPwd(): bool
    {
        return strtolower(trim((string) $this->accepts_disability)) === 'yes';
    }

    /** The last day of the offered window; a one-day request ends where it starts. */
    public function getPreferredDateLastAttribute()
    {
        return $this->preferred_date_end ?: $this->preferred_date;
    }

    /**
     * The day the interview actually happens.
     *
     * Before staff confirm, the best answer is the first day of the window.
     */
    public function getInterviewDateAttribute()
    {
        return $this->confirmed_date ?: $this->preferred_date;
    }

    /**
     * Is the in-house participation prompt due for this posting?
     *
     * Counted from midnight, matching inhouse:send-participation-reminders,
     * which asks the same question in SQL with whereDate. now() carries the
     * time of day with it, so on the interview date itself the difference came
     * out as a negative fraction — a few hours past midnight — and failed the
     * >= 0 test. The one day the prompt matters most was the only day it never
     * appeared.
     */
    public function isInhousePromptDue(): bool
    {
        if ($this->schedule_type !== 'inhouse' || !$this->interview_date) {
            return false;
        }

        $daysUntil = (int) today()->diffInDays($this->interview_date, false);

        return $daysUntil >= 0 && $daysUntil <= 5;
    }

    public function getScheduleWindowLabelAttribute(): string
    {
        if (!$this->preferred_date) return 'None';

        $last = $this->preferred_date_last;

        if (!$last || $last->isSameDay($this->preferred_date)) {
            return $this->preferred_date->format('M d, Y');
        }

        return $this->preferred_date->format('M d') . ' – ' . $last->format('M d, Y');
    }

    // Relationship: Job belongs to a Company (EmployerNsrpRegistration)
    public function company()
    {
        return $this->belongsTo(EmployerNsrpRegistration::class, 'company_id');
    }

    /**
     * The job fair the employer asked to join, if they named one.
     *
     * A request, not a booking. The posting is only on a fair once a
     * JobFairEmploymentRequest row exists for it, which the Job Fair desk
     * writes when it accepts the posting.
     */
    public function requestedJobFair()
    {
        return $this->belongsTo(JobFairEvent::class, 'requested_job_fair_id', 'job_fair_events_id');
    }

    // Relationship: Job has many Applications
    public function applications()
    {
        return $this->hasMany(Application::class, 'job_id');
    }

    // ── Relationship: ang ubang channel sa parehas nga posting. Usa ka
    // ── position nga gi-post sa Company Interview + In-house + Job Fair kay tulo
    // ── ka row nga managsama ug posting_group_id — apil ang kaugalingon. ──
    public function groupJobs()
    {
        return $this->hasMany(self::class, 'posting_group_id', 'posting_group_id')
            ->whereNotNull('posting_group_id');
    }

    // ── Ang group key: ang posting_group_id kung naa, kung wala ang kaugalingon
    // ── nga ID. Ang row nga wala pa sa multi-channel nga panahon (o usa ra ka
    // ── channel) kay group sa iyang kaugalingon, mao nga walay backfill. ──
    public function getGroupKeyAttribute()
    {
        return $this->posting_group_id ?? $this->job_qualifications_id;
    }

    // ── Ang SQL nga mo-ihap sa hired sa TIBUOK group, dili sa usa ka row ra.
    // ── Usa ra ka kopya niini aron ang scopeActive, scopeInactive ug ang
    // ── slots badge sa UI dili gyud magkalahi ug ihap. ──
    // ── Duha ka tinubdan ang mo-hurot ug slot:
    // ──   1. Ang na-hire pinaagi sa system (job_matching, status='hired')
    // ──   2. Ang gi-report sa employer nga gi-hire gawas sa PESO
    // ──      (external_hires) — pananglit ang ig-agaw nga wala mi-apply.
    // ── Duha sila gi-ihap sa TIBUOK posting group, dili sa usa ka row ra.
    // ──
    // ── Usa ra ka `?` gihapon ang binding, ug siya ang una — mao nga ang
    // ── tanan nga scope nga nagtawag niini walay kausaban. ──
    private static function groupHiredCountSql(): string
    {
        return '((select count(*) from `job_matching` `jm`
                    inner join `job_qualifications` `sib`
                        on `sib`.`job_qualifications_id` = `jm`.`job_id`
                  where coalesce(`sib`.`posting_group_id`, `sib`.`job_qualifications_id`)
                      = coalesce(`job_qualifications`.`posting_group_id`, `job_qualifications`.`job_qualifications_id`)
                    and `jm`.`status` = ?)
                + ' . self::groupExternalHiresSql() . ')';
    }

    // ── Ang gi-report nga off-system nga hire sa tibuok group. Bulag nga
    // ── method kay ang Reports magpakita niini nga bulag sa PESO placements:
    // ── ang numero nga isumite sa DOLE kinahanglan mao ra gyud ang tinuod
    // ── nga gi-placed sa PESO. ──
    private static function groupExternalHiresSql(): string
    {
        return '(select coalesce(sum(`ext`.`external_hires`), 0)
                   from `job_qualifications` `ext`
                  where coalesce(`ext`.`posting_group_id`, `ext`.`job_qualifications_id`)
                      = coalesce(`job_qualifications`.`posting_group_id`, `job_qualifications`.`job_qualifications_id`))';
    }

    // ── Scope: ang usa ka row kada posting group — ang unang gi-create nga
    // ── channel. Gamiton kung ang gi-ihap kay ang BAKANTE, dili ang posting:
    // ── ang "Foreman, 3 slots" nga gi-post sa tulo ka channel kay tulo gihapon
    // ── ka bakante, dili siyam. Ang daan nga row (walay group) apil gihapon. ──
    public function scopeGroupLeaders($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('posting_group_id')
              ->orWhereColumn('posting_group_id', 'job_qualifications_id');
        });
    }

    // ── Scope: usa ka row kada posting group — apan ang gipili kay ang
    // ── pinakauna nga BUHI pa nga channel, dili basta ang una nga na-create.
    // ──
    // ── Lahi ni sa scopeGroupLeaders(). Ang groupLeaders mokuha sa row nga
    // ── nag-una ug create bisan patay na kadto nga channel. Sa publiko nga
    // ── listahan mapeligro kana: ang Job Fair nga channel magpabilin nga
    // ── CLOSED hangtod moabli ang staff ug event, ug kung siya ang leader,
    // ── mawala ang TIBUOK posisyon sa landing page bisan abli pa ang Company
    // ── Based niini.
    // ──
    // ── Gamiton kung ang gipakita kay ang BAKANTE, dili ang posting: ang
    // ── "Metal Fabricator" nga gi-post sa Company Interview ug In-house kay usa
    // ── ra gihapon ka trabaho — usa ra ka card, dili duha nga managsama ug
    // ── hitsura.
    // ──
    // ── Ang $filter mao ang parehas nga sala nga gigamit sa gipakita nga
    // ── listahan. Kinahanglan ni: kung ang gipili nga tab kay "Job Fair",
    // ── ang representante sa group kinahanglan kuhaon gikan sa mga row nga
    // ── job fair usab — kay kung dili, ang mapili kay ang Company Interview nga
    // ── row, unya siya sad ang masala pagawas, ug mawala ang posisyon. ──
    public function scopeOnePerGroup($query, ?\Closure $filter = null)
    {
        $keepQuery = static::query()->active();

        if ($filter) {
            $filter($keepQuery);
        }

        $keep = $keepQuery
            ->selectRaw('min(`job_qualifications`.`job_qualifications_id`) as keep_id')
            ->groupByRaw('coalesce(`job_qualifications`.`posting_group_id`, `job_qualifications`.`job_qualifications_id`)')
            ->pluck('keep_id');

        return $query->whereIn('job_qualifications.job_qualifications_id', $keep);
    }

    // ── Scope: idugang ang group-wide nga hired count isip `group_hired_count`,
    // ── para sa listahan nga mag-pakita ug "1 / 3 slot(s) filled" — dili
    // ── withCount, kay ang withCount sa usa ka row ra mo-ihap. ──
    public function scopeWithGroupHiredCount($query)
    {
        return $query
            ->select('job_qualifications.*')
            ->selectRaw(self::groupHiredCountSql() . ' as group_hired_count', ['hired']);
    }

    // ── Ang duha ka numero nga bulag: pila ang gi-placed sa PESO ug pila ang
    // ── gi-hire sa employer sa iyang kaugalingon. Gamiton sa Reports. ──
    public function scopeWithHireBreakdown($query)
    {
        return $query
            ->select('job_qualifications.*')
            ->selectRaw(self::groupHiredCountSql() . ' as group_hired_count', ['hired'])
            ->selectRaw(self::groupExternalHiresSql() . ' as group_external_hires')
            ->selectRaw('(' . self::groupHiredCountSql() . ' - ' . self::groupExternalHiresSql() . ') as group_peso_hires', ['hired']);
    }

    // ── Scope: "active" nga job vacancy. Usa ra ka kahulogan para sa
    // ── Active Job Vacancy page ug sa dashboard stat cards, aron dili
    // ── mag-lahi ug ihap ang duha.
    // ──
    // ── Duha ka managlahi nga rason mo-undang ang posting (PESO interview,
    // ── 2026-08-12). Bisan asa ang mo-una, mahanaw na dayon:
    // ──   1. Milabay na ang deadline. Ang deadline mao ang katapusang
    // ──      adlaw, dili ang adlaw sa pag-undang — parehas sa
    // ──      getIsExpiredAttribute() sa ubos.
    // ──   2. Napuno na ang slots. 3 ka slot ug 3 na ka hired = wala nay
    // ──      bakante, bisan layo pa ang deadline.
    // ── Ang walay deadline mopadayon hangtod mapuno ang slots.
    // ──
    // ── Ang slots gi-ihap sa tibuok posting group. Ang "Foreman, 3 slots"
    // ── nga gi-post sa tulo ka channel kay tulo ka paagi sa pagpuno sa
    // ── parehas nga tulo ka bakante — dili siyam. Pagkapuno, mo-undang ang
    // ── tanan nga channel dungan. ──
    public function scopeActive($query)
    {
        return $query->where('status', 'open')
            ->where(function ($q) {
                $q->whereNull('deadline')
                  ->orWhereDate('deadline', '>=', now()->toDateString());
            })
            ->whereRaw(self::groupHiredCountSql() . ' < `job_qualifications`.`slots`', ['hired']);
    }

    // ── Scope: wala pa malabyi ang deadline. Ang walay deadline apil.
    // ── Bulag ni sa scopeActive kay ang scopeActive nag-require ug
    // ── status='open' — ug ang Job Fair nga posting maghulat man nga CLOSED
    // ── hangtod maka-abli ang staff ug event. Ang pangutana didto kay "buhi pa
    // ── ba?", dili "abli na ba?". ──
    public function scopeWithinDeadline($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('deadline')
              ->orWhereDate('deadline', '>=', now()->toDateString());
        });
    }

    // ── Scope: ang mga posting nga BUHI pa sa mata sa employer.
    // ──
    // ── Lahi ni sa scopeActive: ang scopeActive nagpasabot "makita na sa
    // ── jobseeker", ug kana nag-require ug status='open'. Ang Job Fair nga
    // ── posting nga na-approve na kay magpabilin nga CLOSED hangtod maka-abli
    // ── ang staff ug event — buhi siya, naghulat ra. Kung wala ni nga scope,
    // ── kadto nga row mawagtang sa tanan nga page sa employer ug mo-tungha sa
    // ── Archived nga daw patay na. ──
    public function scopeAlive($query)
    {
        return $query->where('posting_status', 'approved')
            ->withinDeadline()
            ->whereRaw(self::groupHiredCountSql() . ' < `job_qualifications`.`slots`', ['hired'])
            ->where(function ($q) {
                $q->where('status', 'open')
                  ->orWhere(function ($q2) {
                      $q2->where('schedule_type', 'job_fair')->where('status', 'closed');
                  });
            });
    }

    // ── Scope: ang NAHUMAN na nga posting — mao ni ang "Archived Job Postings"
    // ── sa Reports. Wala nay ma-delete, mao nga buhi gihapon ang row ug
    // ── kompleto ang detalye.
    // ──
    // ── Tulo ka rason sa pagkahuman: milabay ang deadline, napuno ang slots,
    // ── o gisira sa staff. Duha ang GILABAN:
    // ──   1. Ang wala pa na-approve (pending/rejected) — naa pa sila sa Job
    // ──      Vacancy Request, gi-ayo pa sa employer, dili pa nahuman.
    // ──   2. Ang Job Fair nga naghulat pa ug event — sirado, apan buhi. ──
    public function scopeInactive($query)
    {
        return $query->where('posting_status', 'approved')
            ->where(function ($q) {
                $q->whereDate('deadline', '<', now()->toDateString())
                  ->orWhereRaw(self::groupHiredCountSql() . ' >= `job_qualifications`.`slots`', ['hired'])
                  ->orWhere(function ($q2) {
                      $q2->where('status', '!=', 'open')
                         ->where('schedule_type', '!=', 'job_fair');
                  });
            });
    }

    // ── Accessor: check kung naglapas na ang deadline (real-time, base sa server date) ──
    public function getIsExpiredAttribute()
    {
        return $this->deadline && \Carbon\Carbon::parse($this->deadline)->endOfDay()->isPast();
    }

    // ── Ang hired count sa tibuok group, para sa usa ka row nga naa na sa
    // ── memorya. Mogamit sa `group_hired_count` kung gi-load na sa
    // ── scopeWithGroupHiredCount, kay kung dili, usa ka query kada tawag. ──
    public function getGroupHiredAttribute(): int
    {
        if (isset($this->attributes['group_hired_count'])) {
            return (int) $this->attributes['group_hired_count'];
        }

        return $this->groupPesoHires + $this->groupExternalHires;
    }

    // ── Ang na-hire pinaagi sa PESO — mao ni ang maihap nga placement. ──
    public function getGroupPesoHiresAttribute(): int
    {
        if (isset($this->attributes['group_peso_hires'])) {
            return (int) $this->attributes['group_peso_hires'];
        }

        return (int) Application::whereIn('job_id', $this->groupJobIds())
            ->where('status', 'hired')
            ->count();
    }

    // ── Ang gi-report sa employer nga gi-hire gawas sa PESO. ──
    public function getGroupExternalHiresAttribute(): int
    {
        if (isset($this->attributes['group_external_hires'])) {
            return (int) $this->attributes['group_external_hires'];
        }

        return (int) self::whereIn('job_qualifications_id', $this->groupJobIds())
            ->sum('external_hires');
    }

    // ── Ang tanan nga row sa parehas nga posting group, apil ang kaugalingon. ──
    public function groupJobIds(): array
    {
        return self::where('posting_group_id', $this->group_key)
            ->orWhere('job_qualifications_id', $this->group_key)
            ->pluck('job_qualifications_id')
            ->all();
    }

    // ── Ang usa ka ngalan sa kahimtang sa posting, gikan sa parehas nga tulo
    // ── ka kondisyon nga gigamit sa scopeActive/scopeInactive. Walay bag-ong
    // ── column: kung magbutang ug column, magkalahi ra dayon kini sa scope
    // ── nga mao ang tinuod nga basihan sa Active Job Vacancy page.
    // ──
    // ── Timan-i: ang `status` = 'closed' duha ka managlahi ang kahulogan —
    // ── ang bag-ong posting mag-sugod nga closed samtang nag-hulat sa staff,
    // ── ug ang nahuman nga posting closed pud. Ang posting_status maoy
    // ── mag-lain sa duha, mao nga siya ang una nga susihon. ──
    //
    // ── PESO interview 2026-08-13: "Mahimo kini i-update basta active pa ang
    // ── vacancy... Pero kung closed o expired na, dili na dapat ma-update." ──
    public function getLifecycleStatusAttribute(): string
    {
        if ($this->posting_status === 'pending') {
            return 'pending';
        }
        if ($this->posting_status === 'rejected') {
            return 'rejected';
        }
        if ($this->group_hired >= (int) $this->slots) {
            return 'filled';
        }
        if ($this->is_expired) {
            return 'expired';
        }
        // Ang Job Fair nga posting na-approve na apan magpabilin nga closed
        // hangtod maka-buhat ang staff ug event. Dili ni "closed" — naghulat.
        if ($this->status !== 'open') {
            return $this->schedule_type === 'job_fair' ? 'waiting' : 'closed';
        }
        return 'active';
    }

    // ── Ang usa ka bakante mahimong i-post sa tulo ka paagi. Kining tulo ka
    // ── pulong makita sa employer, sa jobseeker, sa staff ug sa admin, mao nga
    // ── usa ra ka lugar ang naghupot niini. Kaniadto gisulat kini pinaagi sa
    // ── kamot sa napulo ka file, ug mao nga ang pag-ilis sa usa ka pulong
    // ── nahimong 34 ka file nga trabaho. ──
    public const SCHEDULE_TYPE_LABELS = [
        'company_interview' => 'Company Interview',
        'inhouse'           => 'In-house',
        'job_fair'          => 'Job Fair',
    ];

    /**
     * The label for a schedule type, for any of the three audiences.
     *
     * Falls back to Company Interview, which is what the old `match` arms did:
     * a legacy row with no schedule_type is a posting the employer handles at
     * their own office, because that is all there was before in-house and job
     * fair postings existed.
     */
    public static function scheduleTypeLabel(?string $type): string
    {
        return self::SCHEDULE_TYPE_LABELS[$type] ?? 'Company Interview';
    }

    public const LIFECYCLE_LABELS = [
        'pending'  => 'Pending Approval',
        'rejected' => 'Rejected',
        'active'   => 'Active',
        'waiting'  => 'Waiting for Job Fair',
        'filled'   => 'Filled',
        'expired'  => 'Expired',
        'closed'   => 'Closed',
    ];

    // ── Ang employer makausab samtang wala pa kini nahuman: nag-hulat sa
    // ── approval, gi-balibad (aron matul-id ug ma-resubmit), buhi pa, o
    // ── nag-hulat pa ug job fair event. ──
    public const EDITABLE_LIFECYCLE = ['pending', 'rejected', 'active', 'waiting'];

    public function getIsEditableAttribute(): bool
    {
        return in_array($this->lifecycle_status, self::EDITABLE_LIFECYCLE, true);
    }

    // ── Ngano dili ma-edit. Null kung ma-edit ra. ──
    public function getLifecycleBlockReasonAttribute(): ?string
    {
        return match ($this->lifecycle_status) {
            'filled'  => 'All ' . $this->slots . ' slot(s) for this position have been filled. Post it again if you are hiring more.',
            'expired' => 'This posting expired on ' . optional($this->deadline)->format('M d, Y') . '. Post it again if you are still hiring.',
            'closed'  => 'This posting is closed and can no longer be edited.',
            // Editable gihapon kini — ang teksto nagsulti ra kung kanus-a siya
            // makita sa jobseeker, dili nga gibabagan siya.
            'waiting'  => \App\Support\JobFairPostingWindow::liveNote(),
            'pending'  => \App\Support\JobPostingNotice::pendingNote($this->schedule_type)
                          ?? 'PESO is reviewing this posting. Jobseekers will see it once it is approved.',
            'rejected' => $this->remarks
                          ? 'PESO did not approve this posting. Reason: ' . $this->remarks
                          : 'PESO did not approve this posting.',
            default    => null,
        };
    }
}