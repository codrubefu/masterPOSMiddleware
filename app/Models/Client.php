<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'client';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'idcl';

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
        'activ',
        'cnpcui',
        'den',
        'prenume',
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
        'contact',
        'mobilcontact',
        'emailcontact',
        'dirgen',
        'teldg',
        'emaildg',
        'dircom',
        'teldc',
        'emaildc',
        'pj',
        'modp',
        'discount',
        'valuta',
        'obscui',
        'nrc',
        'tax1',
        'tax2',
        'tax3',
        'tax4',
        'tax5',
        'termenp',
        'limcredit',
        'trsfact',
        'limba',
        'modlivrare',
        'comceruta',
        'scadenta',
        'listapreturi',
        'dataultvanz',
        'dataultincasari',
        'soldcurent',
        'datansf',
        'ultvanz',
        'vanzluna',
        'vanzan',
        'bloccredit',
        'blocproforme',
        'blocfactura',
        'copii',
        'emailfact',
        'emailfisacli',
        'formatfact',
        'formatproforma',
        'formatcotatie',
        'creditmaxim',
        'datacreditmaxim',
        'mediepefactluna',
        'nrtotalfactluna',
        'delegati',
        'agentiv',
        'contvanz1',
        'contvanz2',
        'contvanz3',
        'mediezileplata',
        'datacreare',
        'utilizator',
        'data',
        'cardid',
        'puncte',
        'soldcontab',
        'idpartener',
        'coment1',
        'coment2',
        'litigiu',
        'cudesc2infact',
        'idagent',
        'extern',
        'codtpd',
        'banca',
        'cont',
        'datacodsaga',
        'guvern',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activ' => 'boolean',
        'pj' => 'boolean',
        'discount' => 'decimal:2',
        'tax1' => 'decimal:2',
        'tax2' => 'decimal:2',
        'tax3' => 'decimal:2',
        'tax4' => 'decimal:2',
        'tax5' => 'decimal:2',
        'termenp' => 'integer',
        'limcredit' => 'decimal:2',
        'trsfact' => 'boolean',
        'soldcurent' => 'decimal:2',
        'ultvanz' => 'decimal:2',
        'vanzluna' => 'decimal:2',
        'vanzan' => 'decimal:2',
        'bloccredit' => 'boolean',
        'blocproforme' => 'boolean',
        'blocfactura' => 'boolean',
        'copii' => 'integer',
        'creditmaxim' => 'decimal:2',
        'mediepefactluna' => 'decimal:2',
        'nrtotalfactluna' => 'integer',
        'mediezileplata' => 'integer',
        'puncte' => 'decimal:2',
        'soldcontab' => 'decimal:2',
        'litigiu' => 'boolean',
        'cudesc2infact' => 'boolean',
        'extern' => 'boolean',
        'guvern' => 'boolean',
        'dataultvanz' => 'datetime',
        'dataultincasari' => 'datetime',
        'datansf' => 'datetime',
        'datacreditmaxim' => 'datetime',
        'datacreare' => 'datetime',
        'data' => 'datetime',
        'datacodsaga' => 'datetime',
    ];
}
