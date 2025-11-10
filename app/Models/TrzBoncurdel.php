<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrzBoncurdel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dbo.trzboncurdel';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'idboncur';

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
        'idfirma',
        'idcl',
        'art',
        'cant',
        'pretu',
        'redabs',
        'redproc',
        'tipv',
        'data',
        'utilizator',
        'clasa',
        'grupa',
        'puncte',
        'casa',
        'datac',
        'tip',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idfirma' => 'integer',
        'idcl' => 'integer',
        'art' => 'string',
        'cant' => 'float',
        'pretu' => 'float',
        'redabs' => 'float',
        'redproc' => 'float',
        'tipv' => 'string',
        'data' => 'datetime',
        'utilizator' => 'string',
        'idboncur' => 'integer',
        'clasa' => 'string',
        'grupa' => 'string',
        'puncte' => 'float',
        'casa' => 'integer',
        'datac' => 'datetime',
        'tip' => 'string',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['valoare'];

    // Transaction types constants
    const TYPE_VERIF_PRET = 'VERIF PRET';
    const TYPE_STERGERE = 'STERGERE';
    const TYPE_INCHIDEREB = 'INCHIDEREB';
    const TYPE_RAPORT_X = 'Raport X';
    const TYPE_ARHIVARE = 'ARHIVARE';
    const TYPE_RAPORT_Z = 'Raport Z';

    /**
     * Get calculated value (cant * pretu)
     * Only for STERGERE and INCHIDEREB, otherwise 0.00000
     *
     * @return float
     */
    public function getValoareAttribute()
    {
        if (in_array($this->tip, [self::TYPE_STERGERE, self::TYPE_INCHIDEREB])) {
            return round($this->cant * $this->pretu, 5);
        }
        
        return 0.00000;
    }

    /**
     * Create a price verification record
     *
     * @param int $idfirma
     * @param string $art
     * @param string $clasa
     * @param string $grupa
     * @param float $pretu
     * @return static
     */
    public static function createVerifPret($idfirma, $art, $clasa, $grupa, $pretu)
    {
        return static::create([
            'idfirma' => $idfirma,
            'idcl' => 0,
            'art' => str_pad($art, 30, ' '),
            'cant' => 0.000,
            'pretu' => $pretu,
            'redabs' => 0.00,
            'redproc' => 0.00,
            'tipv' => 'RON',
            'data' => now(),
            'utilizator' => 'CASA',
            'clasa' => str_pad($clasa, 30, ' '),
            'grupa' => str_pad($grupa, 20, ' '),
            'puncte' => 0.00,
            'casa' => 1,
            'datac' => now(),
            'tip' => self::TYPE_VERIF_PRET,
        ]);
    }

    /**
     * Create a deletion record
     *
     * @param int $idfirma
     * @param string $art
     * @param string $clasa
     * @param string $grupa
     * @param float $cant
     * @param float $pretu
     * @param float $redabs
     * @param float $redproc
     * @return static
     */
    public static function createStergere($idfirma, $art, $clasa, $grupa, $cant, $pretu, $redabs = 0.00, $redproc = 0.00)
    {
        return static::create([
            'idfirma' => $idfirma,
            'idcl' => 0,
            'art' => $art,
            'cant' => $cant,
            'pretu' => $pretu,
            'redabs' => $redabs,
            'redproc' => $redproc,
            'tipv' => 'RON',
            'data' => now(),
            'utilizator' => 'CASA',
            'clasa' => $clasa,
            'grupa' => $grupa,
            'puncte' => 0.00,
            'casa' => 1,
            'datac' => now(),
            'tip' => self::TYPE_STERGERE,
        ]);
    }

    /**
     * Create a report record (Raport X, ARHIVARE, Raport Z)
     *
     * @param int $idfirma
     * @param string $tipRaport
     * @return static
     */
    public static function createRaport($idfirma, $tipRaport)
    {
        return static::create([
            'idfirma' => $idfirma,
            'idcl' => 0,
            'art' => str_pad('', 30, ' '),
            'cant' => 0.000,
            'pretu' => 0.00,
            'redabs' => 0.00,
            'redproc' => 0.00,
            'tipv' => 'RON',
            'data' => now(),
            'utilizator' => 'CASA',
            'clasa' => str_pad('', 30, ' '),
            'grupa' => str_pad('', 20, ' '),
            'puncte' => 0.00,
            'casa' => 1,
            'datac' => now(),
            'tip' => $tipRaport,
        ]);
    }

    /**
     * Scope to filter by transaction type
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $tip
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, $tip)
    {
        return $query->where('tip', $tip);
    }

    /**
     * Scope to filter by company
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $idfirma
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCompany($query, $idfirma)
    {
        return $query->where('idfirma', $idfirma);
    }

    /**
     * Scope to filter by date range
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('datac', [$startDate, $endDate]);
    }

    /**
     * Scope to get only records with values (STERGERE or INCHIDEREB)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithValue($query)
    {
        return $query->whereIn('tip', [self::TYPE_STERGERE, self::TYPE_INCHIDEREB]);
    }

    /**
     * Check if this is a price verification
     *
     * @return bool
     */
    public function isVerifPret()
    {
        return $this->tip === self::TYPE_VERIF_PRET;
    }

    /**
     * Check if this is a deletion
     *
     * @return bool
     */
    public function isStergere()
    {
        return $this->tip === self::TYPE_STERGERE;
    }

    /**
     * Check if this is a report
     *
     * @return bool
     */
    public function isRaport()
    {
        return in_array($this->tip, [
            self::TYPE_RAPORT_X,
            self::TYPE_ARHIVARE,
            self::TYPE_RAPORT_Z
        ]);
    }
}
