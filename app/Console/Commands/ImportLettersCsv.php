<?php

namespace App\Console\Commands;

use App\Services\LetterImportService;
use Illuminate\Console\Command;
use Throwable;

class ImportLettersCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'letter:import-csv 
                            {file : Path ke file CSV yang akan diimport} 
                            {--dry-run : Validasi dan pratinjau data tanpa menyimpan ke database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import riwayat nomor surat dari file CSV ke database dengan penomoran unik sistem';

    /**
     * Execute the console command.
     */
    public function handle(LetterImportService $service): int
    {
        $filePath = (string) $this->argument('file');
        $isDryRun = (bool) $this->option('dry-run');

        // Resolve absolute path if relative
        if (! str_starts_with($filePath, '/') && ! preg_match('/^[a-zA-Z]:\\\\/', $filePath)) {
            $filePath = base_path($filePath);
        }

        $this->info("Memulai proses import dari: {$filePath}");
        if ($isDryRun) {
            $this->warn('Mode DRY-RUN aktif: Data hanya divalidasi dan tidak akan disimpan ke database.');
        }

        try {
            $startTime = microtime(true);
            $result = $service->importFromPath($filePath, $isDryRun);
            $duration = round(microtime(true) - $startTime, 2);

            $this->newLine();
            $this->table(
                ['Indikator', 'Jumlah'],
                [
                    ['Total Baris Diproses', $result['total_rows']],
                    ['Berhasil Diimport', $result['imported_count']],
                    ['Gagal / Dilewati', $result['skipped_count']],
                    ['Durasi Waktu', "{$duration} detik"],
                    ['Status', $result['success'] ? 'SUKSES' : 'ADA KESALAHAN'],
                ]
            );

            if (! empty($result['errors'])) {
                $this->newLine();
                $this->error('Daftar Kesalahan Baris:');
                foreach (array_slice($result['errors'], 0, 10) as $err) {
                    $this->line(" - {$err}");
                }
                if (count($result['errors']) > 10) {
                    $remaining = count($result['errors']) - 10;
                    $this->line(" ... dan {$remaining} kesalahan lainnya.");
                }
            }

            if ($result['imported_count'] > 0) {
                $this->newLine();
                $this->info('Contoh 5 Nomor Surat Teratas yang Dihasilkan:');
                $sampleData = $result['letters']->take(5)->map(fn ($letter) => [
                    $letter->reference_number,
                    $letter->branch_code,
                    $letter->target_code,
                    $letter->month_roman.'/'.$letter->year,
                    mb_strimwidth($letter->subject, 0, 40, '...'),
                    $letter->requestor_name,
                    $letter->created_at?->format('d/m/Y H:i'),
                ])->toArray();

                $this->table(
                    ['Nomor Surat Baru', 'Cabang', 'Kode Tujuan', 'Periode', 'Perihal', 'Pemohon', 'Waktu'],
                    $sampleData
                );
            }

            $this->newLine();
            $this->info($isDryRun ? 'Pengecekan validasi selesai.' : 'Proses import riwayat nomor surat berhasil diselesaikan!');

            return $result['success'] ? self::SUCCESS : self::FAILURE;
        } catch (Throwable $e) {
            $this->newLine();
            $this->error("Terjadi kegagalan saat import: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
