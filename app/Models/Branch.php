<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = [
        'hr_code',
        'branch_code',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the letters associated with this branch.
     *
     * @return HasMany<Letter, $this>
     */
    public function letters(): HasMany
    {
        return $this->hasMany(Letter::class);
    }
}
