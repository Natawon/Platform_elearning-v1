<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QA extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'qa';
    protected $primaryKey = 'id';

    protected $fillable = array('question', 'answer', 'status', 'order');
    protected $guarded = array('id', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

}