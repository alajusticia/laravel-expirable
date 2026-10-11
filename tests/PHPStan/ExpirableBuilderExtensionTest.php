<?php

namespace ALajusticia\Expirable\Tests\PHPStan;

use ALajusticia\Expirable\PHPStan\ExpirableBuilderExtension;
use ALajusticia\Expirable\Tests\Types\InheritedSubscription;
use ALajusticia\Expirable\Tests\Types\OrdinaryModel;
use ALajusticia\Expirable\Tests\Types\SubscriptionBuilder;
use ALajusticia\Expirable\Tests\Types\TypedSubscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use PHPStan\Testing\PHPStanTestCase;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;

class ExpirableBuilderExtensionTest extends PHPStanTestCase
{
    public static function getAdditionalConfigFiles(): array
    {
        return [dirname(__DIR__, 2).'/extension.neon'];
    }

    protected function setUp(): void
    {
        require_once __DIR__.'/../Types/expirable.php';

        parent::setUp();
    }

    public function test_macros_are_limited_to_expirable_eloquent_builders(): void
    {
        $extension = new ExpirableBuilderExtension;
        $provider = self::createReflectionProvider();

        $expirable = (new GenericObjectType(Builder::class, [new ObjectType(TypedSubscription::class)]))->getClassReflection();
        $inherited = (new GenericObjectType(Builder::class, [new ObjectType(InheritedSubscription::class)]))->getClassReflection();
        $ordinary = (new GenericObjectType(Builder::class, [new ObjectType(OrdinaryModel::class)]))->getClassReflection();
        $union = (new GenericObjectType(Builder::class, [TypeCombinator::union(
            new ObjectType(TypedSubscription::class),
            new ObjectType(OrdinaryModel::class),
        )]))->getClassReflection();

        foreach (['withExpired', 'onlyExpired', 'onlyEternal', 'expiredSince', 'expire', 'revive', 'makeEternal', 'expiresAt', 'lifetime'] as $method) {
            $this->assertTrue($extension->hasMethod($expirable, $method));
            $this->assertTrue($extension->hasMethod($inherited, $method));
            $this->assertTrue($extension->hasMethod($provider->getClass(SubscriptionBuilder::class), $method));
            $this->assertFalse($extension->hasMethod($ordinary, $method));
            $this->assertFalse($extension->hasMethod($union, $method));
            $this->assertFalse($extension->hasMethod($provider->getClass(Builder::class), $method));
            $this->assertFalse($extension->hasMethod($provider->getClass(QueryBuilder::class), $method));
            $this->assertFalse($extension->hasMethod($provider->getClass(TypedSubscription::class), $method));
        }

        $this->assertFalse($extension->hasMethod($expirable, 'unknownMacro'));
    }
}
