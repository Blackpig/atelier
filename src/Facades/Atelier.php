<?php

namespace Blackpigcreatif\Atelier\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Blackpigcreatif\Atelier\Atelier
 */
class Atelier extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'atelier';
    }
}
