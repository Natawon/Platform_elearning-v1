<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Groups extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'groups';
    protected $primaryKey = 'id';

    protected $fillable = array('title', 'subject', 'key', 'keyset', 'internal', 'need_approval', 'field_approval', 'is_connect_regis', 'targetaudience', 'max_account_age', 'max_password_age', 'max_password_history', 'incorrect_password_limit', 'use_sub_groups_single', 'thumbnail', 'page', 'multi_lang_certificate', 'contact_profile_editing', 'is_show_register_btn', 'status', 'order');
    protected $guarded = array('id', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function admins_groups() {
        return $this->belongsToMany('\App\Models\AdminsGroups', 'admin2group', 'groups_id', 'admins_id');
    }

    public function courses() {
        return $this->belongsToMany('\App\Models\Courses', 'course2group', 'groups_id', 'courses_id');
    }

    public function sub_groups() {
        return $this->hasMany('\App\Models\SubGroups', 'groups_id');
    }

    public function categories() {
        return $this->hasMany('\App\Models\Categories', 'groups_id');
    }

    public function highlights() {
        return $this->hasMany('\App\Models\Highlights', 'groups_id');
    }

    public function questionnaire_packs() {
        return $this->hasMany('\App\Models\QuestionnairePacks', 'groups_id');
    }

}