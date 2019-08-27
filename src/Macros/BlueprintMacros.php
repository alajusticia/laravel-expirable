<?php

namespace AnthonyLajusticia\Expirable\Macros;

class BlueprintMacros
{
    /**
     * Add the required timestamp field for the expiration date.
     *
     * @return \Closure
     */
    public function expirable()
    {
        return function ($columnName = 'expires_at') {
            return $this->timestamp($columnName)->nullable();
        };
    }
}
