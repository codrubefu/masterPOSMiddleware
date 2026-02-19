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
            'path' => '\\\\ARIPOS1\\datecs$\\'   // corect pentru Windows UNC

        ],
        2 => [
            'name' => env('CASA_FILE_NAME', 'casa_output.txt'),
            'path' => '\\\\ARIPOS2\\datecs$\\'
        ],
        3 => [
            'name' => env('CASA_FILE_NAME', 'casa_output.txt'),
            'path' => storage_path('/casa/3')
        ],
        4 => [
            'name' => env('CASA_FILE_NAME', 'casa_output.txt'),
            'path' => storage_path('/casa/4')
        ]
    ],
    'casa' => [
        '192.168.0.57' => 1,
        '192.168.0.58' => 2,
	    '192.168.1.35' => 3
    ]

];
