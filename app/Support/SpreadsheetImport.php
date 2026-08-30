<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;

// ── Ang sumbanan sa ExcelExport, balitok.
// ──
// ── PESO Job Fair staff, 2026-08-23: "dapat pud maka import ang job fair staff
// ── sa iyang manual nga excel report into sa system." Ang iyang report lahi sa
// ── gihimo sa sistema — mao gyud nang punto niini — mao nga walay porma nga
// ── gipahamtang dinhi. Bisan unsang kolum ang naa sa file mao ang mahimong
// ── kolum sa lamesa.
// ──
// ── Modawat ug .xlsx, .xls ug .csv. Kinahanglan gyud ang .xlsx sukad ang
// ── ExcelExport nag-hatag na ug .xlsx: ang staff mo-download ug report,
// ── usbon niya sa Excel, dayon i-upload balik. Kung .csv ra ang dawaton,
// ── mabalda siya sa iyang kaugalingong file.
class SpreadsheetImport
{
    /** Ang labing daghan nga kolum ug laray nga dawaton sa usa ka file. */
    public const MAX_COLUMNS = 40;
    public const MAX_ROWS    = 2000;

    /** Ang extension nga mabasa. */
    public const ALLOWED = ['xlsx', 'xls', 'csv', 'txt'];

    /**
     * Basaha ang usa ka na-upload nga workbook o CSV.
     *
     * Mo-return ug ['headers', 'rows', 'truncated'].
     */
    public static function read(UploadedFile $file, int $maxRows = self::MAX_ROWS, int $maxColumns = self::MAX_COLUMNS): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        $lines = in_array($extension, ['xlsx', 'xls'], true)
            ? self::workbookLines($file)
            : self::csvLines($file);

        return self::normalise($lines, $maxRows, $maxColumns);
    }

    /** Ang laray gikan sa .xlsx o .xls, isip plain nga array sa teksto. */
    private static function workbookLines(UploadedFile $file): \Generator
    {
        $reader = IOFactory::createReaderForFile($file->getRealPath());
        // Ang format ug ang formula wala kinahanglana — ang gipangita mao ang
        // sulod sa cell, ug ang pagbasa nga walay format mas gaan sa memorya.
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($file->getRealPath());

        foreach ($spreadsheet->getActiveSheet()->toArray(null, true, false, false) as $line) {
            yield $line;
        }

        $spreadsheet->disconnectWorksheets();
    }

    /** Ang laray gikan sa .csv o .txt. */
    private static function csvLines(UploadedFile $file): \Generator
    {
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            return;
        }

        try {
            while (($line = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                // fgetcsv mo-return ug [null] para sa usa ka linya nga blangko.
                if ($line === [null]) {
                    continue;
                }
                yield $line;
            }
        } finally {
            fclose($handle);
        }
    }

    /** Ulohan gikan sa unang laray, dayon ang mga laray, pareho ang gilapdon. */
    private static function normalise(iterable $lines, int $maxRows, int $maxColumns): array
    {
        $headers   = [];
        $rows      = [];
        $truncated = false;

        {
            foreach ($lines as $line) {

                $cells = array_map(fn($cell) => trim((string) $cell), (array) $line);

                if (!$headers) {
                    // Ang UTF-8 BOM nga gibutang sa Excel mosunod sa unang
                    // cell. Kung dili ni tangtangon, ang unang ulohan moabot
                    // nga "ï»¿Name" ug dili gyud siya makita sa tawo nga
                    // nagtan-aw sa lamesa.
                    if (isset($cells[0])) {
                        $cells[0] = preg_replace('/^\xEF\xBB\xBF/', '', $cells[0]);
                        $cells[0] = trim($cells[0]);
                    }

                    if (self::isBlank($cells)) {
                        continue;   // laktawi ang mga blangko nga linya sa taas
                    }

                    if (count($cells) > $maxColumns) {
                        $cells     = array_slice($cells, 0, $maxColumns);
                        $truncated = true;
                    }

                    // Ang kolum nga walay ulohan gihatagan ug usa, kay ang
                    // lamesa sa HTML dili makadala ug walay ngalan nga kolum.
                    $headers = array_map(
                        fn($h, $i) => $h !== '' ? $h : 'Column ' . ($i + 1),
                        $cells,
                        array_keys($cells)
                    );
                    continue;
                }

                if (self::isBlank($cells)) {
                    continue;
                }

                if (count($rows) >= $maxRows) {
                    $truncated = true;
                    break;
                }

                // I-parehas ang gilapdon sa ulohan. Ang laray nga mubo o taas
                // pa kay sa ulohan mao ang naghimo sa lamesa nga tikwang.
                $rows[] = array_slice(
                    array_pad($cells, count($headers), ''),
                    0,
                    count($headers)
                );
            }
        }

        return [
            'headers'   => $headers,
            'rows'      => $rows,
            'truncated' => $truncated,
        ];
    }

    private static function isBlank(array $cells): bool
    {
        foreach ($cells as $cell) {
            if ($cell !== '') {
                return false;
            }
        }

        return true;
    }
}
