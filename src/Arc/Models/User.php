<?php

namespace Arc\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    /**
     * Returns the user matching the given email address or null if none exists
     * @param string $email
     * @return \Arc\Models\User|null
     **/
    public static function findByEmail($email)
    {
        return self::whereUserEmail($email)->first();
    }
}

