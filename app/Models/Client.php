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

    public static function saveFromAnafData(array $data)
    {
        $cui = $data['date_generale']['cui'];
        if ($data['inregistrare_scop_Tva']['scpTVA'] === true) {
            $cui = 'RO' . $data['date_generale']['cui'];
        }
        
        $clientData = [
            'cnpcui' => $cui,
            'activ' => 1,
            'den' => $data['date_generale']['denumire'],
            'prenume' => ' ',
            'adresa1' => $data['date_generale']['adresa'],
            'adresa2' => ' ',
            'oras' => $data['adresa_sediu_social']['sdenumire_Localitate'],
            'judet' => $data['adresa_sediu_social']['scod_Judet'] ?? 40,
            'tara' => 1,
            'reg' => ' ',
            'cp' => ' ',
            'email' => ' ',
            'tel' => ' ',
            'fax' => ' ',
            'contact' => ' ',
            'mobilcontact' => ' ',
            'emailcontact' => ' ',
            'dirgen' => ' ',
            'teldg' => ' ',
            'emaildg' => ' ',
            'dircom' => ' ',
            'teldc' => ' ',
            'emaildc' => ' ',
            'pj' => 1,
            'modp' => 'Numerar',
            'discount' => 0.00,
            'valuta' => 'RON',
            'obscui' => null,
            'nrc' => $data['date_generale']['nrRegCom'],
            'tax1' => 0.21,
            'tax2' => 0.00,
            'tax3' => 0.00,
            'tax4' => null,
            'tax5' => null,
            'termenp' => 0,
            'limcredit' => 0.00,
            'trsfact' =>  ' ',
            'limba' => 'Romana',
            'modlivrare' => 'EXW',
            'comceruta' => 0.00,
            'scadenta' => 0,
            'listapreturi' => 1,
            'dataultvanz' => null,
            'dataultincasari' => null,
            'soldcurent' => null,
            'datansf' => null,
            'ultvanz' => null,
            'vanzluna' => null,
            'vanzan' => null,
            'bloccredit' => 0,
            'blocproforme' => 0,
            'blocfactura' => 0,
            'copii' => 1,
            'emailfact' => 0,
            'emailfisacli' => 0,
            'formatfact' => 'implicit',
            'formatproforma' => 'implicit',
            'formatcotatie' => 'implicit',
            'creditmaxim' => null,
            'datacreditmaxim' => null,
            'mediepefactluna' => null,
            'nrtotalfactluna' => null,
            'delegati' => 0,
            'agentiv' => 0,
            'contvanz1' => ' ',
            'contvanz2' => ' ',
            'contvanz3' => ' ',
            'mediezileplata' => null,
            'datacreare' => now(),
            'utilizator' => 'ARIPOS',
            'data' => now(),
            'cardid' => ' ',
            'puncte' => 0.00,
            'soldcontab' => 0.00,
            'idpartener' => 0,
            'coment1' => null,
            'coment2' => null,
            'litigiu' => 0,
            'cudesc2infact' => 0,
            'idagent' => 0,
            'extern' => 0,
            'codtpd' => null,
            'banca' => 'XX',
            'cont' => 'XXXX',
            'datacodsaga' => now(),
            'guvern' => 0
        ];
        $client = parent::create($clientData);
        return $client;
    }
}
