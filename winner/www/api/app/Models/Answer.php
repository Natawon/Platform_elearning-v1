<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'answer';
    protected $primaryKey = 'id';

    protected $fillable = array('question_id', 'answer', 'correct', 'status', 'order');
    protected $guarded = array('id', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public  function questions2answer(){
        return $this->hasMany('\App\Models\Questions2Answer', 'answer_id');
    }

}