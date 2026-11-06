<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

// ── PESO interview 2026-08-13: "ang report nga makita sa dashboard pwede
// ── i-download ug ma-convert ngadto sa Excel file aron magamit sa
// ── documentation, monitoring, ug reporting."
// ──
// ── Ang una nga buhat CSV, kay ang tuyo mao ang paglikay sa bag-ong
// ── dependency samtang walay budget ang proyekto. Apan ang gipangayo sa
// ── opisina tinuod nga Excel nga file, dili CSV nga giablihan sa Excel — ang
// ── CSV walay bold nga ulohan, walay gilapdon sa kolum, ug mangutana pa ang
// ── Excel kada abli. Ang phpoffice/phpspreadsheet libre ug MIT, walay bayad
// ── bisan human sa deploy, mao nga wala nay hinungdan nga magpabilin sa CSV.
// ──
// ── Ang .xlsx, dili .xls: ang .xls kay binuhat sa 1997 ug ang Excel
// ── mopasidaan na kada abli niini. Ang .xlsx maoy naandan sa Excel karon ug
// ── siya ang moabli nga walay reklamo.
class ExcelExport
{
    /** Ang berde sa PESO, para sa ulohan nga laray. */
    private const HEADER_BG = 'FF28812F';

    public static function stream(string $filename, array $columns, iterable $rows, array $preamble = []): StreamedResponse
    {
        // Ang filename kay gikan sa code, dili sa user — apan i-sanitize
        // gihapon aron dili gyud mahimong header injection ang usa ka
        // pag-usab ugma.
        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);
        $safeName = preg_replace('/\.(csv|txt|xls)$/i', '', $safeName);
        if (!str_ends_with(strtolower($safeName), '.xlsx')) {
            $safeName .= '.xlsx';
        }

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Report');

        $line = 1;

        // ── PREPARED BY — kinsa ang nagkuha niini nga kopya.
        // ──
        // ── PESO CDO, 2026-11-06: ang gi-print nga report permahan sa staff
        // ── nga nagpasa niini, mao nga ang iyang ngalan kinahanglan naa sa
        // ── papel. Sa wala pa kini, ang kopya gikan sa LRA ug ang kopya
        // ── gikan sa SRA managsama gyud — walay bisan unsa sa file nga
        // ── nagsulti kinsa ang mibarog para niini.
        // ──
        // ── Dinhi siya, dili sa pito ka export nga method: ang gidugang
        // ── ugma makakuha niini nga walay mahinumdom.
        $preamble = array_merge($preamble, self::preparedBy($preamble));

        // ── PREAMBLE — kinsa, unsa, ug kanus-a. Naa siya sa taas sa ulohan
        // ── aron ang gi-print nga kopya makatubag sa iyang kaugalingon. ──
        foreach ($preamble as $entry) {
            $cells = is_array($entry) ? array_values($entry) : [$entry];
            foreach ($cells as $i => $value) {
                self::put($sheet, $i + 1, $line, $value);
            }
            $sheet->getStyle([1, $line, 1, $line])->getFont()->setBold(true);
            $line++;
        }
        if ($preamble) {
            $line++;
        }

        // ── ULOHAN ──
        $headerRow = $line;
        foreach (array_values($columns) as $i => $label) {
            self::put($sheet, $i + 1, $headerRow, $label);
        }

        $lastColumn = max(count($columns), 1);
        $headerRange = [1, $headerRow, $lastColumn, $headerRow];

        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB(self::HEADER_BG);
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($headerRow)->setRowHeight(20);

        $line = $headerRow + 1;

        // ── DATA ──
        foreach ($rows as $row) {
            foreach (array_values((array) $row) as $i => $cell) {
                self::put($sheet, $i + 1, $line, $cell);
            }
            $line++;
        }

        $lastRow = max($line - 1, $headerRow);

