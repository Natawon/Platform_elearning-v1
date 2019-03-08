<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{

    protected $table = 'orders';
    protected $primaryKey = 'id';

    protected $fillable = array('groups_id', 'methods_id', 'members_id', 'courses_id', 'courses_code', 'courses_title', 'courses_price', 'currency', 'type_tax_invoice', 'inv_name', 'inv_branch', 'inv_branch_no', 'inv_tax_id', 'inv_email', 'inv_tel', 'inv_address', 'inv_zip_code', 'token', 'user_agent', 'ip', 'isoCode', 'country', 'city', 'timezone', 'continent', 'device', 'platform', 'platform_version', 'browser', 'browser_version');
    protected $guarded = array('id', 'create_datetime', 'create_by', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function methods() {
        return $this->hasOne('\App\Models\Methods', 'id', 'methods_id');
    }

    public function members() {
        return $this->hasOne('\App\Models\Members', 'id', 'members_id');
    }

    public function courses() {
        return $this->hasOne('\App\Models\Courses', 'id', 'courses_id');
    }

    // public function payments() {
    //     return $this->hasMany('\App\Models\Payments', 'orders_id');
    // }

    public function payments() {
        return $this->hasOne('\App\Models\Payments', 'orders_id', 'id');
    }

    public function groups() {
        return $this->hasOne('\App\Models\Groups', 'id', 'groups_id');
    }

}



