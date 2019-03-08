<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Highlights extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'highlights';
    protected $primaryKey = 'id';

    protected $fillable = array('title', 'groups_id', 'url', 'picture', 'start_date', 'end_date', 'status', 'order');
    protected $guarded = array('id', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function groups() {
        return $this->hasOne('\App\Models\Groups', 'id', 'groups_id');
    }

}