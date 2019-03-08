<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveResults extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'live_results';
    protected $primaryKey = 'id';

    protected $fillable = array('topic_id', 'video_name', 'filesize', 'live_start_datetime', 'live_end_datetime', 'is_record', 'video_status', 'order', 'status');
    protected $guarded = array('id', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function topics() {
        return $this->hasOne('\App\Models\Topics', 'id', 'topic_id');
    }
}