<?php

namespace App\Models\Absen;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
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
    protected $table = 'jabatan';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];
}
