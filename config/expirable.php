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
    | Mode
    |--------------------------------------------------------------------------
    |
    | Whether the expirable:purge command deletion defaults to hard or soft.
    | Defaults to hard for backward compatibility.
    |
    */
    'mode' => 'hard',

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
