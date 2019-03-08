<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordHistories extends Model
{
    // use \Rutorika\Sortable\SortableTrait;
    // protected static $sortableField = 'order';

    protected $table = 'password_histories';
    protected $primaryKey = 'id';

    protected $fillable = array('member_id', 'password', 'active');
    protected $guarded = array('id', 'create_datetime', 'create_by', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function members() {
        return $this->hasOne('\App\Models\Members', 'id', 'member_id');
    }

}