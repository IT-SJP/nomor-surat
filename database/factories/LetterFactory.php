<?php

namespace Database\Factories;

use App\Models\Letter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Letter>
 */
class LetterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $companies = ['SJP', 'SJPRA', 'SJK', 'SJE', 'SJR', 'SAS', 'PAS', 'SPEK', 'SKORD', 'BTJ', 'PTU', 'RBJ', 'CSI'];
        $branchCode = fake()->randomElement($companies);
        $romanMonths = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $monthIndex = fake()->numberBetween(1, 12);
        $monthRoman = $romanMonths[$monthIndex - 1];
        $year = (int) date('Y');
        $seq = fake()->numberBetween(1, 99);
        $paddedSeq = str_pad((string) $seq, 3, '0', STR_PAD_LEFT);

        return [
            'reference_number' => "{$branchCode}/{$monthRoman}/{$year}/{$paddedSeq}",
            'sequence_number' => $seq,
            'branch_code' => $branchCode,
            'branch_name' => "PT {$branchCode} Group",
            'target_code' => fake()->randomElement(['IJTM', 'JKT', 'BDG', 'SBY', 'SMG']),
            'month_roman' => $monthRoman,
            'month' => $monthIndex,
            'year' => $year,
            'subject' => fake()->sentence(4),
            'purpose' => fake()->paragraph(2),
            'archive_location' => 'Arsip '.fake()->word(),
            'requestor_department' => fake()->randomElement(['HR', 'IT', 'Finance', 'Marketing']),
            'requestor_position' => fake()->randomElement(['Staff', 'Manager', 'Supervisor']),
            'requestor_name' => fake()->name(),
            'requestor_email' => fake()->safeEmail(),
            'requestor_phone' => fake()->phoneNumber(),
        ];
    }
}
