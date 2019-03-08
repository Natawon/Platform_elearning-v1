<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionnaireChoices extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'questionnaire_choices';
    protected $primaryKey = 'id';

    protected $fillable = array('questionnaires_id', 'answer', 'answer_known', 'condition_list', 'condition_fix_list', 'status', 'order');
    protected $guarded = array('id', 'create_datetime', 'create_by', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function questionnaires() {
        return $this->hasOne('\App\Models\Questionnaires', 'id', 'questionnaires_id');
    }

}