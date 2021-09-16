<?php

namespace ALajusticia\Expirable\Macros;

use Illuminate\Container\Container;

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
            return $this->timestamp($columnName ?: $this->getDefaultColumnName())->nullable();
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
            return $this->dropColumn($columnName ?: $this->getDefaultColumnName());
        };
    }

    protected function getDefaultColumnName()
    {
        return Container::getInstance()->make('config', [])->get('expirable.attribute_name', 'expires_at');
    }
}
