<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Auth;

class Certificates extends Model
{

    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'certificates';
    protected $primaryKey = 'id';

    protected $fillable = array('groups_id', 'courses_id', 'title', 'file_preview', 'body_text_1', 'body_text_1_en', 'body_text_2', 'body_text_2_en', 'footer_text', 'footer_text_en', 'number_of_logo', 'logo_align', 'logo_1', 'logo_1_en', 'logo_2', 'logo_2_en', 'logo_3', 'logo_3_en', 'number_of_signature', 'signature_align', 'signature_1', 'signature_1_en', 'name_of_signature_1', 'name_of_signature_1_en', 'position_of_signature_1', 'position_of_signature_1_en', 'remark_of_signature_1', 'remark_of_signature_1_en', 'signature_2', 'signature_2_en', 'name_of_signature_2', 'name_of_signature_2_en', 'position_of_signature_2', 'position_of_signature_2_en', 'remark_of_signature_2', 'remark_of_signature_2_en', 'signature_3', 'signature_3_en', 'name_of_signature_3', 'name_of_signature_3_en', 'position_of_signature_3', 'position_of_signature_3_en', 'remark_of_signature_3', 'remark_of_signature_3_en', 'background_color', 'text_color', 'is_border', 'border_color', 'border_style', 'is_control_logo', 'is_upload_logo', 'is_control_signature', 'is_upload_signature', 'is_control_footer', 'is_edit_footer', 'order', 'status');
    protected $guarded = array('id', 'create_datetime', 'created_by', 'modify_datetime', 'modify_by');

    public $timestamps = false;

    public function groups() {
        return $this->hasOne('\App\Models\Groups', 'id', 'groups_id');
    }

    public function courses() {
        return $this->hasOne('\App\Models\Courses', 'id', 'courses_id');
    }


}