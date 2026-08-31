{{--
    Printable nga report nga naay signatory block.

    PESO interview 2026-08-13: "ang AO ang responsable sa pag-submit sa report.
    Ang report mahimong i-download ug i-print, ug adunay mga report nga
    kinahanglan adunay signatory. Ang mga printed reports mahimong isumite sa
    Mayor's Office ug DOL."

    Browser print ra ang gamiton — walay PDF library, mao nga walay bag-ong
    dependency ug walay bayronon.

    Gamit gikan sa controller:
        return view('reports.print', [
            'title'    => 'Hired Applicants',
            'subtitle' => 'Golden Harvest Foods Inc.',
            'range'    => $range,
            'columns'  => ['Job Position', 'Schedule Type', 'Hired'],
            'rows'     => [['Welder', 'Company Interview', '2 / 3'], ...],
            'preparedBy' => 'Maria Santos',
        ]);
--}}
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
@include('partials.head-brand', ['pageTitle' => $title . ' — PESO'])
<style>
    * { box-sizing: border-box; }
    body {
        font-family: "Segoe UI", Arial, sans-serif;
        color: #1c1f24;
        margin: 0;
        padding: 28px 34px;
        font-size: 12px;
    }
    .head { text-align: center; border-bottom: 2px solid #1c1f24; padding-bottom: 10px; margin-bottom: 6px; }
    .head .office { font-size: 15px; font-weight: 700; letter-spacing: .4px; }
    .head .unit { font-size: 12px; }
    .head .addr { font-size: 10.5px; color: #555; }

    h1 { font-size: 15px; margin: 16px 0 2px; text-align: center; text-transform: uppercase; letter-spacing: .5px; }
    .subtitle { text-align: center; font-size: 12px; margin-bottom: 2px; }
    .period { text-align: center; font-size: 11.5px; color: #444; margin-bottom: 16px; }

    table { width: 100%; border-collapse: collapse; margin-bottom: 26px; }
    th, td { border: 1px solid #9aa0a6; padding: 6px 8px; text-align: left; vertical-align: top; }
    th { background: #eef1f4; font-size: 11px; text-transform: uppercase; letter-spacing: .3px; }
    td { font-size: 11.5px; }
    tbody tr:nth-child(even) td { background: #fafbfc; }
    .empty { text-align: center; color: #666; font-style: italic; padding: 18px; }

    .totals { font-size: 11.5px; margin-bottom: 26px; }
    .totals strong { display: inline-block; min-width: 190px; }

    .signatures { display: flex; gap: 60px; margin-top: 46px; page-break-inside: avoid; }
    .sig { flex: 1; }
    .sig .line { border-top: 1px solid #1c1f24; margin-top: 40px; padding-top: 4px; font-weight: 700; }
    .sig .role { font-size: 10.5px; color: #555; }

    .footnote { margin-top: 34px; font-size: 10px; color: #666; border-top: 1px solid #d5d9de; padding-top: 6px; }

    .toolbar { text-align: center; margin-bottom: 18px; }
    .toolbar button {
        font: inherit; padding: 7px 16px; border-radius: 6px; cursor: pointer;
        border: 1px solid #1c6b52; background: #1c6b52; color: #fff;
    }
    @media print {
        .toolbar { display: none; }
        body { padding: 0; }
        thead { display: table-header-group; }
    }
</style>
</head>
<body>

<div class="toolbar">
    <button type="button" onclick="window.print()">Print this report</button>
</div>

<div class="head">
    <div class="office">CITY GOVERNMENT OF CAGAYAN DE ORO</div>
    <div class="unit">Public Employment Service Office (PESO)</div>
    <div class="addr">City Hall, Cagayan de Oro City, Misamis Oriental</div>
</div>

<h1>{{ $title }}</h1>
@if(!empty($subtitle))
    <div class="subtitle">{{ $subtitle }}</div>
@endif
<div class="period">Covering period: {{ $range->label() }}</div>

<table>
    <thead>
        <tr>
            @foreach($columns as $column)
                <th>{{ $column }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $row)
            <tr>
                @foreach($row as $cell)
                    <td>{{ $cell }}</td>
                @endforeach
            </tr>
        @empty
            <tr><td class="empty" colspan="{{ count($columns) }}">No records for this period.</td></tr>
        @endforelse
    </tbody>
</table>

@if(!empty($totals))
<div class="totals">
    @foreach($totals as $totalLabel => $totalValue)
        <div><strong>{{ $totalLabel }}:</strong> {{ $totalValue }}</div>
    @endforeach
</div>
@endif

<div class="signatures">
    <div class="sig">
        <div class="line">{{ $preparedBy ?? '' }}</div>
        <div class="role">Prepared by</div>
    </div>
    <div class="sig">
        <div class="line">&nbsp;</div>
        <div class="role">Noted by — PESO Manager</div>
    </div>
</div>

<div class="footnote">
    Generated from the PESO Cagayan de Oro job management system on
    {{ now()->format('M d, Y \a\t g:i A') }}. Figures cover only the period stated above.
</div>

</body>
</html>
