<?php

namespace App\Models;

class ActivityLog extends CrmModel
{
    protected $table = 'logs';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $hidden = ['id'];
}
