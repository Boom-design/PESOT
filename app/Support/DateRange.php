<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;

// ── PESO interview 2026-08-13: "kung January 1 hangtod January 31 ang gipili
// ── nga date range, makita ang total applicants sulod ana nga period... Dili
// ── kinahanglan nga calendar mismo; pwede ra nga date filter diin ang user
// ── mopili og starting date ug ending date."
// ──
// ── Usa ra ka pagbasa sa `from`/`to` para sa tanan nga report page, aron ang
// ── employer, staff ug admin dili magkalahi ug sabot sa parehas nga URL.
// ──
// ── Ang sayop nga petsa mahimong "walay filter", DILI query nga sigurado nga
// ── walay resulta — parehas nga disiplina sa dateFilterPart() sa
// ── CompanyWebController. Ang report nga blangko tungod sa na-type nga sayop
// ── kay bakak nga report. ──
class DateRange
{
    public readonly ?Carbon $from;
    public readonly ?Carbon $to;

    private function __construct(?Carbon $from, ?Carbon $to)
    {
        // Kung nabaliktad ang gi-type, i-usab imbes i-refuse — klaro man ang
        // buot ipasabot sa tawo.
        if ($from && $to && $from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        $this->from = $from?->startOfDay();
        $this->to   = $to?->endOfDay();
    }

    public static function fromRequest(Request $request, string $fromKey = 'from', string $toKey = 'to'): self
    {
        $from = self::parse($request->input($fromKey));
        $to   = self::parse($request->input($toKey));

        // ── Usa ka tuig, usa ka pagpili.
        // ──
        // ── "Pila ang na-hire ninyo sa 2026" mao ang pangutana nga kanunay
        // ── gipangutana, ug ang pagtubag niini pinaagi sa duha ka date box
        // ── nagpasabot nga i-type ang January 1 ug December 31 sa kada
        // ── higayon. Ang tuig mao ang mopuno sa duha ka kilid.
        // ──
        // ── Ang gi-type nga petsa mo-una: kung naay gibutang nga from o to,
        // ── kana ang gipangayo sa tawo, dili ang dropdown. ──
        $year = $request->input('year');

        if (!$from && !$to && is_string($year) && preg_match('/^\d{4}$/', trim($year))) {
            $year = (int) $year;
            $from = Carbon::create($year, 1, 1);
            $to   = Carbon::create($year, 12, 31);
        }

        return new self($from, $to);
    }

    /** The year this range covers, when it covers exactly one. */
    public function wholeYear(): ?int
    {
        if (!$this->from || !$this->to) return null;
        if ($this->from->year !== $this->to->year) return null;

        return ($this->from->isSameDay($this->from->copy()->startOfYear())
             && $this->to->isSameDay($this->to->copy()->endOfYear()))
            ? $this->from->year
            : null;
    }

    private static function parse($value): ?Carbon
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        try {
            $date = Carbon::createFromFormat('Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }

        // Ang createFromFormat mo-roll over: ang "2026-13-45" mahimong
        // Feb 14, 2027 imbes mo-sayop. Ang round-trip check maoy mag-lain sa
        // tinuod nga petsa gikan sa na-usab nga numero.
        return ($date && $date->format('Y-m-d') === $value) ? $date : null;
    }

    public function isActive(): bool
    {
        return $this->from !== null || $this->to !== null;
    }

    // ── I-apply sa query. Ang walay gi-butang nga kilid bukas — ang "from"
    // ── ra nagpasabot "gikan ana hangtod karon". ──
    public function apply($query, string $column)
    {
        if ($this->from) {
            $query->where($column, '>=', $this->from);
        }
        if ($this->to) {
            $query->where($column, '<=', $this->to);
        }
        return $query;
    }

    // ── Closure nga porma, para sa whereHas ug sa ubang nested nga query. ──
    public function filter(string $column): \Closure
    {
        return fn($query) => $this->apply($query, $column);
    }

    // ── Basahon sa tawo, gamiton sa caption ug sa printed nga report. ──
    public function label(): string
    {
        if ($year = $this->wholeYear()) {
            return 'Year ' . $year;
        }
        if ($this->from && $this->to) {
            return $this->from->format('M d, Y') . ' – ' . $this->to->format('M d, Y');
        }
        if ($this->from) {
            return 'From ' . $this->from->format('M d, Y');
        }
        if ($this->to) {
            return 'Up to ' . $this->to->format('M d, Y');
        }
        return 'All dates';
    }

    // ── Para sa pag-balik sa parehas nga range sa export ug print link. ──
    public function queryParams(string $fromKey = 'from', string $toKey = 'to'): array
    {
        return array_filter([
            $fromKey => $this->from?->format('Y-m-d'),
            $toKey   => $this->to?->format('Y-m-d'),
        ]);
    }
}
