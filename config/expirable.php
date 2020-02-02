<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Attribute Name
    |--------------------------------------------------------------------------
    |
    | Use this option to customize the default attribute name for the
    | expiration date. This name will be used if the EXPIRES_AT constant is
    | not set on your eloquent model.
    |
    */

    'attribute_name' => 'expires_at',


    /*
    |--------------------------------------------------------------------------
    | Purge
    |--------------------------------------------------------------------------
    |
    | Models that should be purged by the expirable:purge command.
    |
    | (see the README file for an example)
    |
    */

    'purge' => [],

];
