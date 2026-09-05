<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Letter;
use App\Models\LetterTarget;
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
     * Regenerate a letter's reference number with an updated branch code.
     */
    public static function regenerateReferenceNumber(Letter $letter, string $branchCode): string
    {
        $paddedSequence = str_pad((string) $letter->sequence_number, 3, '0', STR_PAD_LEFT);
        $monthRoman = $letter->month_roman ?: self::monthToRoman((int) $letter->month);
        $year = $letter->year;

        $targetCode = null;
        if (! empty($letter->target_code)) {
            $matchedTarget = LetterTarget::findMatching($letter->target_code);
            $targetCode = $matchedTarget ? $matchedTarget->code : trim((string) $letter->target_code);
        }

        return ! empty($targetCode)
            ? "{$paddedSequence}/{$targetCode}/{$branchCode}/{$monthRoman}/{$year}"
            : "{$paddedSequence}/{$branchCode}/{$monthRoman}/{$year}";
    }

    /**
     * Generate the next sequence number and formatted reference number atomically.
     *
     * @param  array{
     *     branch_id?: int|null,
     *     branch_code: string,
     *     branch_name?: string|null,
     *     target_code: string,
     *     month: int,
     *     year: int,
     *     subject: string,
     *     purpose?: string|null,
     *     archive_location?: string|null,
     *     requestor_department?: string|null,
     *     requestor_position?: string|null,
     *     requestor_name: string,
     *     requestor_email?: string|null,
     *     requestor_phone?: string|null
     * }  $data
     */
    public function createLetter(array $data): Letter
    {
        return DB::transaction(function () use ($data) {
            $branchCode = strtoupper(trim($data['branch_code']));
            $rawTarget = trim($data['target_code']);
            $targetCode = (! str_contains($rawTarget, ' ') && strlen($rawTarget) <= 10)
                ? strtoupper($rawTarget)
                : $rawTarget;
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

            // Format nomor surat dibalik:
            // 1. Jika tujuan cocok dengan database tujuan baku: nomor/tujuan/cabang/bulan/tahun (e.g. 001/IM/SJP/IX/2026)
            // 2. Jika tujuan di luar database: nomor/cabang/bulan/tahun (e.g. 001/SJP/IX/2026)
            $matchedTarget = LetterTarget::findMatching($targetCode);
            $referenceNumber = $matchedTarget
                ? "{$paddedSequence}/{$matchedTarget->code}/{$branchCode}/{$monthRoman}/{$year}"
                : "{$paddedSequence}/{$branchCode}/{$monthRoman}/{$year}";

            // Find matching branch to link relation
            $matchedBranch = null;
            if (! empty($data['branch_id'])) {
                $matchedBranch = Branch::find($data['branch_id']);
            }
            if (! $matchedBranch && ! empty($branchCode)) {
                $matchedBranch = Branch::where('branch_code', $branchCode)
                    ->orWhere('hr_code', $branchCode)
                    ->first();
            }

            return Letter::create([
                'branch_id' => $matchedBranch?->id,
                'reference_number' => $referenceNumber,
                'sequence_number' => $nextSequence,
                'branch_code' => $branchCode,
                'branch_name' => $matchedBranch ? $matchedBranch->name : ($data['branch_name'] ?? null),
                'target_code' => $targetCode,
                'month_roman' => $monthRoman,
                'month' => $month,
                'year' => $year,
                'subject' => trim($data['subject']),
                'purpose' => ! empty($data['purpose']) ? trim((string) $data['purpose']) : null,
                'archive_location' => ! empty($data['archive_location']) ? trim((string) $data['archive_location']) : null,
                'requestor_department' => ! empty($data['requestor_department']) ? trim((string) $data['requestor_department']) : null,
                'requestor_position' => ! empty($data['requestor_position']) ? trim((string) $data['requestor_position']) : null,
                'requestor_name' => trim($data['requestor_name']),
                'requestor_email' => ! empty($data['requestor_email']) ? trim((string) $data['requestor_email']) : null,
                'requestor_phone' => ! empty($data['requestor_phone']) ? trim((string) $data['requestor_phone']) : null,
            ]);
        });
    }

    /**
     * Preview the next available letter number for a given branch, month, and year.
     */
    public function previewNextNumber(string $branchCode, int $month, int $year, ?string $targetInput = null): string
    {
        $branchCode = strtoupper(trim($branchCode));
        $monthRoman = self::monthToRoman($month);

        $maxSeq = Letter::query()
            ->where('branch_code', $branchCode)
            ->where('year', $year)
            ->where('month', $month)
            ->max('sequence_number') ?? 0;

        $nextSeq = str_pad((string) ($maxSeq + 1), 3, '0', STR_PAD_LEFT);
        $matchedTarget = LetterTarget::findMatching($targetInput);

        return $matchedTarget
            ? "{$nextSeq}/{$matchedTarget->code}/{$branchCode}/{$monthRoman}/{$year}"
            : "{$nextSeq}/{$branchCode}/{$monthRoman}/{$year}";
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
