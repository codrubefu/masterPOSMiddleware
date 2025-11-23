<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function config()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        
        $casa = 1; // Default value
        
        if ($ip !== null) {
            $casaConfig = config('casa.casa');
            if (isset($casaConfig[$ip])) {
                $casa = $casaConfig[$ip];
            }
        }
        
        $config = [
            'casa' => $casa
        ];

        return response()->json($config);
    }
}