<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PESO Job Fair
    |--------------------------------------------------------------------------
    |
    | Office rules that are policy rather than code. They live here so the
    | office can change them without a developer editing a controller.
    |
    */

    'jobfair' => [

        // How many employers must confirm their participation before
        // jobseekers are notified about the event. The office is still
        // settling on 3 vs 5, so it is a setting instead of a literal.
        'min_confirmed_employers' => (int) env('JOBFAIR_MIN_EMPLOYERS', 3),

        // How far ahead a job fair event has to be created.
        //
        // PESO Job Fair staff, 2026-08-23: a fair is planned a month out. The
        // invitations go to the employers on the day it is created, and they
        // need that month to decide, clear the date and prepare a booth.
        'create_lead_days' => (int) env('JOBFAIR_CREATE_LEAD_DAYS', 30),

        // How long an employer has to answer a job fair invitation before it
        // lapses.
        //
        // PESO Job Fair staff, 2026-08-23: "pag 1 week dili mo accept si
        // employer mangita syag lahi na employer." The lapse is the office's
        // signal, not a punishment — the event is created a month out, so a
        // week gives the staff three more to find someone else. An employer
        // who answers late is still taken.
        'confirm_window_days' => (int) env('JOBFAIR_CONFIRM_DAYS', 7),

        // How many days before an invitation lapses the employer is reminded.
        //
        // This used to count back from the event date, which put the reminder
        // five days out — after the DOLE cutoff below, too late to be any use
        // in filling the roster. It now counts back from the invitation's own
        // deadline, which is what the employer has to act on.
        'reminder_days_before' => (int) env('JOBFAIR_REMINDER_DAYS', 2),

        // How many days before the event the confirmed roster is submitted to
        // DOLE.
        //
        // Project manager, 2026-08-23: "10 days before the job fair dapat ma
        // occupied niya tanan ang slot sa employer para maoy ipasa niya sa
        // DOLE." This is the office's own deadline to have the roster full,
        // not a door shut on employers — a confirmation after this date is
        // still accepted and is marked as arriving after the submission.
        'dole_cutoff_days_before' => (int) env('JOBFAIR_DOLE_CUTOFF_DAYS', 10),

        // How many days before the event the approved job fair postings become
        // visible to jobseekers.
        //
        // An event is created a month ahead, so opening the postings the moment
        // it exists would leave jobseekers looking at vacancies they cannot act
        // on for four weeks. Five days out the fair is close enough to be worth
        // reading. Kept separate from reminder_days_before: the office may want
        // to chase employers sooner than it advertises to jobseekers.
        'open_postings_days_before' => (int) env('JOBFAIR_OPEN_DAYS', 5),

        // Minimum match percentage a jobseeker must reach on at least one of
        // the event's postings before they are told about the job fair.
        //
        // PESO interview 2026-08-13: anyone may APPLY to a vacancy no matter
        // their match — the employer verifies at the interview. The job fair
        // notice is different: only qualified jobseekers are texted, so the
        // office does not pay to invite people the employers cannot use.
        'qualified_match_percentage' => (int) env('JOBFAIR_QUALIFIED_MATCH', 75),

    ],

    /*
    |--------------------------------------------------------------------------
    | PESO Office
    |--------------------------------------------------------------------------
    |
    | Shown when an employer cannot get into their account any other way — the
    | HR who registered has left and the reset code goes to a mailbox nobody at
    | the company can open. The only route left is to phone the office.
    |
    | The default is a PLACEHOLDER. Set the real number in .env before go-live
    | or that screen is useless to whoever reads it.
    |
    */

    'office' => [
        'contact_number' => env('PESO_CONTACT_NUMBER', '(088) 000-0000'),
        'hours'          => env('PESO_OFFICE_HOURS', 'Monday to Friday, 8:00 AM – 5:00 PM'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Scheduling
    |--------------------------------------------------------------------------
    */

    'schedule' => [

        // How many days ahead the earliest bookable date is.
        //
        // 1 means tomorrow: the office cannot take a booking for today. There
        // is preparation to do before an employer arrives — the room, the
        // applicants to call, the staff to free — and none of it fits into
        // the same day the request comes in.
        'min_lead_days' => (int) env('PESO_SCHEDULE_LEAD_DAYS', 1),

        // How many different companies the PESO Office can host for in-house
        // interviews on one day.
        //
        // LRA staff, 2026-08-23: two, not the three the system had. It is the
        // room that sets this number, so it holds for LRA and SRA alike — one
        // office, one limit. If it were enforced for local bookings only, an
        // overseas booking would slip past it and three companies would still
        // end up in the room.
        'inhouse_daily_companies' => (int) env('PESO_INHOUSE_DAILY_COMPANIES', 2),

        // How long after an in-house interview the report becomes visible to
        // staff.
        //
        // LRA staff, 2026-08-23: a week. The employer needs that time to decide
        // on the people they saw, and a report read before then is a report of
        // blanks.
        'report_delay_days' => (int) env('PESO_INHOUSE_REPORT_DAYS', 7),

    ],

    /*
    |--------------------------------------------------------------------------
    | Employer activity
    |--------------------------------------------------------------------------
    |
    | A company that closes down simply stops posting. Nobody tells the office,
    | because logging in to say so is a waste of the employer's time — so the
    | office cannot tell a dead account from one that is merely between hires.
    |
    | The sweep asks. After `inactive_months` with no new vacancy the employer
    | is emailed; `inactivity_grace_days` later, if they have not written back
    | inside the system, the account goes dormant until staff switch it on.
    |
    */

    'employer' => [

        // Months with no new job vacancy before the office asks what happened.
        //
        // PESO, 2026-08-24: one month. Two was considered and rejected — a
        // company that has shut down would sit in the list for two months
        // before anyone noticed.
        'inactive_months' => (int) env('PESO_EMPLOYER_INACTIVE_MONTHS', 1),

        // Months with no new vacancy before the office asks a second time and
        // tells the desk that owns the account.
        //
        // PESO, 2026-08-30: the office asks twice before anyone acts. The first
        // letter is a reminder; the second is the one that starts a clock, and
        // it is the point at which Job Vacancy (local) or SRA (overseas) is
        // brought in. Two months of silence is the office's own threshold for
        // "this is no longer just a quiet season".
        'inactive_second_months' => (int) env('PESO_EMPLOYER_INACTIVE_SECOND_MONTHS', 2),

        // Days the employer has to answer the second letter before the desk is
        // told the account is theirs to switch off. The answer is typed inside
        // the system: there is no inbox here, so a reply sent to the Gmail
        // account cannot be detected.
        //
        // Nothing is disabled automatically. When this runs out the sweep
        // notifies staff and stops; a person makes the last decision, which is
        // how the office has always done it.
        'inactivity_grace_days' => (int) env('PESO_EMPLOYER_INACTIVITY_GRACE_DAYS', 7),

        // Months into the new year that an employer may keep working on last
        // year's CDO business permit.
        //
        // The permit is issued per calendar year and lapses on the 31st of
        // December, but the city does not hand out the new one on the 1st of
        // January — renewal season runs through the first quarter. PESO allows
        // three months for it. A 2026 permit therefore carries the account
        // until the 31st of March 2027, and the account is restricted on the
        // 1st of April until the 2027 permit is uploaded.
        'business_permit_grace_months' => (int) env('PESO_BUSINESS_PERMIT_GRACE_MONTHS', 3),

    ],

    /*
    |--------------------------------------------------------------------------
    | Holidays
    |--------------------------------------------------------------------------
    |
    | App\Support\Holidays already computes the dates that follow a rule —
    | fixed dates, Holy Week, National Heroes Day. What it cannot compute is
    | a proclamation: Eid'l Fitr and Eid'l Adha follow the lunar calendar and
    | are announced yearly, and Malacanang adds one-off dates.
    |
    | Type those here as 'Y-m-d' => 'Name'. They override the computed list,
    | so this is also where a wrong date gets corrected. Nothing breaks if the
    | list is empty — a missing holiday only means the calendar does not warn.
    |
    */

    'holidays' => [

        'extra' => [
            // '2026-03-20' => "Eid'l Fitr",
            // '2026-05-27' => "Eid'l Adha",
            // '2026-08-28' => 'Higalaay Festival (Cagayan de Oro)',
        ],

    ],

];
