<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrzFactBf extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dbo.trzfactbf';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'nrfact';

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
        'nrfactfisc',
        'nrdep',
        'nrgest',
        'idcl',
        'stotalron',
        'redabs',
        'redproc',
        'tva',
        'cotatva',
        'totalron',
        'sold',
        'itotalron',
        'itotaleur',
        'itotalusd',
        'modp',
        'nrtrzcc',
        'tipcc',
        'tipv',
        'nume',
        'cnp',
        'ciserie',
        'cinr',
        'cipol',
        'auto',
        'nrauto',
        'datafact',
        'datascad',
        'data',
        'compid',
        'tip',
        'nrfactspec',
        'idpers',
        'costtot',
        'avans',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idfirma' => 'integer',
        'nrfactfisc' => 'integer',
        'nrdep' => 'integer',
        'nrgest' => 'integer',
        'idcl' => 'integer',
        'stotalron' => 'float',
        'redabs' => 'float',
        'redproc' => 'float',
        'tva' => 'float',
        'cotatva' => 'integer',
        'totalron' => 'float',
        'sold' => 'float',
        'itotalron' => 'float',
        'itotaleur' => 'float',
        'itotalusd' => 'float',
        'modp' => 'string',
        'nrtrzcc' => 'string',
        'tipcc' => 'string',
        'tipv' => 'string',
        'nume' => 'string',
        'cnp' => 'string',
        'ciserie' => 'string',
        'cinr' => 'string',
        'cipol' => 'string',
        'auto' => 'string',
        'nrauto' => 'string',
        'datafact' => 'datetime',
        'datascad' => 'datetime',
        'data' => 'datetime',
        'compid' => 'string',
        'nrfact' => 'string',
        'tip' => 'string',
        'nrfactspec' => 'string',
        'idpers' => 'integer',
        'costtot' => 'float',
        'avans' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['totalron'];

    /**
     * Payment mode constants.
     */
    const PAYMENT_CASH = 'NUMERAR';
    const PAYMENT_CARD = 'CARD';
    const PAYMENT_MIXED = 'MIXT';

    /**
     * Currency type constants.
     */
    const CURRENCY_RON = 'RON';
    const CURRENCY_EUR = 'EUR';
    const CURRENCY_USD = 'USD';

    /**
     * Get calculated totalron (subtotal minus discount plus VAT).
     *
     * @return float
     */
    public function getTotalronAttribute()
    {
        return round(($this->stotalron ?? 0) - ($this->redabs ?? 0) + ($this->tva ?? 0), 2);
    }

    /**
     * Create a new fiscal invoice entry from POS request data
     *
     * @param array $data Request data from POS system
     * @return static
     */
    public static function createFromPOS(array $data, $totalWithoutVat)
    {


        $compId = 'AriPos' . ($data['casa'] ?? 1);  
        // Get the biggest nrfact value from database
        $maxNrFact = self::max('nrfact') ?? 0;
        $nrfact = $maxNrFact + 1;
        $factNo = str_pad($nrfact, 5, '0', STR_PAD_LEFT);
        $dataToAdd = [
            'idfirma' => 1,
            'nrfactfisc' => ' ',
            'nrdep' => 1,
            'nrgest' => $data['items'][0]['product']['gest'],
            'idcl' => $data['customer']['id'] ?? null,
            'stotalron' => $totalWithoutVat, // Subtotal before VAT
            'redabs' => null,
            'redproc' => null,
            'tva' => $data['subtotal'] - $totalWithoutVat, //valoare tva
            'cotatva' => 1,
            'totalron' => $data['subtotal'] , // Total amount
            'sold' => $data['subtotal'] ,  // cu tva
            'itotalron' => 0.00, // Total cu tva
            'itotaleur' => null,
            'itotalusd' => null,
            'modp' => null,
            'nrtrzcc' => null,
            'tipcc' => null,
            'tipv' => 'RON',
            'nume' => ' ',
            'cnp' =>  ' ',
            'ciserie' => ' ',
            'cinr' => ' ',
            'cipol' => '',
            'auto' =>  ' ',
            'nrauto' => $data['customer']['nrAuto'] ?? ' ',
            'datafact' => now(),
            'datascad' => now(),
            'data' => now(),
            'compid' => $compId,
            'tip' => 'CP',
            'nrfactspec' => 'FB1202503' . $factNo,
            'idpers' => 0,
            'costtot' => 0,
            'avans' => false,
        ];
        return parent::create($dataToAdd);
    }

    /**
     * Scope to filter by company.
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
     * Scope to filter by department.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $nrdep
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDepartment($query, $nrdep)
    {
        return $query->where('nrdep', $nrdep);
    }

    /**
     * Scope to filter by date range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('datafact', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by payment mode.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $modp
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPaymentMode($query, $modp)
    {
        return $query->where('modp', $modp);
    }

    /**
     * Check if payment was by cash.
     *
     * @return bool
     */
    public function isCashPayment()
    {
        return $this->modp === self::PAYMENT_CASH;
    }

    /**
     * Check if payment was by card.
     *
     * @return bool
     */
    public function isCardPayment()
    {
        return $this->modp === self::PAYMENT_CARD;
    }

    /**
     * Check if payment was mixed.
     *
     * @return bool
     */
    public function isMixedPayment()
    {
        return $this->modp === self::PAYMENT_MIXED;
    }

    /**
     * Relationship with client (if Client model exists).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'idcl', 'idcl');
    }

    protected static function getPriceWithoutVat($priceWithVat, $vatRate = 0.21)
    {
        return round($priceWithVat / (1 + $vatRate), 10);
    }
}
