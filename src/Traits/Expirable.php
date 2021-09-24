<?php

namespace ALajusticia\Expirable\Traits;

use ALajusticia\Expirable\Scopes\ExpirationScope;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

/**
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder withExpired()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder onlyExpired()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder onlyEternal()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder expiredSince()
 */
trait Expirable
{
    /**
     * Boot the Expirable trait for a model.
     *
     * @return void
     */
    public static function bootExpirable()
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
    public function initializeExpirable()
    {
        $this->dates[] = self::getExpirationAttribute();
    }

    /**
     * Get the expiration date.
     *
     * @return object
     */
    public function getExpirationDate()
    {
        return $this->{self::getExpirationAttribute()};
    }

    /**
     * Set the expiration date and return the instance.
     *
     * @param object|null $expirationDate
     * @return self
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
     * @return self
     */
    public function lifetime(?string $period): self
    {
        $this->{self::getExpirationAttribute()} = is_string($period) ? Carbon::now()->add($period) : null;

        return $this;
    }

    /**
     * Revive an expired model.
     *
     * @param object|string|null $newExpirationDate
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
     * @return self
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
     * @return self
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
     * @return self
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
     * @param  Collection|array|int  $ids
     * @return int
     */
    public static function expireByKey($ids): int
    {
        // Support for collections
        if ($ids instanceof Collection) {
            $ids = $ids->all();
        }

        // Convert parameters into an array if needed
        $ids = is_array($ids) ? $ids : func_get_args();

        // Create a new static instance and get the primary key for the model
        $key = ($instance = new static)->getKeyName();

        // Perform the query
        return $instance->whereIn($key, $ids)->expire();
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
     * @return object|null
     */
    public static function defaultExpiresAt(): ?object
    {
        return null;
    }
}
