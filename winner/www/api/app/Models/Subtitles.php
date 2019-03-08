<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subtitles extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'subtitles';
    protected $primaryKey = 'id';

    protected $fillable = array('video_id', 'message', 'from_time', 'from_mill_time', 'to_time', 'to_mill_time', 'status', 'order');
    protected $guarded = array('id', 'create_datetime', 'create_by', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function videos() {
        return $this->hasOne('\App\Models\Videos', 'id', 'video_id');
    }


}