<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pret extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dbo.pret';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'idpret';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'idfirma',
        'clasa',
        'grupa',
        'art',
        'pret',
        'pretdesc',
        'cf',
        'pretnir',
        'data',
        'compid',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idfirma' => 'integer',
        'clasa' => 'integer',
        'grupa' => 'integer',
        'art' => 'integer',
        'pret' => 'float',
        'pretdesc' => 'float',
        'cf' => 'float',
        'pretnir' => 'float',
        'data' => 'datetime',
        'compid' => 'integer',
        'idpret' => 'integer',
    ];

    /**
     * Get the product associated with this price (manual relationship due to composite keys)
     *
     * @return UpcGenprod|null
     */
    public function getProductAttribute()
    {
        return UpcGenprod::where('idfirma', $this->idfirma)
            ->where('clasa', $this->clasa)
            ->where('grupa', $this->grupa)
            ->where('art', $this->art)
            ->first();
    }

    /**
     * Get price by product identifiers
     *
     * @param int $idfirma
     * @param int $clasa
     * @param int $grupa
     * @param int $art
     * @return Pret|null
     */
    public static function findByProduct($idfirma, $clasa, $grupa, $art)
    {
        return static::where('idfirma', $idfirma)
            ->where('clasa', $clasa)
            ->where('grupa', $grupa)
            ->where('art', $art)
            ->first();
    }

    /**
     * Get latest price for a product
     *
     * @param int $idfirma
     * @param int $clasa
     * @param int $grupa
     * @param int $art
     * @return Pret|null
     */
    public static function getLatestPrice($idfirma, $clasa, $grupa, $art)
    {
        return static::where('idfirma', $idfirma)
            ->where('clasa', $clasa)
            ->where('grupa', $grupa)
            ->where('art', $art)
            ->orderBy('data', 'desc')
            ->first();
    }

    /**
     * Filter by company
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
     * Filter prices by date range
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
     * Get active prices (latest for each product)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('data', 'desc');
    }

    /**
     * Get the full product code
     *
     * @return string
     */
    public function getFullProductCodeAttribute()
    {
        return sprintf('%s-%s-%s-%s', $this->idfirma, $this->clasa, $this->grupa, $this->art);
    }

    /**
     * Get the effective price (price with discount applied)
     *
     * @return float
     */
    public function getEffectivePriceAttribute()
    {
        return $this->pretdesc ?? $this->pret;
    }

    /**
     * Check if product has discount
     *
     * @return bool
     */
    public function hasDiscount()
    {
        return $this->pretdesc !== null && $this->pretdesc < $this->pret;
    }

    /**
     * Get discount percentage
     *
     * @return float|null
     */
    public function getDiscountPercentage()
    {
        if (!$this->hasDiscount() || $this->pret == 0) {
            return null;
        }

        return round((($this->pret - $this->pretdesc) / $this->pret) * 100, 2);
    }
}
