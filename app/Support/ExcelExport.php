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
