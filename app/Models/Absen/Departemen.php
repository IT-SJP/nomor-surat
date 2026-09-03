<?php

namespace App\Models\Absen;

use Illuminate\Database\Eloquent\Model;

class Departemen extends Model
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
    protected $table = 'departemen';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'kode_dept';

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
}
