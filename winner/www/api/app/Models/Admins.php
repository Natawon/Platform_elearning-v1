<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class Admins extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'admins';
    protected $primaryKey = 'id';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array('admins_groups_id','username', 'password', 'first_name', 'last_name', 'email', 'mobile', 'phone', 'address', 'avatar', 'groups_id', 'sub_groups_id', 'super_users', 'limit_groups', 'upload_status', 'active', 'incorrect_password', 'status');
    protected $guarded = array('id', 'my_session_id', 'last_login', 'remember_token', 'last_changed_password', 'active_remark', 'suspended_datetime', 'create_datetime', 'create_by', 'modify_datetime', 'modify_by');
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = array('remember_token');

    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getReminderEmail()
    {
        return "";
    }

    public function setRememberToken($token) {
        $this->remember_token = $token;
    }

    public function getRememberToken() {
        return $this->remember_token;
    }

    public function getRememberTokenName() {
        return 'remember_token';
    }

    public function admins_groups() {
        return $this->hasOne('\App\Models\AdminsGroups', 'id', 'admins_groups_id');
    }

    public function groups() {
        return $this->hasOne('\App\Models\Groups', 'id', 'groups_id');
    }

    public function sub_groups() {
        return $this->hasOne('\App\Models\SubGroups', 'id', 'sub_groups_id');
    }

    public function admin2level_group() {
        return $this->belongsToMany('\App\Models\LevelGroups', 'admin2level_group', 'admins_id', 'level_groups_id');
    }

    public function level_groups() {
        return $this->hasMany('\App\Models\LevelGroups', 'admins_id');
    }
}
