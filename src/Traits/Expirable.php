<?php

namespace AnthonyLajusticia\Expirable\Traits;

use AnthonyLajusticia\Expirable\ExpirableEloquentQueryBuilder;
use AnthonyLajusticia\Expirable\Scopes\ExpirationScope;
use Carbon\Carbon;
use Illuminate\Support\Collection as BaseCollection;

trait Expirable
{
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ExpirationScope);

        static::creating(function($model) {
            // Set the default expiration date if needed
            if (! array_key_exists($model::getExpirationAttribute(), $model->attributes)) {
                $model->attributes[$model::getExpirationAttribute()] = $model::defaultExpiresAt();
            }
        });
    }

    /**
     * Set the expiration date and return the instance.
     *
     * @param object|null $expirationDate
     * @return self
     */
    public function expiresAt($expirationDate)
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
    public function lifetime($period)
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
    public function revive($newExpirationDate = null)
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
     * Make a model eternal (set expiration date to null).
     *
     * @return bool
     */
    public function makeEternal()
    {
        $this->{self::getExpirationAttribute()} = null;

        return $this->save();
    }

    /**
     * Set the status to "expired" at the current timestamp.
     *
     * @return bool
     */
    public function expire()
    {
        $this->{self::getExpirationAttribute()} = Carbon::now();

        return $this->save();
    }

    /**
     * Set the status to "expired" for the given model IDs.
     *
     * @param  \Illuminate\Support\Collection|array|int  $ids
     * @return int
     */
    public static function expireByKey($ids)
    {
        // Support for collections
        if ($ids instanceof BaseCollection) {
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
    public function isExpired()
    {
        return !is_null($this->{self::getExpirationAttribute()}) && $this->{self::getExpirationAttribute()} <= Carbon::now();
    }

    /**
     * Check if the model is eternal.
     *
     * @return bool
     */
    public function isEternal()
    {
        return is_null($this->{self::getExpirationAttribute()});
    }

    /**
     * Get the real name of the "expires_at" column.
     *
     * @return string
     */
    public static function getExpirationAttribute()
    {
        return defined('static::EXPIRES_AT') ? static::EXPIRES_AT : config('expirable.attribute_name', 'expires_at');
    }

    /**
     * The default expiration date
     *
     * @return object|null
     */
    public static function defaultExpiresAt()
    {
        return null;
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new ExpirableEloquentQueryBuilder($query);
    }
}
