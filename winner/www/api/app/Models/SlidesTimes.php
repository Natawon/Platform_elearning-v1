<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlidesTimes extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'slides_times';
    protected $primaryKey = 'id';

    protected $fillable = array('slides_id', 'courses_id', 'topics_id', 'time', 'status', 'order');
    protected $guarded = array('id', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function slides() {
        return $this->hasOne('\App\Models\Slides', 'id', 'slides_id');
    }

    public function courses() {
        return $this->hasOne('\App\Models\Courses', 'id', 'courses_id');
    }

    public function topics() {
        return $this->hasOne('\App\Models\Topics', 'id', 'topics_id');
    }


}