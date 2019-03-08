<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Domains extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'domains';
    protected $primaryKey = 'id';

    protected $fillable = array('sub_groups_id', 'title', 'status', 'order');
    protected $guarded = array('id', 'create_datetime', 'create_by', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function sub_groups() {
        return $this->hasOne('\App\Models\SubGroups', 'id', 'sub_groups_id');
    }

}