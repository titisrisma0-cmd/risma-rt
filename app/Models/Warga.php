<?php

namespace App\Models;

use App\Models\Scopes\RtWargaScope;

class Warga extends User
{
    protected $table = 'users';

    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope(new RtWargaScope);
    }
}
