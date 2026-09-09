<?php

namespace App\Models;

use Database\Factories\LetterFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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
        'branch_id',
        'parent_id',
        'reference_number',
        'sequence_number',
        'sub_number',
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
            'branch_id' => 'integer',
            'parent_id' => 'integer',
            'sequence_number' => 'integer',
            'sub_number' => 'integer',
            'month' => 'integer',
            'year' => 'integer',
        ];
    }

    /**
     * Get the branch associated with this letter.
     *
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the parent letter if this is a sub-letter.
     *
     * @return BelongsTo<Letter, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Letter::class, 'parent_id');
    }

    /**
     * Get the sub-letters under this parent letter.
     *
     * @return HasMany<Letter, $this>
     */
    public function subLetters(): HasMany
    {
        return $this->hasMany(Letter::class, 'parent_id')->orderBy('sub_number');
    }

    /**
     * Check if this letter is a sub-letter.
     */
    public function isSubLetter(): bool
    {
        return ! is_null($this->parent_id);
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

    /**
     * Return created_at converted to Asia/Jakarta (UTC+7 / WIB) timezone.
     */
    public function getCreatedAtWibAttribute(): ?Carbon
    {
        return $this->created_at?->timezone('Asia/Jakarta');
    }

    /**
     * Scope query to filter by date (created_at converted to WIB).
     *
     * @param  Builder<self>  $query
     */
    public function scopeDate($query, ?string $date): void
    {
        if (! empty($date)) {
            $start = Carbon::parse($date, 'Asia/Jakarta')->startOfDay()->utc();
            $end = Carbon::parse($date, 'Asia/Jakarta')->endOfDay()->utc();
            $query->whereBetween('created_at', [$start, $end]);
        }
    }
}
