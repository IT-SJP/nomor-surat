<?php

namespace App\Models;

use Database\Factories\LetterFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Letter extends Model
{
    /** @use HasFactory<LetterFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'reference_number',
        'sequence_number',
        'branch_code',
        'branch_name',
        'target_code',
        'month_roman',
        'month',
        'year',
        'subject',
        'purpose',
        'archive_location',
        'requestor_department',
        'requestor_position',
        'requestor_name',
        'requestor_email',
        'requestor_phone',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sequence_number' => 'integer',
            'month' => 'integer',
            'year' => 'integer',
        ];
    }

    /**
     * Scope query to search by keywords.
     *
     * @param  Builder<self>  $query
     */
    public function scopeSearch($query, ?string $search): void
    {
        if (empty($search)) {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('reference_number', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%")
                ->orWhere('purpose', 'like', "%{$search}%")
                ->orWhere('requestor_name', 'like', "%{$search}%")
                ->orWhere('requestor_department', 'like', "%{$search}%")
                ->orWhere('target_code', 'like', "%{$search}%")
                ->orWhere('branch_code', 'like', "%{$search}%");
        });
    }

    /**
     * Scope query to filter by branch code.
     *
     * @param  Builder<self>  $query
     */
    public function scopeBranch($query, ?string $branchCode): void
    {
        if (! empty($branchCode)) {
            $query->where('branch_code', $branchCode);
        }
    }

    /**
     * Scope query to filter by year and month.
     *
     * @param  Builder<self>  $query
     */
    public function scopePeriod($query, ?int $year = null, ?int $month = null): void
    {
        if ($year) {
            $query->where('year', $year);
        }

        if ($month) {
            $query->where('month', $month);
        }
    }
}
