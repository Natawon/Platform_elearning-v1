<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminsMenu extends Model
{
	use \Rutorika\Sortable\SortableTrait;
	protected static $sortableField = 'order';

    protected $table = 'admins_menu';
    protected $primaryKey = 'id';

    protected $fillable = array('parent_id', 'title', 'link', 'icon', 'in_process', 'order', 'status');
    protected $guarded = array('id', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function admins_groups() {
        return $this->belongsToMany('\App\Models\AdminsGroups', 'admins_menu2admins_groups', 'admins_menu_id', 'admins_groups_id');
    }

}