<?php

namespace Shahrukh\Instagram\Facades;

use Illuminate\Support\Facades\Facade;

class Instagram extends Facade {
    /**
     * Get the binding in the IoC container
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return '\Shahrukh\Instagram\Instagram';
    }
}