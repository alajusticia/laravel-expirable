<?php

namespace AnthonyLajusticia\Expirable;

use AnthonyLajusticia\Expirable\Scopes\ExpirationScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ExpirableEloquentQueryBuilder extends Builder
{
    /**
     * Scope a query to include expired models.
     *
     * @return self
     */
    public function withExpired()
    {
        return $this->withoutGlobalScope(ExpirationScope::class);
    }

    /**
     * Scope a query to include only expired models.
     *
     * @return self
     */
    public function onlyExpired()
    {
        return $this->withoutGlobalScope(ExpirationScope::class)
            ->where($this->model->getExpirationAttribute(), '<=', Carbon::now());
    }

    /**
     * Scope a query to include only eternal models.
     *
     * @return self
     */
    public function onlyEternal()
    {
        return $this->withoutGlobalScope(ExpirationScope::class)
            ->whereNull($this->model->getExpirationAttribute());
    }

    /**
     * Scope a query to include only expired models since a given period of time.
     *
     * @param string $period
     * @return self
     */
    public function expiredSince($period)
    {
        return $this->withoutGlobalScope(ExpirationScope::class)
            ->where($this->model->getExpirationAttribute(), '<=', Carbon::now()->sub($period));
    }

    /**
     * Make the models expired.
     *
     * @return int
     */
    public function expire()
    {
        return $this->update([
            $this->model->getExpirationAttribute() => Carbon::now()
        ]);
    }

    /**
     * Revive the models.
     *
     * @param object|string|null $newExpirationDate
     * @return int
     */
    public function revive($newExpirationDate = null)
    {
        if (is_string($newExpirationDate)) {
            $newExpirationDate = Carbon::now()->add($newExpirationDate);
        } elseif (is_null($newExpirationDate)) {
            $newExpirationDate = $this->model::defaultExpiresAt();
        }

        return $this->onlyExpired()->update([
            $this->model->getExpirationAttribute() => $newExpirationDate
        ]);
    }

    /**
     * Make the models eternal (set expiration date to null).
     *
     * @return int
     */
    public function makeEternal()
    {
        return $this->update([
            $this->model->getExpirationAttribute() => null
        ]);
    }

    /**
     * Update the expiration date.
     *
     * @param object|null $expirationDate
     * @return int
     */
    public function expiresAt($expirationDate)
    {
        return $this->update([
            $this->model->getExpirationAttribute() => $expirationDate
        ]);
    }

    /**
     * Set the lifetime in a more human readable way.
     *
     * @param string|null $period
     * @return int
     */
    public function lifetime($period)
    {
        return $this->update([
            $this->model->getExpirationAttribute() => is_string($period) ? Carbon::now()->add($period) : null
        ]);
    }
}
