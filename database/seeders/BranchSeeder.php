<?php

namespace Database\Seeders;

use App\Models\Absen\Cabang;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // Fetch all branches from the HRIS absen_db
            $cabangList = Cabang::all();

            foreach ($cabangList as $cabang) {
                Branch::updateOrCreate(
                    ['hr_code' => $cabang->kode_cabang],
                    [
                        'name' => $cabang->branch_name,
                        'is_active' => (bool) ($cabang->status ?? true),
                    ]
                );
            }
        } catch (\Throwable $e) {
            $this->command->error('Gagal mengambil data cabang dari absen_db: '.$e->getMessage());
        }
    }
}
