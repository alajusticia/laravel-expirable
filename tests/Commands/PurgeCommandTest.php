<?php

namespace ALajusticia\Expirable\Tests\Commands;

use ALajusticia\Expirable\Tests\Subscription;
use ALajusticia\Expirable\Tests\TestCase;

class PurgeCommandTest extends TestCase
{
    public function test_purge_command()
    {
        factory(Subscription::class, 10)->create();

        Subscription::take(5)->expire();

        $this->assertCount(10, Subscription::withExpired()->get());

        $this->artisan('expirable:purge')->run();

        $this->assertCount(5, Subscription::withExpired()->get());
    }
}
