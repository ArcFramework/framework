<?php

namespace Arc\Models;

use Illuminate\Database\Eloquent\Model;

class PostMeta extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $table = 'postmeta';

    protected $primaryKey = 'meta_id';
}
