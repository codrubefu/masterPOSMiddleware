<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrzCfeAux extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dbo.trzcfeaux';

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
        'nrbonspec',
        'costtot',
        'chit',
        'idtrzcf',
        'casa',
        'nrdispliv',
        'nrbontrzcfe',
        'platit',
        'tichete',
        'numerar',
        'card',
        'cuibf',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idfirma' => 'integer',
        'idcl' => 'integer',
        'stotalron' => 'decimal:2',
        'redabs' => 'decimal:2',
        'redproc' => 'decimal:2',
        'itotalron' => 'decimal:2',
        'itotaleur' => 'decimal:2',
        'itotalusd' => 'decimal:2',
        'modp' => 'string',
        'nrtrzcc' => 'string',
        'tipcc' => 'string',
        'tipv' => 'string',
        'data' => 'datetime',
        'compid' => 'string',
        'nrbonspec' => 'string',
        'costtot' => 'decimal:2',
        'chit' => 'boolean',
        'idtrzcf' => 'integer',
        'nrbonfint' => 'integer',
        'casa' => 'integer',
        'nrdispliv' => 'integer',
        'nrbontrzcfe' => 'float',
        'platit' => 'boolean',
        'tichete' => 'decimal:2',
        'numerar' => 'decimal:2',
        'card' => 'decimal:2',
        'cuibf' => 'string',
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
     * Get calculated totalron ((stotalron - redabs) - redproc * stotalron)
     * This mirrors the computed column in the database
     *
     * @return float
     */
    public function getTotalronAttribute()
    {
        $stotalron = $this->stotalron ?? 0;
        $redabs = $this->redabs ?? 0;
        $redproc = $this->redproc ?? 0;
        
        return round(($stotalron - $redabs) - ($redproc * $stotalron), 2);
    }

   /**
     * Create a new receipt entry from POS request data
     *
     * @param array $data Request data from POS system
     * @param Company|null $company Company instance
     * @return static
     */
    public static function create(array $data, $company = null)
    {
        // Determine payment type
        $paymentType = 'numRON'; // Default to cash
        if (isset($data['pendingPayment']['type'])) {
            if ($data['pendingPayment']['type'] == 'cash') {
                $paymentType = 'numRON';
            } elseif ($data['pendingPayment']['type'] == 'card') {
                $paymentType = 'ccRON';
            } else {
                $paymentType = 'ppRON'; // Mixed payment
            }
        }

        return static::create([
            'idfirma' => $company->idfirma ?? $data['idfirma'] ?? 1,
            'idcl' => $data['customer']['id'] ?? $data['idcl'] ?? null,
            'stotalron' => $data['subtotal'] ?? $data['stotalron'] ?? 0,
            'redabs' => $data['discount'] ?? $data['redabs'] ?? 0,
            'redproc' => $data['discount_percentage'] ?? $data['redproc'] ?? 0,
            'itotalron' => $data['subtotal'] ?? $data['itotalron'] ?? 0,
            'itotaleur' => null,
            'itotalusd' => null,
            'modp' => $paymentType,
            'nrtrzcc' => null,
            'tipcc' => null,
            'tipv' => 'RON',
            'data' => now(),
            'compid' => null,
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
            'cuibf' => '0',
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
        return $query->whereBetween('data', [$startDate, $endDate]);
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
     * Scope to get only paid receipts
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaid($query)
    {
        return $query->where('platit', true);
    }

    /**
     * Scope to get only unpaid receipts
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnpaid($query)
    {
        return $query->where('platit', false);
    }

    /**
     * Check if this receipt is paid
     *
     * @return bool
     */
    public function isPaid()
    {
        return $this->platit === true;
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
     * Mark this receipt as paid
     *
     * @return bool
     */
    public function markAsPaid()
    {
        $this->platit = true;
        return $this->save();
    }

    /**
     * Mark this receipt as unpaid
     *
     * @return bool
     */
    public function markAsUnpaid()
    {
        $this->platit = false;
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
     * Relationship with main receipt (TrzCfe)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mainReceipt()
    {
        return $this->belongsTo(TrzCfe::class, 'idtrzcf', 'nrbonfint');
    }
}
