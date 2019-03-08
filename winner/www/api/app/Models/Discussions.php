<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discussions extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'discussions';
    protected $primaryKey = 'id';

    protected $fillable = array('parent_id', 'mention_id', 'enroll_id', 'groups_id', 'courses_id', 'topics_id', 'members_id', 'topic', 'description', 'file', 'type', 'view', 'approve', 'count_like', 'count_dislike', 'ip', 'is_public', 'is_sent_instructor', 'is_reject', 'reject_remark', 'is_read', 'status', 'order');
    protected $guarded = array('id', 'reject_datetime', 'reject_by', 'read_datetime', 'read_by', 'create_datetime', 'create_by', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function enroll() {
        return $this->hasOne('\App\Models\Enroll', 'id', 'enroll_id');
    }

    public function groups() {
        return $this->hasOne('\App\Models\Groups', 'id', 'groups_id');
    }

    public function courses() {
        return $this->hasOne('\App\Models\Courses', 'id', 'courses_id');
    }

    public function members() {
        return $this->hasOne('\App\Models\Members', 'id', 'members_id');
    }

    public function instructors() {
        return $this->hasOne('\App\Models\Instructors', 'id', 'instructors_id');
    }

    public function instructors_read() {
        return $this->belongsToMany('\App\Models\Instructors', 'instructor2discussion', 'discussion_id', 'instructor_id');
    }

}