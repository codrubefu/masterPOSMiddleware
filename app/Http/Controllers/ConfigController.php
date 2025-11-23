<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function config()
    {
        $config = [
            'casa' => 2
        ];

        return response()->json($config);
    }
}