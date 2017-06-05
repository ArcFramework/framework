<?php

namespace Arc\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $table = 'options';

    protected $primaryKey = 'option_id';
}

