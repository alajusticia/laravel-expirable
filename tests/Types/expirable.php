<?php

namespace ALajusticia\Expirable\Tests\Types;

use ALajusticia\Expirable\Traits\Expirable;
use DateTime;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Schema\Blueprint;

use function PHPStan\Testing\assertType;

class TypedSubscription extends Model
{
    use Expirable;

    /** @param Builder<TypedSubscription> $query */
    public function scopeForKey(Builder $query, string $key): void
    {
        $query->where('key', $key);
    }
}

class InheritedSubscription extends TypedSubscription
{
}

class OrdinaryModel extends Model
{
}

class DateDefaultSubscription extends TypedSubscription
{
    public static function defaultExpiresAt(): ?DateTimeImmutable
    {
        return new DateTimeImmutable('2030-01-02 12:00:00 UTC');
    }
}

/**
 * @template TModel of TypedSubscription
 * @extends Builder<TModel>
 */
class GenericSubscriptionBuilder extends Builder
{
}

/** @extends Builder<TypedSubscription> */
class SubscriptionBuilder extends Builder
{
    public function active(): self
    {
        return $this;
    }
}

function checkTypes(TypedSubscription $subscription): void
{
    assertType('Illuminate\\Database\\Eloquent\\Builder<ALajusticia\\Expirable\\Tests\\Types\\TypedSubscription>', TypedSubscription::withExpired());
    assertType('ALajusticia\\Expirable\\Tests\\Types\\TypedSubscription|null', TypedSubscription::onlyExpired()->first());
    assertType('Illuminate\\Database\\Eloquent\\Builder<ALajusticia\\Expirable\\Tests\\Types\\TypedSubscription>', TypedSubscription::query()->onlyEternal());
    assertType('Illuminate\\Database\\Eloquent\\Builder<ALajusticia\\Expirable\\Tests\\Types\\TypedSubscription>', TypedSubscription::expiredSince('1 day'));
    assertType('int', TypedSubscription::query()->expire());
    assertType('int', TypedSubscription::onlyExpired()->revive('1 day'));
    assertType('Carbon\\Carbon|null', $subscription->getExpirationDate());
    assertType('ALajusticia\\Expirable\\Tests\\Types\\TypedSubscription', $subscription->lifetime('1 day'));
    assertType('int', TypedSubscription::expireByKey('uuid'));
    assertType('int', TypedSubscription::expireByKey(1, 2, 3));
    assertType('int', TypedSubscription::expireByKey([1, 'uuid']));
    assertType('int', TypedSubscription::expireByKey(collect([1, 'uuid'])));
    assertType('int', TypedSubscription::query()->makeEternal());
    assertType('int', TypedSubscription::query()->expiresAt(new DateTime));
    assertType('int', TypedSubscription::query()->expiresAt(new DateTimeImmutable));
    assertType('int', TypedSubscription::query()->expiresAt(null));
    assertType('int', TypedSubscription::query()->lifetime('1 day'));
    assertType('int', TypedSubscription::query()->lifetime(null));
    assertType('int', TypedSubscription::query()->revive());
    assertType('int', TypedSubscription::query()->revive(new DateTimeImmutable));
    assertType('bool', $subscription->expire());
    assertType('bool', $subscription->revive(new DateTimeImmutable));
    assertType('bool', $subscription->makeEternal());
    assertType('ALajusticia\\Expirable\\Tests\\Types\\TypedSubscription', $subscription->expiresAt(new DateTime));
    assertType('ALajusticia\\Expirable\\Tests\\Types\\TypedSubscription', $subscription->expiresAt(new DateTimeImmutable));
    assertType('ALajusticia\\Expirable\\Tests\\Types\\TypedSubscription', $subscription->expiresAt(null));
    assertType('ALajusticia\\Expirable\\Tests\\Types\\TypedSubscription', $subscription->extendLifetimeBy('1 day'));
    assertType('ALajusticia\\Expirable\\Tests\\Types\\TypedSubscription', $subscription->shortenLifetimeBy('1 day'));
    assertType('ALajusticia\\Expirable\\Tests\\Types\\TypedSubscription', $subscription->resetExpiration());
    assertType('Illuminate\\Database\\Eloquent\\Collection<int, ALajusticia\\Expirable\\Tests\\Types\\TypedSubscription>', TypedSubscription::query()->withExpired()->onlyExpired()->expiredSince('1 day')->get());
}

function checkInheritedTypes(InheritedSubscription $subscription): void
{
    assertType('ALajusticia\\Expirable\\Tests\\Types\\InheritedSubscription|null', InheritedSubscription::withExpired()->first());
    assertType('Illuminate\\Database\\Eloquent\\Builder<ALajusticia\\Expirable\\Tests\\Types\\InheritedSubscription>', InheritedSubscription::query()->onlyExpired());
    assertType('ALajusticia\\Expirable\\Tests\\Types\\InheritedSubscription', $subscription->lifetime('1 day'));
}

function checkCustomBuilder(SubscriptionBuilder $builder): void
{
    assertType('ALajusticia\\Expirable\\Tests\\Types\\SubscriptionBuilder', $builder->withExpired()->onlyExpired()->active());
    assertType('ALajusticia\\Expirable\\Tests\\Types\\TypedSubscription|null', $builder->onlyEternal()->first());
    assertType('int', $builder->expiredSince('1 day')->expire());
}

/** @param GenericSubscriptionBuilder<TypedSubscription> $builder */
function checkGenericBuilder(GenericSubscriptionBuilder $builder): void
{
    assertType('ALajusticia\\Expirable\\Tests\\Types\\GenericSubscriptionBuilder<ALajusticia\\Expirable\\Tests\\Types\\TypedSubscription>', $builder->withExpired()->expiredSince('1 day'));
    assertType('ALajusticia\\Expirable\\Tests\\Types\\TypedSubscription|null', $builder->onlyExpired()->first());
}

function checkBlueprint(Blueprint $blueprint): void
{
    assertType('Illuminate\\Database\\Schema\\ColumnDefinition', $blueprint->expirable());
    assertType('Illuminate\\Database\\Schema\\ColumnDefinition', $blueprint->expirable('ends_at'));
    assertType('Illuminate\\Support\\Fluent', $blueprint->dropExpirable());
    assertType('Illuminate\\Support\\Fluent', $blueprint->dropExpirable('ends_at'));
}

/** @param HasMany<TypedSubscription, OrdinaryModel> $subscriptions */
function checkRelationships(HasMany $subscriptions): void
{
    assertType('bool', $subscriptions->forKey('license-key')->onlyEternal()->exists());
    assertType('ALajusticia\\Expirable\\Tests\\Types\\TypedSubscription', $subscriptions->onlyEternal()->getRelated());
    assertType('ALajusticia\\Expirable\\Tests\\Types\\OrdinaryModel', $subscriptions->onlyEternal()->getParent());
    assertType('ALajusticia\\Expirable\\Tests\\Types\\TypedSubscription|null', $subscriptions->withExpired()->first());
    assertType('Illuminate\\Database\\Eloquent\\Collection<int, ALajusticia\\Expirable\\Tests\\Types\\TypedSubscription>', $subscriptions->onlyExpired()->expiredSince('1 day')->get());
    assertType('int', $subscriptions->expire());
    assertType('int', $subscriptions->revive('1 day'));
    assertType('int', $subscriptions->makeEternal());
    assertType('int', $subscriptions->expiresAt(new DateTimeImmutable));
    assertType('int', $subscriptions->lifetime(null));
}
