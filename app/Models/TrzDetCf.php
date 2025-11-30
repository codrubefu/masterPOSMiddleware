<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrzDetCf extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dbo.trzdetcf';

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
        'idfirma', // 1
        'nrbonf', // nr bon
        'casa', // casa
        'idcl', // id client
        'art', // Numele articolului
        'cant', // cantitate
        'pretueur', // 0
        'preturon', //pret pe bucata
        'redabs', // 0
        'redproc', //0
        'valoare', // valoare totală
        'data', // data si ora
        'compid', // AriPos1 sau A
        'idtrzf', // autoincrement
        'inchidzi', // false
        'genconsum', // false
        'pretfaradisc', // pret unitate
        'upc', // cod de bare
        'cotatva', // 0.21
        'art2', // NULL
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
        'cant' => 'float',
        'pretueur' => 'float',
        'preturon' => 'float',
        'redabs' => 'float',
        'redproc' => 'float',
        'valoare' => 'float',
        'data' => 'datetime',
        'compid' => 'string',
        'idtrzf' => 'integer',
        'inchidzi' => 'boolean',
        'genconsum' => 'boolean',
        'pretfaradisc' => 'float',
        'upc' => 'string',
        'cotatva' => 'float',
        'art2' => 'string',
        'depart' => 'integer',
    ];

    /**
     * Create a new receipt detail entry
     *
     * @param array $data
     * @return static
     */
    public static function createDetail(array $data, array $client, $nrBon)
    {


        if ($data['product']['departament'] == 1) {
            $tva = $data['product']['tax1'];
        } elseif ($data['product']['departament'] == 2) {
            $tva = $data['product']['tax2'];
        } elseif ($data['product']['departament'] == 3) {
            $tva = $data['product']['tax3'];
        }

        if ($data['casa'] == 1) {
            if ($data['product']['gest'] == 3) {
                $casa = 8;
                $compId = 'POS' . $data['casa'] . '-D'; // Default compId
            } else {
                $casa = 9;
                $compId = 'POS' . $data['casa'] . '-B';
            }
        } elseif ($data['casa'] == 2) {
            if ($data['product']['gest'] == 3) {
                $casa = 10;
                $compId = 'POS' . $data['casa'] . '-D'; // Default compId
            } else {
                $casa = 11;
                $compId = 'POS' . $data['casa'] . '-B';
            }
        } elseif ($data['casa'] == 3) {

            if ($data['product']['gest'] == 3) {
                $casa = 12;
                $compId = 'POS' . $data['casa'] . '-D'; // Default compId
            } else {
                $casa = 13;
                $compId = 'POS' . $data['casa'] . '-B';
            }
        }

        return static::create([
            'idfirma' =>  1,
            'casa' => $casa,
            'nrbonf' => $nrBon,
            'idcl' => $client['id'] ?? 1,
            'art' => $data['product']['name'],
            'cant' => $data['qty'] . '.000',
            'pretueur' => $data['pretueur'] ?? 0.00,
            'preturon' => $data['product']['price'],
            'redabs' => $data['redabs'] ?? 0.00,
            'redproc' => $data['redproc'] ?? 0.00,
            'valoare' => $data['product']['price'] * $data['qty'],
            'data' =>  now(),
            'compid' => $compId,
            'inchidzi' =>  false,
            'genconsum' => false,
            'pretfaradisc' => $data['product']['price'],
            'upc' => $data['product']['upc'] ?? null,
            'cotatva' => $tva,
            'art2' => $data['art2'] ?? null,

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
     * Relationship with receipt header (TrzCfe)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function receipt()
    {
        return $this->belongsTo(TrzCfe::class, 'nrbonf', 'nrbonfint');
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
