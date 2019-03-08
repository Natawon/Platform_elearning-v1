<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enroll2Quiz extends Model
{

    protected $table = 'enroll2quiz';
    protected $primaryKey = 'id';

    protected $fillable = array('enroll_id', 'quiz_id', 'type', 'score', 'count');
    protected $guarded = array('id', 'datetime');

    public $timestamps = false;

    public function enroll() {
        return $this->hasOne('\App\Models\Enroll', 'id', 'enroll_id');
    }

    public function quiz() {
        return $this->hasOne('\App\Models\Quiz', 'id', 'quiz_id');
    }

}