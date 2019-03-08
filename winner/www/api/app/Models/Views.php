<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Auth;

class Views extends Model
{

    // use \Rutorika\Sortable\SortableTrait;
    // protected static $sortableField = 'order';

    protected $table = 'views';
    protected $primaryKey = 'id';

    protected $fillable = array('enroll_id', 'topics_id', 'state');
    protected $guarded = array('id', 'start_datetime', 'end_datetime');

    public $timestamps = false;

    public function enroll() {
        return $this->hasOne('\App\Models\Enroll', 'id', 'enroll_id');
    }

    public function topics() {
        return $this->hasOne('\App\Models\Topics', 'id', 'topics_id');
    }

}