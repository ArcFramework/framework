<?php

namespace Arc\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $primaryKey = 'ID';

    /**
     * Returns the user matching the given email address or null if none exists
     * @param string $email
     * @return \Arc\Models\User|null
     **/
    public static function findByEmail($email)
    {
        return self::whereUserEmail($email)->first();
    }

    /**
     * Returns the user matching the given username (user_login) or null if none exists
     * @param string $username
     * @return \Arc\Models\User|null
     **/
    public static function findByUsername($username)
    {
        return self::whereUserLogin($username)->first();
    }

    /**
     * Set the role of the user to 'administrator'
     **/
    public function makeAdministrator()
    {
        $this->setRole('administrator');
    }

    /**
     * Set the user's role to the given role
     * @param string $role
     * @return mixed
     **/
    public function setRole($role)
    {
        return wp_update_user([
            'ID' => $this->ID,
            'role' => $role
        ]);
    }

    /**
     * Sets the given usermeta key to the given value if a key value pair is provided
     * or sets the key value pairs in the array if an array is provided as the first argument
     * @param array|string $key
     * @param string|null $value
     **/
    public function setMeta($key, $value = null)
    {
        collect(is_array($key) ? $key : [$key => $value])->each(function ($value, $key) {
            update_user_meta($this->ID, $key, $value);
        });
    }
}

