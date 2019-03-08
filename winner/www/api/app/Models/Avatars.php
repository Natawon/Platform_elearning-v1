<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avatars extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'avatars';
    protected $primaryKey = 'id';

    protected $fillable = array('avatar_img', 'status', 'order');
    protected $guarded = array('id', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

}