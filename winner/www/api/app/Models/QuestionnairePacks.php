<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionnairePacks extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'questionnaire_packs';
    protected $primaryKey = 'id';

    protected $fillable = array('groups_id', 'title', 'description', 'status', 'order');
    protected $guarded = array('id', 'force_datetime', 'force_by', 'create_datetime', 'create_by', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function groups() {
        return $this->hasOne('\App\Models\Groups', 'id', 'groups_id');
    }

    public function questionnaires() {
        return $this->hasMany('\App\Models\Questionnaires', 'questionnaire_packs_id');
    }

}