<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilterCoursesDetail extends Model
{
    // use \Rutorika\Sortable\SortableTrait;
    // protected static $sortableField = 'order';

    protected $table = 'filter_courses_detail';
    protected $primaryKey = 'id';

    protected $fillable = array('filter_courses_id', 'question', 'question_type', 'question_known', 'question_condition_type', 'answer', 'answer_known', 'answer_condition_list', 'answer_condition_fix_list');
    protected $guarded = array('id');

    public $timestamps = false;

    public function filter_courses() {
        return $this->hasOne('\App\Models\FilterCourses', 'id', 'filter_courses_id');
    }

}