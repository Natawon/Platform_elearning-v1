<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documents extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'documents';
    protected $primaryKey = 'id';

    protected $fillable = array('courses_id', 'title', 'file', 'type', 'size', 'link', 'status', 'order');
    protected $guarded = array('id', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function courses() {
        return $this->hasOne('\App\Models\Courses', 'id', 'courses_id');
    }

}