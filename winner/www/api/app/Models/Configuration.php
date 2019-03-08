<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{

    protected $table = 'configuration';
    protected $primaryKey = 'id';

    protected $fillable = array('title', 'meta_description', 'meta_keywords', 'description', 'description_status', 'about', 'email', 'telephone', 'address', 'facebook', 'twitter', 'google', 'youtube', 'footer', 'logo');
    protected $guarded = array('id', 'modify_datetime', 'modify_by');

    public $timestamps = false;

}