<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institutions extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'institutions';
    protected $primaryKey = 'id';

    protected $fillable = array('groups_id', 'code', 'title', 'status', 'order');
    protected $guarded = array('id', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function groups() {
        return $this->hasOne('\App\Models\AdminsGroups', 'id', 'groups_id');
    }

    public function admins() {
        return $this->hasMany('\App\Models\Admins', 'institutions_id');
    }

    public function level_groups() {
        return $this->hasMany('\App\Models\LevelGroups', 'institutions_id');
    }

}