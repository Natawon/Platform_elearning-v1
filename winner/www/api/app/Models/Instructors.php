<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instructors extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'instructors';
    protected $primaryKey = 'id';

    protected $fillable = array('title', 'email', 'code', 'admins_id', 'sub_groups_id', 'groups_id', 'short_remark', 'subject', 'pdf', 'status', 'order');
    protected $guarded = array('id', 'admins_id', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function groups() {
        return $this->hasOne('\App\Models\Groups', 'id', 'groups_id');
    }

    public function sub_groups() {
        return $this->hasOne('\App\Models\SubGroups', 'id', 'sub_groups_id');
    }

    public function courses() {
        return $this->belongsToMany('\App\Models\Courses', 'course2instructor', 'instructors_id', 'courses_id');
    }

    public function discussions_read() {
        return $this->belongsToMany('\App\Models\Discussions', 'instructor2discussion', 'instructor_id', 'discussion_id');
    }

}