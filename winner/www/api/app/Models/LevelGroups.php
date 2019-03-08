<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LevelGroups extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'level_groups';
    protected $primaryKey = 'id';

    protected $fillable = array('groups_id', 'sub_groups_id', 'admins_id', 'title', 'approve', 'order');
    protected $guarded = array('id', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function sub_groups() {
        return $this->hasOne('\App\Models\SubGroups', 'id', 'sub_groups_id');
    }

    public function groups() {
        return $this->hasOne('\App\Models\Groups', 'id', 'groups_id');
    }

    public function admins() {
        return $this->hasOne('\App\Models\Admins', 'id', 'admins_id');
    }

    public function members() {
        return $this->belongsToMany('\App\Models\Members', 'member2level_group', 'level_groups_id', 'members_id');
    }

    public function admin2level_group() {
        return $this->belongsToMany('\App\Models\LevelGroups', 'admin2level_group', 'admins_id', 'level_groups_id');
    }

    public function members_pre_approved() {
        return $this->belongsToMany('\App\Models\MembersPreApproved', 'members_pre_approved2level_group', 'level_groups_id');
    }

    public function classrooms() {
        return $this->belongsToMany('\App\Models\ClassRooms', 'classroom2level_group', 'level_groups_id' , 'classrooms_id');
    }

    public function courses() {
        return $this->belongsToMany('\App\Models\Courses', 'course2level_group', 'level_groups_id' , 'courses_id');
    }

}