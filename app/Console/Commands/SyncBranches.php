<?php

namespace App\Console\Commands;

use App\Models\Absen\Cabang;
use App\Models\Branch;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:sync-branches')]
#[Description('Sync new branches from HRIS to local database without company code maps, leaving letter code empty if new.')]
class SyncBranches extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting branch synchronization from HRIS (absen_db)...');

        try {
            $cabangList = Cabang::all();
            $newCount = 0;
            $updatedCount = 0;

            foreach ($cabangList as $cabang) {
                // Match primarily by HRIS unique branch code (hr_code)
                $branch = Branch::where('hr_code', $cabang->kode_cabang)->first();

                if (! $branch) {
                    // Automatically add new branch from HRIS with empty/null branch_code
                    $branch = Branch::create([
                        'hr_code' => $cabang->kode_cabang,
                        'branch_code' => null,
                        'name' => $cabang->nama_cabang,
                        'is_active' => (bool) ($cabang->status ?? true),
                    ]);

                    $newCount++;
                    $this->line("- Added new branch from HRIS: {$cabang->nama_cabang} (HR Code: {$cabang->kode_cabang}, Kode Surat: [Kosong])");
                } else {
                    // Sync name and active status from HRIS while keeping customized branch_code intact
                    $needsSave = false;
                    if ($branch->name !== $cabang->nama_cabang) {
                        $branch->name = $cabang->nama_cabang;
                        $needsSave = true;
                    }
                    if (isset($cabang->status) && $branch->is_active !== (bool) $cabang->status) {
                        $branch->is_active = (bool) $cabang->status;
                        $needsSave = true;
                    }
                    if ($needsSave) {
                        $branch->save();
                        $updatedCount++;
                    }
                }
            }

            $this->info("Synchronization completed! {$newCount} new branch(es) added, {$updatedCount} updated.");
        } catch (\Throwable $e) {
            $this->error('Failed to sync branches: '.$e->getMessage());
        }
    }
}
