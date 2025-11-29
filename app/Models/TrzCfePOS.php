<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrzCfePOS extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dbo.trzcfePOS';

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
        'idcl',
        'stotalron',
        'redabs',
        'redproc',
        'itotalron',
        'itotaleur',
        'itotalusd',
        'modp',
        'nrtrzcc',
        'tipcc',
        'tipv',
        'data',
        'compid',
        'nrbonfint',
        'nrbonspec',
        'costtot',
        'chit',
        'idtrzcf',
        'casa',
        'nrdispliv',
        'nrbontrzcfeaux',
        'idlogin',
        'userlogin',
        'numerar',
        'card',
        'nrnp',
        'datac',
        'tichete',
        'cuibf',
        'idrapz',
        'anulat',
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
     * @return static
     */
    public static function createFromPOS(array $data)
    {
        $paymentType = 'numRON';
        if (isset($data['type'])) {
            if ($data['type'] == 'cash') {
                $paymentType = 'numRON';
            } elseif ($data['type'] == 'card') {
                $paymentType = 'ccRON';
            } else {
                $paymentType = 'ppRON';
            }
        }
        $compId = 'AriPos'.$data['casa'];
        return parent::create([
            'idfirma' => 1,
            'idcl' => $data['customer']['id'] ?? $data['idcl'] ?? 1,
            'stotalron' => $data['subtotal'] ?? $data['subtotal'] ?? 0,
            'redabs' => $data['redabs'] ?? $data['redabs'] ?? 0,
            'redproc' => $data['discount_percentage'] ?? $data['discount_percentage'] ?? 0,
            'itotalron' => $data['subtotal'] ?? $data['subtotal'] ?? 0,
            'itotaleur' => null,
            'itotalusd' => null,
            'modp' => $paymentType,
            'nrtrzcc' => null,
            'tipcc' => null,
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
            'userlogin' => ' ',
            'numerar' => $data['numerarAmount'] ?? 0.00,
            'card' => $data['cardAmount'] ?? 0.00,
            'nrnp' => null,
            'datac' => now(),
            'tichete' => 0.00,
            'cuibf' => $data['customer']['cui'] ?? 0,
            'idrapz' => 0,
            'anulat' => false,
        ]);
    }

    public function scopeByCompany($query, $idfirma)
    {
        return $query->where('idfirma', $idfirma);
    }

    public function scopeByCasa($query, $casa)
    {
        return $query->where('casa', $casa);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('datac', [$startDate, $endDate]);
    }

    public function scopeByPaymentMode($query, $modp)
    {
        return $query->where('modp', $modp);
    }

    public function scopeActive($query)
    {
        return $query->where('anulat', false);
    }

    public function scopeCancelled($query)
    {
        return $query->where('anulat', true);
    }

    public function isCancelled()
    {
        return $this->anulat === true;
    }

    public function isCashPayment()
    {
        return $this->modp === self::PAYMENT_CASH;
    }

    public function isCardPayment()
    {
        return $this->modp === self::PAYMENT_CARD;
    }

    public function isMixedPayment()
    {
        return $this->modp === self::PAYMENT_MIXED;
    }

    public function cancel()
    {
        $this->anulat = true;
        return $this->save();
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'idcl', 'idcl');
    }

    public function bonItems()
    {
        return $this->hasMany(TrzBoncurdel::class, 'idtrzcf', 'nrbonfint');
    }
}
