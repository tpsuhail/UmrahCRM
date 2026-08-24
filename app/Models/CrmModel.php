<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Every CRM table is a flat, string-keyed record with an application-generated
 * primary key and no Eloquent timestamps — the sheet-era `createdAt` columns
 * are plain strings the client already knows how to read.
 */
abstract class CrmModel extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $guarded = [];

    /**
     * The client was written against a spreadsheet, where a blank cell reads as
     * "" and every value is text. Rows leave the API in that same shape so the
     * front end never has to guard against nulls or numeric types.
     */
    public function toRow(): array
    {
        $out = [];
        foreach ($this->attributesToArray() as $key => $value) {
            $out[$key] = $value === null ? '' : (string) $value;
        }

        return $out;
    }

    /** @return array<int, array<string, string>> */
    public static function rows(): array
    {
        return static::query()->get()->map(fn (self $m) => $m->toRow())->all();
    }
}
