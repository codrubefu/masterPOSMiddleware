<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrzDetFactBf extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dbo.trzdetfactbf';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'idtrzdetfactbf';

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
        'nrfact',
        'idcl',
        'clasa',
        'grupa',
        'art',
        'cant',
        'cantf',
        'pretueur',
        'preturon',
        'redabs',
        'redproc',
        'valoare',
        'tva',
        'data',
        'compid',
        'detkit',
        'preturondisc',
        'cotatva',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idfirma' => 'integer',
        'nrfact' => 'integer',
        'idcl' => 'integer',
        'clasa' => 'string',
        'grupa' => 'string',
        'art' => 'string',
        'cant' => 'float',
        'cantf' => 'float',
        'pretueur' => 'float',
        'preturon' => 'float',
        'redabs' => 'float',
        'redproc' => 'float',
        'valoare' => 'float',
        'tva' => 'float',
        'data' => 'datetime',
        'compid' => 'string',
        'idtrzdetfactbf' => 'integer',
        'detkit' => 'string',
        'preturondisc' => 'float',
        'cotatva' => 'float',
    ];

    /**
     * Create a new invoice detail entry from POS request data
     *
     * @param array $data Item data from POS system
     * @param array $client Customer data
     * @param int $nrFact Invoice number
     * @return static
     */
    public static function createDetail(array $data, array $client, $nrFact)
    {
        $compId = 'AriPos' . ($data['casa'] ?? 1);

        // Calculate VAT based on department
        $tva = 0.21; // Default VAT rate
        if (isset($data['product']['departament'])) {
            if ($data['product']['departament'] == 1) {
                $tva = $data['product']['tax1'] ?? 0.21;
            } elseif ($data['product']['departament'] == 2) {
                $tva = $data['product']['tax2'] ?? 0.21;
            } elseif ($data['product']['departament'] == 3) {
                $tva = $data['product']['tax3'] ?? 0.21;
            }
        }

        $price = $data['product']['price'] ?? 0;
        $qty = $data['qty'] ?? 0;
        $valoare = $price * $qty;

        if ($data['product']['departament'] == 1) {
            $tva = $data['product']['tax1'];
        } elseif ($data['product']['departament'] == 2) {
            $tva = $data['product']['tax2'];
        } elseif ($data['product']['departament'] == 3) {
            $tva = $data['product']['tax3'];
        }
        $priceWithoutVat = self::getPriceWithoutVat($price, $tva);
        $dataValues = [
            'idfirma' => 1,
            'nrfact' => $nrFact,
            'idcl' => $client['id'] ?? null,
            'clasa' => $data['product']['clasa'] ?? null,
            'grupa' => $data['product']['grupa'] ?? null,
            'art' => $data['product']['name'] ?? null,
            'cant' => $qty,
            'cantf' => $qty,
            'pretueur' => null,
            'preturon' => $priceWithoutVat, // pret pe unitate fara tva 10 decimal
            'redabs' => null,
            'redproc' => 0.00,
            'valoare' => $priceWithoutVat * $qty, // total fara tva
            'valoare2' => round($priceWithoutVat * $qty, 2), // total fara tva rotunjit
            'tva' => $valoare - ($priceWithoutVat * $qty), //  tva produs pe toal
            'tva2' => round($valoare - ($priceWithoutVat * $qty), 2), // tva produs pe toal rotunjit
            'data' => now(),
            'compid' => $compId,
            'detkit' => '0',
            'preturondisc' => $priceWithoutVat,
            'cotatva' => $tva, // 0.21
        ];
        return static::create($dataValues);
    }

    /**
     * Scope to filter by invoice number
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $nrfact
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByInvoice($query, $nrfact)
    {
        return $query->where('nrfact', $nrfact);
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
        return $query->whereBetween('data', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by product
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
     * Relationship with invoice header (TrzFactBf)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice()
    {
        return $this->belongsTo(TrzFactBf::class, 'nrfact', 'nrfactfisc');
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

    protected static function getPriceWithoutVat($priceWithVat, $vatRate = 0.21)
    {
        return round($priceWithVat / (1 + $vatRate), 10);
    }
}
