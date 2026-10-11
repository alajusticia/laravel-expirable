<?php

namespace ALajusticia\Expirable\Traits;

use ALajusticia\Expirable\Scopes\ExpirationScope;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

/**
 * @phpstan-require-extends Model
 *
 * @method static Builder<static> withExpired()
 * @method static Builder<static> onlyExpired()
 * @method static Builder<static> onlyEternal()
 * @method static Builder<static> expiredSince(string $period)
 */
trait Expirable
{
    /**
     * Boot the Expirable trait for a model.
     *
     * @return void
     */
    public static function bootExpirable(): void
    {
        static::addGlobalScope(new ExpirationScope);

        static::creating(function($model) {
            // Set the default expiration date if needed
            if (! array_key_exists($model::getExpirationAttribute(), $model->attributes)) {
                $model->attributes[$model::getExpirationAttribute()] = $model::defaultExpiresAt();
            }
        });
    }

    /**
     * Initialize the Expirable trait for an instance.
     *
     * @return void
     */
    public function initializeExpirable(): void
    {
        $this->casts[self::getExpirationAttribute()] = 'datetime';
    }

    /**
     * Get the expiration date.
     *
     * @return Carbon|null
     */
    public function getExpirationDate(): ?Carbon
    {
        return $this->{self::getExpirationAttribute()};
    }

    /**
     * Set the expiration date and return the instance.
     *
     * @param DateTimeInterface|null $expirationDate
     * @return $this
     */
    public function expiresAt(?object $expirationDate): self
    {
        $this->{self::getExpirationAttribute()} = $expirationDate;

        return $this;
    }

    /**
     * Set the lifetime in a more human readable way and return the instance.
     *
     * @param string|null $period
     * @return $this
     */
    public function lifetime(?string $period): self
    {
        $this->{self::getExpirationAttribute()} = is_string($period) ? Carbon::now()->add($period) : null;

        return $this;
    }

    /**
     * Revive an expired model.
     *
     * @param DateTimeInterface|string|null $newExpirationDate
     * @return bool
     */
    public function revive($newExpirationDate = null): bool
    {
        if ($this->isExpired()) {

            if (is_string($newExpirationDate)) {
                $newExpirationDate = Carbon::now()->add($newExpirationDate);
            } elseif (is_null($newExpirationDate)) {
                $newExpirationDate = self::defaultExpiresAt();
            }

            $this->{self::getExpirationAttribute()} = $newExpirationDate;

            return $this->save();
        }

        return false;
    }

    /**
     * Extend the lifetime by a human readable period.
     *
     * @param string $period
     * @return $this
     */
    public function extendLifetimeBy(string $period): self
    {
        $currentExpirationDate = $this->{self::getExpirationAttribute()};

        $this->{self::getExpirationAttribute()} = $currentExpirationDate->add($period);

        return $this;
    }

    /**
     * Shorten the lifetime by a human readable period.
     *
     * @param string $period
     * @return $this
     */
    public function shortenLifetimeBy(string $period): self
    {
        $currentExpirationDate = $this->{self::getExpirationAttribute()};

        $this->{self::getExpirationAttribute()} = $currentExpirationDate->sub($period);

        return $this;
    }

    /**
     * Reset the expiration date to the default value.
     *
     * @return $this
     */
    public function resetExpiration(): self
    {
        $this->{self::getExpirationAttribute()} = self::defaultExpiresAt();

        return $this;
    }

    /**
     * Make a model eternal (set expiration date to null).
     *
     * @return bool
     */
    public function makeEternal(): bool
    {
        $this->{self::getExpirationAttribute()} = null;

        return $this->save();
    }

    /**
     * Set the status to "expired" at the current timestamp.
     *
     * @return bool
     */
    public function expire(): bool
    {
        $this->{self::getExpirationAttribute()} = Carbon::now();

        return $this->save();
    }

    /**
     * Set the status to "expired" for the given model IDs.
     *
     * @param Collection<array-key, int|string>|array<array-key, int|string>|int|string $ids
     * @param int|string ...$additionalIds
     * @return int
     */
    public static function expireByKey($ids, ...$additionalIds): int
    {
        // Support for collections
        if ($ids instanceof Collection) {
            $ids = $ids->all();
        }

        // Convert parameters into an array if needed
        $ids = is_array($ids) ? $ids : [$ids, ...$additionalIds];

        $query = static::query();
        $key = $query->getModel()->getKeyName();

        // Perform the query
        return $query->whereIn($key, $ids)->expire();
    }

    /**
     * Check for expired model.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return !is_null($this->{self::getExpirationAttribute()}) && $this->{self::getExpirationAttribute()} <= Carbon::now();
    }

    /**
     * Check if the model is eternal.
     *
     * @return bool
     */
    public function isEternal(): bool
    {
        return is_null($this->{self::getExpirationAttribute()});
    }

    /**
     * Get the name of the "expires at" column.
     *
     * @return string
     */
    public static function getExpirationAttribute(): string
    {
        return defined('static::EXPIRES_AT') ? static::EXPIRES_AT : Config::get('expirable.attribute_name', 'expires_at');
    }

    /**
     * Get the fully qualified "expires at" column.
     *
     * @return string
     */
    public function getQualifiedExpirationColumn(): string
    {
        return $this->qualifyColumn($this->getExpirationAttribute());
    }

    /**
     * The default expiration date
     *
     * @return DateTimeInterface|null
     */
    public static function defaultExpiresAt(): ?object
    {
        return null;
    }
}
