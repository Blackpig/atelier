<?php

namespace BlackpigCreatif\Atelier\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \BlackpigCreatif\Atelier\Atelier
 */
class Atelier extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'atelier';
    }
}
