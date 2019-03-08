<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminsLogs extends Model
{

    protected $table = 'admins_logs';
    protected $primaryKey = 'id';

    protected $guarded = array('id', 'admins_groups_id', 'groups_id', 'sub_groups_id', 'admins_id', 'type', 'data', 'return', 'status', 'datetime', 'user_agent', 'ip', 'isoCode', 'country', 'city', 'state', 'timezone', 'continent', 'device', 'platform', 'platform_version', 'browser', 'browser_version');

    public $timestamps = false;

    public function groups() {
        return $this->hasOne('\App\Models\Groups', 'id', 'groups_id');
    }

    public function sub_groups() {
        return $this->hasOne('\App\Models\SubGroups', 'id', 'sub_groups_id');
    }

    public function admins() {
        return $this->hasOne('\App\Models\Admins', 'id', 'admins_id');
    }
}