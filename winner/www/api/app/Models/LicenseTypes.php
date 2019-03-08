<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Auth;

class LicenseTypes extends Model
{

    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'license_types';
    protected $primaryKey = 'id';

    protected $fillable = array('title', 'title_en', 'expire_datetime', 'remark', 'status', 'order');
    protected $guarded = array('id', 'create_datetime', 'create_by', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function members() {
        return $this->belongsToMany('\App\Models\Members', 'members2license_types', 'license_types_id', 'members_id');
    }

}