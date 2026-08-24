<?php

namespace App\Support;

use Illuminate\Support\Str;

class Ids
{
    /**
     * A sortable, opaque id in the original "P-yymmddHHmmss-xxxxxx" shape.
     *
     * The suffix is wide enough that a burst of records created inside the
     * same second — a seeded master list, a wizard saving twenty trips —
     * cannot collide on the primary key.
     */
    public static function make(string $prefix): string
    {
        return $prefix.'-'.Dates::now()->format('ymdHis').'-'.Str::lower(Str::random(6));
    }
}
