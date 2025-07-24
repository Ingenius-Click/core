<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Basic Packages
    |--------------------------------------------------------------------------
    |
    | This array contains the basic packages that can be installed when
    | running the ingenius:install command. Each package should have a name
    | and an optional version constraint.
    |
    */

    'basic_packages' => [
        // Example packages - replace with your actual basic packages
        'ingenius/auth' => '^1.0.0',
        'ingenius/coins' => '^1.0.0',
        'ingenius/orders' => '^1.0.0',
        'ingenius/payforms' => '^1.0.0',
        'ingenius/products' => '^1.0.0',
        'ingenius/shopcart' => '^1.0.0',
        'ingenius/storefront' => '^1.0.0',
        'ingenius/shipment' => '^1.0.0'
        // Add more packages as needed
    ],

    /*
    |--------------------------------------------------------------------------
    | Installation Prompt
    |--------------------------------------------------------------------------
    |
    | The message to display when asking the user if they want to install
    | the basic packages.
    |
    */
    'installation_prompt' => 'Would you like to install the basic packages?',
];
