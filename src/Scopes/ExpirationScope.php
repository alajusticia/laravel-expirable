<?php

namespace ALajusticia\Expirable\Scopes;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ExpirationScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $expirationColumn = $model->getQualifiedExpirationColumn();

        $builder
            ->where(function ($query) use ($expirationColumn) {
                $query->whereNull($expirationColumn)
                      ->orWhere($expirationColumn, '>', Carbon::now());
            });
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        $builder->macro('withExpired', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });

        $builder->macro('onlyExpired', function (Builder $builder) {
            return $builder->withoutGlobalScope($this)
                ->where($builder->getModel()->getQualifiedExpirationColumn(), '<=', Carbon::now());
        });

        $builder->macro('onlyEternal', function (Builder $builder) {
            return $builder->withoutGlobalScope($this)
                ->whereNull($builder->getModel()->getQualifiedExpirationColumn());
        });

        $builder->macro('expiredSince', function (Builder $builder, $period) {
            return $builder->withoutGlobalScope($this)
                ->where($builder->getModel()->getQualifiedExpirationColumn(), '<=', Carbon::now()->sub($period));
        });

        $builder->macro('expire', function (Builder $builder) {
            return $builder->update([
                $builder->getModel()->getExpirationAttribute() => Carbon::now(),
            ]);
        });

        $builder->macro('revive', function (Builder $builder, $newExpirationDate = null) {
            $model = $builder->getModel();

            if (is_string($newExpirationDate)) {
                $newExpirationDate = Carbon::now()->add($newExpirationDate);
            } elseif (is_null($newExpirationDate)) {
                $newExpirationDate = $model::defaultExpiresAt();
            }

            return $builder->onlyExpired()->update([
                $model->getExpirationAttribute() => $newExpirationDate,
            ]);
        });

        $builder->macro('makeEternal', function (Builder $builder) {
            return $builder->update([
                $builder->getModel()->getExpirationAttribute() => null,
            ]);
        });

        $builder->macro('expiresAt', function (Builder $builder, $expirationDate) {
            return $builder->update([
                $builder->getModel()->getExpirationAttribute() => $expirationDate,
            ]);
        });

        $builder->macro('lifetime', function (Builder $builder, $period) {
            return $builder->update([
                $builder->getModel()->getExpirationAttribute() => is_string($period) ? Carbon::now()->add($period) : null,
            ]);
        });
    }
}
