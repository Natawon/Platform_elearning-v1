<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRooms extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'classrooms';
    protected $primaryKey = 'id';

    protected $fillable = array('groups_id', 'sub_groups_id', 'title', 'start_datetime', 'end_datetime', 'status', 'order');
    protected $guarded = array('id', 'admins_id', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function admins() {
        return $this->hasOne('\App\Models\Admins', 'id', 'admins_id');
    }

    public function groups() {
        return $this->hasOne('\App\Models\Groups', 'id', 'groups_id');
    }

    public function sub_groups() {
        return $this->belongsToMany('\App\Models\SubGroups', 'classroom2sub_group', 'classrooms_id', 'sub_groups_id');
    }

    public function sub_groupsBySubGroups($sub_groupsArr) {
        return $this->belongsToMany('\App\Models\SubGroups', 'classroom2sub_group', 'classrooms_id', 'sub_groups_id')->wherePivotIn('sub_groups_id', $sub_groupsArr);
    }

    public function level_groups() {
        return $this->belongsToMany('\App\Models\LevelGroups', 'classroom2level_group', 'classrooms_id', 'level_groups_id');
    }

    public function level_groupsByLevelGroups($level_groupsArr) {
        return $this->belongsToMany('\App\Models\LevelGroups', 'classroom2level_group', 'classrooms_id', 'level_groups_id')->wherePivotIn('level_groups_id', $level_groupsArr);
    }

    public function members() {
        return $this->belongsToMany('\App\Models\Members', 'classroom2member', 'classrooms_id', 'members_id');
    }

    public function members_pre_approved() {
        return $this->belongsToMany('\App\Models\MembersPreApproved', 'classroom2members_pre_approved', 'classrooms_id', 'members_pre_approved_id');
    }

    public function courses() {
        return $this->belongsToMany('\App\Models\Courses', 'classroom2course', 'classrooms_id', 'courses_id');
    }

    public function coursesByCourses($coursesArr) {
        return $this->belongsToMany('\App\Models\Courses', 'classroom2course', 'classrooms_id', 'courses_id')->wherePivotIn('courses_id', $coursesArr);
    }

}