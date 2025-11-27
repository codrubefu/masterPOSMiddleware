<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpcGenprod extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'UPC_GENPROD';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The primary key associated with the table.
     * Since this is a view with composite keys, we'll set it to null
     *
     * @var string|null
     */
    protected $primaryKey = null;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

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
        'upc',
        'ambsgr',
        'activ',
        'tax1',
        'tax2',
        'tax3',
        'depart',
        'um',
        'codint',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idfirma' => 'integer',
        'clasa' => 'string',
        'grupa' => 'string',
        'art' => 'string',
        'ambsgr' => 'string',
        'activ' => 'boolean',
        'tax1' => 'float',
        'tax2' => 'float',
        'tax3' => 'float',
        'depart' => 'integer',
    ];

    /**
     * Get products by UPC code
     *
     * @param string $upc
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function findByUpc($upc)
    {
        return static::where('upc', $upc)->get();
    }

    /**
     * Get product by UPC and company ID
     *
     * @param string $upc
     * @param int $idfirma
     * @return UpcGenprod|null
     */
    public static function findByUpcAndCompany($upc, $idfirma)
    {
        return static::where('upc', $upc)
            ->where('idfirma', $idfirma)
            ->first();
    }

    /**
     * Get active products
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('activ', true);
    }

    /**
     * Get products with SGR (deposit system)
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithSgr($query)
    {
        return $query->where('ambsgr', true);
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
     * Filter by department
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $depart
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDepartment($query, $depart)
    {
        return $query->where('depart', $depart);
    }

    /**
     * Get all prices for this product (manual relationship due to composite keys)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPricesAttribute()
    {
        return Pret::where('idfirma', $this->idfirma)
            ->where('clasa', $this->clasa)
            ->where('grupa', $this->grupa)
            ->where('art', $this->art)
            ->get();
    }

    /**
     * Get the latest price for this product
     *
     * @return Pret|null
     */
    public function getLatestPriceAttribute()
    {
        return Pret::where('idfirma', $this->idfirma)
            ->where('clasa', $this->clasa)
            ->where('grupa', $this->grupa)
            ->where('art', $this->art)
            ->orderBy('data', 'desc')
            ->first();
    }

    public static function getByUpc($upc)
    {
        return self::where('upc', $upc)->first();
    }

    /**
     * Get the full product code
     *
     * @return string
     */
    public function getFullCodeAttribute()
    {
        return sprintf('%s-%s-%s-%s', $this->idfirma, $this->clasa, $this->grupa, $this->art);
    }
}
