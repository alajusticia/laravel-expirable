<?php

namespace ALajusticia\Expirable\Tests;

use ALajusticia\Expirable\Tests\Database\Factories\SubscriptionFactory;
use ALajusticia\Expirable\Traits\Expirable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use Expirable;
    use HasFactory;

    public $timestamps = false;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return SubscriptionFactory::new();
    }
}
