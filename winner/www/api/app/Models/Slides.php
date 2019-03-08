<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slides extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'slides';
    protected $primaryKey = 'id';

    protected $fillable = array('courses_id', 'title', 'picture', 'slide_active', 'status', 'order');
    protected $guarded = array('id', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function courses() {
        return $this->hasOne('\App\Models\Courses', 'id', 'courses_id');
    }

    public function topics() {
        return $this->hasOne('\App\Models\Topics', 'id', 'topics_id');
    }

    public function slides_times() {
        return $this->hasMany('\App\Models\SlidesTimes', 'slides_id');
    }

}