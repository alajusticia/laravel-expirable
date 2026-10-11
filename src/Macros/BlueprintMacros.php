<?php

namespace ALajusticia\Expirable\Macros;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Fluent;
use Illuminate\Support\Facades\Config;

/**
 * @mixin Blueprint
 */
class BlueprintMacros
{
    /**
     * Add the required timestamp column for the expiration date.
     *
     * @return Closure(?string=): ColumnDefinition
     */
    public function expirable(): Closure
    {
        return function (?string $columnName = null): ColumnDefinition {
            return $this->timestamp($columnName ?: Config::get('expirable.attribute_name', 'expires_at'))->nullable();
        };
    }

    /**
     * Drop the timestamp column added for the expiration date.
     *
     * @return Closure(?string=): Fluent<string, mixed>
     */
    public function dropExpirable(): Closure
    {
        return function (?string $columnName = null): Fluent {
            return $this->dropColumn($columnName ?: Config::get('expirable.attribute_name', 'expires_at'));
        };
    }
}
