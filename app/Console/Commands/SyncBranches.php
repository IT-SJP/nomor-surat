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

                // If not matched by hr_code, check if there is an existing manual branch matching branch_code or name with empty/null hr_code
                if (! $branch) {
                    $branch = Branch::where(function ($q) {
                        $q->whereNull('hr_code')->orWhere('hr_code', '');
                    })
                        ->where(function ($q) use ($cabang) {
                            $q->where('branch_code', $cabang->kode_cabang)
                                ->orWhere('name', 'ILIKE', $cabang->nama_cabang);
                        })
                        ->first();

                    if ($branch) {
                        $branch->hr_code = $cabang->kode_cabang;
                        if ($branch->name !== $cabang->nama_cabang) {
                            $branch->name = $cabang->nama_cabang;
                        }
                        if (isset($cabang->status)) {
                            $branch->is_active = (bool) $cabang->status;
                        }
                        $branch->save();
                        $updatedCount++;
                        $this->line("- Unified manual branch {$branch->name} with HRIS code: {$cabang->kode_cabang}");

                        continue;
                    }
                }

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
