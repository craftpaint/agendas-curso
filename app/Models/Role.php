<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    //incluir el nuevo campo en fillable
    protected $fillable = ['name', 'guard_name', 'icon'];
}
