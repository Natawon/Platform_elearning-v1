<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz2Score extends Model
{

    protected $table = 'quiz2score';
    protected $primaryKey = 'id';

    protected $fillable = array('enroll2quiz_id', 'questions_id');
    protected $guarded = array('id', 'correct', 'datetime');

    public $timestamps = false;

}