<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topics extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';
    // protected static $sortableGroupField = 'parent';

    protected $table = 'topics';
    protected $primaryKey = 'id';

    protected $fillable = array(
        'courses_id', 'quiz_id', 'auto_quiz', 'parent', 'title', 'start_time', 'end_time', 'live_start_datetime',
        'live_end_datetime', 'state', 'streaming_url', 'streaming_server', 'streaming_server_cdn', 'live_transcode_server',
        'streaming_applications', 'streaming_prefix_streamname', 'streaming_streamname', 'streaming_record_part',
        'streaming_record_filename', 'streaming_status', 'streaming_pause', 'current_duration_record', 'is_stop_record',
        'slide_delay', 'live_event_id', 'live_event_status', 'detail', 'status', 'order', 'is_stop_stream', 'is_auto_convert',
        'vod_format', 'videos_id', 'is_show_subtitles', 'hit_live', 'uip_live');
    protected $guarded = array('id', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function courses() {
        return $this->hasOne('\App\Models\Courses', 'id', 'courses_id');
    }

    public function quiz() {
        return $this->hasOne('\App\Models\Quiz', 'id', 'quiz_id');
    }

    public function slides() {
        return $this->hasOne('\App\Models\Slides', 'topics_id', 'id');
    }

    public function slides_times() {
        return $this->hasMany('\App\Models\SlidesTimes', 'topics_id');
    }

    public function enroll2topic(){
        return $this->hasOne('\App\Models\Enroll2Topic', 'topics_id');
    }

    public function videos() {
        return $this->hasMany('\App\Models\Videos', 'topic_id');
    }

    public function video() {
        return $this->hasOne('\App\Models\Videos', 'id', 'videos_id');
    }

    public function sub_topics() {
        return $this->hasMany('\App\Models\Topics', 'parent');
    }

    public function parent_topics() {
        return $this->hasOne('\App\Models\Topics', 'id', 'parent');
    }

}