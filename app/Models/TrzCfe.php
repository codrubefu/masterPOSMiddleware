<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrzCfe extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dbo.trzcfe';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'nrbonfint';

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
        'idcl', // 1 data persf , id codul fiscal
        'stotalron', // total cu tva
        'redabs', // 0 valoare reducere absoluta
        'redproc', // 
        'itotalron', // total cu tva
        'itotaleur', // 0
        'itotalusd', //
        'modp', // numRON, ccRON, ppRON
        'nrtrzcc', // 0
        'tipcc', // 0
        'tipv', // RON
        'data', // data si ora
        'compid', // AriPos1 sau 
        'nrbonfint', // autoincrement
        'nrbonspec', // NULL
        'costtot', // NULL  
        'chit', // false
        'idtrzcf', // NULL
        'casa', // numarul casei
        'nrdispliv', // 0
        'nrbontrzcfeaux', // NULL
        'idlogin', // 0
        'userlogin', // MULL
        'numerar', // NULL
        'card', // NULL
        'nrnp', // NULL
        'datac',
        'tichete', // o
        'cuibf', // 0 sau CUI persoana juridica
        'idrapz', // 0
        'anulat', // false
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idfirma' => 'integer',
        'idcl' => 'integer',
        'stotalron' => 'integer',
        'redabs' => 'float',
        'redproc' => 'float',
        'itotalron' => 'float',
        'itotaleur' => 'float',
        'itotalusd' => 'float',
        'modp' => 'string',
        'nrtrzcc' => 'string',
        'tipcc' => 'string',
        'tipv' => 'string',
        'data' => 'datetime',
        'compid' => 'string',
        'nrbonfint' => 'integer',
        'nrbonspec' => 'string',
        'costtot' => 'float',
        'chit' => 'boolean',
        'idtrzcf' => 'integer',
        'casa' => 'integer',
        'nrdispliv' => 'integer',
        'nrbontrzcfeaux' => 'integer',
        'idlogin' => 'integer',
        'userlogin' => 'string',
        'numerar' => 'float',
        'card' => 'float',
        'nrnp' => 'integer',
        'datac' => 'datetime',
        'tichete' => 'float',
        'cuibf' => 'integer',
        'idrapz' => 'integer',
        'anulat' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['totalron'];

    /**
     * Payment mode constants
     */
    const PAYMENT_CASH = 'NUMERAR';
    const PAYMENT_CARD = 'CARD';
    const PAYMENT_MIXED = 'MIXT';

    /**
     * Currency type constants
     */
    const CURRENCY_RON = 'RON';
    const CURRENCY_EUR = 'EUR';
    const CURRENCY_USD = 'USD';

    /**
     * Get calculated totalron (stotalron - redabs)
     * This mirrors the computed column in the database
     *
     * @return float
     */
    public function getTotalronAttribute()
    {
        return round(($this->stotalron ?? 0) - ($this->redabs ?? 0), 2);
    }

    /**
     * Create a new receipt entry from POS request data
     *
     * @param array $data Request data from POS system
     * @param Company|null $company Company instance
     * @return static
     */
    public static function createFromPOS(array $data, $gest = null)
    {
        // Determine payment type
        $paymentType = 'numRON'; // Default to cash
        if (isset($data['type'])) {
            if ($data['type'] == 'cash') {
                $paymentType = 'numRON';
            } elseif ($data['type'] == 'card') {
                $paymentType = 'ccRON';
            } else {
                $paymentType = 'ppRON'; // Mixed payment
            }
        }
        
        if ($gest == 3) {
            $compId = 'POS' . $data['casa'] . '-D'; // Default compId

        } else {
            $compId = 'POS' . $data['casa'] . '-D'; // Default compId

        }

        return parent::create([
            'idfirma' => 1,
            'idcl' => $data['customer']['id'] ??  $data['customer']['id'] ?? 1,
            'stotalron' => $data['subtotal'] ?? $data['subtotal'] ?? 0,
            'redabs' => $data['redabs'] ?? $data['redabs'] ?? 0,
            'redproc' => $data['discount_percentage'] ?? $data['discount_percentage'] ?? 0,
            'itotalron' => $data['subtotal'] ?? $data['subtotal'] ?? 0,
            'itotaleur' => 0.00,
            'itotalusd' => 0.00,
            'modp' => $paymentType,
            'nrtrzcc' => 0,
            'tipcc' => 0,
            'tipv' => 'RON',
            'data' => now(),
            'compid' => $compId,
            'nrbonspec' => null,
            'costtot' => null,
            'chit' => false,
            'idtrzcf' => null,
            'casa' => $data['casa'] ?? 1,
            'nrdispliv' => 0,
            'nrbontrzcfeaux' => null,
            'idlogin' => 0,
            'userlogin' => null,
            'numerar' => null, // $data['numerarAmount'] ?? 
            'card' => null, // $data['cardAmount']
            'nrnp' => null,
            'datac' => now(),
            'tichete' => 0.00,
            'cuibf' => $data['customer']['cui'] ?? 0,
            'idrapz' => 0,
            'anulat' => false,
        ]);
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
     * Scope to filter by casa (register)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $casa
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCasa($query, $casa)
    {
        return $query->where('casa', $casa);
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
     * Scope to filter by payment mode
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
     * Scope to get only active (non-cancelled) receipts
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('anulat', false);
    }

    /**
     * Scope to get only cancelled receipts
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCancelled($query)
    {
        return $query->where('anulat', true);
    }

    /**
     * Check if this receipt is cancelled
     *
     * @return bool
     */
    public function isCancelled()
    {
        return $this->anulat === true;
    }

    /**
     * Check if payment was by cash
     *
     * @return bool
     */
    public function isCashPayment()
    {
        return $this->modp === self::PAYMENT_CASH;
    }

    /**
     * Check if payment was by card
     *
     * @return bool
     */
    public function isCardPayment()
    {
        return $this->modp === self::PAYMENT_CARD;
    }

    /**
     * Check if payment was mixed
     *
     * @return bool
     */
    public function isMixedPayment()
    {
        return $this->modp === self::PAYMENT_MIXED;
    }

    /**
     * Cancel this receipt
     *
     * @return bool
     */
    public function cancel()
    {
        $this->anulat = true;
        return $this->save();
    }

    /**
     * Relationship with client (if Client model exists)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'idcl', 'idcl');
    }

    /**
     * Relationship with bon detail items
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bonItems()
    {
        return $this->hasMany(TrzBoncurdel::class, 'idtrzcf', 'nrbonfint');
    }
}
