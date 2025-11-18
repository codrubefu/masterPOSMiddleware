<?php

namespace App\Services;

use App\Models\Company;
use App\Models\TrzCfe;
use App\Models\TrzCfeAux;
use App\Models\TrzDetCf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class BonDatabaseService
{
    protected $company;

    public function __construct()
    {
        $this->company = Company::first();
    }

    public function save(Request $request)
    {
        $data = $request->all();
        
        // Save transaction header to TrzCfe (not TrzDetCf!)
        $this->saveTrzCfe($data);
        $this->saveTrzCfeAux($data);
    }

    protected function saveTrzCfe($data)
    {
        // Use the model's helper method for cleaner code
        $model = TrzCfe::create($data, $this->company);
        
        return $model;
    }

    protected function saveTrzCfeAux($data)
    {
        // Use the model's helper method for cleaner code
        $model = TrzCfeAux::create($data, $this->company);
        
        return $model;
    }
}
