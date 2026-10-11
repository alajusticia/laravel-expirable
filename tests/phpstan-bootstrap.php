<?php

use ALajusticia\Expirable\ExpirableServiceProvider;
use Illuminate\Support\Facades\Config;

app()->register(ExpirableServiceProvider::class);
Config::set('expirable.attribute_name', 'expires_at');
