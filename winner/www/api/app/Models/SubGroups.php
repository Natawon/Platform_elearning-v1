<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubGroups extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'sub_groups';
    protected $primaryKey = 'id';

    protected $fillable = array('groups_id', 'code', 'title', 'restriction_mode', 'status', 'order');
    protected $guarded = array('id', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function groups() {
        return $this->hasOne('\App\Models\Groups', 'id', 'groups_id');
    }

    public function admins() {
        return $this->hasMany('\App\Models\Admins', 'sub_groups_id');
    }

    public function members() {
        return $this->hasMany('\App\Models\Members', 'sub_groups_id');
    }

    public function level_groups() {
        return $this->hasMany('\App\Models\LevelGroups', 'sub_groups_id');
    }

    public function domains() {
        return $this->hasMany('\App\Models\Domains', 'sub_groups_id');
    }

}