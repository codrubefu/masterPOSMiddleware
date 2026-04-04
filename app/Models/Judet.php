<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Judet extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dbo.judet';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'idjudet';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'codjudet',
        'den',
        'etrsp',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idjudet' => 'integer',
        'codjudet' => 'string',
        'den' => 'string',
        'etrsp' => 'string',
    ];

    public static function findByEtrsp($etrsp)
    {
        return self::where('etrsp', $etrsp)->first();
    }
}