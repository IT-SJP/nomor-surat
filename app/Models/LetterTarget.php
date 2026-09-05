<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LetterTarget extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'letter_targets';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope query to only active targets.
     *
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope query to search code or name.
     *
     * @param  Builder<self>  $query
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        if (empty($search)) {
            return;
        }

        $operator = $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $query->where(function (Builder $q) use ($search, $operator) {
            $q->where('code', $operator, "%{$search}%")
                ->orWhere('name', $operator, "%{$search}%")
                ->orWhere('description', $operator, "%{$search}%");
        });
    }

    /**
     * Find matching standard target by code, name, or formatted "CODE - Name" (case-insensitive).
     */
    public static function findMatching(?string $input): ?self
    {
        if (empty($input)) {
            return null;
        }

        $trimmed = trim($input);
        $normalized = strtoupper($trimmed);

        // 1. Direct match by code or name
        $target = self::query()
            ->active()
            ->where(function (Builder $q) use ($normalized) {
                $q->whereRaw('UPPER(code) = ?', [$normalized])
                    ->orWhereRaw('UPPER(name) = ?', [$normalized]);
            })
            ->first();

        if ($target) {
            return $target;
        }

        // 2. Format: "CODE - Name" (e.g. "EXT - Eksternal / Instansi Luar")
        if (str_contains($trimmed, '-')) {
            $parts = explode('-', $trimmed, 2);
            $codePart = strtoupper(trim($parts[0]));
            $namePart = strtoupper(trim($parts[1]));

            return self::query()
                ->active()
                ->where(function (Builder $q) use ($codePart, $namePart) {
                    $q->whereRaw('UPPER(code) = ?', [$codePart])
                        ->orWhereRaw('UPPER(name) = ?', [$namePart]);
                })
                ->first();
        }

        return null;
    }
}
