<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Members extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'members';
    protected $primaryKey = 'id';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array('groups_id', 'sub_groups_id', 'company_code', 'ref_id', 'password', 'name_title', 'name_title_en', 'gender', 'birth_date', 'nationality', 'first_name', 'first_name_en', 'last_name', 'last_name_en', 'email', 'id_card', 'is_foreign', 'mobile_number', 'position_id', 'department', 'role', 'institution_id', 'license_type_id', 'license_id', 'education_degree_id', 'faculty_id', 'occupation_id', 'field_study_id', 'education_level_id', 'table_number', 'chief_name', 'session_id', 'avatar_id', 'expire', 'active', 'incorrect_password', 'filter_courses_status', 'latest_type_tax', 'inv_personal_first_name', 'inv_personal_last_name', 'inv_personal_tax_id', 'inv_personal_email', 'inv_personal_tel', 'inv_personal_address', 'inv_personal_zip_code', 'inv_corporate_name', 'inv_corporate_branch', 'inv_corporate_branch_no', 'inv_corporate_tax_id', 'inv_corporate_email', 'inv_corporate_tel', 'inv_corporate_address', 'inv_corporate_zip_code', 'status');
    protected $guarded = array('id', 'encrypt_password', 'last_login', 'last_logout', 'last_changed_password', 'token', 'ip', 'my_session_id', 'country', 'city', 'device', 'platform', 'platform_version', 'approved_type', 'approved_field', 'approved_by', 'approved_datetime', 'active_remark', 'reject_status', 'rejected_by', 'rejected_datetime', 'suspended_datetime', 'create_datetime', 'created_type', 'created_by', 'modify_datetime', 'modify_by');

    public function groups() {
        return $this->hasOne('\App\Models\Groups', 'id', 'groups_id');
    }

    public function sub_groups() {
        return $this->hasOne('\App\Models\SubGroups', 'id', 'sub_groups_id');
    }

    public function sub_groupsList() {
        return $this->belongsToMany('\App\Models\SubGroups', 'member2sub_group', 'members_id', 'sub_groups_id');
    }

    public function level_groups() {
        return $this->belongsToMany('\App\Models\LevelGroups', 'member2level_group', 'members_id', 'level_groups_id');
    }

    public function level_groupsByLevelGroups($level_groupsArr) {
        return $this->belongsToMany('\App\Models\LevelGroups', 'member2level_group', 'members_id', 'level_groups_id')->wherePivotIn('level_groups_id', $level_groupsArr);
    }

    public function classrooms() {
        return $this->belongsToMany('\App\Models\ClassRooms', 'classroom2member', 'members_id' , 'classrooms_id');
    }

    public function courses() {
        return $this->belongsToMany('\App\Models\Courses', 'course2member', 'members_id' , 'courses_id');
    }

    public function passowod_histories() {
        return $this->hasMany('\App\Models\PasswordHistories', 'member_id');
    }

    public function license_types() {
        return $this->belongsToMany('\App\Models\LicenseTypes', 'members2license_types', 'members_id' , 'license_types_id');
    }

    public function filter_courses() {
        return $this->hasMany('\App\Models\FilterCourses', 'members_id');
    }

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */

    public function setRememberToken($token) {
        $this->token = $token;
    }

    public function getRememberToken() {
        return $this->token;
    }

    public function getRememberTokenName() {
        return 'token';
    }

}
