<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transcodings extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'transcodings';
    protected $primaryKey = 'id';

    protected $fillable = array('video_id', 'title', 'filename', 'url', 'log_file', 'transcode_status', 'transcode_status_remark', 'order', 'status');
    protected $guarded = array('id', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function videos() {
        return $this->hasOne('\App\Models\Videos', 'id', 'video_id');
    }


}