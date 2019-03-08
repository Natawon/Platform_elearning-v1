<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Auth;

class Categories extends Model
{

    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'categories';
    protected $primaryKey = 'id';

    protected $fillable = array('admins_id', 'groups_id', 'title', 'description', 'css_class', 'hex_color', 'icon', 'status', 'order');
    protected $guarded = array('id', 'create_datetime', 'create_by', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function groups() {
        return $this->hasOne('\App\Models\Groups', 'id', 'groups_id');
    }

    public function courses() {
        return $this->belongsToMany('\App\Models\Courses', 'course2category', 'categories_id', 'courses_id');
    }

}