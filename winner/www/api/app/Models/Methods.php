<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Methods extends Model
{
	use \Rutorika\Sortable\SortableTrait;
	protected static $sortableField = 'order';

    protected $table = 'methods';
    protected $primaryKey = 'id';

    protected $fillable = array('title', 'type', 'business_email', 'payment_url', 'currency', 'merchant', 'term', 'md5_key', 'picture', 'status', 'order');
    protected $guarded = array('id', 'create_datetime', 'modify_datetime', 'modify_by');

    public $timestamps = false;

}