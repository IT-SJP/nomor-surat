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
     * The attributes that should be hidden for serialization to prevent NIK / sensitive data leakage.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'nik',
    ];

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
     * Department relationship.
     *
     * @return BelongsTo<Departemen, $this>
     */
    public function departemen(): BelongsTo
    {
        return $this->belongsTo(Departemen::class, 'kode_dept', 'kode_dept');
    }

    /**
     * Position relationship.
     *
     * @return BelongsTo<Jabatan, $this>
     */
    public function jabatanRel(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id', 'id');
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
    public static function searchEmployees(string $keyword = '', int $limit = 10): Collection
    {
        try {
            DB::connection('absen_db')->getPdo();

            /** @var \Illuminate\Database\Eloquent\Collection<int, self> $query */
            $query = static::query()
                ->with(['cabang', 'departemen', 'jabatanRel'])
                ->where('status_aktif', 'Aktif');

            if (trim($keyword) !== '') {
                $query->where(function (Builder $q) use ($keyword) {
                    $q->where('nik', 'ilike', "%{$keyword}%")
                        ->orWhere('nama_lengkap', 'ilike', "%{$keyword}%")
                        ->orWhere('nama_panggilan', 'ilike', "%{$keyword}%");
                });
            }

            $query = $query->orderBy('nama_lengkap', 'asc')
                ->limit($limit)
                ->get();

            /** @var Collection<int, array<string, mixed>> $mapped */
            $mapped = $query->map(function (self $emp): array {
                $rawCode = (string) ($emp->getAttribute('kode_cabang') ?? '');
                $localBranch = Branch::where('hr_code', $rawCode)->first();
                $branchCode = $localBranch?->branch_code ?? '';
                $branchName = $localBranch?->name ?? $emp->cabang?->branch_name ?? "Cabang {$rawCode}";

                // Generate safe opaque hash identifier so raw NIK primary key is never exposed
                $opaqueId = substr(hash_hmac('sha256', (string) $emp->getKey(), (string) config('app.key')), 0, 16);

                return [
                    'id' => $opaqueId,
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
