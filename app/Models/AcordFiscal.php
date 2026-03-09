<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcordFiscal extends Model
{
    protected $table = 'erp.dbo.acordFiscal';
    public $timestamps = false;
    protected $primaryKey = 'acordFiscalId';
    protected $fillable = [
        'code',
        'idnr',
        'dateUpdate',
    ];
    // 'lastModified' is a SQL Server timestamp/rowversion, not fillable
}
