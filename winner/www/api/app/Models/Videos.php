<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Videos extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'videos';
    protected $primaryKey = 'id';

    protected $fillable = array('sc_job_id', 'course_id', 'topic_id', 'dir_name', 'name', 'size', 'extFile', 'type', 'contentType', 'isVideoType', 'url', 'thumbnailUrl', 'deleteType', 'deleteUrl', 'deleteWithCredentials', 'modifiedDateFile', 'smil_name', 'smil_url', 'subtitle_edge_style', 'subtitle_font_color', 'subtitle_font_opacity', 'subtitle_background_color', 'subtitle_background_opacity', 'subtitle_window_color', 'subtitle_window_opacity', 'order', 'status');
    protected $guarded = array('id', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function courses() {
        return $this->hasOne('\App\Models\Courses', 'id', 'course_id');
    }

    public function topics() {
        return $this->hasOne('\App\Models\Topics', 'id', 'topic_id');
    }

    public function transcodings() {
        return $this->hasMany('\App\Models\Transcodings', 'video_id');
    }

    public function jobs() {
        return $this->hasMany('\App\Models\Jobs', 'video_id');
    }

    public function subtitles() {
        return $this->hasMany('\App\Models\Subtitles', 'video_id');
    }


}