<?php

namespace ALajusticia\Expirable\Tests;

use ALajusticia\Expirable\Traits\Expirable;
use Carbon\Carbon;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Fluent;
use PHPUnit\Framework\Attributes\DataProvider;

class TypingCompatibilityTest extends TestCase
{
    public function test_expiration_date_is_nullable_carbon_and_setters_are_fluent(): void
    {
        $this->travelTo(Carbon::parse('2030-01-01 12:00:00', 'UTC'));
        $subscription = new Subscription;

        $this->assertNull($subscription->getExpirationDate());

        foreach ([new DateTime('2030-01-02 12:00:00 UTC'), new DateTimeImmutable('2030-01-02 12:00:00 UTC')] as $date) {
            $this->assertSame($subscription, $subscription->expiresAt($date));
            $this->assertInstanceOf(Carbon::class, $subscription->getExpirationDate());
            $this->assertSame($date->getTimestamp(), $subscription->getExpirationDate()->getTimestamp());
        }

        $this->assertSame($subscription, $subscription->lifetime('1 day'));
        $this->assertSame($subscription, $subscription->extendLifetimeBy('1 day'));
        $this->assertSame($subscription, $subscription->shortenLifetimeBy('1 day'));
        $this->assertSame($subscription, $subscription->resetExpiration());
        $this->assertNull($subscription->getExpirationDate());
        $this->assertSame($subscription, $subscription->expiresAt(null));
    }

    public function test_date_objects_work_with_bulk_updates_and_revival(): void
    {
        $this->travelTo(Carbon::parse('2030-01-01 12:00:00', 'UTC'));
        $subscription = Subscription::create();
        $date = new DateTimeImmutable('2030-01-02 12:00:00 UTC');

        $this->assertSame(1, Subscription::query()->expiresAt($date));
        $this->assertSame($date->getTimestamp(), $subscription->refresh()->getExpirationDate()->getTimestamp());
        $this->assertSame(1, Subscription::query()->expire());
        $this->assertTrue($subscription->refresh()->revive($date));
        $this->assertSame(1, Subscription::query()->expire());
        $this->assertSame(1, Subscription::onlyExpired()->revive($date));
        $this->assertSame(1, Subscription::query()->lifetime(null));
        $this->assertNull($subscription->refresh()->getExpirationDate());
    }

    public function test_datetime_defaults_remain_overridable(): void
    {
        $subscription = DefaultSubscription::create();

        $this->assertSame('2030-01-02 12:00:00', $subscription->getExpirationDate()->toDateTimeString());
        $subscription->expiresAt(null)->resetExpiration();
        $this->assertSame('2030-01-02 12:00:00', $subscription->getExpirationDate()->toDateTimeString());
    }

    #[DataProvider('keyFormats')]
    public function test_expire_by_key_accepts_supported_key_formats(string $format): void
    {
        $first = Subscription::create();
        $second = Subscription::create();
        $keys = [$first->getKey(), $second->getKey()];

        $count = match ($format) {
            'integer' => Subscription::expireByKey($keys[0]),
            'string' => Subscription::expireByKey((string) $keys[0]),
            'variadic' => Subscription::expireByKey(...$keys),
            'array' => Subscription::expireByKey($keys),
            'collection' => Subscription::expireByKey(collect($keys)),
        };

        $expected = in_array($format, ['integer', 'string'], true) ? 1 : 2;
        $this->assertSame($expected, $count);
        $this->assertCount($expected, Subscription::onlyExpired()->get());
    }

    public static function keyFormats(): array
    {
        return array_map(fn ($format) => [$format], ['integer', 'string', 'variadic', 'array', 'collection']);
    }

    public function test_expire_by_key_accepts_uuid_primary_keys(): void
    {
        Schema::create('string_key_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->expirable();
        });

        try {
            $first = new StringKeySubscription;
            $first->id = '11111111-1111-4111-8111-111111111111';
            $first->save();
            $second = new StringKeySubscription;
            $second->id = '22222222-2222-4222-8222-222222222222';
            $second->save();

            $this->assertSame(2, StringKeySubscription::expireByKey($first->id, $second->id));
            $this->assertCount(2, StringKeySubscription::onlyExpired()->get());
        } finally {
            Schema::dropIfExists('string_key_subscriptions');
        }
    }

    public function test_schema_macros_return_definitions_and_commands(): void
    {
        Schema::create('expiration_macro_test', function (Blueprint $table) {
            $table->id();
            $this->assertInstanceOf(ColumnDefinition::class, $table->expirable());
            $this->assertInstanceOf(ColumnDefinition::class, $table->expirable('ends_at'));
        });

        try {
            $this->assertTrue(Schema::hasColumn('expiration_macro_test', 'expires_at'));
            $this->assertTrue(Schema::hasColumn('expiration_macro_test', 'ends_at'));

            Schema::table('expiration_macro_test', function (Blueprint $table) {
                $this->assertInstanceOf(Fluent::class, $table->dropExpirable());
                $this->assertInstanceOf(Fluent::class, $table->dropExpirable('ends_at'));
            });

            $this->assertFalse(Schema::hasColumn('expiration_macro_test', 'expires_at'));
            $this->assertFalse(Schema::hasColumn('expiration_macro_test', 'ends_at'));
        } finally {
            Schema::dropIfExists('expiration_macro_test');
        }
    }
}

class DefaultSubscription extends Model
{
    use Expirable;

    protected $table = 'subscriptions';

    public $timestamps = false;

    public static function defaultExpiresAt(): ?DateTimeInterface
    {
        return new DateTimeImmutable('2030-01-02 12:00:00 UTC');
    }
}

class StringKeySubscription extends Subscription
{
    protected $table = 'string_key_subscriptions';

    protected $keyType = 'string';

    public $incrementing = false;
}
