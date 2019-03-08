<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilterCourses extends Model
{

    protected $table = 'filter_courses';
    protected $primaryKey = 'id';

    protected $fillable = array('members_id', 'questionnaire_packs_title', 'questionnaire_packs_description', 'questionnaire_packs_force_datetime');
    protected $guarded = array('id', 'datetime');

    public $timestamps = false;

    public function members() {
        return $this->hasOne('\App\Models\Members', 'id', 'members_id');
    }

    public function filter_courses_detail() {
        return $this->hasMany('\App\Models\FilterCoursesDetail', 'filter_courses_id');
    }

    public function courses() {
        return $this->belongsToMany('\App\Models\Courses', 'filter_courses2courses', 'filter_courses_id', 'courses_id');
    }

}