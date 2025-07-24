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
        'ingenius/auth' => '^0.0.1',
        'ingenius/coins' => '^0.0.1',
        'ingenius/orders' => '^0.0.1',
        'ingenius/payforms' => '^0.0.1',
        'ingenius/products' => '^0.0.1',
        'ingenius/shopcart' => '^0.0.1',
        'ingenius/storefront' => '^0.0.1',
        'ingenius/shipment' => '^0.0.1'
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
