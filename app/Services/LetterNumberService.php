<?php

namespace App\Services;

use App\Models\Letter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LetterNumberService
{
    /**
     * Map of numeric months to Roman numerals.
     *
     * @var array<int, string>
     */
    public const ROMAN_MONTHS = [
        1 => 'I',
        2 => 'II',
        3 => 'III',
        4 => 'IV',
        5 => 'V',
        6 => 'VI',
        7 => 'VII',
        8 => 'VIII',
        9 => 'IX',
        10 => 'X',
        11 => 'XI',
        12 => 'XII',
    ];

    /**
     * Map of numeric months to Indonesian month names.
     *
     * @var array<int, string>
     */
    public const MONTH_NAMES = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    /**
     * Convert integer month (1-12) to Roman numeral string.
     */
    public static function monthToRoman(int $month): string
    {
        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException("Bulan harus di antara 1 dan 12, diberikan: {$month}");
        }

        return self::ROMAN_MONTHS[$month];
    }

    /**
     * Convert Roman numeral string to integer month (1-12).
     */
    public static function romanToMonth(string $roman): int
    {
        $roman = strtoupper(trim($roman));
        $flipped = array_flip(self::ROMAN_MONTHS);

        if (! isset($flipped[$roman])) {
            throw new InvalidArgumentException("Bulan Romawi tidak valid: {$roman}");
        }

        return $flipped[$roman];
    }

    /**
     * Generate the next sequence number and formatted reference number atomically.
     *
     * @param  array{
     *     branch_code: string,
     *     branch_name?: string|null,
     *     target_code: string,
     *     month: int,
     *     year: int,
     *     subject: string,
     *     purpose: string,
     *     archive_location?: string|null,
     *     requestor_nik?: string|null,
     *     requestor_name: string,
     *     requestor_email?: string|null,
     *     requestor_phone?: string|null
     * }  $data
     */
    public function createLetter(array $data): Letter
    {
        return DB::transaction(function () use ($data) {
            $branchCode = strtoupper(trim($data['branch_code']));
            $targetCode = strtoupper(trim($data['target_code']));
            $month = (int) $data['month'];
            $year = (int) $data['year'];
            $monthRoman = self::monthToRoman($month);

            // Lock and get the highest sequence number in this branch, month, and year
            $latestLetter = Letter::query()
                ->where('branch_code', $branchCode)
                ->where('year', $year)
                ->where('month', $month)
                ->lockForUpdate()
                ->orderByDesc('sequence_number')
                ->first();

            $nextSequence = ($latestLetter ? $latestLetter->sequence_number : 0) + 1;
            $paddedSequence = str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
            $referenceNumber = "{$branchCode}/{$monthRoman}/{$year}/{$paddedSequence}";

            return Letter::create([
                'reference_number' => $referenceNumber,
                'sequence_number' => $nextSequence,
                'branch_code' => $branchCode,
                'branch_name' => $data['branch_name'] ?? null,
                'target_code' => $targetCode,
                'month_roman' => $monthRoman,
                'month' => $month,
                'year' => $year,
                'subject' => trim($data['subject']),
                'purpose' => trim($data['purpose']),
                'archive_location' => isset($data['archive_location']) ? trim($data['archive_location']) : null,
                'requestor_nik' => isset($data['requestor_nik']) ? trim($data['requestor_nik']) : null,
                'requestor_name' => trim($data['requestor_name']),
                'requestor_email' => isset($data['requestor_email']) ? trim($data['requestor_email']) : null,
                'requestor_phone' => isset($data['requestor_phone']) ? trim($data['requestor_phone']) : null,
            ]);
        });
    }

    /**
     * Preview the next available letter number for a given branch, month, and year.
     */
    public function previewNextNumber(string $branchCode, int $month, int $year): string
    {
        $branchCode = strtoupper(trim($branchCode));
        $monthRoman = self::monthToRoman($month);

        $maxSeq = Letter::query()
            ->where('branch_code', $branchCode)
            ->where('year', $year)
            ->where('month', $month)
            ->max('sequence_number') ?? 0;

        $nextSeq = str_pad((string) ($maxSeq + 1), 3, '0', STR_PAD_LEFT);

        return "{$branchCode}/{$monthRoman}/{$year}/{$nextSeq}";
    }

    /**
     * Get statistics of total letters per company/branch.
     *
     * @param  Collection<int, array<string, mixed>>  $branches
     * @return array<string, int>
     */
    public function getBranchStats(Collection $branches): array
    {
        $counts = Letter::query()
            ->select('branch_code', DB::raw('count(*) as total'))
            ->groupBy('branch_code')
            ->pluck('total', 'branch_code')
            ->toArray();

        $result = [];
        foreach ($branches as $branch) {
            $code = (string) ($branch['code'] ?? '');
            $result[$code] = (int) ($counts[$code] ?? 0);
        }

        return $result;
    }
}
