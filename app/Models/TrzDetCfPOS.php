<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrzDetCfPOS extends Model
{
    protected $table = 'dbo.trzdetcfPOS';
    protected $primaryKey = 'idtrzf';
    public $incrementing = true;
    public $timestamps = false;

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
        'idtrzf',
        'inchidzi',
        'genconsum',
        'pretfaradisc',
        'upc',
        'cotatva',
        'art2',
    ];

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
        'idtrzf' => 'integer',
        'inchidzi' => 'boolean',
        'genconsum' => 'boolean',
        'pretfaradisc' => 'decimal:2',
        'upc' => 'string',
        'cotatva' => 'decimal:4',
        'art2' => 'string',
        'depart' => 'integer',
    ];

    public static function createDetail(array $data, array $client, $type = null, $tva = 0.21)
    {
        if ($type) {
            if ($type == 'cash') {
                $paymentType = 'numRON';
            } elseif ($type == 'card') {
                $paymentType = 'ccRON';
            } else {
                $paymentType = 'ppRON';
            }
        }

        return static::create([
            'idfirma' => 1,
            'casa' => $data['casa'] ?? 1,
            'idcl' => $client['id'] ?? 1,
            'art' => $data['product']['name'],
            'cant' => $data['qty'] . '.000',
            'pretueur' => $data['pretueur'] ?? 0.00,
            'preturon' => $data['product']['price'],
            'redabs' => $data['redabs'] ?? 0.00,
            'redproc' => $data['redproc'] ?? 0.00,
            'valoare' => $data['product']['price'] * $data['qty'],
            'data' => now(),
            'compid' => $paymentType,
            'inchidzi' => false,
            'genconsum' => false,
            'pretfaradisc' => $data['product']['price'],
            'upc' => $data['product']['upc'] ?? null,
            'cotatva' => $tva,
            'art2' => $data['art2'] ?? null,
        ]);
    }

    public function scopeByReceipt($query, $nrbonf)
    {
        return $query->where('nrbonf', $nrbonf);
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
        return $query->whereBetween('data', [$startDate, $endDate]);
    }

    public function scopeByProduct($query, $art)
    {
        return $query->where('art', $art);
    }

    public function scopeByUpc($query, $upc)
    {
        return $query->where('upc', $upc);
    }

    public function scopeClosed($query)
    {
        return $query->where('inchidzi', true);
    }

    public function scopeOpen($query)
    {
        return $query->where('inchidzi', false);
    }

    public function isClosed()
    {
        return $this->inchidzi === true;
    }

    public function generatesConsumption()
    {
        return $this->genconsum === true;
    }

    public function getFinalPrice()
    {
        $price = $this->preturon ?? 0;
        $absDiscount = $this->redabs ?? 0;
        $percDiscount = $this->redproc ?? 0;
        return round($price - $absDiscount - ($price * $percDiscount / 100), 2);
    }

    public function getTotalValue()
    {
        return round($this->cant * $this->getFinalPrice(), 2);
    }

    public function getVatAmount()
    {
        $value = $this->valoare ?? 0;
        $vatRate = $this->cotatva ?? 0;
        return round($value * $vatRate / (100 + $vatRate), 2);
    }

    public function close()
    {
        $this->inchidzi = true;
        return $this->save();
    }

    public function receipt()
    {
        return $this->belongsTo(TrzCfePOS::class, 'nrbonf', 'nrbonfint');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'idcl', 'idcl');
    }

    public function product()
    {
        return $this->belongsTo(UpcGenprod::class, 'upc', 'upc');
    }
}
