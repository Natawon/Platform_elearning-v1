<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Questions2Answer extends Model
{

    protected $table = 'questions2answer';
    protected $primaryKey = 'id';

    protected $fillable = array('enroll2quiz_id', 'questions_id', 'answer_id', 'answer_text', 'answer_type');
    protected $guarded = array('id', 'correct', 'datetime');

    public $timestamps = false;

    public function questions() {
        return $this->hasOne('\App\Models\Questions', 'id', 'questions_id');
    }

    public function enroll2quiz() {
        return $this->hasOne('\App\Models\Enroll2Quiz', 'id', 'enroll2quiz_id');
    }

}