<?php

namespace ALajusticia\Expirable\Tests;

use ALajusticia\Expirable\Traits\Expirable;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use Expirable;

    public $timestamps = false;
}
