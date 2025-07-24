<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sequence Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the default configuration for sequence generation.
    | You can override these settings for each type of sequence.
    |
    */

    // Invoice sequence configuration
    'invoice' => [
        'prefix' => 'INV-',
        'suffix' => null,
        'start_number' => 1000,
        'random' => false,
    ],

    // Order sequence configuration
    'order' => [
        'prefix' => 'ORD-',
        'suffix' => null,
        'start_number' => 1000,
        'random' => false,
    ],

    // Add more sequence types as needed
];
