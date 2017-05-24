<?php

namespace Arc\Models;

use Illuminate\Database\Eloquent\Model;

class UserMeta extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $table = 'usermeta';
}
