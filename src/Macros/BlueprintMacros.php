<?php

namespace ALajusticia\Expirable\Macros;

use Illuminate\Support\Facades\Config;

class BlueprintMacros
{
    /**
     * Add the required timestamp column for the expiration date.
     *
     * @return \Closure
     */
    public function expirable()
    {
        return function ($columnName = null) {
            return $this->timestamp($columnName ?: Config::get('expirable.attribute_name', 'expires_at'))->nullable();
        };
    }

    /**
     * Drop the timestamp column added for the expiration date.
     *
     * @return \Closure
     */
    public function dropExpirable()
    {
        return function ($columnName = null) {
            return $this->dropColumn($columnName ?: Config::get('expirable.attribute_name', 'expires_at'));
        };
    }
}
