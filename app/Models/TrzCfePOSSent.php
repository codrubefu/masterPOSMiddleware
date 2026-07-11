<?php

namespace App\Models;

class TrzCfePOSSent extends TrzCfePOS
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dbo.trzcfePOSSent';

    /**
     * Create a new sent receipt backup entry from POS request data.
     *
     * @param array $data Request data from POS system
     * @return static
     */
    public static function createFromPOS(array $data)
    {
        $paymentType = 'numRON';
        if (isset($data['type'])) {
            if ($data['type'] == 'cash') {
                $paymentType = 'numRON';
            } elseif ($data['type'] == 'card') {
                $paymentType = 'ccRON';
            } else {
                $paymentType = 'ppRON';
            }
        }

        $compId = 'AriPos' . $data['casa'];

        return static::create([
            'idfirma' => 1,
            'idcl' => $data['customer']['id'] ?? $data['idcl'] ?? 1,
            'stotalron' => $data['subtotal'] ?? 0,
            'redabs' => $data['redabs'] ?? 0,
            'redproc' => $data['discount_percentage'] ?? 0,
            'itotalron' => $data['subtotal'] ?? 0,
            'itotaleur' => null,
            'itotalusd' => null,
            'modp' => $paymentType,
            'nrtrzcc' => null,
            'tipcc' => null,
            'tipv' => 'RON',
            'data' => now(),
            'compid' => $compId,
            'nrbonspec' => null,
            'costtot' => null,
            'chit' => false,
            'idtrzcf' => null,
            'casa' => $data['casa'] ?? 1,
            'nrdispliv' => 0,
            'nrbontrzcfeaux' => null,
            'idlogin' => 0,
            'userlogin' => ' ',
            'numerar' => $data['numerarAmount'] ?? 0.00,
            'card' => $data['cardAmount'] ?? 0.00,
            'nrnp' => null,
            'datac' => now(),
            'tichete' => 0.00,
            'cuibf' => $data['customer']['cui'] ?? 0,
            'idrapz' => 0,
            'anulat' => false,
        ]);
    }
}
