<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Auth;

class Images extends Model
{

    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'images';
    protected $primaryKey = 'id';

    protected $fillable = array('admins_id', 'groups_id', 'title', 'type', 'picture', 'status', 'order');
    protected $guarded = array('id', 'created_by', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function groups() {
        return $this->hasOne('\App\Models\Groups', 'id', 'groups_id');
    }

}