<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dbo.company';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'idfirma';

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
        'cnpcui',
        'den',
        'adresa1',
        'adresa2',
        'oras',
        'judet',
        'tara',
        'reg',
        'cp',
        'tel',
        'fax',
        'email',
        'nrc',
        'data',
        'utilizator',
        'nrbt',
        'nrbp',
        'nrnp',
        'nrchit',
        'adresaweb',
        'serie',
        'capitalsoc',
        'endfact1',
        'endfact2',
        'upcint',
        'banca1',
        'iban1',
        'banca2',
        'iban2',
        'seriepro',
        'smtpserver',
        'smtpport',
        'smtpuser',
        'smtppass',
        'emailfact',
        'tvainc',
        'ultv',
        'dataultv',
        'ultupd',
        'dataultupd',
        'declconf',
        'banca3',
        'iban3',
        'so',
        'tipb',
        'emailauto',
        'passauto',
        'banca4',
        'iban4',
        'tax1',
        'tax2',
        'tax3',
        'tax4',
        'tax5',
        'serverftpdsv',
        'adrlivtravizoblig',
        'gestmp',
        'gestmpconsum',
        'login',
        'modelcf',
        'blocpvcostult',
        'bloccantvstoc0',
        'rezervat',
        'denrest',
        'comfactedi',
        'tax6',
        'platacardmasterspa',
        'artmasterspaTax1',
        'artmasterspaTax2',
        'nrbf',
        'erestaurant',
        'nrbfdude',
        'drivercf',
        'userdisp',
        'pasdisp',
        'nrtranscmd',
        'lot',
        'codsaga',
        'idclz',
        'gestfabrmp',
        'gestmpsemifabr',
        'gestmpconsumsemifabr',
        'userchit',
        'paschit',
        'emailcomanda',
        'butondesk',
        'nrspec2',
        'deneticheta',
        'numecontact',
        'prenumecontact',
        'telcontact',
        'autogencodsaga',
        'izcur',
        'saft',
        'sector_efactura',
        'pctlucruden',
        'pctlucruadr',
        'contstoc',
        'contvenit',
        'contchelt',
        'contstocmp',
        'contcash',
        'contcard',
        'neplatitortva',
        'pretdesc_importprod',
        'blocprodcr',
        'faradisc',
        'centralizare',
        'limcant',
        'posretailcautprodinfo',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cnpcui' => 'string',
        'den' => 'string',
        'adresa1' => 'string',
        'adresa2' => 'string',
        'oras' => 'string',
        'judet' => 'string',
        'tara' => 'string',
        'reg' => 'string',
        'cp' => 'string',
        'tel' => 'string',
        'fax' => 'string',
        'email' => 'string',
        'nrc' => 'string',
        'data' => 'datetime',
        'utilizator' => 'string',
        'idfirma' => 'integer',
        'nrbt' => 'integer',
        'nrbp' => 'integer',
        'nrnp' => 'integer',
        'nrchit' => 'integer',
        'adresaweb' => 'string',
        'serie' => 'string',
        'capitalsoc' => 'string',
        'endfact1' => 'string',
        'endfact2' => 'string',
        'upcint' => 'integer',
        'banca1' => 'string',
        'iban1' => 'string',
        'banca2' => 'string',
        'iban2' => 'string',
        'seriepro' => 'string',
        'smtpserver' => 'string',
        'smtpport' => 'string',
        'smtpuser' => 'string',
        'smtppass' => 'string',
        'emailfact' => 'string',
        'tvainc' => 'boolean',
        'ultv' => 'string',
        'dataultv' => 'datetime',
        'ultupd' => 'string',
        'dataultupd' => 'datetime',
        'declconf' => 'integer',
        'banca3' => 'string',
        'iban3' => 'string',
        'so' => 'string',
        'tipb' => 'string',
        'emailauto' => 'string',
        'passauto' => 'string',
        'banca4' => 'string',
        'iban4' => 'string',
        'tax1' => 'decimal:4',
        'tax2' => 'decimal:4',
        'tax3' => 'decimal:4',
        'tax4' => 'decimal:4',
        'tax5' => 'decimal:4',
        'tax6' => 'decimal:4',
        'serverftpdsv' => 'string',
        'adrlivtravizoblig' => 'boolean',
        'gestmp' => 'integer',
        'gestmpconsum' => 'integer',
        'login' => 'boolean',
        'modelcf' => 'string',
        'blocpvcostult' => 'boolean',
        'bloccantvstoc0' => 'boolean',
        'rezervat' => 'boolean',
        'denrest' => 'string',
        'comfactedi' => 'string',
        'platacardmasterspa' => 'boolean',
        'artmasterspaTax1' => 'string',
        'artmasterspaTax2' => 'string',
        'nrbf' => 'integer',
        'erestaurant' => 'boolean',
        'nrbfdude' => 'integer',
        'drivercf' => 'string',
        'userdisp' => 'string',
        'pasdisp' => 'string',
        'nrtranscmd' => 'integer',
        'lot' => 'boolean',
        'codsaga' => 'string',
        'idclz' => 'string',
        'gestfabrmp' => 'integer',
        'gestmpsemifabr' => 'integer',
        'gestmpconsumsemifabr' => 'integer',
        'userchit' => 'string',
        'paschit' => 'string',
        'emailcomanda' => 'string',
        'butondesk' => 'boolean',
        'nrspec2' => 'boolean',
        'deneticheta' => 'string',
        'numecontact' => 'string',
        'prenumecontact' => 'string',
        'telcontact' => 'string',
        'autogencodsaga' => 'boolean',
        'izcur' => 'boolean',
        'saft' => 'boolean',
        'sector_efactura' => 'string',
        'pctlucruden' => 'string',
        'pctlucruadr' => 'string',
        'contstoc' => 'string',
        'contvenit' => 'string',
        'contchelt' => 'string',
        'contstocmp' => 'string',
        'contcash' => 'string',
        'contcard' => 'string',
        'neplatitortva' => 'boolean',
        'pretdesc_importprod' => 'string',
        'blocprodcr' => 'boolean',
        'faradisc' => 'boolean',
        'centralizare' => 'boolean',
        'limcant' => 'integer',
        'posretailcautprodinfo' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'smtppass',
        'passauto',
        'pasdisp',
        'paschit',
    ];

    /**
     * Get the full company address
     *
     * @return string
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->adresa1,
            $this->adresa2,
            $this->oras,
            $this->judet,
            $this->tara,
            $this->cp,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get the full contact name
     *
     * @return string
     */
    public function getFullContactNameAttribute()
    {
        return trim(($this->prenumecontact ?? '') . ' ' . ($this->numecontact ?? ''));
    }

    /**
     * Check if company is VAT payer
     *
     * @return bool
     */
    public function isVatPayer()
    {
        return $this->neplatitortva !== true;
    }

    /**
     * Check if company includes VAT in prices
     *
     * @return bool
     */
    public function includesVat()
    {
        return $this->tvainc === true;
    }

    /**
     * Check if e-restaurant is enabled
     *
     * @return bool
     */
    public function isErestaurantEnabled()
    {
        return $this->erestaurant === true;
    }

    /**
     * Get all tax rates as an array
     *
     * @return array
     */
    public function getTaxRates()
    {
        return [
            'tax1' => $this->tax1 ?? 0,
            'tax2' => $this->tax2 ?? 0,
            'tax3' => $this->tax3 ?? 0,
            'tax4' => $this->tax4 ?? 0,
            'tax5' => $this->tax5 ?? 0,
            'tax6' => $this->tax6 ?? 0,
        ];
    }

    /**
     * Get all bank accounts
     *
     * @return array
     */
    public function getBankAccounts()
    {
        $accounts = [];

        if ($this->banca1 && $this->iban1) {
            $accounts[] = ['bank' => $this->banca1, 'iban' => $this->iban1];
        }
        if ($this->banca2 && $this->iban2) {
            $accounts[] = ['bank' => $this->banca2, 'iban' => $this->iban2];
        }
        if ($this->banca3 && $this->iban3) {
            $accounts[] = ['bank' => $this->banca3, 'iban' => $this->iban3];
        }
        if ($this->banca4 && $this->iban4) {
            $accounts[] = ['bank' => $this->banca4, 'iban' => $this->iban4];
        }

        return $accounts;
    }

    /**
     * Get SMTP configuration
     *
     * @return array
     */
    public function getSmtpConfig()
    {
        return [
            'host' => $this->smtpserver,
            'port' => $this->smtpport,
            'username' => $this->smtpuser,
            'password' => $this->smtppass,
            'from_address' => $this->emailfact ?? $this->email,
        ];
    }

    /**
     * Scope to filter by active VAT payers
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVatPayers($query)
    {
        return $query->where('neplatitortva', false);
    }

    /**
     * Scope to filter by non-VAT payers
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNonVatPayers($query)
    {
        return $query->where('neplatitortva', true);
    }

    /**
     * Relationship with receipts (TrzCfe)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function receipts()
    {
        return $this->hasMany(TrzCfe::class, 'idfirma', 'idfirma');
    }

    /**
     * Relationship with receipt details (TrzDetCf)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function receiptDetails()
    {
        return $this->hasMany(TrzDetCf::class, 'idfirma', 'idfirma');
    }

    /**
     * Relationship with clients
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clients()
    {
        return $this->hasMany(Client::class, 'idfirma', 'idfirma');
    }
}
