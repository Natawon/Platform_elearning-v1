<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enroll2Topic extends Model
{

    protected $table = 'enroll2topic';
    protected $primaryKey = 'id';

    protected $fillable = array('enroll_id', 'topics_id', 'parent_id', 'duration', 'status');
    protected $guarded = array('id', 'datetime');

    public $timestamps = false;

    public function enroll() {
        return $this->hasOne('\App\Models\Enroll', 'id', 'enroll_id');
    }

    public function topics() {
        return $this->hasOne('\App\Models\Topics', 'id', 'topics_id');
    }

}