<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jobs extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'jobs';
    protected $primaryKey = 'id';

    protected $fillable = array('video_id', 'sc_job_id', 'sc_job_filename', 'sc_job_status', 'sc_job_error_code', 'sc_job_error_message', 'sc_job_submit_time', 'sc_job_start_time', 'sc_job_finish_time', 'sc_raw_data', 'streaming_url_type', 'is_notify', 'is_moved_file', 'is_generate_smil', 'is_sent_start_mail', 'is_sent_finish_mail', 'order', 'status');
    protected $guarded = array('id', 'create_by', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function videos() {
        return $this->hasOne('\App\Models\Videos', 'id', 'video_id');
    }

}