<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminsGroups extends Model
{
	use \Rutorika\Sortable\SortableTrait;
	protected static $sortableField = 'order';

    protected $table = 'admins_groups';
    protected $primaryKey = 'id';

    protected $fillable = array('title', 'external', 'max_account_age', 'max_password_age', 'max_password_history', 'incorrect_password', 'order', 'status');
    protected $guarded = array('id', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function admins_menu() {
        return $this->belongsToMany('\App\Models\AdminsMenu', 'admins_menu2admins_groups', 'admins_groups_id', 'admins_menu_id');
    }

    public function admins() {
        return $this->hasMany('\App\Models\Admins', 'admins_groups_id');
    }

    public function groups() {
        return $this->belongsToMany('\App\Models\Groups', 'admin2group', 'admins_id', 'groups_id');
    }

}