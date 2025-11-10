<?php

return [
    /*
    |--------------------------------------------------------------------------
    | File Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the text file that will be written.
    |
    */

    'file' => [
        1 => [
            'name' => env('CASA_FILE_NAME', 'casa_output.txt'),
            'path' => storage_path('app/casa/1')
        ],
        2 => [
            'name' => env('CASA_FILE_NAME', 'casa_output.txt'),
            'path' => storage_path('app/casa/2')
        ],
        3 => [
            'name' => env('CASA_FILE_NAME', 'casa_output.txt'),
            'path' => storage_path('app/casa/3')
        ],
        4 => [
            'name' => env('CASA_FILE_NAME', 'casa_output.txt'),
            'path' => storage_path('app/casa/4')
        ]
    ]

];
