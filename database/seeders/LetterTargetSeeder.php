<?php

namespace Database\Seeders;

use App\Models\LetterTarget;
use Illuminate\Database\Seeder;

class LetterTargetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $targets = [
            [
                'code' => 'IM',
                'name' => 'Internal Memo',
                'description' => 'Surat memo dinas antar divisi, departemen, atau unit kerja internal.',
                'is_active' => true,
            ],
            [
                'code' => 'ND',
                'name' => 'Nota Dinas',
                'description' => 'Nota dinas kedinasan, instruksi operasional, atau telaah staf internal.',
                'is_active' => true,
            ],
            [
                'code' => 'SK',
                'name' => 'Surat Keputusan',
                'description' => 'Surat keputusan direksi atau pimpinan entitas perusahaan.',
                'is_active' => true,
            ],
            [
                'code' => 'SP',
                'name' => 'Surat Tugas / Perintah',
                'description' => 'Surat penugasan dinas, perjalanan dinas, atau perintah pelaksanaan kerja.',
                'is_active' => true,
            ],
            [
                'code' => 'PKS',
                'name' => 'Perjanjian Kerja Sama',
                'description' => 'Dokumen kontrak atau perjanjian kemitraan resmi.',
                'is_active' => true,
            ],
            [
                'code' => 'EXT',
                'name' => 'Eksternal / Instansi Luar',
                'description' => 'Surat resmi untuk instansi pemerintah, perbankan, vendor, atau pihak eksternal.',
                'is_active' => true,
            ],
        ];

        foreach ($targets as $target) {
            LetterTarget::updateOrCreate(
                ['code' => $target['code']],
                [
                    'name' => $target['name'],
                    'description' => $target['description'],
                    'is_active' => $target['is_active'],
                ]
            );
        }
    }
}
