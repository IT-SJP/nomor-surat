<?php

namespace App\Models\Absen;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Cabang extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'absen_db';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cabang';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'kode_cabang';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * Get employees belonging to this branch.
     *
     * @return HasMany<Karyawan, $this>
     */
    public function karyawan(): HasMany
    {
        return $this->hasMany(Karyawan::class, 'kode_cabang', 'kode_cabang');
    }

    /**
     * Get branch name reliably across schema variations.
     */
    public function getBranchNameAttribute(): string
    {
        return (string) ($this->attributes['nama_cabang'] ?? $this->attributes['nama'] ?? $this->attributes['name'] ?? $this->kode_cabang);
    }

    /**
     * Get active configured local branches for letter selection.
     */
    public static function getActiveBranches(): Collection
    {
        $branches = Branch::where('is_active', true)
            ->whereNotNull('branch_code')
            ->where('branch_code', '!=', '')
            ->orderBy('name', 'asc')
            ->get();

        return $branches->map(fn ($b) => [
            'id' => $b->id,
            'code' => $b->branch_code,
            'raw_code' => $b->hr_code,
            'name' => $b->name,
        ]);
    }
}
