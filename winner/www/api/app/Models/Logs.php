<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{

    protected $table = 'logs';
    protected $primaryKey = 'id';

    protected $guarded = array('id', 'groups_id', 'sub_groups_id', 'enroll_type_id', 'enroll_type', 'courses_id', 'members_id', 'type', 'data', 'return', 'status', 'datetime', 'user_agent', 'ip', 'isoCode', 'country', 'city', 'state', 'timezone', 'continent', 'device', 'platform', 'platform_version', 'browser', 'browser_version');

    public $timestamps = false;

    public function groups() {
        return $this->hasOne('\App\Models\Groups', 'id', 'groups_id');
    }

    public function sub_groups() {
        return $this->hasOne('\App\Models\SubGroups', 'id', 'sub_groups_id');
    }

    public function courses() {
        return $this->hasOne('\App\Models\Courses', 'id', 'courses_id');
    }

    public function members() {
        return $this->hasOne('\App\Models\Members', 'id', 'members_id');
    }
}