<?php

namespace App\Models;

class BookingRequest extends CrmModel
{
    protected $table = 'requests';

    protected $primaryKey = 'requestId';

    /** `seq` mirrors the old sheet row order, so "newest last" still holds. */
    protected $hidden = ['seq'];
}
