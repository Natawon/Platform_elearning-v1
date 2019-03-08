<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enroll extends Model
{

    protected $table = 'enroll';
    protected $primaryKey = 'id';

    protected $fillable = array('groups_id', 'sub_groups_id', 'members_id', 'courses_id', 'certificate_reference_number', 'certificate_datetime');
    protected $guarded = array('id', 'enroll_datetime', 'last_datetime');

    public $timestamps = false;

    public function enroll2quiz() {
        return $this->hasMany('\App\Models\Enroll2Quiz', 'enroll_id');
    }

    public function latest_pre_test() {
        return $this->hasOne('\App\Models\Enroll2Quiz', 'enroll_id')->with('quiz')->where('type', 1)->orderBy('id', 'desc');
    }

    public function latest_post_test() {
        return $this->hasOne('\App\Models\Enroll2Quiz', 'enroll_id')->with('quiz')->where('type', 4)->orderBy('id', 'desc');
    }

    public function latest_exam() {
        return $this->hasOne('\App\Models\Enroll2Quiz', 'enroll_id')->with('quiz')->where('type', 3)->orderBy('id', 'desc');
    }

    public function latest_exam_with_score() {
        return $this->hasOne('\App\Models\Enroll2Quiz', 'enroll_id')->with('quiz')->where('type', 3)->whereNotNull('score')->orderBy('id','desc');
    }

    public function first_exam_with_score() {
        return $this->hasOne('\App\Models\Enroll2Quiz', 'enroll_id')->with('quiz')->where('type', 3)->whereNotNull('score')->orderBy('id','asc');
    }

    public function latest_survey() {
        return $this->hasOne('\App\Models\Enroll2Quiz', 'enroll_id')->where('type', 5)->orderBy('id', 'desc');
    }

    public function enroll2topic() {
        return $this->hasMany('\App\Models\Enroll2Topic', 'enroll_id');
    }

    public function courses() {
        return $this->hasOne('\App\Models\Courses', 'id', 'courses_id');
    }

    public function groups() {
        return $this->hasOne('\App\Models\Groups', 'id', 'groups_id');
    }

    public function sub_groups() {
        return $this->hasOne('\App\Models\SubGroups', 'id', 'sub_groups_id');
    }

    public function members() {
        return $this->hasOne('\App\Models\Members', 'id', 'members_id');
    }

}