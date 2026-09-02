<?php

namespace App\Models\Absen;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Karyawan extends Model
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
    protected $table = 'karyawan';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'nik';

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
     * The attributes that aren't mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * Branch relationship.
     *
     * @return BelongsTo<Cabang, $this>
     */
    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'kode_cabang', 'kode_cabang');
    }

    /**
     * Get employee full name reliably.
     */
    public function getFullNameAttribute(): string
    {
        return (string) ($this->attributes['nama_lengkap'] ?? $this->attributes['nama'] ?? $this->attributes['nama_panggilan'] ?? '');
    }

    /**
     * Get employee NIK reliably.
     */
    public function getNikNumberAttribute(): string
    {
        return (string) ($this->attributes['nik'] ?? '');
    }

    /**
     * Search active employees by NIK or Name.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function searchEmployees(string $keyword, int $limit = 10): Collection
    {
        if (trim($keyword) === '') {
            return collect();
        }

        try {
            DB::connection('absen_db')->getPdo();

            /** @var \Illuminate\Database\Eloquent\Collection<int, self> $query */
            $query = static::query()
                ->with(['cabang', 'departemen', 'jabatanRel'])
                ->where(function (Builder $q) use ($keyword) {
                    $q->where('nik', 'like', "%{$keyword}%")
                        ->orWhere('nama_lengkap', 'ilike', "%{$keyword}%")
                        ->orWhere('nama_panggilan', 'ilike', "%{$keyword}%");
                })
                ->when(
                    DB::connection('absen_db')->getSchemaBuilder()->hasColumn('karyawan', 'status_aktif'),
                    fn (Builder $q) => $q->where('status_aktif', 'Aktif')
                )
                ->limit($limit)
                ->get();

            /** @var Collection<int, array<string, mixed>> $mapped */
            $mapped = $query->map(function (self $emp): array {
                $rawCode = (string) ($emp->getAttribute('kode_cabang') ?? '');
                $localBranch = Branch::where('hr_code', $rawCode)->first();
                $branchCode = $localBranch?->branch_code ?? '';
                $branchName = $localBranch?->name ?? $emp->cabang?->branch_name ?? "Cabang {$rawCode}";

                return [
                    'id' => $emp->getKey(),
                    'nik' => $emp->nik_number,
                    'name' => $emp->full_name,
                    'branch_id' => $rawCode,
                    'branch_code' => $branchCode,
                    'branch_name' => $branchName,
                    'email' => $emp->getAttribute('email'),
                    'phone' => $emp->getAttribute('no_hp'),
                    'department' => $emp->departemen?->nama_dept ?? 'SJP Group',
                    'position' => $emp->jabatanRel?->nama_jabatan ?? 'Karyawan',
                ];
            });

            return $mapped;
        } catch (\Throwable $e) {
            // Fallback for testing/offline mode
            return collect();
        }
    }
}
