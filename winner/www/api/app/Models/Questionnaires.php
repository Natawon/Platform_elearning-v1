<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Questionnaires extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'questionnaires';
    protected $primaryKey = 'id';

    protected $fillable = array('questionnaire_packs_id', 'question', 'type', 'question_known', 'condition_type', 'status', 'order');
    protected $guarded = array('id', 'create_datetime', 'create_by', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function questionnaire_packs() {
        return $this->hasOne('\App\Models\QuestionnairePacks', 'id', 'questionnaire_packs_id');
    }

    public function questionnaire_choices() {
        return $this->hasMany('\App\Models\QuestionnaireChoices', 'questionnaires_id');
    }

}