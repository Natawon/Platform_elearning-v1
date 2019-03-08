<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'quiz';
    protected $primaryKey = 'id';

    protected $fillable = array('courses_id', 'title', 'type', 'description', 'passing_score', 'take_new_exam', 'pass', 'time', 'answer_submit', 'answer', 'limit_questions', 'random_questions', 'random_answer', 'status', 'order');
    protected $guarded = array('id', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function courses() {
        return $this->hasOne('\App\Models\Courses', 'id', 'courses_id');
    }

    public function questions() {
        return $this->hasMany('\App\Models\Questions', 'quiz_id');
    }

}