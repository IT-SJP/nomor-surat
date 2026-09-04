<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Letter;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class LetterImportService
{
    /**
     * Cache for branch names by branch code.
     *
     * @var array<string, string>
     */
    protected array $branchNameCache = [];

    /**
     * Cache for sequence counters keyed by "{branch_code}_{year}_{month}".
     *
     * @var array<string, int>
     */
    protected array $sequenceCounters = [];

    /**
     * Parse and import letters from a given CSV file path.
     *
     * @param  string  $filePath  Absolute path to the CSV file
     * @param  bool  $dryRun  If true, validates and previews without persisting
     * @return array{
     *     success: bool,
     *     total_rows: int,
     *     imported_count: int,
     *     skipped_count: int,
     *     errors: list<string>,
     *     letters: Collection<int, Letter>
     * }
     */
    public function importFromPath(string $filePath, bool $dryRun = false): array
    {
        if (! file_exists($filePath) || ! is_readable($filePath)) {
            throw new InvalidArgumentException("File CSV tidak ditemukan atau tidak dapat dibaca: {$filePath}");
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new InvalidArgumentException("Gagal membuka file CSV: {$filePath}");
        }

        try {
            return $this->importFromHandle($handle, $dryRun);
        } finally {
            fclose($handle);
        }
    }

    /**
     * Import letters from an open file handle resource.
     *
     * @param  resource  $handle
     * @return array{
     *     success: bool,
     *     total_rows: int,
     *     imported_count: int,
     *     skipped_count: int,
     *     errors: list<string>,
     *     letters: Collection<int, Letter>
     * }
     */
    public function importFromHandle($handle, bool $dryRun = false): array
    {
        // Strip UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle, 0, ',');
        if ($header === false) {
            return [
                'success' => false,
                'total_rows' => 0,
                'imported_count' => 0,
                'skipped_count' => 0,
                'errors' => ['File CSV kosong atau header tidak ditemukan.'],
                'letters' => collect(),
            ];
        }

        $columnMap = $this->mapHeaderColumns($header);
        $this->loadBranchNames();

        $rowsToInsert = [];
        $errors = [];
        $rowNumber = 1; // 1 is header, data starts at 2

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $rowNumber++;

            // Skip completely empty rows
            if (empty(array_filter($row, fn ($val) => trim((string) $val) !== ''))) {
                continue;
            }

            try {
                $record = $this->parseRow($row, $columnMap, $rowNumber);
                if ($record) {
                    $rowsToInsert[] = $record;
                }
            } catch (Throwable $e) {
                $errors[] = "Baris {$rowNumber}: {$e->getMessage()}";
            }
        }

        $totalRows = count($rowsToInsert);
        $importedLetters = collect();

        if ($dryRun) {
            return [
                'success' => count($errors) === 0,
                'total_rows' => $totalRows,
                'imported_count' => $totalRows,
                'skipped_count' => count($errors),
                'errors' => $errors,
                'letters' => collect($rowsToInsert)->map(function ($r) {
                    $l = new Letter($r);
                    $l->created_at = $r['created_at'];
                    $l->updated_at = $r['updated_at'];

                    return $l;
                }),
            ];
        }

        // Persist records within database transaction
        DB::transaction(function () use ($rowsToInsert, &$importedLetters) {
            foreach ($rowsToInsert as $data) {
                $letter = new Letter;
                $letter->fill($data);

                // Explicitly set timestamps from parsed source
                if (isset($data['created_at'])) {
                    $letter->created_at = $data['created_at'];
                    $letter->updated_at = $data['updated_at'] ?? $data['created_at'];
                }

                $letter->save();
                $importedLetters->push($letter);
            }
        });

        return [
            'success' => true,
            'total_rows' => $totalRows,
            'imported_count' => $importedLetters->count(),
            'skipped_count' => count($errors),
            'errors' => $errors,
            'letters' => $importedLetters,
        ];
    }

    /**
     * Map CSV header column names to standardized keys.
     *
     * @param  list<string>  $header
     * @return array<string, int>
     */
    protected function mapHeaderColumns(array $header): array
    {
        $map = [];

        foreach ($header as $index => $name) {
            $clean = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', (string) $name)));

            if (str_contains($clean, 'timestamp') || str_contains($clean, 'tanggal') || str_contains($clean, 'waktu')) {
                $map['timestamp'] = $index;
            } elseif (str_contains($clean, 'perusahaan') || str_contains($clean, 'cabang')) {
                $map['branch_code'] = $index;
            } elseif (str_contains($clean, 'kodetujuan') || str_contains($clean, 'targetcode')) {
                $map['target_code'] = $index;
            } elseif (str_contains($clean, 'bulan')) {
                $map['month'] = $index;
            } elseif (str_contains($clean, 'tahun') || str_contains($clean, 'year')) {
                $map['year'] = $index;
            } elseif (str_contains($clean, 'perihal') || str_contains($clean, 'subject')) {
                $map['subject'] = $index;
            } elseif ($clean === 'tujuan' || str_contains($clean, 'purpose') || str_contains($clean, 'instansi')) {
                $map['purpose'] = $index;
            } elseif (str_contains($clean, 'arsip') || str_contains($clean, 'letak')) {
                $map['archive_location'] = $index;
            } elseif (str_contains($clean, 'requestor') || str_contains($clean, 'pemohon')) {
                $map['requestor_name'] = $index;
            }
        }

        return $map;
    }

    /**
     * Parse and normalize an individual CSV row into Letter model attributes.
     *
     * @param  list<string>  $row
     * @param  array<string, int>  $map
     * @return array<string, mixed>|null
     */
    protected function parseRow(array $row, array $map, int $rowNumber): ?array
    {
        $rawSubject = trim((string) ($row[$map['subject'] ?? 7] ?? ''));
        if ($rawSubject === '') {
            return null; // Skip rows without perihal/subject
        }

        // 1. Company / Branch code
        $rawBranch = trim((string) ($row[$map['branch_code'] ?? 3] ?? 'SJP'));
        $branchCode = strtoupper($rawBranch ?: 'SJP');
        $branchName = $this->branchNameCache[$branchCode] ?? "Cabang {$branchCode}";

        // 2. Timestamp / Date
        $rawTimestamp = trim((string) ($row[$map['timestamp'] ?? 1] ?? ''));
        $parsedDate = $this->parseTimestamp($rawTimestamp);

        // 3. Year and Month
        $rawYear = trim((string) ($row[$map['year'] ?? 6] ?? ''));
        $year = ! empty($rawYear) && is_numeric($rawYear) ? (int) $rawYear : (int) $parsedDate->format('Y');

        $rawMonth = trim((string) ($row[$map['month'] ?? 5] ?? ''));
        $month = $this->resolveMonth($rawMonth, $parsedDate);
        $monthRoman = LetterNumberService::monthToRoman($month);

        // 4. Target Code (clean up leading/trailing slashes or excessive punctuation)
        $rawTarget = trim((string) ($row[$map['target_code'] ?? 4] ?? ''));
        $targetCode = trim($rawTarget, "/ \t\n\r\0\x0B");

        // 5. Sequence number and Reference number
        $seqKey = "{$branchCode}_{$year}_{$month}";
        if (! isset($this->sequenceCounters[$seqKey])) {
            $maxExisting = Letter::query()
                ->where('branch_code', $branchCode)
                ->where('year', $year)
                ->where('month', $month)
                ->max('sequence_number') ?? 0;
            $this->sequenceCounters[$seqKey] = (int) $maxExisting;
        }

        $this->sequenceCounters[$seqKey]++;
        $sequenceNumber = $this->sequenceCounters[$seqKey];
        $paddedSequence = str_pad((string) $sequenceNumber, 3, '0', STR_PAD_LEFT);

        // Standardized system format: [No Urut]/[Kode Tujuan]/[Cabang]/[Bulan]/[Tahun]
        $referenceNumber = ! empty($targetCode)
            ? "{$paddedSequence}/{$targetCode}/{$branchCode}/{$monthRoman}/{$year}"
            : "{$paddedSequence}/{$branchCode}/{$monthRoman}/{$year}";

        // 6. Purpose, Archive, Requestor
        $rawPurpose = trim((string) ($row[$map['purpose'] ?? 8] ?? ''));
        $rawArchive = trim((string) ($row[$map['archive_location'] ?? 9] ?? ''));
        $rawRequestor = trim((string) ($row[$map['requestor_name'] ?? 10] ?? 'Karyawan'));

        return [
            'reference_number' => $referenceNumber,
            'sequence_number' => $sequenceNumber,
            'branch_code' => $branchCode,
            'branch_name' => $branchName,
            'target_code' => $targetCode ?: 'INTERNAL',
            'month_roman' => $monthRoman,
            'month' => $month,
            'year' => $year,
            'subject' => $rawSubject,
            'purpose' => $rawPurpose !== '' ? $rawPurpose : null,
            'archive_location' => $rawArchive !== '' ? $rawArchive : null,
            'requestor_department' => $rawArchive !== '' ? $rawArchive : null,
            'requestor_position' => null,
            'requestor_name' => $rawRequestor ?: 'Karyawan',
            'requestor_email' => null,
            'requestor_phone' => null,
            'created_at' => $parsedDate,
            'updated_at' => $parsedDate,
        ];
    }

    /**
     * Resolve numeric month (1-12) from string or fallback date.
     */
    protected function resolveMonth(string $rawMonth, Carbon $fallbackDate): int
    {
        $rawMonth = strtoupper(trim($rawMonth));

        if (empty($rawMonth)) {
            return (int) $fallbackDate->format('n');
        }

        // Try Roman numeral parsing
        try {
            return LetterNumberService::romanToMonth($rawMonth);
        } catch (InvalidArgumentException) {
            // Check if it's already an integer string 1-12
            if (is_numeric($rawMonth)) {
                $intMonth = (int) $rawMonth;
                if ($intMonth >= 1 && $intMonth <= 12) {
                    return $intMonth;
                }
            }

            return (int) $fallbackDate->format('n');
        }
    }

    /**
     * Parse date string in various Indonesian and ISO formats.
     * Examples from CSV: "2/1/2026, 09.43.45" or "10/8/2026, 21.45.57"
     */
    protected function parseTimestamp(string $rawTimestamp): Carbon
    {
        $cleaned = trim($rawTimestamp);
        if ($cleaned === '') {
            return Carbon::now();
        }

        // Normalize dots in time to colons: "09.43.45" -> "09:43:45"
        $normalized = preg_replace('/(\d{1,2})\.(\d{2})\.(\d{2})/', '$1:$2:$3', $cleaned);
        // Remove commas: "2/1/2026, 09:43:45" -> "2/1/2026 09:43:45"
        $normalized = str_replace(',', '', (string) $normalized);
        $normalized = preg_replace('/\s+/', ' ', (string) $normalized);

        $formats = [
            'd/m/Y H:i:s',
            'j/n/Y H:i:s',
            'd/m/Y H:i',
            'j/n/Y H:i',
            'd/m/Y',
            'j/n/Y',
            'Y-m-d H:i:s',
            'Y-m-d',
        ];

        foreach ($formats as $fmt) {
            try {
                $d = Carbon::createFromFormat($fmt, $normalized);
                if ($d !== false) {
                    return $d;
                }
            } catch (Throwable) {
                // continue to next format
            }
        }

        try {
            return Carbon::parse($normalized);
        } catch (Throwable) {
            return Carbon::now();
        }
    }

    /**
     * Load branch names from database into cache.
     */
    protected function loadBranchNames(): void
    {
        try {
            $branches = Branch::all(['branch_code', 'name']);
            foreach ($branches as $b) {
                if (! empty($b->branch_code)) {
                    $this->branchNameCache[strtoupper((string) $b->branch_code)] = (string) $b->name;
                }
            }
        } catch (Throwable) {
            $this->branchNameCache = [
                'SJP' => 'PT Selamat Jaya Persada',
            ];
        }
    }
}