        // Usa ka utlanan palibot sa lamesa, aron mabasa ang gi-print nga kopya.
        if ($lastRow > $headerRow) {
            $sheet->getStyle([1, $headerRow, $lastColumn, $lastRow])
                ->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()->setARGB('FFD1D5DB');
        }

        // Ang ulohan magpabilin nga makita samtang nag-scroll.
        $sheet->freezePane([1, $headerRow + 1]);

        for ($col = 1; $col <= $lastColumn; $col++) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $safeName, [
            'Content-Type'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    /**
     * Isulat ang usa ka cell sa tipo nga angay niya.
     *
     * Ang numero isulat isip numero aron ma-sum siya sa Excel. Ang tanan nga
     * lain isulat isip teksto nga tin-aw — mao kini ang nagpapas sa CSV formula
     * injection: ang cell nga nagsugod sa "=" dili na mahimong formula kung
     * gisulat siya nga string, mao nga wala nay kinahanglan nga kudlit sa
     * atubangan sama sa CSV.
     */

    /**
     * Ang tulo ka laray nga nagsulti kinsa ang nagkuha niini nga kopya.
     *
     * Ang ngalan gikan sa staff record, dili sa users table: didto ang
     * pangalan nga gigamit sa opisina, ug mao pud nay giprint sa ubang
     * report ("by Juan Dela Cruz" sa In-house nga lista).
     *
     * Ang desk giapil kay ang parehas nga report nga gikan sa LRA ug sa SRA
     * managlahi ug sakup, ug ang nagbasa sa papel kinahanglan masayod asa
     * kini gikan.
     */
    private static function preparedBy(array $existing): array
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user) {
            return [];
        }

        // A name with no middle name comes back with two spaces in it,
        // because full_name joins three parts whatever is there.
        $tidy = fn ($s) => trim(preg_replace('/\s+/', ' ', (string) $s));

        $name = $tidy($user->name) ?: $user->email;
        $desk = null;

        if ($user->role === 'staff') {
            $staff = \App\Models\Staff::where('user_id', $user->users_id)->first();
            if ($staff) {
                $name = $tidy($staff->full_name) ?: $name;
                $desk = [
                    'lra'         => 'LRA Staff',
                    'sra'         => 'SRA Staff',
                    'job_fair'    => 'Job Fair Staff',
                    'job_vacancy' => 'Job Vacancy Staff',
                ][$staff->staff_role] ?? 'PESO Staff';
            }
        } elseif ($user->role === 'admin') {
            $desk = 'Administrator';
        } elseif ($user->role === 'company') {
            $desk = 'Employer';
        }

        $rows = [['Prepared by:', $name]];

        if ($desk) {
            $rows[] = ['', $desk];
        }

        // Some reports already stamp the moment as "Generated". Two
        // timestamps a line apart, worded differently, read as a mistake.
        $stamped = false;
        foreach ($existing as $entry) {
            $first = is_array($entry) ? (string) (array_values($entry)[0] ?? '') : (string) $entry;
            if (stripos($first, 'generated') === 0) {
                $stamped = true;
                break;
            }
        }

        if (!$stamped) {
            $rows[] = ['Downloaded:', now()->format('F d, Y g:i A')];
        }

        return $rows;
    }

    private static function put($sheet, int $column, int $row, $value): void
    {
        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('Y-m-d');
        }

        if ($value === null || $value === '') {
            return;
        }

        // Ang numero nga nagsugod sa 0 (contact number, invitation code) dili
        // numero — identifier siya, ug mawala ang unang 0 kung isulat nga numero.
        $isPlainNumber = is_int($value) || is_float($value)
            || (is_string($value) && is_numeric($value) && !str_starts_with($value, '0') && !str_starts_with($value, '+'));

        if ($isPlainNumber) {
            $sheet->setCellValue([$column, $row], $value + 0);
            return;
        }

        $sheet->setCellValueExplicit([$column, $row], (string) $value, DataType::TYPE_STRING);
    }
}
