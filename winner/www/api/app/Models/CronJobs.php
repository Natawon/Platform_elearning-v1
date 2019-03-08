<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CronJobs extends Model
{
    // use \Rutorika\Sortable\SortableTrait;
    // protected static $sortableField = 'order';

    protected $table = 'cron_jobs';
    protected $primaryKey = 'id';

    protected $fillable = array('code', 'is_notify_mail', 'status', 'status_remark');
    protected $guarded = array('id', 'notify_mail_datetime', 'action_datetime');

    public $timestamps = false;

    // public function courses() {
    //     return $this->hasOne('\App\Models\Courses', 'id', 'courses_id');
    // }

}