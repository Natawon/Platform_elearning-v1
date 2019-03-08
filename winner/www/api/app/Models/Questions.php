<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Questions extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'questions';
    protected $primaryKey = 'id';

    protected $fillable = array('quiz_id', 'questions', 'answer_info', 'type', 'status', 'order');
    protected $guarded = array('id', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function quiz() {
        return $this->hasOne('\App\Models\Quiz', 'id', 'quiz_id');
    }

    public function answer() {
        return $this->hasMany('\App\Models\Answer', 'questions_id');
    }

    public  function questions2answer(){
        return $this->hasMany('\App\Models\Questions2Answer', 'questions_id');
    }

}