<?php

namespace App\Console\Commands;

use App\Models\Letter;
use App\Services\LetterNumberService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class RenumberLetters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'letter:renumber
                            {--branch= : Filter kode cabang tertentu (contoh: PRTM)}
                            {--year= : Filter tahun tertentu (contoh: 2026)}
                            {--dry-run : Pratinjau revisi nomor surat tanpa menyimpan ke database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revisi dan urutkan ulang nomor surat per cabang dan tahun secara berkesinambungan antar bulan';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $branchOption = $this->option('branch') ? strtoupper(trim((string) $this->option('branch'))) : null;
        $yearOption = $this->option('year') ? (int) $this->option('year') : null;
        $isDryRun = (bool) $this->option('dry-run');

        $this->info('=== REVISI PENOMORAN SURAT SISTEM ===');
        if ($isDryRun) {
            $this->warn('Mode DRY-RUN aktif: Data hanya dianalisis dan TIDAK akan disimpan ke database.');
        }

        if ($branchOption) {
            $this->line("Filter Cabang: <comment>{$branchOption}</comment>");
        }
        if ($yearOption) {
            $this->line("Filter Tahun : <comment>{$yearOption}</comment>");
        }

        $startTime = microtime(true);

        // 1. Fetch distinct branch and year combinations
        $groupsQuery = Letter::query()
            ->select('branch_code', 'year')
            ->distinct();

        if ($branchOption) {
            $groupsQuery->where('branch_code', $branchOption);
        }
        if ($yearOption) {
            $groupsQuery->where('year', $yearOption);
        }

        $groups = $groupsQuery->orderBy('branch_code')->orderBy('year')->get();

        if ($groups->isEmpty()) {
            $this->warn('Tidak ada data surat yang cocok dengan kriteria pencarian.');

            return self::SUCCESS;
        }

        $totalExamined = 0;
        $totalChanged = 0;
        $allChanges = [];

        try {
            // Group analysis
            foreach ($groups as $group) {
                $branchCode = $group->branch_code;
                $year = (int) $group->year;

                $letters = Letter::query()
                    ->where('branch_code', $branchCode)
                    ->where('year', $year)
                    ->orderBy('month', 'asc')
                    ->orderBy('sequence_number', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                $newSequence = 1;
                foreach ($letters as $letter) {
                    $totalExamined++;
                    $paddedSequence = str_pad((string) $newSequence, 3, '0', STR_PAD_LEFT);

                    if (preg_match('/^\d+/', $letter->reference_number)) {
                        $newReferenceNumber = (string) preg_replace('/^\d+/', $paddedSequence, $letter->reference_number);
                    } else {
                        $monthRoman = $letter->month_roman ?: LetterNumberService::monthToRoman((int) $letter->month);
                        $newReferenceNumber = ! empty($letter->target_code)
                            ? "{$paddedSequence}/{$letter->target_code}/{$letter->branch_code}/{$monthRoman}/{$letter->year}"
                            : "{$paddedSequence}/{$letter->branch_code}/{$monthRoman}/{$letter->year}";
                    }

                    $changed = ($letter->sequence_number !== $newSequence)
                        || ($letter->reference_number !== $newReferenceNumber);

                    if ($changed) {
                        $totalChanged++;
                    }

                    $allChanges[] = [
                        'id' => $letter->id,
                        'branch_code' => $letter->branch_code,
                        'month' => $letter->month,
                        'year' => $letter->year,
                        'old_seq' => $letter->sequence_number,
                        'new_seq' => $newSequence,
                        'old_ref' => $letter->reference_number,
                        'new_ref' => $newReferenceNumber,
                        'changed' => $changed,
                    ];

                    $newSequence++;
                }
            }

            // 2. Persist changes safely using two-pass update inside transaction if not dry run
            if (! $isDryRun && $totalChanged > 0) {
                DB::transaction(function () use ($allChanges) {
                    $changedRecords = array_filter($allChanges, fn ($item) => $item['changed']);

                    // Pass 1: Set temporary reference number to prevent unique constraint collisions
                    foreach ($changedRecords as $record) {
                        $tempRef = 'TMP_'.str_pad((string) $record['id'], 6, '0', STR_PAD_LEFT).'_'.bin2hex(random_bytes(4));
                        DB::table('letters')
                            ->where('id', $record['id'])
                            ->update([
                                'reference_number' => $tempRef,
                            ]);
                    }

                    // Pass 2: Set final sequence_number and reference_number
                    foreach ($changedRecords as $record) {
                        DB::table('letters')
                            ->where('id', $record['id'])
                            ->update([
                                'sequence_number' => $record['new_seq'],
                                'reference_number' => $record['new_ref'],
                                'updated_at' => now(),
                            ]);
                    }
                });
            }

            $duration = round(microtime(true) - $startTime, 2);

            // 3. Display summary
            $this->newLine();
            $this->table(
                ['Indikator', 'Hasil'],
                [
                    ['Cabang & Tahun Diproses', $groups->count().' grup'],
                    ['Total Surat Diperiksa', $totalExamined],
                    ['Total Surat Berubah', $totalChanged],
                    ['Total Surat Tetap', $totalExamined - $totalChanged],
                    ['Durasi Eksekusi', "{$duration} detik"],
                    ['Status Eksekusi', $isDryRun ? 'DRY-RUN (SIMULASI)' : 'BERHASIL DIPERBARUI'],
                ]
            );

            // 4. Display sample of changes
            $changedRows = array_values(array_filter($allChanges, fn ($item) => $item['changed']));
            if (! empty($changedRows)) {
                $this->newLine();
                $this->info('Contoh Perubahan Nomor Surat (Maksimal 15 Baris):');
                $sampleDisplay = array_map(function ($row) {
                    return [
                        $row['id'],
                        $row['branch_code'],
                        $row['month'],
                        $row['year'],
                        $row['old_seq'],
                        $row['new_seq'],
                        $row['old_ref'],
                        $row['new_ref'],
                    ];
                }, array_slice($changedRows, 0, 15));

                $this->table(
                    ['ID', 'Cabang', 'Bln', 'Thn', 'Seq Lama', 'Seq Baru', 'Nomor Surat Lama', 'Nomor Surat Baru'],
                    $sampleDisplay
                );

                if (count($changedRows) > 15) {
                    $remaining = count($changedRows) - 15;
                    $this->line("... dan {$remaining} nomor surat lainnya diperbarui.");
                }
            } else {
                $this->newLine();
                $this->info('Seluruh nomor surat sudah sesuai dengan aturan terbaru (tidak ada perubahan).');
            }

            $this->newLine();
            if ($isDryRun) {
                $this->warn('Simulasi selesai. Jalankan tanpa flag --dry-run untuk menerapkan perubahan ke database.');
            } else {
                $this->info('Semua nomor surat telah berhasil diperbarui sesuai aturan penomoran per cabang dan reset tahunan!');
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->newLine();
            $this->error("Gagal melakukan revisi nomor surat: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
