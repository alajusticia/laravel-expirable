<?php

namespace ALajusticia\Expirable\Tests;

use ALajusticia\Expirable\ExpirableServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $this->artisan('migrate')->run();

        $this->withFactories(__DIR__.'/database/factories');
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ExpirableServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Testing purge command
        // Setup Subscription model to be purged
        $app['config']->set('expirable.purge', [
            Subscription::class,
        ]);
    }
}
