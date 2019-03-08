<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Courses extends Model
{
    use \Rutorika\Sortable\SortableTrait;
    protected static $sortableField = 'order';

    protected $table = 'courses';
    protected $primaryKey = 'id';

    protected $fillable = array('sub_groups_id', 'code', 'title', 'subject', 'duration', 'information', 'objective', 'suitable', 'level', 'introductory', 'getting_certificate', 'getting_certificate_url', 'more_details', 'structure', 'thumbnail', 'view', 'latest', 'latest_end_datetime', 'random_quiz', 'recommended', 'price', 'free', 'start_datetime', 'end_datetime', 'review_streaming_url', 'streaming_url',
                                'slide_delay', 'percentage', 'not_skip', 'not_seek', 'sync_slides', 'activity_type', 'activity_focus', 'activity_detail', 'level_public', 'download_certificate', 'status', 'order', 'certificates_id', 'certificates_used_type', 'certificates_show_logo', 'certificates_logo_1', 'certificates_logo_1_en', 'certificates_logo_2', 'certificates_logo_2_en', 'certificates_logo_3', 'certificates_logo_3_en', 'certificates_show_signature', 'certificates_signature_1', 'certificates_signature_1_en', 'certificates_name_of_signature_1', 'certificates_name_of_signature_1_en', 'certificates_position_of_signature_1', 'certificates_position_of_signature_1_en', 'certificates_remark_of_signature_1', 'certificates_remark_of_signature_1_en', 'certificates_signature_2', 'certificates_signature_2_en', 'certificates_name_of_signature_2', 'certificates_name_of_signature_2_en', 'certificates_position_of_signature_2', 'certificates_position_of_signature_2_en', 'certificates_remark_of_signature_2', 'certificates_remark_of_signature_2_en', 'certificates_signature_3', 'certificates_signature_3_en', 'certificates_name_of_signature_3', 'certificates_name_of_signature_3_en', 'certificates_position_of_signature_3', 'certificates_position_of_signature_3_en', 'certificates_remark_of_signature_3', 'certificates_remark_of_signature_3_en', 'certificates_show_footer_text', 'certificates_footer_text', 'certificates_footer_text_en', 'is_discussion', 'is_filter');
                                // 'state', 'streaming_server', 'streaming_server_cdn', 'live_transcode_server', 'streaming_applications', 'streaming_prefix_streamname', 'streaming_streamname', 'streaming_record_part', 'streaming_record_filename', 'streaming_status', 'current_duration_record', 'is_stop_record',
    protected $guarded = array('id', 'admins_id', 'create_datetime', 'create_by', 'modify_datetime', 'modify_by');

    public $timestamps = false;

//    public function sub_groups() {
//        return $this->hasOne('\App\Models\SubGroups', 'id', 'sub_groups_id');
//    }

    public function sub_groups() {
        return $this->belongsToMany('\App\Models\SubGroups', 'course2sub_group', 'courses_id', 'sub_groups_id');
    }

    public function sub_groupsBySubGroups($sub_groupsArr) {
        return $this->belongsToMany('\App\Models\SubGroups', 'course2sub_group', 'courses_id', 'sub_groups_id')->wherePivotIn('sub_groups_id', $sub_groupsArr);
    }

    public function admins() {
        return $this->hasOne('\App\Models\Admins', 'id', 'admins_id');
    }

    public function level_groups() {
        return $this->belongsToMany('\App\Models\LevelGroups', 'course2level_group', 'courses_id', 'level_groups_id');
    }

    public function level_groupsByLevelGroups($level_groupsArr) {
        return $this->belongsToMany('\App\Models\LevelGroups', 'course2level_group', 'courses_id', 'level_groups_id')->wherePivotIn('level_groups_id', $level_groupsArr);
    }

    public function members() {
        return $this->belongsToMany('\App\Models\Members', 'course2member', 'courses_id', 'members_id');
    }

    public function members_pre_approved() {
        return $this->belongsToMany('\App\Models\MembersPreApproved', 'course2members_pre_approved', 'courses_id', 'members_pre_approved_id');
    }

    public function classrooms() {
        return $this->belongsToMany('\App\Models\ClassRooms', 'classroom2course', 'courses_id', 'classrooms_id');
    }

    public function groups() {
        return $this->belongsToMany('\App\Models\Groups', 'course2group', 'courses_id', 'groups_id');
    }

    public function groupsByGroups($groupsArr) {
        return $this->belongsToMany('\App\Models\Groups', 'course2group', 'courses_id', 'groups_id')->wherePivotIn('groups_id', $groupsArr);
    }

    public function categories() {
        return $this->belongsToMany('\App\Models\Categories', 'course2category', 'courses_id', 'categories_id');
    }

    public function categoriesByCategories($categoriesArr) {
        return $this->belongsToMany('\App\Models\Categories', 'course2category', 'courses_id', 'categories_id')->wherePivotIn('categories_id', $categoriesArr);
    }

    public function instructors() {
        return $this->belongsToMany('\App\Models\Instructors', 'course2instructor', 'courses_id', 'instructors_id');
    }

    public function instructorsByInstructors($instructorsArr) {
        return $this->belongsToMany('\App\Models\Instructors', 'course2instructor', 'courses_id', 'instructors_id')->wherePivotIn('instructors_id', $instructorsArr);
    }

    public function related() {
        return $this->belongsToMany('\App\Models\Courses', 'course2related', 'courses_id', 'related_id')->withTimestamps();
    }

    public function relatedByRelated($relatedArr) {
        return $this->belongsToMany('\App\Models\Courses', 'course2related', 'courses_id', 'related_id')->withTimestamps()->wherePivotIn('related_id', $relatedArr);
    }

    public function topics() {
        return $this->hasMany('\App\Models\Topics', 'courses_id');
    }

    public function slides() {
        return $this->hasMany('\App\Models\Slides', 'courses_id');
    }

    public function enroll() {
        return $this->hasMany('\App\Models\Enroll', 'courses_id');
    }

    public function documents() {
        return $this->hasMany('\App\Models\Documents', 'courses_id');
    }

    public function discussions() {
        return $this->hasMany('\App\Models\Discussions', 'courses_id');
    }

    public function quiz() {
        return $this->hasMany('\App\Models\Quiz', 'courses_id');
    }

    public function videos() {
        return $this->hasMany('\App\Models\Videos', 'course_id');
    }

    public function filter_courses() {
        return $this->belongsToMany('\App\Models\FilterCourses', 'filter_courses2courses', 'courses_id', 'filter_courses_id');
    }

}