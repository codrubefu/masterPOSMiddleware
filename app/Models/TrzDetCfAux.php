<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrzDetCfAux extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dbo.trzdetcfaux';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'idtrzf';

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
        'nrbonf',
        'casa',
        'idcl',
        'art',
        'cant',
        'pretueur',
        'preturon',
        'redabs',
        'redproc',
        'valoare',
        'data',
        'compid',
        'inchidzi',
        'genconsum',
        'upc',
        'cotatva',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idfirma' => 'integer',
        'nrbonf' => 'integer',
        'casa' => 'integer',
        'idcl' => 'integer',
        'art' => 'string',
        'cant' => 'decimal:3',
        'pretueur' => 'decimal:2',
        'preturon' => 'decimal:2',
        'redabs' => 'decimal:2',
        'redproc' => 'decimal:2',
        'valoare' => 'decimal:2',
        'data' => 'datetime',
        'compid' => 'string',
        'inchidzi' => 'boolean',
        'idtrzf' => 'integer',
        'genconsum' => 'boolean',
        'upc' => 'string',
        'cotatva' => 'decimal:4',
    ];

    /**
     * Create a new auxiliary receipt detail entry
     *
     * @param array $data
     * @return static
     */
    public static function createAuxDetail(array $data)
    {
        return static::create([
            'idfirma' => $data['idfirma'] ?? 1,
            'nrbonf' => $data['nrbonf'],
            'casa' => $data['casa'] ?? 1,
            'idcl' => $data['idcl'] ?? 0,
            'art' => $data['art'],
            'cant' => $data['cant'],
            'pretueur' => $data['pretueur'] ?? 0.00,
            'preturon' => $data['preturon'],
            'redabs' => $data['redabs'] ?? 0.00,
            'redproc' => $data['redproc'] ?? 0.00,
            'valoare' => $data['valoare'],
            'data' => $data['data'] ?? now(),
            'compid' => $data['compid'] ?? null,
            'inchidzi' => $data['inchidzi'] ?? false,
            'genconsum' => $data['genconsum'] ?? false,
            'upc' => $data['upc'] ?? null,
            'cotatva' => $data['cotatva'] ?? 0.0000,
        ]);
    }

    /**
     * Scope to filter by receipt number
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $nrbonf
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByReceipt($query, $nrbonf)
    {
        return $query->where('nrbonf', $nrbonf);
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
     * Scope to filter by product code
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $art
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByProduct($query, $art)
    {
        return $query->where('art', $art);
    }

    /**
     * Scope to filter by UPC
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $upc
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUpc($query, $upc)
    {
        return $query->where('upc', $upc);
    }

    /**
     * Scope to get closed items (inchidzi = true)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeClosed($query)
    {
        return $query->where('inchidzi', true);
    }

    /**
     * Scope to get open items (inchidzi = false)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOpen($query)
    {
        return $query->where('inchidzi', false);
    }

    /**
     * Check if this detail is closed
     *
     * @return bool
     */
    public function isClosed()
    {
        return $this->inchidzi === true;
    }

    /**
     * Check if this detail generates consumption
     *
     * @return bool
     */
    public function generatesConsumption()
    {
        return $this->genconsum === true;
    }

    /**
     * Get the final price after discount
     *
     * @return float
     */
    public function getFinalPrice()
    {
        $price = $this->preturon ?? 0;
        $absDiscount = $this->redabs ?? 0;
        $percDiscount = $this->redproc ?? 0;
        
        return round($price - $absDiscount - ($price * $percDiscount / 100), 2);
    }

    /**
     * Get the total value (quantity * final price)
     *
     * @return float
     */
    public function getTotalValue()
    {
        return round($this->cant * $this->getFinalPrice(), 2);
    }

    /**
     * Get the VAT amount
     *
     * @return float
     */
    public function getVatAmount()
    {
        $value = $this->valoare ?? 0;
        $vatRate = $this->cotatva ?? 0;
        
        return round($value * $vatRate / (100 + $vatRate), 2);
    }

    /**
     * Close this detail item
     *
     * @return bool
     */
    public function close()
    {
        $this->inchidzi = true;
        return $this->save();
    }

    /**
     * Relationship with auxiliary receipt header (TrzCfeAux)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function auxReceipt()
    {
        return $this->belongsTo(TrzCfeAux::class, 'nrbonf', 'nrbonfint');
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
     * Relationship with product (if UpcGenprod model exists)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(UpcGenprod::class, 'upc', 'upc');
    }
}
