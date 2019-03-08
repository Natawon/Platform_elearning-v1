<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bandwidths extends Model
{
    // use \Rutorika\Sortable\SortableTrait;
    // protected static $sortableField = 'order';

    protected $table = 'bandwidths';
    protected $primaryKey = 'id';

    protected $fillable = array('server_name', 'bandwidth_rx', 'bandwidth_tx');
    protected $guarded = array('id', 'datetime');

    public $timestamps = false;


}