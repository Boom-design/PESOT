<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Job Fair — daily check para i-send ang attendance confirmation sa mga jobseeker nga naay event karong adlawa ──
Schedule::command('jobfair:send-attendance-confirmations')->dailyAt('06:00');

// ── Job Fair — i-lapse ang imbitasyon nga wala natubag sulod sa usa ka semana.
// ──
// ── 06:05 — human sa update-event-statuses (00:05) aron sakto na ang status
// ── nga iyang basahon, ug sa dili pa ang reminder sa 06:15, aron ang na-lapse
// ── nga imbitasyon dili pa gukdon nianang buntaga. ──
Schedule::command('jobfair:expire-invitations')->dailyAt('06:05');

// ── Job Fair — i-pahinumdom ang employer nga wala pa mo-tubag sa dili pa
// ── mo-lapse ang iyang imbitasyon. Kaniadto usa ni ka buton nga pindoton sa
// ── staff; karon ang sistema na mismo.
// ──
// ── Kaniadto giihap ni gikan sa adlaw sa event — lima ka adlaw sa wala pa,
// ── nga human na sa DOLE cutoff ug ulahi na para makatabang sa pagpuno sa
// ── roster. Karon giihap gikan sa deadline sa imbitasyon mismo. ──
Schedule::command('jobfair:send-confirmation-reminders')->dailyAt('06:15');

// ── In-house — daily check para i-send ang participation reminder 5 days sa dili pa ang schedule ──
Schedule::command('inhouse:send-participation-reminders')->dailyAt('06:30');

// ── Job Fair — daily auto-update sa event status (upcoming → ongoing → completed) base sa event_date ──
Schedule::command('jobfair:update-event-statuses')->dailyAt('00:05');

// ── Job Fair — i-abli ang mga posting 5 ka adlaw sa dili pa ang event, dili sa
// ── pag-create sa event. Ang event usa ka bulan nga abante, mao nga kung mo-abli
// ── sila dayon, upat ka semana nga magtan-aw ang jobseeker ug bakante nga layo
// ── pa kaayo ang adlaw nga kaadtoan. Lima ka adlaw sa wala pa, bag-o pa ang
// ── bakante sa iyang hunahuna inig-abot sa adlaw sa fair.
// ──
// ── Modagan human sa update-event-statuses aron sakto na ang status sa event
// ── nga basahon niini. ──
Schedule::command('jobfair:open-postings')->dailyAt('00:10');

// ── Employer Requirements — daily check para i-notify ang employer 1 week sa dili pa ma-expire ang usa ka document ──
Schedule::command('employer-requirements:send-expiry-warnings')->dailyAt('07:00');

// ── Employer Requirements — daily check para i-flip ang status ngadto 'expired' kung na-abot na ang expiry date ──
Schedule::command('employer-requirements:expire')->dailyAt('07:15');

// ── Employer nga hunong na sa pagpasa ug bakante.
// ──
// ── Ang kompanya nga nagsara dili mag-login para lang magsulti nga nagsara na
// ── sila, mao nga ang opisina ang mangutana. Ang una mo-email human sa usa ka
// ── bulan nga walay bag-ong bakante; ang ikaduha mo-disable sa account kung
// ── walay tubag sulod sa usa ka semana. Managlahi ang oras aron ang tubag sa
// ── buntag mismo dili malaktawan sa ikaduha. ──
Schedule::command('employers:send-inactivity-warnings')->dailyAt('07:30');
Schedule::command('employers:disable-inactive')->dailyAt('07:45');

// ── Job Postings — daily close sa nalabyan na ang deadline, ug notice sa
// ── employer nga pwede na siya mo-post pag-usab.
// ──
// ── Wala nay auto-DELETE sa job postings. Ang row magpabilin aron kompleto
// ── ang Reports — apil ang qualification requirements ug ang listahan sa
// ── na-hire. Ang status ra ang mausab ngadto 'closed'. ──
Schedule::command('jobs:expire-monthly')->dailyAt('00:15');
