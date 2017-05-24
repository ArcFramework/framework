<?php

namespace Arc\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $primaryKey = 'ID';

    /**
     * Returns the user matching the given email address or null if none exists.
     *
     * @param string $email
     *
     * @return \Arc\Models\User|null
     **/
    public static function findByEmail($email)
    {
        return self::whereUserEmail($email)->first();
    }

    /**
     * Returns the user matching the given username (user_login) or null if none exists.
     *
     * @param string $username
     *
     * @return \Arc\Models\User|null
     **/
    public static function findByUsername($username)
    {
        return self::whereUserLogin($username)->first();
    }

    /**
     * Set the role of the user to 'administrator'.
     **/
    public function makeAdministrator()
    {
        $this->setRole('administrator');
    }

    /**
     * Set the user's role to the given role.
     *
     * @param string $role
     *
     * @return mixed
     **/
    public function setRole($role)
    {
        return wp_update_user([
            'ID'   => $this->ID,
            'role' => $role,
        ]);
    }

    public function addMeta($key, $value, $unique = false)
    {
        return add_user_meta($this->ID, $key, $value, $unique);
    }

    /**
     * Sets the given usermeta key to the given value if a key value pair is provided
     * or sets the key value pairs in the array if an array is provided as the first argument.
     *
     * @param array|string $key
     * @param string|null  $value
     **/
    public function setMeta($key, $value = null)
    {
        collect(is_array($key) ? $key : [$key => $value])->each(function ($value, $key) {
            update_user_meta($this->ID, $key, $value);
        });
    }

    /**
     * Returns the usermeta value matching the given key. To return multiple values if they
     * are avaiable pass false as the second paramater.
     *
     * @param string $key
     * @param bool   $single = true
     *
     * @return mixed
     **/
    public function findMeta($key, $single = true)
    {
        return get_user_meta($this->ID, $key, true);
    }

    /**
     * Returns the PostMeta rows matching the given key in a Collection.
     *
     * @param string $key The meta_key
     *
     * @return Illuminate\Support\Collection
     **/
    public function getMeta($key)
    {
        return $this->userMeta()
            ->where('meta_key', $key)
            ->get();
    }

    /**
     * Deletes the all the usermeta for the user matching the given key or key and value
     * if a value is provided.
     *
     * @param string $key
     * @param mixed  $value (optional)
     **/
    public function deleteMeta($key, $value = null)
    {
        // dump($this->ID, $key, $value);
        return delete_user_meta($this->ID, $key, $value);
    }

    /**
     * A User has many UserMeta.
     **/
    public function userMeta()
    {
        return $this->hasMany(UserMeta::class, 'user_id', 'ID');
    }

    /**
     * Returns true if the user has a usermeta record matching the given key
     * and value if provided.
     *
     * @param string $key
     * @param mixed  $value (optional)
     *
     * @return bool
     **/
    public function hasMeta($key, $value = null)
    {
        $query = $this->userMeta()
            ->where('meta_key', $key);

        if ($value) {
            $query = $query->where('meta_value', $value);
        }

        return !is_null($query->first());
    }

    public function addUniqueMeta($key, $value)
    {
        if (!is_null(UserMeta::where('user_id', $this->ID)
            ->where('meta_key', $key)
            ->where('meta_value', $value)
            ->first())) {
            return;
        }

        return $this->addMeta($key, $value);
    }
}
