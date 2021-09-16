<?php

namespace ALajusticia\Expirable\Tests;

class ExpirableTest extends TestCase
{
    public function test_single_model()
    {
        // Test expirable model creation
        $subscription = Subscription::create();
        $this->assertNotEmpty(Subscription::all());

        // Test lifetime() method
        $subscription->lifetime('2 seconds')->save();
        $this->assertNotEmpty(Subscription::all());
        sleep(2);
        $this->assertEmpty(Subscription::all());

        // Test revive() method
        $this->assertTrue($subscription->revive());
        $this->assertNotEmpty(Subscription::all());

        // Test expiresAt() method
        $subscription->expiresAt(now()->addSeconds(2))->save();
        $this->assertNotEmpty(Subscription::all());
        sleep(2);
        $this->assertEmpty(Subscription::all());

        // Test makeEternal() method
        $subscription->makeEternal();
        $this->assertNotEmpty(Subscription::all());

        // Test expire() method
        $subscription->expire();
        $this->assertEmpty(Subscription::all());

        // Test isExpired() and isEternal() methods
        $this->assertTrue($subscription->isExpired());
        $this->assertFalse($subscription->isEternal());

        // Test revive() method passing a date
        $subscription->revive(now()->addSeconds(2));
        $this->assertNotEmpty(Subscription::all());
        sleep(2);
        $this->assertEmpty(Subscription::all());

        // Test extendLifetimeBy() method
        $subscription->revive(now()->addDay());
        $subscription->extendLifetimeBy('1 day');
        $this->assertTrue(now()->addDays(2)->isSameDay($subscription->getExpirationDate()));

        // Test shortenLifetimeBy() method
        $subscription->revive(now()->addDays(2));
        $subscription->shortenLifetimeBy('1 day');
        $this->assertTrue(now()->addDay()->isSameDay($subscription->getExpirationDate()));

        // Test resetExpiration() method
        $subscription->resetExpiration();
        $this->assertTrue($subscription->isEternal());
    }

    public function test_multiple_models()
    {
        $subscription1 = Subscription::create();
        $subscription2 = Subscription::create();
        $subscription3 = Subscription::create();

        // Retrieve valid models
        $this->assertCount(3, Subscription::all());

        $subscription1->expire();
        $subscription2->lifetime('1 day')->save();

        // Retrieve valid models
        $this->assertCount(2, Subscription::all());

        // Test withExpired scope
        $this->assertCount(3, Subscription::withExpired()->get());

        // Test onlyExpired scope
        $this->assertCount(1, Subscription::onlyExpired()->get());

        // Test onlyEternal scope
        $this->assertCount(1, Subscription::onlyEternal()->get());

        // Test expiredSince scope
        $this->assertEmpty(Subscription::expiredSince('1 year')->get());
        $subscription4 = Subscription::create();
        $subscription4->expiresAt(now()->subDay())->save();
        $this->assertCount(1, Subscription::expiredSince('1 day')->get());
    }

    public function test_grouped_actions()
    {
        factory(Subscription::class, 10)->create();

        // Test expireByKey() method ------------------------------------------

        Subscription::expireByKey(1);
        $this->assertCount(9, Subscription::all());

        Subscription::expireByKey(2, 3, 4);
        $this->assertCount(6, Subscription::all());

        Subscription::expireByKey([5, 6, 7]);
        $this->assertCount(3, Subscription::all());

        Subscription::expireByKey(collect([8, 9, 10]));
        $this->assertEmpty(Subscription::all());

        // Test revive() method -----------------------------------------------

        Subscription::onlyExpired()->take(10)->revive();
        $this->assertCount(10, Subscription::all());

        // Test expire() method -----------------------------------------------

        Subscription::take(5)->expire();
        $this->assertCount(5, Subscription::all());

        // Test makeEternal() method ------------------------------------------

        Subscription::withExpired()->take(10)->makeEternal();
        $this->assertCount(10, Subscription::all());

        // Test expiresAt() method ------------------------------------------

        Subscription::whereKey([1, 2, 3])->expiresAt(now());
        $this->assertCount(7, Subscription::all());
    }
}
