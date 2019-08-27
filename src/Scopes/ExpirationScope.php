<?php

namespace AnthonyLajusticia\Expirable\Scopes;

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
        $expirationColumn = $model->getExpirationAttribute();

        $builder
            ->where(function ($query) use ($expirationColumn) {
                $query->whereNull($expirationColumn)
                      ->orWhere($expirationColumn, '>', Carbon::now());
            });
    }
}
