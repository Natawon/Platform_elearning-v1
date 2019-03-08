<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use DB;
use Hash;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client as httpClient;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use Carbon\Carbon;

use App\Models\Admins;
use App\Models\Members;
use App\Models\MembersPreApproved;
use App\Models\Courses;
use App\Models\Groups;
use App\Models\SubGroups;
use App\Models\LevelGroups;
use App\Models\Topics;
use App\Models\Enroll;
use App\Models\Quiz;
use App\Models\Slides;
use App\Models\SlidesTimes;
use App\Models\LiveResults;

use Auth;
use Mail;

class LiveStreamsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request, _RolesController $oRole)
    {

        $data = new Courses;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if ($authSession->super_users) {
            $data = $data->where('admins_id', $authSession->id);
        } else if (!$oRole->isSuper()) {
            $data = $data->whereHas('groups', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });
        }

        if($request->has('search')){
            $data = $data->where('title', 'like', '%'.$request['search'].'%')
                         ->orWhere('code', 'like', '%'.$request['search'].'%');
        }

        $data = $data->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);

        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->modify_by = $admins->username;
            $data[$i]->groups = $data[$i]->groups()->get();
            $data[$i]->sub_groups = $data[$i]->sub_groups()->first();
            $data[$i]->categories = $data[$i]->categories()->get();
            $data[$i]->instructors = $data[$i]->instructors()->get();
            $data[$i]->topics = $data[$i]->topics()->get();
        }
        return response()->json($data, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request, _SecurityController $_security)
    {
        //
        $authSession = Auth::user();
        $input = $request->json()->all();

        $optionValidate = [];
        $optionValidateMessages = [];

        if ($authSession->super_users != 1) {
            $optionValidate = [
                'course2group' => 'required|array|min:1',
            ];
            $optionValidateMessages = [
                'course2group.min' => 'The course must be at least :min group.',
            ];
        }

        $validator = Validator::make($input, [
            'code' => 'required|max:10',
            'title' => 'required|max:255',
            'subject' => 'required|max:255',
            'duration' => 'required|max:255',
            'course2category' => 'required_if:status,1|array|min:1',
            // 'course2instructor' => 'required|array|min:1',
            // 'thumbnail' => 'required|max:255',
            'information' => 'required',
            'objective' => 'required',
            'suitable' => 'required',
            'level' => 'required|max:255',
            // 'certificates_id' => 'required_if:download_certificate,1',
        ]+$optionValidate, []+$optionValidateMessages);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        DB::beginTransaction();
        $data = new Courses;
        $data->fill($input);

        if (empty($input['start_datetime']) && empty($input['end_datetime'])) {
            $dtNow = Carbon::now();
            $data->start_datetime = $dtNow->toDateTimeString();
            $data->end_datetime = $dtNow->addYears(5)->toDateTimeString();
        } else if (empty($input['start_datetime'])) {
            $data->start_datetime = Carbon::now()->toDateTimeString();
        } else if (empty($input['end_datetime'])) {
            $data->end_datetime = Carbon::now()->addYears(5)->toDateTimeString();
        }

        if (empty($input['latest_end_datetime'])) {
            $data->latest_end_datetime = null;
        }

        if($authSession->super_users){
            $data->admins_id = $authSession->id;
        }

        $data->live_transcode_server = env('LIVE_TRANSCODE_SERVER');
        $data->streaming_server = env('STREAMING_SERVER');
        $data->streaming_server_cdn = env('STREAMING_SERVER_CDN');
        $data->streaming_applications = env('STREAMING_APPLICATIONS');
        $data->streaming_prefix_streamname = "c_" . str_random(8);
        $data->streaming_streamname = str_random(8);

        if ($data->state == 'live') {
            $data->streaming_url = $data->streaming_server_cdn . '/' . $data->streaming_applications . '/smil:' . $data->streaming_prefix_streamname .'.smil/playlist.m3u8';
        }

        $data->create_datetime = date('Y-m-d H:i:s');
        $data->create_by = $authSession->id;
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;

        $uniqueCode = Courses::where('code', '=', $data->code)->count();

        if ($uniqueCode > 0) {
            $message = "The code ".$data->code." already exist.";
            $is_success = false;

            return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
        }

        $is_success = $data->save();

        if ($is_success) {
            $data->streaming_record_part = "C".$data->id;
            $data->streaming_record_filename = "EL".$data->id;

            $is_success = $data->save();
        }

        if (isset($input['course2sub_group'])) {
            $course2sub_group = $input['course2sub_group'];
            $data->sub_groups()->syncWithoutDetaching($course2sub_group);
        }

        if (isset($input['course2level_group'])) {
            $course2level_group = $input['course2level_group'];
            $data->level_groups()->syncWithoutDetaching($course2level_group);
        }

        if (isset($input['course2related'])) {
            $course2related = $input['course2related'];
            $data->related()->syncWithoutDetaching($course2related);
        }

        if($authSession->super_users){

            $course2group = array("0" => $authSession->groups_id);
            $data->groups()->sync($course2group);

        }else{

            if (isset($input['course2group'])) {
                $course2group = $input['course2group'];
                $data->groups()->sync($course2group);
            }
        }

        if (isset($input['course2category'])) {
            $course2category = $input['course2category'];
            $data->categories()->sync($course2category);
        }

        if (isset($input['course2instructor'])) {
            $course2instructor = $input['course2instructor'];
            $data->instructors()->sync($course2instructor);
        }

        if (isset($input['course2member'])) {
            $course2member = $input['course2member'];
            $data->members()->sync($course2member);
        }

        if ($is_success) {
            $message = "The courses has been created.";

            $data->groupsConnectRegis = $data->groups()->where('is_connect_regis', 1)->get();
            if ($data->groupsConnectRegis->count() > 0) {
                $groupid = array_pluck($data->groupsConnectRegis, 'keyset');
                $targetaudience = array_pluck($data->groupsConnectRegis, 'targetaudience');

                /* ===== START R1 (CREATE/UPDATE COURSE) ===== */
                $paramCourse = array(
                    "courseid" => $data->id,
                    "groupid" => $groupid,
                    "code" => $data->code,
                    "coursename" => $data->title,
                    "description" => $data->information,
                    "createdate" => Carbon::parse($data->create_datetime)->format("d-m-Y"),
                    "activityfocus" => $data->activity_focus,
                    "activitydetail" => $data->activity_detail,
                    "fee" => 0,
                    "startdate" => Carbon::parse($data->start_datetime)->format("d-m-Y H:i:s"),
                    "enddate" => Carbon::parse($data->end_datetime)->format("d-m-Y H:i:s"),
                    "status" => $data->status == 1 ? 'A' : 'X',
                    "activitytype" => $data->activity_type,
                    "publishdate" => Carbon::parse($data->create_datetime)->format("d-m-Y"),
                    "startregistdatetime" => Carbon::parse($data->create_datetime)->format("d-m-Y H:i:s"),
                    "endregistdatetime" => Carbon::parse($data->create_datetime)->format("d-m-Y H:i:s"),
                    "targetaudience" => $targetaudience
                );

                $results = $_security->encryptAndSignData(json_encode($paramCourse, JSON_UNESCAPED_UNICODE));

                $oClient = new httpClient();

                try {
                    $response = $oClient->request('POST', config('constants._SET_URL.R1'), [
                        'json' => $results
                    ]);
                } catch(RequestException $e) {
                    $data->delete();
                    // $data->groups()->detach();
                    // $data->categories()->detach();
                    // $data->instructors()->detach();
                    // $data->related()->detach();
                    DB::rollBack();
                    DB::update("ALTER TABLE courses AUTO_INCREMENT = 1;");
                    if ($e->hasResponse()) {
                        $clientErr = json_decode($e->getResponse()->getBody(), true);
                        return response()->json(["message" => $clientErr['error_msg']." - (R)"], $e->getResponse()->getStatusCode());
                    }

                    return response()->json(["message" => "Failed to connect to SET regis server."], 500);
                }
                /* ===== END R1 (CREATE/UPDATE COURSE) ===== */

            }

            DB::commit();

        } else {
            $message = "Failed to create the courses.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message, 'createdId' => $data->id), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id, _RolesController $oRole)
    {
        // Check Permission Acces
        if (!$oRole->haveAccess($id, "courses")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        $data = Courses::find($id);
        // $data->groups = $data->groups()->get();
        // $data->sub_groups = $data->sub_groups()->get();
        // $data->level_groups = $data->level_groups()->get();
        // $data->categories = $data->categories()->get();
        // $data->instructors = $data->instructors()->get();
        $data->topics = $data->topics()->whereNull('parent')->orderBy('order','asc')->get();

        if ($authSession->super_users) {
            $data->sub_groups = $data->sub_groups()->where('sub_groups.id', $authSession->sub_groups_id)->get();
            $data->level_groups = $data->level_groups()->where('admins_id', $authSession->id)->get();
            $data->categories = $data->categories()->where('admins_id', $authSession->id)->get();
            $data->instructors = $data->instructors()->where('groups_id', $authSession->groups_id)->get();
            $data->related = $data->related()->where('admins_id', $authSession->id)->orderBy('course2related.order', 'ASC')->get();
        } else if (!$oRole->isSuper()) {
            $data->groups = $data->groups()->whereIn('groups.id', array_pluck($authSessionGroups, 'id'))->get();
            $data->sub_groups = $data->sub_groups()->whereIn('groups_id', array_pluck($authSessionGroups, 'id'))->get();
            $data->level_groups = $data->level_groups()->whereIn('groups_id', array_pluck($authSessionGroups, 'id'))->get();
            $data->categories = $data->categories()->whereIn('groups_id', array_pluck($authSessionGroups, 'id'))->get();
            $data->instructors = $data->instructors()->whereIn('groups_id', array_pluck($authSessionGroups, 'id'))->get();
            $data->related = $data->related()->whereHas('groups', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            })->orderBy('course2related.order', 'ASC')->get();
        } else {
            $data->groups = $data->groups()->get();
            $data->sub_groups = $data->sub_groups()->get();
            $data->level_groups = $data->level_groups()->get();
            $data->categories = $data->categories()->get();
            $data->instructors = $data->instructors()->get();
            $data->related = $data->related()->orderBy('course2related.order', 'ASC')->get();
        }

        // $data->members = $data->members()->get();

        // $data->members = $data->members()->whereNotNull('approved_type')->orderBy('approved_datetime', 'DESC')->get();
        // $data->members_count = $data->members->count();
        // for($i=0; $i<count($data->members); $i++) {
        //     $data->members[$i]->num = $data->members_count - $i;
        //     $data->members[$i]->approved_admin = Admins::select('first_name')->find($data->members[$i]->approved_by);
        //     $data->members[$i]->created_admin = Admins::select('first_name')->find($data->members[$i]->created_by);
        // }

        // $data->members_pre_approved = $data->members_pre_approved()->orderBy('id', 'DESC')->get();
        // $data->members_pre_approved_count = $data->members_pre_approved->count();
        // for($i=0; $i<count($data->members_pre_approved); $i++) {
        //     $data->members_pre_approved[$i]->num = $data->members_pre_approved_count - $i;
        //     $data->members_pre_approved[$i]->created_admin = Admins::select('first_name')->find($data->members_pre_approved[$i]->created_by);
        // }

        // $data->members_not_approved = $data->members()->whereNull('approved_type')->orderBy('create_datetime', 'DESC')->get();
        // $data->members_not_approved_count = $data->members_not_approved->count();
        // for($i=0; $i<count($data->members_not_approved); $i++) {
        //     $data->members_not_approved[$i]->num = $data->members_not_approved_count - $i;
        //     $data->members_not_approved[$i]->rejected_admin = Admins::select('first_name')->find($data->members_not_approved[$i]->rejected_by);
        //     $data->members_not_approved[$i]->created_admin = Admins::select('first_name')->find($data->members_not_approved[$i]->created_by);
        // }

        $data->admins = $data->admins()->with('groups')->first();
        $data->url_for_streaming = $data->live_transcode_server . '/' . $data->streaming_applications . '/' . $data->streaming_prefix_streamname;

        for($i=0; $i<count($data->topics); $i++) {
            $data->topics[$i]->parent = Topics::where('parent', $data->topics[$i]->id)->orderBy('order','asc')->get();
        }
        return response()->json($data, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id, Request $request, _SecurityController $_security, _RolesController $oRole)
    {
        // Check Permission Acces
        if (!$oRole->haveAccess($id, "courses")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();
        $input = $request->json()->all();

        $optionValidate = [];
        $optionValidateMessages = [];

        if ($authSession->super_users != 1) {
            $optionValidate = [
                'course2group' => (empty($input['_mode']) ? 'required|' : '').'array|min:1',
            ];
            $optionValidateMessages = [
                'course2group.min' => 'The course must be at least :min group.',
            ];
        }

        $validator = Validator::make($input, [
            'code' => 'required|max:10',
            'title' => 'required|max:255',
            'subject' => 'required|max:255',
            'duration' => 'required|max:255',
            'course2category' => (empty($input['_mode']) ? 'required_if:status,1|' : '').'array|min:1',
            // 'course2instructor' => (empty($input['_mode']) ? 'required|' : '').'array|min:1',
            // 'thumbnail' => 'required|max:255',
            'information' => 'required',
            'objective' => 'required',
            'suitable' => 'required',
            'level' => 'required|max:255',
            // 'certificates_id' => 'required_if:download_certificate,1',
        ]+$optionValidate, []+$optionValidateMessages);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = Courses::find($id);
        $data->fill($input);

        if (empty($input['start_datetime']) && empty($input['end_datetime'])) {
            $dtNow = Carbon::now();
            $data->start_datetime = $dtNow->toDateTimeString();
            $data->end_datetime = $dtNow->addYears(5)->toDateTimeString();
        } else if (empty($input['start_datetime'])) {
            $data->start_datetime = Carbon::now()->toDateTimeString();
        } else if (empty($input['end_datetime'])) {
            $data->end_datetime = Carbon::now()->addYears(5)->toDateTimeString();
        }

        if (empty($input['latest_end_datetime'])) {
            $data->latest_end_datetime = null;
        }

        if ($data->state == 'live') {
            $data->streaming_url = $data->streaming_server_cdn . '/' . $data->streaming_applications . '/smil:' . $data->streaming_prefix_streamname .'.smil/playlist.m3u8';
        } else {
            $data->streaming_url = '';
        }

        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;

        $uniqueCode = Courses::where('id', '!=', $data->id)->where('code', '=', $data->code)->count();

        if ($uniqueCode > 0) {
            $message = "The code ".$data->code." already exist.";
            $is_success = false;

            return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
        }

        $is_success = $data->save();

        if (is_array($input['course2sub_group'])) {
            $course2sub_group = $input['course2sub_group'];

            if ($authSession->super_users) {
                $dataSubGroups = $data->sub_groups()->where('sub_groups.id', $authSession->sub_groups_id)->get();
                $data->sub_groupsBySubGroups(array_pluck($dataSubGroups, 'id'))->sync($course2sub_group);
            } else if (!$oRole->isSuper()) {
                $dataSubGroups = $data->sub_groups()->whereIn('groups_id', array_pluck($authSessionGroups, 'id'))->get();
                $data->sub_groupsBySubGroups(array_pluck($dataSubGroups, 'id'))->sync($course2sub_group);
            } else {
                $data->sub_groups()->sync($course2sub_group);
            }
        }

        if (is_array($input['course2level_group'])) {
            $course2level_group = $input['course2level_group'];

            if ($authSession->super_users) {
                $dataLevelGroups = $data->level_groups()->where('admins_id', $authSession->id)->get();
                $data->level_groupsByLevelGroups(array_pluck($dataLevelGroups->toArray() + $authSession->admin2level_group->toArray(), 'id'))->sync($course2level_group);
            } else if (!$oRole->isSuper()) {
                $dataLevelGroups = $data->level_groups()->whereIn('groups_id', array_pluck($authSessionGroups, 'id'))->get();
                $data->level_groupsByLevelGroups(array_pluck($dataLevelGroups, 'id'))->sync($course2level_group);
            } else {
                $data->level_groups()->sync($course2level_group);
            }
        }

        if (is_array($input['course2related'])) {
            $course2related = $input['course2related'];

            if ($authSession->super_users) {
                $dataRelated = $data->related()->where('admins_id', $authSession->id)->get();

                $data->relatedByRelated(array_pluck($dataRelated, 'id'))->sync($course2related);
            } else if (!$oRole->isSuper()) {
                $dataRelated = $data->related()->whereHas('groups', function($query) use ($authSessionGroups) {
                    $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                })->get();

                $data->relatedByRelated(array_pluck($dataRelated, 'id'))->sync($course2related);
            } else {
                $data->related()->sync($course2related);
            }

        }

        if (is_array($input['course2group'])) {
            $course2group = $input['course2group'];

            if ($authSession->super_users) {
                #
            } else if (!$oRole->isSuper()) {
                $data->groupsByGroups(array_pluck($authSessionGroups, 'id'))->sync($course2group);
            } else {
                $data->groups()->sync($course2group);
            }
        }


        if (is_array($input['course2category'])) {
            $course2category = $input['course2category'];

            if ($authSession->super_users) {
                $dataCategories = $data->categories()->where('admins_id', $authSession->id)->get();
                $data->categoriesByCategories(array_pluck($dataCategories, 'id'))->sync($course2category);
            } else if (!$oRole->isSuper()) {
                $dataCategories = $data->categories()->whereIn('groups_id', array_pluck($authSessionGroups, 'id'))->get();
                $data->categoriesByCategories(array_pluck($dataCategories, 'id'))->sync($course2category);
            } else {
                $data->categories()->sync($course2category);
            }
        }

        if (is_array($input['course2instructor'])) {
            $course2instructor = $input['course2instructor'];

            if ($authSession->super_users) {
                $dataInstructors = $data->instructors()->where('groups_id', $authSession->groups_id)->get();
                $data->instructorsByInstructors(array_pluck($dataInstructors, 'id'))->sync($course2instructor);
            } else if (!$oRole->isSuper()) {
                $dataInstructors = $data->instructors()->whereIn('groups_id', array_pluck($authSessionGroups, 'id'))->get();
                $data->instructorsByInstructors(array_pluck($dataInstructors, 'id'))->sync($course2instructor);
            } else {
                $data->instructors()->sync($course2instructor);
            }
        }

        // if (isset($input['course2member'])) {
        //     $course2member = $input['course2member'];
        //     $data->members()->sync($course2member);
        // }

        if ($is_success) {
            $message = "The courses has been updated.";

            $data->groupsConnectRegis = $data->groups()->where('is_connect_regis', 1)->get();
            if ($data->groupsConnectRegis->count() > 0) {
                $groupid = array_pluck($data->groupsConnectRegis, 'keyset');
                $targetaudience = array_pluck($data->groupsConnectRegis, 'targetaudience');

                /* ===== START R1 (CREATE/UPDATE COURSE) ===== */
                $paramCourse = array(
                    "courseid" => $data->id,
                    "groupid" => $groupid,
                    "code" => $data->code,
                    "coursename" => $data->title,
                    "description" => $data->information,
                    "createdate" => Carbon::parse($data->create_datetime)->format("d-m-Y"),
                    "activityfocus" => $data->activity_focus,
                    "activitydetail" => $data->activity_detail,
                    "fee" => 0,
                    "startdate" => Carbon::parse($data->start_datetime)->format("d-m-Y H:i:s"),
                    "enddate" => Carbon::parse($data->end_datetime)->format("d-m-Y H:i:s"),
                    "status" => $data->status == 1 ? 'A' : 'X',
                    "activitytype" => $data->activity_type,
                    "publishdate" => Carbon::parse($data->create_datetime)->format("d-m-Y"),
                    "startregistdatetime" => Carbon::parse($data->create_datetime)->format("d-m-Y H:i:s"),
                    "endregistdatetime" => Carbon::parse($data->create_datetime)->format("d-m-Y H:i:s"),
                    "targetaudience" => $targetaudience
                );

                $results = $_security->encryptAndSignData(json_encode($paramCourse, JSON_UNESCAPED_UNICODE));

                $oClient = new httpClient();

                try {
                    $response = $oClient->request('POST', config('constants._SET_URL.R1'), [
                        'json' => $results
                    ]);
                } catch(RequestException $e) {
                    // $data->delete();
                    // // $data->groups()->detach();
                    // // $data->categories()->detach();
                    // // $data->instructors()->detach();
                    // // $data->related()->detach();
                    // DB::rollBack();
                    // DB::update("ALTER TABLE courses AUTO_INCREMENT = 1;");
                    if ($e->hasResponse()) {
                        $clientErr = json_decode($e->getResponse()->getBody(), true);
                        return response()->json(["message" => $clientErr['error_msg']." - (R)"], $e->getResponse()->getStatusCode());
                    }

                    return response()->json(["message" => "Failed to connect to SET regis server."], 500);
                }
                /* ===== END R1 (CREATE/UPDATE COURSE) ===== */

            }

        } else {
            $message = "Failed to update the courses.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id, _SecurityController $_security, _RolesController $oRole)
    {
        // Check Permission Acces
        if (!$oRole->haveAccess($id, "courses")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = Courses::find($id);
        // $dataGroups = $data->groups()->get();
        $data->delete();
        $data->categories()->detach();
        $data->groups()->detach();
        $data->instructors()->detach();
        $data->level_groups()->detach();
        $data->members()->detach();
        $data->related()->detach();
        $is_success = $data;

        if ($is_success) {
            $message = "The courses has been deleted.";

            $data->groupsConnectRegis = $data->groups()->where('is_connect_regis', 1)->get();
            if ($data->groupsConnectRegis->count() > 0) {
                $groupid = array_pluck($data->groupsConnectRegis, 'keyset');
                $targetaudience = array_pluck($data->groupsConnectRegis, 'targetaudience');

                /* ===== START R1 (CREATE/UPDATE COURSE) ===== */
                $paramCourse = array(
                    "courseid" => $data->id,
                    "groupid" => $groupid,
                    "code" => $data->code,
                    "coursename" => $data->title,
                    "description" => $data->information,
                    "createdate" => Carbon::parse($data->create_datetime)->format("d-m-Y"),
                    "activityfocus" => $data->activity_focus,
                    "activitydetail" => $data->activity_detail,
                    "fee" => 0,
                    "startdate" => Carbon::parse($data->start_datetime)->format("d-m-Y H:i:s"),
                    "enddate" => Carbon::parse($data->end_datetime)->format("d-m-Y H:i:s"),
                    "status" => 'X',
                    "activitytype" => $data->activity_type,
                    "publishdate" => Carbon::parse($data->create_datetime)->format("d-m-Y"),
                    "startregistdatetime" => Carbon::parse($data->create_datetime)->format("d-m-Y H:i:s"),
                    "endregistdatetime" => Carbon::parse($data->create_datetime)->format("d-m-Y H:i:s"),
                    "targetaudience" => $targetaudience
                );

                $results = $_security->encryptAndSignData(json_encode($paramCourse, JSON_UNESCAPED_UNICODE));

                $oClient = new httpClient();

                try {
                    $response = $oClient->request('POST', config('constants._SET_URL.R1'), [
                        'json' => $results
                    ]);
                } catch(RequestException $e) {
                    // $data->delete();
                    // // $data->groups()->detach();
                    // // $data->categories()->detach();
                    // // $data->instructors()->detach();
                    // // $data->related()->detach();
                    // DB::rollBack();
                    // DB::update("ALTER TABLE courses AUTO_INCREMENT = 1;");
                    if ($e->hasResponse()) {
                        $clientErr = json_decode($e->getResponse()->getBody(), true);
                        return response()->json(["message" => $clientErr['error_msg']." - (R)"], $e->getResponse()->getStatusCode());
                    }

                    return response()->json(["message" => "Failed to connect to SET regis server."], 500);
                }
                /* ===== END R1 (CREATE/UPDATE COURSE) ===== */

            }
        } else {
            $message = "Failed to delete the courses.";
        }
        return response()->json(array('is_error' => !$is_success, 'message'=>$message), 200);
    }

    public function toggleStreamingStatus($id, Request $request) {
        $data = topics::find($id);

        $input = $request->json()->all();        
        $data->fill($input);
        $is_success = $data->save();

        if ($is_success) {
            $message = "The topics has been updated.";
        } else {
            $message = "Failed to update the topics.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function toggleStreamingPause($id, Request $request) {
        $data = topics::find($id);

        $input = $request->json()->all();        
        $data->fill($input);
        $is_success = $data->save();

        if ($is_success) {
            $message = "The topics has been updated.";
        } else {
            $message = "Failed to update the topics.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function getIncomingStream($id, Request $request) {

        $data = topics::find($id);

        $client = new httpClient();

        try {
            $response = $client->request('GET', env('WOWZA_URL_API').'servers/_defaultServer_/vhosts/_defaultVHost_/applications/'.$data['streaming_applications'].'/instances/_definst_', [
                'auth' => [env('WOWZA_USERNAME'), env('WOWZA_PASSWORD'), env('WOWZA_AUTH_TYPE')]
            ]);
        } catch(RequestException $e) {
            if ($e->hasResponse()) {
                return response($this->namespacedXMLToArray($e->getResponse()->getBody()), $e->getResponse()->getStatusCode());
            }
        }

        if ($response->getStatusCode() != 200) {
            return response('No Response.', 404);
        }

        $dataXML = $this->namespacedXMLToArray($response->getBody());

        return response()->json($dataXML, 200);

    }

    public function getIncomingStreamDuration($id, Request $request) {

        $data = topics::find($id);

        $client = new httpClient();

        try {
            $response = $client->request('GET', env('WOWZA_URL_API').'servers/_defaultServer_/vhosts/_defaultVHost_/applications/'.$data['streaming_applications'].'/instances/_definst_/streamrecorders/'.$data['streaming_streamname'], [
                'auth' => [env('WOWZA_USERNAME'), env('WOWZA_PASSWORD'), env('WOWZA_AUTH_TYPE')]

            ]);
        } catch(RequestException $e) {
            if ($e->hasResponse()) {
                return response($this->namespacedXMLToArray($e->getResponse()->getBody()), $e->getResponse()->getStatusCode());
            }
        }

        if ($response->getStatusCode() != 200) {
            return response('No Response.', 404);
        }

        $dataXML = $this->namespacedXMLToArray($response->getBody());

        if ($data['is_stop_record'] == 0) {
            $data['current_duration_record'] = $dataXML['CurrentDuration'];
        } else {
            $data['current_duration_record'] += $dataXML['CurrentDuration'];
        }

        return response()->json($data['current_duration_record'], 200);

    }

    public function getIncomeDuration($id, Request $request) {

        $data = topics::find($id);

        return response()->json($data['current_duration_record'], 200);
    }

    public function startRecord($id, Request $request) {
        //
        $data = topics::find($id);
        $prefix_rec_part = 'topics';

        $client = new httpClient();

        $params = array(
            "instanceName" => "_definst_",
            "fileVersionDelegateName" => "com.wowza.wms.livestreamrecord.manager.StreamRecorderFileVersionDelegate",
            "serverName" => "_defaultServer_",
            "recorderName" => $data['streaming_streamname'],
            // "recorderName" => $request['recorderName'],
            "startOnKeyFrame" => true,
            "outputPath" => env('_BASE_UPLOAD_DIR').$prefix_rec_part.'/'.$data['streaming_record_part'],
            "applicationName" => $data['streaming_applications'],
            "moveFirstVideoFrameToZero" => true,
            "defaultRecorder" => true,
            "splitOnTcDiscontinuity" => false,
            "baseFile" => $data['streaming_record_filename'].".mp4",
            "RecordData" => false,
            "SplitOnTcDiscontinuity" => false,
            "BackBufferTime" => 0,
            "fileTemplate" => $data['streaming_record_filename'].".mp4",
            "segmentationType" => "None",
            "currentDuration" => 0,
            "fileFormat" => "MP4",
            "recorderState" => "Waiting for stream",
            "option" => "Append to existing file"
        );

        try {
            $response = $client->request('POST', env('WOWZA_URL_API').'servers/_defaultServer_/vhosts/_defaultVHost_/applications/'.$data['streaming_applications'].'/instances/_definst_/streamrecorders', [
                'auth' => [env('WOWZA_USERNAME'), env('WOWZA_PASSWORD'), env('WOWZA_AUTH_TYPE')],
                'json' => $params
            ]);
        } catch(RequestException $e) {
            if ($e->hasResponse()) {
                return response($this->namespacedXMLToArray($e->getResponse()->getBody()), $e->getResponse()->getStatusCode());
            }
        }

        $dataXML = $this->namespacedXMLToArray($response->getBody());

        return response()->json($dataXML, 200);

    }

    public function stopRecord($id, Request $request) {
        //
        $data = topics::find($id);

        $client = new httpClient();

        try {
            $responseDuration = $client->request('GET', env('WOWZA_URL_API').'servers/_defaultServer_/vhosts/_defaultVHost_/applications/'.$data['streaming_applications'].'/instances/_definst_/streamrecorders/'.$data['streaming_streamname'], [
                'auth' => [env('WOWZA_USERNAME'), env('WOWZA_PASSWORD'), env('WOWZA_AUTH_TYPE')]

            ]);
        } catch(RequestException $e) {
            if ($e->hasResponse()) {
                return responseDuration($this->namespacedXMLToArray($e->getResponse()->getBody()), $e->getResponse()->getStatusCode());
            }
        }

        if ($responseDuration->getStatusCode() != 200) {
            return responseDuration('No Response.', 404);
        }

        $dataDuration = $this->namespacedXMLToArray($responseDuration->getBody());

        if ($data['is_stop_record'] == 0) {
            $data['is_stop_record'] = 1;
        }

        $data['current_duration_record'] += $dataDuration['CurrentDuration'];
        $data->save();

        try {
            $response = $client->request('PUT', env('WOWZA_URL_API').'servers/_defaultServer_/vhosts/_defaultVHost_/applications/'.$data['streaming_applications'].'/instances/_definst_/streamrecorders/'.$data['streaming_streamname'].'/actions/stopRecording', [
                'auth' => [env('WOWZA_USERNAME'), env('WOWZA_PASSWORD'), env('WOWZA_AUTH_TYPE')]
            ]);
        } catch(RequestException $e) {
            if ($e->hasResponse()) {
                return response($this->namespacedXMLToArray($e->getResponse()->getBody()), $e->getResponse()->getStatusCode());
            }
        }

        if ($response->getStatusCode() != 200) {
            return response('No Response.', 404);
        }

        $dataXML = $this->namespacedXMLToArray($response->getBody());

        return response()->json($dataXML, 200);

    }

    public function update_on_demand($id, Request $request)
    {
        //
        $data = topics::find($id);
        $input = $request->json()->all();
        $data->streaming_url = $input['streaming_url'];
        $data->state = "vod";
        // $data->question_status = "0";
        $is_success = $data->save();
        if ($is_success) {
            $message = "The course has been update on demand.";
        } else {
            $message = "Failed to update the update on demand.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    private function removeNamespaceFromXML( $xml )
    {
        // Because I know all of the the namespaces that will possibly appear in
        // in the XML string I can just hard code them and check for
        // them to remove them
        $toRemove = ['rap', 'turss', 'crim', 'cred', 'j', 'rap-code', 'evic'];
        // This is part of a regex I will use to remove the namespace declaration from string
        $nameSpaceDefRegEx = '(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?';

        // Cycle through each namespace and remove it from the XML string
        foreach( $toRemove as $remove ) {
            // First remove the namespace from the opening of the tag
            $xml = str_replace('<' . $remove . ':', '<', $xml);
            // Now remove the namespace from the closing of the tag
            $xml = str_replace('</' . $remove . ':', '</', $xml);
            // This XML uses the name space with CommentText, so remove that too
            $xml = str_replace($remove . ':commentText', 'commentText', $xml);
            // Complete the pattern for RegEx to remove this namespace declaration
            $pattern = "/xmlns:{$remove}{$nameSpaceDefRegEx}/";
            // Remove the actual namespace declaration using the Pattern
            $xml = preg_replace($pattern, '', $xml, 1);
        }

        // Return sanitized and cleaned up XML with no namespaces
        return $xml;
    }

    private function namespacedXMLToArray($xml)
    {
        // One function to both clean the XML string and return an array
        return json_decode(json_encode(simplexml_load_string($this->removeNamespaceFromXML($xml))), true);
    }

    public function slidesForSync($id) {
        //
        $data = Slides::where('courses_id', '=', $id)->orderBy('order', 'asc')->get();
        for($i=0; $i<count($data); $i++) {
            $data[$i]->slides_times = $data[$i]->slides_times()->get();
            for($t=0; $t<count($data[$i]->slides_times); $t++) {
                $data[$i]->slides_times[$t]->topics = $data[$i]->slides_times[$t]->topics()->first();

            }
        }
        return response()->json($data, 200);
    }

    public function updateSyncSlide($id, Request $request)
    {
        //
        $data = Courses::find($id);
        $input = $request->json()->all();
        $data->sync_slides = $input['sync_slides'];
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();

        if ($is_success) {
            $message = "The event sync slide has been updated.";
        } else {
            $message = "Failed to update the event sync slide.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function nextSlides($id, $slides_order) {
        //
        $data = Courses::find($id)->slides()->where('order', '>', $slides_order)->orderBy('order', 'asc')->first();
        return response()->json($data, 200);
    }

    public function previousSlides($id, $slides_order) {
        //
        $data = Courses::find($id)->slides()->where('order', '<', $slides_order)->orderBy('order', 'desc')->first();
        return response()->json($data, 200);
    }

    public function slidesActive($id) {
        //
        $data = Courses::find($id)->slides()->where("slide_active", "=", "1")->first();
        // $data->slides_groups = $data->slides_groups()->first();
        return response()->json($data, 200);
    }

    public function documents($id, Request $request) {
        //
        $data = Courses::find($id)->documents()->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);
        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->no = $i + 1;
            $data[$i]->modify_by = $admins->username;
            $data[$i]->courses = $data[$i]->courses()->first();
        }
        return response()->json($data, 200);
    }

    public function quiz($id, Request $request) {
        //
        $data = Courses::find($id)->quiz()->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);
        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->no = $i + 1;
            $data[$i]->modify_by = $admins->username;
            $data[$i]->courses = $data[$i]->courses()->first();
            $data[$i]->questions = $data[$i]->questions()->get();
            for($s=0; $s<count($data[$i]->questions); $s++) {
                $data[$i]->questions[$s]->answer = $data[$i]->questions[$s]->answer()->get();
            }
        }
        return response()->json($data, 200);
    }

    public function usage_statistic($id, Request $request, _RolesController $oRole) {

        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        $search = $request['search'];
        $data = Enroll::where('courses_id', $id);
        if ($authSession->super_users) {
            $data = $data->where('admins_id', $authSession->id);
        } else if (!$oRole->isSuper()) {
            $data = $data->whereHas('groups', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });
        }
        $data = $data->with('members');
        $data = $data->whereHas('members', function($query) use ($search) {
            if($search){
                $query->where('email', 'like' , "%".$search."%");
                $query->orWhere('first_name', 'like' , "%".$search."%");
            }
        });
        $data = $data->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);
        for($i=0; $i<count($data); $i++) {
            $data[$i]->members = $data[$i]->members()->first();
            $data[$i]->groups = $data[$i]->members->groups()->first();
            $data[$i]->courses = $data[$i]->courses()->first();

            $data[$i]->topics = $data[$i]->courses->topics()->whereNull('parent')->orderBy('order','asc')->get();
            for($a=0; $a<count($data[$i]->topics); $a++) {
                $data[$i]->topics[$a]->parent = Topics::where('parent', $data[$i]->topics[$a]->id)->orderBy('order','asc')->get();
                for($x=0; $x<count($data[$i]->topics[$a]->parent); $x++) {

                    $data[$i]->topics[$a]->parent[$x]->enroll2topic = $data[$i]->topics[$a]->parent[$x]->enroll2topic()->where('enroll_id', $data[$i]->id)->first();
                    $data[$i]->topics[$a]->parent[$x]->duration = (strtotime($data[$i]->topics[$a]->parent[$x]->end_time) - strtotime('TODAY')) - (strtotime($data[$i]->topics[$a]->parent[$x]->start_time) - strtotime('TODAY'));

                    if($data[$i]->topics[$a]->parent[$x]->enroll2topic){
                        if($data[$i]->topics[$a]->parent[$x]->enroll2topic->status){
                            $data[$i]->topics[$a]->parent[$x]->duration_enroll = $data[$i]->topics[$a]->parent[$x]->duration;
                        }else{
                            $data[$i]->topics[$a]->parent[$x]->duration_enroll = $data[$i]->topics[$a]->parent[$x]->enroll2topic->duration;
                        }
                    }else{
                        $data[$i]->topics[$a]->parent[$x]->duration_enroll = 0;
                    }

                    if($data[$i]->topics[$a]->parent[$x]->duration){
                        $data[$i]->topics[$a]->parent[$x]->progress = $data[$i]->topics[$a]->parent[$x]->duration_enroll/$data[$i]->topics[$a]->parent[$x]->duration;
                        $data[$i]->topics[$a]->parent[$x]->percentage = number_format($data[$i]->topics[$a]->parent[$x]->progress * 100);

                        $data[$i]->duration2topic += $data[$i]->topics[$a]->parent[$x]->duration;
                        $data[$i]->duration2enroll += $data[$i]->topics[$a]->parent[$x]->duration_enroll;

                        $data[$i]->duration2progress = $data[$i]->duration2enroll/$data[$i]->duration2topic;
                        $data[$i]->duration2percentage = number_format($data[$i]->duration2progress * 100);
                    }

                }
            }

            if($data[$i]->courses->percentage <= $data[$i]->duration2percentage){
                $data[$i]->courses->learning = true;
            }else{
                $data[$i]->courses->learning = false;
            }


            $data[$i]->pre_test = $data[$i]->enroll2quiz()->where('type', 1)->orderBy('score', 'desc')->first();
            if($data[$i]->pre_test){
                if($data[$i]->pre_test->score){
                    $data[$i]->pre_test->progress = $data[$i]->pre_test->score/$data[$i]->pre_test->count;
                }
                $data[$i]->pre_test->percentage = number_format($data[$i]->pre_test->progress * 100);
                $data[$i]->pre_test->quiz = Quiz::find($data[$i]->pre_test->quiz_id);
                if($data[$i]->pre_test->quiz->passing_score <= $data[$i]->pre_test->percentage){
                    $data[$i]->pre_test->learning = true;
                }else{
                    $data[$i]->pre_test->learning = false;
                }

            }

            $data[$i]->post_test = $data[$i]->enroll2quiz()->where('type', 4)->orderBy('score', 'desc')->first();
            if($data[$i]->post_test){
                if($data[$i]->post_test->score){
                    $data[$i]->post_test->progress = $data[$i]->post_test->score/$data[$i]->post_test->count;
                }
                $data[$i]->post_test->percentage = number_format($data[$i]->post_test->progress * 100);
                $data[$i]->post_test->quiz = Quiz::find($data[$i]->post_test->quiz_id);
                if($data[$i]->post_test->quiz->passing_score <= $data[$i]->post_test->percentage){
                    $data[$i]->post_test->learning = true;
                }else{
                    $data[$i]->post_test->learning = false;
                }

            }

            $data[$i]->exam = $data[$i]->enroll2quiz()->where('type', 3)->orderBy('score', 'desc')->first();
            if($data[$i]->exam){
                if($data[$i]->exam->score){
                    $data[$i]->exam->progress = $data[$i]->exam->score/$data[$i]->exam->count;
                }
                $data[$i]->exam->percentage = number_format($data[$i]->exam->progress * 100);
                $data[$i]->exam->quiz = Quiz::find($data[$i]->exam->quiz_id);
                if($data[$i]->exam->quiz->passing_score <= $data[$i]->exam->percentage){
                    $data[$i]->exam->learning = true;
                }else{
                    $data[$i]->exam->learning = false;
                }

            }

            $data[$i]->survey = $data[$i]->enroll2quiz()->where('type', 5)->orderBy('score', 'desc')->first();

        }
        return response()->json($data, 200);
    }

    public function orders(Request $request)
    {
        $input = $request->json()->all();
        for($i=0; $i<count($input); $i++) {
            $data[$i] = Courses::find($input[$i]['id']);
            $data[$i]->fill($input[$i]);
            $data[$i]->save();
        }
    }

    public function sort(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'type' => 'in:moveAfter,moveBefore',
            'positionEntityId' => 'numeric',
            'order' => 'numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $entity = Courses::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = Courses::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = Courses::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = Courses::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = Courses::where('order', '>', $request['order'])->min('id');
                    Courses::find($next)->decrement('order');
                    $entity->moveBefore(Courses::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The courses has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

    private function updateMembersSSO($dataCourse, $dataGroups, $dataLevelGroups, $dataMembers, $dataFromExcel, $active, $activeRemark, $approved_type = null)
    {
        $authSession = Auth::user();

        $dataMembers->sub_groups_id = $dataFromExcel[9];

        $dataMembers->active = $active;
        $dataMembers->active_remark = $activeRemark;

        switch ($approved_type) {
            case 1:
                $dataMembers->approved_type = $approved_type;
                $dataMembers->approved_field = $dataGroups->field_approval;
                $dataMembers->approved_by = $authSession->id;
                $dataMembers->approved_datetime = date('Y-m-d H:i:s');
                break;

            case 2:
                $dataMembers->approved_type = $approved_type;
                $dataMembers->approved_by = $authSession->id;
                $dataMembers->approved_datetime = date('Y-m-d H:i:s');
                break;

            case 3:
                $dataMembers->approved_type = $approved_type;
                $dataMembers->approved_datetime = date('Y-m-d H:i:s');
                break;

            default:
                # code...
                break;
        }

        $dataMembers->modify_datetime = date('Y-m-d H:i:s');
        $dataMembers->modify_by = $authSession->id;
        $dataMembers->save();
        $dataMembers->sub_groupsList()->syncWithoutDetaching([$dataFromExcel[9] => ['active' => 1]]);
        $dataMembers->level_groups()->syncWithoutDetaching([$dataFromExcel[11]]);
        $dataMembers->courses()->syncWithoutDetaching([$dataCourse->id]);

        return $dataMembers;
    }

    private function updateMembers($dataCourse, $dataGroups, $dataLevelGroups, $dataMembers, $dataFromExcel, $active, $activeRemark, $approved_type = null, $allowUpdateEmail = true)
    {
        $authSession = Auth::user();

        $dataMembers->name_title = $dataFromExcel[0];
        $dataMembers->gender = $dataFromExcel[1];
        $dataMembers->first_name = $dataFromExcel[2];
        $dataMembers->last_name = $dataFromExcel[3];

        if ($allowUpdateEmail) {
            $dataMembers->email = $dataFromExcel[4];
        }

        // $dataMembers->password = $dataFromExcel[5];
        // $dataMembers->encrypt_password = Hash::make($dataFromExcel[5]);
        $dataMembers->id_card = $dataFromExcel[6];
        $dataMembers->birth_date = $dataFromExcel[7];
        $dataMembers->mobile_number = $dataFromExcel[8];
        $dataMembers->sub_groups_id = $dataFromExcel[9];
        $dataMembers->occupation_id = $dataFromExcel[10];
        // $dataMembers->level_groups_id = $dataLevelGroups->id;

        switch ($dataGroups->id) {
            case 1:
                // $dataMembers->education_level_id = $dataFromExcel[12];
                break;

            case 2:
                $dataMembers->position_id = $dataFromExcel[12];
                $dataMembers->role = $dataFromExcel[13];
                break;

            case 3:
                $dataMembers->license_id = $dataFromExcel[12];
                $dataMembers->position_id = $dataFromExcel[13];
                $dataMembers->education_level_id = $dataFromExcel[14];
                break;

            case 4:
                $dataMembers->education_level_id = $dataFromExcel[12];
                break;

            case 5:
                $dataMembers->position_id = $dataFromExcel[12];
                $dataMembers->table_number = $dataFromExcel[13];
                $dataMembers->chief_name = $dataFromExcel[14];
                break;

            default:
                // $dataMembers->education_level_id = $dataFromExcel[12];
                break;
        }

        $dataMembers->active = $active;
        $dataMembers->active_remark = $activeRemark;

        switch ($approved_type) {
            case 1:
                $dataMembers->approved_type = $approved_type;
                $dataMembers->approved_field = $dataGroups->field_approval;
                $dataMembers->approved_by = $authSession->id;
                $dataMembers->approved_datetime = date('Y-m-d H:i:s');
                break;

            case 2:
                $dataMembers->approved_type = $approved_type;
                $dataMembers->approved_by = $authSession->id;
                $dataMembers->approved_datetime = date('Y-m-d H:i:s');
                break;

            case 3:
                $dataMembers->approved_type = $approved_type;
                $dataMembers->approved_datetime = date('Y-m-d H:i:s');
                break;

            default:
                # code...
                break;
        }

        $dataMembers->modify_datetime = date('Y-m-d H:i:s');
        $dataMembers->modify_by = $authSession->id;
        $dataMembers->save();
        // $dataMembers->sub_groupsList()->syncWithoutDetaching($dataFromExcel[9]);
        $dataMembers->level_groups()->syncWithoutDetaching([$dataFromExcel[11]]);
        $dataMembers->courses()->syncWithoutDetaching([$dataCourse->id]);

        return $dataMembers;
    }

    private function updateMembersPreApproved($dataCourse, $dataGroups, $dataLevelGroups, $dataMembersPreApproved, $dataFromExcel)
    {
        $dataMembersPreApproved->name_title = $dataFromExcel[0];
        $dataMembersPreApproved->gender = $dataFromExcel[1];
        $dataMembersPreApproved->first_name = $dataFromExcel[2];
        $dataMembersPreApproved->last_name = $dataFromExcel[3];
        $dataMembersPreApproved->email = $dataFromExcel[4];
        // $dataMembersPreApproved->password = $dataFromExcel[5];
        // $dataMembersPreApproved->encrypt_password = Hash::make($dataFromExcel[5]);
        $dataMembersPreApproved->id_card = $dataFromExcel[6];
        $dataMembersPreApproved->birth_date = $dataFromExcel[7];
        $dataMembersPreApproved->mobile_number = $dataFromExcel[8];
        $dataMembersPreApproved->sub_groups_id = $dataFromExcel[9];
        $dataMembersPreApproved->occupation_id = $dataFromExcel[10];
        // $dataMembersPreApproved->level_groups_id = $dataLevelGroups->id;

        switch ($dataGroups->id) {
            case 1:
                // $dataMembersPreApproved->education_level_id = $dataFromExcel[12];
                break;

            case 2:
                $dataMembersPreApproved->position_id = $dataFromExcel[12];
                $dataMembersPreApproved->role = $dataFromExcel[13];
                break;

            case 3:
                $dataMembersPreApproved->license_id = $dataFromExcel[12];
                $dataMembersPreApproved->position_id = $dataFromExcel[13];
                $dataMembersPreApproved->education_level_id = $dataFromExcel[14];
                break;

            case 4:
                $dataMembersPreApproved->education_level_id = $dataFromExcel[12];
                break;

            case 5:
                $dataMembersPreApproved->position_id = $dataFromExcel[12];
                $dataMembersPreApproved->table_number = $dataFromExcel[13];
                $dataMembersPreApproved->chief_name = $dataFromExcel[14];
                break;

            default:
                // $dataMembersPreApproved->education_level_id = $dataFromExcel[12];
                break;
        }

        $dataMembersPreApproved->modify_datetime = date('Y-m-d H:i:s');
        $dataMembersPreApproved->modify_by = Auth::user()->id;
        $dataMembersPreApproved->save();
        $dataMembersPreApproved->level_groups()->syncWithoutDetaching([$dataFromExcel[11]]);
        $dataMembersPreApproved->courses()->syncWithoutDetaching([$dataCourse->id]);

        return $dataMembersPreApproved;
    }

    private function handleRejected($dataGroups, $dataReject = [], $dataFromExcel, $rowRejected, $rowCurrent, $errorMessage)
    {
        $dataReject[$rowRejected]['row'] = $rowCurrent;
        $dataReject[$rowRejected]['name_title'] = isset($dataFromExcel[0]) ? $dataFromExcel[0] : null;
        $dataReject[$rowRejected]['gender'] = isset($dataFromExcel[1]) ? $dataFromExcel[1] : null;
        $dataReject[$rowRejected]['first_name'] = isset($dataFromExcel[2]) ? $dataFromExcel[2] : null;
        $dataReject[$rowRejected]['last_name'] = isset($dataFromExcel[3]) ? $dataFromExcel[3] : null;
        $dataReject[$rowRejected]['email'] = isset($dataFromExcel[4]) ? $dataFromExcel[4] : null;
        $dataReject[$rowRejected]['password'] = isset($dataFromExcel[5]) ? $dataFromExcel[5] : null;
        $dataReject[$rowRejected]['id_card'] = isset($dataFromExcel[6]) ? $dataFromExcel[6] : null;
        $dataReject[$rowRejected]['birth_date'] = isset($dataFromExcel[7]) ? $dataFromExcel[7] : null;
        $dataReject[$rowRejected]['mobile_number'] = isset($dataFromExcel[8]) ? $dataFromExcel[8] : null;
        $dataReject[$rowRejected]['sub_groups_id'] = isset($dataFromExcel[9]) ? $dataFromExcel[9] : null;
        $dataReject[$rowRejected]['occupation_id'] = isset($dataFromExcel[10]) ? $dataFromExcel[10] : null;
        $dataReject[$rowRejected]['level_groups_id'] = isset($dataFromExcel[11]) ? $dataFromExcel[11] : null;

        switch ($dataGroups->id) {
            case 1:
                // $dataReject[$rowRejected]['education_level_id'] = isset($dataFromExcel[12]) ? $dataFromExcel[12] : null;
                break;

            case 2:
                $dataReject[$rowRejected]['position_id'] = isset($dataFromExcel[12]) ? $dataFromExcel[12] : null;
                $dataReject[$rowRejected]['role'] = isset($dataFromExcel[13]) ? $dataFromExcel[13] : null;
                break;

            case 3:
                $dataReject[$rowRejected]['license_id'] = isset($dataFromExcel[12]) ? $dataFromExcel[12] : null;
                $dataReject[$rowRejected]['position_id'] = isset($dataFromExcel[13]) ? $dataFromExcel[13] : null;
                $dataReject[$rowRejected]['education_level_id'] = isset($dataFromExcel[14]) ? $dataFromExcel[14] : null;
                break;

            case 4:
                $dataReject[$rowRejected]['education_level_id'] = isset($dataFromExcel[12]) ? $dataFromExcel[12] : null;
                break;

            case 5:
                $dataReject[$rowRejected]['position_id'] = isset($dataFromExcel[12]) ? $dataFromExcel[12] : null;
                $dataReject[$rowRejected]['table_number'] = isset($dataFromExcel[13]) ? $dataFromExcel[13] : null;
                $dataReject[$rowRejected]['chief_name'] = isset($dataFromExcel[14]) ? $dataFromExcel[14] : null;
                break;

            default:
                // $dataReject[$rowRejected]['education_level_id'] = isset($dataFromExcel[12]) ? $dataFromExcel[12] : null;
                break;
        }

        $dataReject[$rowRejected]['message'] = $errorMessage;

        return $dataReject;
    }

    private function handleCommonRejected($dataReject = [], $dataFromExcel, $rowRejected, $rowCurrent, $errorMessage)
    {
        $dataReject[$rowRejected]['row'] = $rowCurrent;
        $dataReject[$rowRejected]['name_title'] = isset($dataFromExcel[0]) ? $dataFromExcel[0] : null;
        $dataReject[$rowRejected]['gender'] = isset($dataFromExcel[1]) ? $dataFromExcel[1] : null;
        $dataReject[$rowRejected]['first_name'] = isset($dataFromExcel[2]) ? $dataFromExcel[2] : null;
        $dataReject[$rowRejected]['last_name'] = isset($dataFromExcel[3]) ? $dataFromExcel[3] : null;
        $dataReject[$rowRejected]['email'] = isset($dataFromExcel[4]) ? $dataFromExcel[4] : null;
        $dataReject[$rowRejected]['password'] = isset($dataFromExcel[5]) ? $dataFromExcel[5] : null;
        $dataReject[$rowRejected]['id_card'] = isset($dataFromExcel[6]) ? $dataFromExcel[6] : null;
        $dataReject[$rowRejected]['birth_date'] = isset($dataFromExcel[7]) ? $dataFromExcel[7] : null;
        $dataReject[$rowRejected]['mobile_number'] = isset($dataFromExcel[8]) ? $dataFromExcel[8] : null;
        $dataReject[$rowRejected]['sub_groups_id'] = isset($dataFromExcel[9]) ? $dataFromExcel[9] : null;
        $dataReject[$rowRejected]['occupation_id'] = isset($dataFromExcel[10]) ? $dataFromExcel[10] : null;
        $dataReject[$rowRejected]['level_groups_id'] = isset($dataFromExcel[11]) ? $dataFromExcel[11] : null;

        return $dataReject;
    }

    private function handleValidations($dataGroups, $dataFromExcel, $isUploadMembers)
    {
        $oFunc = new _FunctionsController;
        $authSession = Auth::user();
        $errorMessage =  null;

        $dataAdminsGroups = $authSession->admins_groups()->with(['groups' => function ($query) use ($dataGroups) {
            $query->where('groups.id', $dataGroups->id);
        }])->first();

        // if ($isUploadMembers) {
        //     $validator = Validator::make(['password' => $dataFromExcel[5]], [
        //         'password' => 'between:8,255|case_diff|numbers|letters|symbols|not_contain_credentials:'.implode(",", [$dataFromExcel[2], $dataFromExcel[3], $dataFromExcel[4]]),
        //     ]);
        // }

        switch ($dataGroups->id) {
            case 1:
                if (count($dataFromExcel) != 12) {
                    $errorMessage = "Column must be length 12 not ".count($dataFromExcel).".";
                }
                break;

            case 2:
                if (count($dataFromExcel) != 14) {
                    $errorMessage = "Column must be length 14 not ".count($dataFromExcel).".";
                } else if (empty($dataFromExcel[12]) && $isUploadMembers) {
                    $errorMessage = "The column position id has no value.";
                } else if (empty($dataFromExcel[13]) && $isUploadMembers) {
                    $errorMessage = "The column role has no value.";
                }
                break;

            case 3:
                if (count($dataFromExcel) != 15) {
                    $errorMessage = "Column must be length 15 not ".count($dataFromExcel).".";
                } else if (empty($dataFromExcel[12]) && $dataGroups->field_approval == "license_id") {
                    $errorMessage = "The column license id has no value.";
                } else if (empty($dataFromExcel[13]) && $isUploadMembers) {
                    $errorMessage = "The column position id has no value.";
                } else if (empty($dataFromExcel[14]) && $isUploadMembers) {
                    $errorMessage = "The column education level id has no value.";
                }
                break;

            case 4:
                if (count($dataFromExcel) != 13) {
                    $errorMessage = "Column must be length 13 not ".count($dataFromExcel).".";
                } else if (empty($dataFromExcel[12]) && $isUploadMembers) {
                    $errorMessage = "The column education level id has no value.";
                }
                break;

            case 5:
                if (count($dataFromExcel) != 15) {
                    $errorMessage = "Column must be length 15 not ".count($dataFromExcel).".";
                } else if (empty($dataFromExcel[12]) && $isUploadMembers) {
                    $errorMessage = "The column position id has no value.";
                } else if (empty($dataFromExcel[13]) && $isUploadMembers) {
                    $errorMessage = "The column table number has no value.";
                } else if (empty($dataFromExcel[14]) && $isUploadMembers) {
                    $errorMessage = "The column chief name has no value.";
                }
                break;

            default:
                if (count($dataFromExcel) != 12) {
                    $errorMessage = "Column must be length 12 not ".count($dataFromExcel).".";
                }
                break;
        }

        if (is_null($errorMessage)) {
            if (empty($dataFromExcel[0]) && $isUploadMembers) {
                $errorMessage = "The column prefix name has no value.";
            } else if ((empty($dataFromExcel[1]) || !in_array($dataFromExcel[1], ['M','m','F','f'])) && $isUploadMembers) {
                $errorMessage = "The column gender is invalid format. (Only M and F)";
            } else if (empty($dataFromExcel[2]) && $dataGroups->field_approval == "full_name") {
                $errorMessage = "The column first name has no value.";
            } else if (empty($dataFromExcel[3]) && $dataGroups->field_approval == "full_name") {
                $errorMessage = "The column last name has no value.";
            } else if (filter_var($dataFromExcel[4], FILTER_VALIDATE_EMAIL) === false && $isUploadMembers) {
                $errorMessage = "The column e-mail is invalid format.";
            }/* else if (!empty($dataFromExcel[5]) && $isUploadMembers && $validator->errors()->has('password')) {
                $errorMessage = $validator->errors()->get('password');
            }*/ else if (empty($dataFromExcel[6]) && $dataGroups->field_approval == "id_card") {
                $errorMessage = "The column id card has no value.";
            } else if (!empty($dataFromExcel[6]) && !$oFunc->checkIDCard($dataFromExcel[6])) {
                $errorMessage = "The column id card is invalid format.";
            } else if (!$oFunc->validateDate($dataFromExcel[7]) && $isUploadMembers) {
                $errorMessage = "The column birth date is invalid format. (yyyy-mm-dd)";
            } else if (empty($dataFromExcel[8]) && $isUploadMembers) {
                $errorMessage = "The column mobile number has no value.";
            } else if (empty($dataFromExcel[9])) {
                $errorMessage = "The subgroup id was not found.";
            } else if (empty($dataFromExcel[10]) && $dataGroups->field_approval == "occupation_id") {
                $errorMessage = "The column occupation id has no value.";
            } else if (empty($dataFromExcel[11])) {
                $errorMessage = "The column unit id has no value.";
            } else {
                $dataSubGroup = $dataGroups->sub_groups()->find($dataFromExcel[9]);
                $dataLevelGroups = LevelGroups::find($dataFromExcel[11]);

                if ($dataSubGroup && $isUploadMembers) {
                    $dataDomainExist = $dataSubGroup->domains()->where('domains.title', explode('@', $dataFromExcel[4])[1])->first();
                } else {
                    $dataDomainExist = false;
                }

                if ((!$dataSubGroup || ($authSession->sub_groups_id != $dataSubGroup->id) && count($dataAdminsGroups->groups) == 0)) {
                    $errorMessage = "The subgroup id was not found.";
                } else if (!$dataLevelGroups || $dataSubGroup->id != $dataLevelGroups->sub_groups_id) {
                    $errorMessage = "The unit of not matched in sub group.";
                } else if ($dataSubGroup->restriction_mode == "allow" && !$dataDomainExist && $isUploadMembers) {
                    $errorMessage = "The email domain is not allowed.";
                } else if ($dataSubGroup->restriction_mode == "deny" && $dataDomainExist && $isUploadMembers) {
                    $errorMessage = "The email domain is denied.";
                } else {
                    $dataLevelGroupsExist = LevelGroups::where('id', $dataFromExcel[11])->where('admins_id', $authSession->id)->first();
                    $dataLevelGroupsHasPerm = $authSession->admin2level_group()->where('level_groups_id', $dataFromExcel[11])->first();
                    if (!$dataLevelGroupsHasPerm && !$dataLevelGroupsExist && count($dataAdminsGroups->groups) == 0) {
                        $errorMessage = "The unit id was not found.";
                    }
                }
            }
        }

        return [
            "errorMessage" => $errorMessage,
            "dataSubGroup" => isset($dataSubGroup) ? $dataSubGroup : null,
            "dataLevelGroupsExist" => isset($dataLevelGroupsExist) ? $dataLevelGroupsExist : null,
            "dataLevelGroupsHasPerm" => isset($dataLevelGroupsHasPerm) ? $dataLevelGroupsHasPerm : null
        ];
    }

    private function createMembersPreApproved($dataCourse, $dataGroups, $dataLevelGroups, $dataFromExcel)
    {
        $dataMembersPreApproved = new MembersPreApproved;
        $dataMembersPreApproved->groups_id = $dataGroups->id;
        $dataMembersPreApproved->name_title = $dataFromExcel[0];
        $dataMembersPreApproved->gender = $dataFromExcel[1];
        $dataMembersPreApproved->first_name = $dataFromExcel[2];
        $dataMembersPreApproved->last_name = $dataFromExcel[3];
        $dataMembersPreApproved->email = $dataFromExcel[4];
        // $dataMembersPreApproved->password = $dataFromExcel[5];
        // $dataMembersPreApproved->encrypt_password = Hash::make($dataFromExcel[5]);
        $dataMembersPreApproved->id_card = $dataFromExcel[6];
        $dataMembersPreApproved->birth_date = $dataFromExcel[7];
        $dataMembersPreApproved->mobile_number = $dataFromExcel[8];
        $dataMembersPreApproved->sub_groups_id = $dataFromExcel[9];
        $dataMembersPreApproved->occupation_id = $dataFromExcel[10];

        switch ($dataGroups->id) {
            case 1:
                // $dataMembersPreApproved->education_level_id = $dataFromExcel[12];
                break;

            case 2:
                $dataMembersPreApproved->position_id = $dataFromExcel[12];
                $dataMembersPreApproved->role = $dataFromExcel[13];
                break;

            case 3:
                $dataMembersPreApproved->license_id = $dataFromExcel[12];
                $dataMembersPreApproved->position_id = $dataFromExcel[13];
                $dataMembersPreApproved->education_level_id = $dataFromExcel[14];
                break;

            case 4:
                $dataMembersPreApproved->education_level_id = $dataFromExcel[12];
                break;

            case 5:
                $dataMembersPreApproved->position_id = $dataFromExcel[12];
                $dataMembersPreApproved->table_number = $dataFromExcel[13];
                $dataMembersPreApproved->chief_name = $dataFromExcel[14];
                break;

            default:
                // $dataMembersPreApproved->education_level_id = $dataFromExcel[12];
                break;
        }

        $dataMembersPreApproved->create_datetime = date('Y-m-d H:i:s');
        $dataMembersPreApproved->created_by = Auth::user()->id;
        $dataMembersPreApproved->modify_datetime = date('Y-m-d H:i:s');
        $dataMembersPreApproved->modify_by = Auth::user()->id;
        $dataMembersPreApproved->status = 1;
        $dataMembersPreApproved->save();
        $dataMembersPreApproved->level_groups()->syncWithoutDetaching([$dataFromExcel[11]]);
        $dataMembersPreApproved->courses()->syncWithoutDetaching([$dataCourse->id]);

        return $dataMembersPreApproved;
    }

    private function createMembers($dataCourse, $dataGroups, $dataLevelGroups, $dataFromExcel)
    {
        $oFunc = new _FunctionsController;
        $dataMembers = new Members;
        $dataMembers->groups_id = $dataGroups->id;
        $dataMembers->name_title = $dataFromExcel[0];
        $dataMembers->gender = $dataFromExcel[1];
        $dataMembers->first_name = $dataFromExcel[2];
        $dataMembers->last_name = $dataFromExcel[3];
        $dataMembers->email = $dataFromExcel[4];

        if (empty($dataFromExcel[5])) {
            $dataMembers->password = $oFunc->generateSecurePassword();
        } else {
            $dataMembers->password = $dataFromExcel[5];
        }

        $dataMembers->encrypt_password = Hash::make($dataMembers->password);
        $dataMembers->id_card = $dataFromExcel[6];
        $dataMembers->birth_date = $dataFromExcel[7];
        $dataMembers->mobile_number = $dataFromExcel[8];
        $dataMembers->sub_groups_id = $dataFromExcel[9];
        $dataMembers->occupation_id = $dataFromExcel[10];

        switch ($dataGroups->id) {
            case 1:
                // $dataMembers->education_level_id = $dataFromExcel[12];
                break;

            case 2:
                $dataMembers->position_id = $dataFromExcel[12];
                $dataMembers->role = $dataFromExcel[13];
                break;

            case 3:
                $dataMembers->license_id = $dataFromExcel[12];
                $dataMembers->position_id = $dataFromExcel[13];
                $dataMembers->education_level_id = $dataFromExcel[14];
                break;

            case 4:
                $dataMembers->education_level_id = $dataFromExcel[12];
                break;

            case 5:
                $dataMembers->position_id = $dataFromExcel[12];
                $dataMembers->table_number = $dataFromExcel[13];
                $dataMembers->chief_name = $dataFromExcel[14];
                break;

            default:
                // $dataMembers->education_level_id = $dataFromExcel[12];
                break;
        }

        $dataMembers->approved_type = 1;
        $dataMembers->approved_field = $dataGroups->field_approval;
        $dataMembers->approved_datetime = date('Y-m-d H:i:s');
        $dataMembers->approved_by = Auth::user()->id;
        $dataMembers->active = 1;
        $dataMembers->create_datetime = date('Y-m-d H:i:s');
        $dataMembers->created_type = 2;
        $dataMembers->created_by = Auth::user()->id;
        $dataMembers->modify_datetime = date('Y-m-d H:i:s');
        $dataMembers->modify_by = Auth::user()->id;
        $dataMembers->status = 1;
        $dataMembers->save();
        $dataMembers->sub_groupsList()->sync([$dataFromExcel[9] => ['active' => 1]]);
        $dataMembers->level_groups()->syncWithoutDetaching([$dataFromExcel[11]]);
        $dataMembers->courses()->syncWithoutDetaching([$dataCourse->id]);

        return $dataMembers;
    }

    private function readMembersPreApprovedExcel($fileExcel, $countHeader, $dataCourse, $dataGroups)
    {
        $oFunc = new _FunctionsController;
        $authSession = Auth::user();
        $dataInsert = array();
        $dataInserted = array();
        $dataUpdated = array();
        $dataReject = array();
        $resourceFile = fopen($fileExcel, "r");

        $countHeader = (int)$countHeader;
        if (count($countHeader) > 0) {
            for ($i=0; $i < $countHeader; $i++) {
                $arrHeader[] = fgetcsv($resourceFile);
            }
        }

        $row = 1;
        $rowInserted = 0;
        $rowRejected = 0;

        while (($arrDetail = fgetcsv($resourceFile)) !== false) {
            $dataFormValidations = $this->handleValidations($dataGroups, $arrDetail, false);

            if (!is_null($dataFormValidations['errorMessage'])) {
                if ($dataGroups) {
                    $dataReject = $this->handleRejected($dataGroups, $dataReject, $arrDetail, $rowRejected, $row, $dataFormValidations['errorMessage']);
                } else {
                    $dataReject = $this->handleCommonRejected($dataReject, $arrDetail, $rowRejected, $row, $dataFormValidations['errorMessage']);
                }
                $rowRejected++;
            } else {

                if ($dataFormValidations['dataLevelGroupsExist']) {
                    $dataLevelGroups = $dataFormValidations['dataLevelGroupsExist'];
                } else {
                    $dataLevelGroups = $dataFormValidations['dataLevelGroupsHasPerm'];
                }

                if ($dataGroups->id == 3) {
                    if ($dataGroups->field_approval == "full_name") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where('first_name', $arrDetail[2])->where('last_name', $arrDetail[3])->first();
                    } else if ($dataGroups->field_approval == "id_card") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[6])->first();
                    } else if ($dataGroups->field_approval == "occupation_id") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[10])->first();
                    } else if ($dataGroups->field_approval == "license_id") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[12])->first();
                    } else {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where('email', $arrDetail[4])->first();
                    }

                    if ($dataExist) {
                        if ($dataExist->sub_groups_id == $arrDetail[9]) {
                            if ($dataExist->active == 1) {
                                $dataCourseExist = $dataExist->courses()->where('courses_id', $dataCourse->id)->first();
                                if (!$dataCourseExist) {

                                    if (empty($dataExist->last_login)) {
                                        $dataUpdate = $this->updateMembers($dataCourse, $dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 0);
                                        $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "This member has been updated. (Added member to course)"];
                                        $dataMember = $dataUpdate;
                                    } else {
                                        if (!$dataExist->level_groups()->where('level_groups.id', $arrDetail[11])->first()) {
                                            $dataExist->level_groups()->syncWithoutDetaching([$arrDetail[11]]);
                                            // $dataUpdated[] = ["row" => $row] + $dataExist->toArray() + ["message" => "Added member to unit."];
                                        }

                                        $dataExist->courses()->syncWithoutDetaching([$dataCourse->id]);
                                        $dataUpdated[] = ["row" => $row] + $dataExist->toArray() + ["message" => "This member already exists. (Added member to course)"];
                                        $dataMember = $dataExist;
                                    }

                                    /* BEGIN E-MAIL FUNCTION */
                                    // Notify Mail (Confirm Register Course *use old member)
                                    $url = config('constants._BASE_URL').$dataGroups->key."/courses/".$dataCourse->id."/info";
                                    $dataMail = array(
                                        'dataMembers'=>$dataMember,
                                        'dataGroups'=> $dataGroups,
                                        'dataCourses'=>$dataCourse,
                                        'url' => $url
                                    );
                                    Mail::send('courses-register-oldmembers-mail', $dataMail, function($mail) use ($dataMail) {
                                        if ($dataMail['dataMembers']['is_foreign'] != 1) {
                                            $receiverName = $dataMail['dataMembers']['first_name']." ".$dataMail['dataMembers']['last_name'];
                                        } else {
                                            $receiverName = $dataMail['dataMembers']['first_name_en']." ".$dataMail['dataMembers']['last_name_en'];
                                        }
                                        $mail->to($dataMail['dataMembers']['email'], $receiverName)->subject('แจ้งยืนยันการลงทะเบียนหลักสูตร '.$dataMail['dataCourses']['title']);
                                        $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                    });
                                    /* END E-MAIL FUNCTION */
                                } else {
                                    if (empty($dataExist->last_login)) {
                                        $dataUpdate = $this->updateMembers($dataCourse, $dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 0);
                                        $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "This member has been updated."];
                                    } else {
                                        if (!$dataExist->level_groups()->where('level_groups.id', $arrDetail[11])->first()) {
                                            $dataExist->level_groups()->syncWithoutDetaching([$arrDetail[11]]);
                                            $dataUpdated[] = ["row" => $row] + $dataExist->toArray() + ["message" => "Added member to unit."];
                                        } else {
                                            $dataUpdated[] = ["row" => $row] + $dataExist->toArray() + ["message" => "This member already exists in this course."];
                                        }
                                    }
                                }
                            } else {
                                $dataUpdate = $this->updateMembers($dataCourse, $dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 3, 1);
                                $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "Activated member."];
                                /* BEGIN E-MAIL FUNCTION */
                                // Notify Mail (Member's reactivated)
                                $url_login = config('constants._BASE_URL').$dataGroups->key."/login";
                                $url_courses = config('constants._BASE_URL').$dataGroups->key."/courses/".$dataCourse['id']."/info";
                                $dataMail = array(
                                    'dataMembers'=>$dataExist,
                                    'dataGroups'=> $dataGroups,
                                    'dataSubGroups' => $dataExist->sub_groups()->first(),
                                    'dataLevelGroups' => $dataExist->level_groups()->first(),
                                    'dataCourses'=>$dataCourse,
                                    'url_login' => $url_login,
                                    'url_courses' => $url_courses
                                );
                                Mail::send('courses-reactivated-mail', $dataMail, function($mail) use ($dataMail) {
                                    if ($dataMail['dataMembers']['is_foreign'] != 1) {
                                        $receiverName = $dataMail['dataMembers']['first_name']." ".$dataMail['dataMembers']['last_name'];
                                    } else {
                                        $receiverName = $dataMail['dataMembers']['first_name_en']." ".$dataMail['dataMembers']['last_name_en'];
                                    }
                                    $mail->to($dataMail['dataMembers']['email'], $receiverName)->subject('แจ้งการเปิดใช้งานสมาชิก '.$dataMail['dataGroups']['subject']);
                                    $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                });
                                /* END E-MAIL FUNCTION */
                            }
                        } else {
                            $countNewEmailExist = Members::where("email", $arrDetail[4])->where('groups_id', $dataGroups->id)->where('id', '!=', $dataExist->id)->count();
                            if ($countNewEmailExist > 0) {
                                $dataReject = $this->handleRejected($dataGroups, $dataReject, $arrDetail, $rowRejected, $row, "Duplicated new email.");
                                $rowRejected++;
                            } else {
                                if (empty($dataExist->last_login)) {
                                    $dataUpdate = $this->updateMembers($dataCourse, $dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 2, null, false);
                                    $dataMember = $dataUpdate;
                                } else {
                                    $dataExist->active = 1;
                                    $dataExist->active_remark = 2;
                                    $dataExist->save();
                                    $dataExist->level_groups()->syncWithoutDetaching([$arrDetail[11]]);
                                    $dataExist->courses()->syncWithoutDetaching([$dataCourse->id]);
                                    $dataMember = $dataExist;
                                }

                                $dataMember->sub_groupsList()->syncWithoutDetaching([$arrDetail[9] => ['active' => 2, 'email' => $arrDetail[4]]]);
                                $dataUpdated[] = ["row" => $row] + $dataMember->toArray() + ["message" => "This member has changing of subgroup."];

                                /* BEGIN E-MAIL FUNCTION */
                                // Notify Mail (Member's Group Changed)
                                $url_login = config('constants._BASE_URL').$dataGroups->key."/login";
                                $url_courses = config('constants._BASE_URL').$dataGroups->key."/courses/".$dataCourse->id."/info";
                                $dataMail = array(
                                    'dataMember'=>$dataMember,
                                    'dataGroups'=> $dataGroups,
                                    'dataOldSubGroups' => $dataMember->sub_groups()->first(),
                                    'dataNewSubGroups' => $dataMember->sub_groupsList()->where('active', 2)->orderBy('id', 'desc')->first(),
                                    'dataCourses'=>$dataCourse,
                                    'url_login' => $url_login,
                                    'url_courses' => $url_courses,
                                    'newEmail' => $arrDetail[4]
                                );
                                Mail::send('courses-change-subgroups-alert-mail', $dataMail, function($mail) use ($dataMail) {
                                    if ($dataMail['dataMember']['is_foreign'] != 1) {
                                        $receiverName = $dataMail['dataMember']['first_name']." ".$dataMail['dataMember']['last_name'];
                                    } else {
                                        $receiverName = $dataMail['dataMember']['first_name_en']." ".$dataMail['dataMember']['last_name_en'];
                                    }
                                    $mail->to([$dataMail['dataMember']['email'], $dataMail['newEmail']], $receiverName)->subject('แจ้งเปลี่ยนสถานะการเป็นสมาชิกจาก '.$dataMail['dataOldSubGroups']['title'].' เป็น '.$dataMail['dataNewSubGroups']['title']);
                                    $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                });
                                /* END E-MAIL FUNCTION */
                            }
                        }
                    } else {
                        if ($dataGroups->field_approval == "full_name") {
                            $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where('first_name', $arrDetail[2])->where('last_name', $arrDetail[3])->first();
                        } else if ($dataGroups->field_approval == "id_card") {
                            $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[6])->first();
                        } else if ($dataGroups->field_approval == "occupation_id") {
                            $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[10])->first();
                        } else if ($dataGroups->field_approval == "license_id") {
                            $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[12])->first();
                        } else {
                            $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where('email', $arrDetail[4])->first();
                        }

                        if ($dataPreApprovedExist) {
                            $dataUpdate = $this->updateMembersPreApproved($dataCourse, $dataGroups, $dataLevelGroups, $dataPreApprovedExist, $arrDetail);
                            if ($dataUpdate) {
                                $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "This member has been updated."];
                            }
                        } else {
                            $dataCreate = $this->createMembersPreApproved($dataCourse, $dataGroups, $dataLevelGroups, $arrDetail);
                            if ($dataCreate) {
                                $dataInserted[] = ["row" => $row] + $dataCreate->toArray();
                            }
                        }
                    }
                } else {
                    if ($dataGroups->field_approval == "full_name") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where('first_name', $arrDetail[2])->where('last_name', $arrDetail[3])->first();
                    } else if ($dataGroups->field_approval == "id_card") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[6])->first();
                    } else if ($dataGroups->field_approval == "occupation_id") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where('sub_groups_id', $arrDetail[9])->where($dataGroups->field_approval, $arrDetail[10])->first();
                    } else {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where('email', $arrDetail[4])->first();
                    }

                    if ($dataExist) {
                        if ($dataExist->sub_groups_id == $arrDetail[9]) {
                            if ($dataExist->active == 1) {
                                $dataCourseExist = $dataExist->courses()->where('courses_id', $dataCourse->id)->first();
                                if (!$dataCourseExist) {

                                    if (empty($dataExist->last_login)) {
                                        $dataUpdate = $this->updateMembers($dataCourse, $dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 0);
                                        $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "This member has been updated. (Added member to course)"];
                                        $dataMember = $dataUpdate;
                                    } else {
                                        if (!$dataExist->level_groups()->where('level_groups.id', $arrDetail[11])->first()) {
                                            $dataExist->level_groups()->syncWithoutDetaching([$arrDetail[11]]);
                                            // $dataUpdated[] = ["row" => $row] + $dataExist->toArray() + ["message" => "Added member to unit."];
                                        }

                                        $dataExist->courses()->syncWithoutDetaching([$dataCourse->id]);
                                        $dataUpdated[] = ["row" => $row] + $dataExist->toArray() + ["message" => "This member already exists. (Added member to course)"];
                                        $dataMember = $dataExist;
                                    }

                                    /* BEGIN E-MAIL FUNCTION */
                                    // Notify Mail (Confirm Register Course *use old member)
                                    $url = config('constants._BASE_URL').$dataGroups->key."/courses/".$dataCourse->id."/info";
                                    $dataMail = array(
                                        'dataMembers'=>$dataMember,
                                        'dataGroups'=> $dataGroups,
                                        'dataCourses'=>$dataCourse,
                                        'url' => $url
                                    );
                                    Mail::send('courses-register-oldmembers-mail', $dataMail, function($mail) use ($dataMail) {
                                        if ($dataMail['dataMembers']['is_foreign'] != 1) {
                                            $receiverName = $dataMail['dataMembers']['first_name']." ".$dataMail['dataMembers']['last_name'];
                                        } else {
                                            $receiverName = $dataMail['dataMembers']['first_name_en']." ".$dataMail['dataMembers']['last_name_en'];
                                        }
                                        $mail->to($dataMail['dataMembers']['email'], $receiverName)->subject('แจ้งยืนยันการลงทะเบียนหลักสูตร '.$dataMail['dataCourses']['title']);
                                        $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                    });
                                    /* END E-MAIL FUNCTION */
                                } else {
                                    if (empty($dataExist->last_login)) {
                                        $dataUpdate = $this->updateMembers($dataCourse, $dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 0);
                                        $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "This member has been updated."];
                                    } else {
                                        if (!$dataExist->level_groups()->where('level_groups.id', $arrDetail[11])->first()) {
                                            $dataExist->level_groups()->syncWithoutDetaching([$arrDetail[11]]);
                                            $dataUpdated[] = ["row" => $row] + $dataExist->toArray() + ["message" => "Added member to unit."];
                                        } else {
                                            $dataUpdated[] = ["row" => $row] + $dataExist->toArray() + ["message" => "This member already exists in this course."];
                                        }
                                    }
                                }
                            } else {
                                $dataUpdate = $this->updateMembers($dataCourse, $dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 3, 1);
                                $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "Activated member."];
                                /* BEGIN E-MAIL FUNCTION */
                                // Notify Mail (Member's reactivated)
                                $url_login = config('constants._BASE_URL').$dataGroups->key."/login";
                                $url_courses = config('constants._BASE_URL').$dataGroups->key."/courses/".$dataCourse['id']."/info";
                                $dataMail = array(
                                    'dataMembers'=>$dataExist,
                                    'dataGroups'=> $dataGroups,
                                    'dataSubGroups' => $dataExist->sub_groups()->first(),
                                    'dataLevelGroups' => $dataExist->level_groups()->first(),
                                    'dataCourses'=>$dataCourse,
                                    'url_login' => $url_login,
                                    'url_courses' => $url_courses
                                );
                                Mail::send('courses-reactivated-mail', $dataMail, function($mail) use ($dataMail) {
                                    if ($dataMail['dataMembers']['is_foreign'] != 1) {
                                        $receiverName = $dataMail['dataMembers']['first_name']." ".$dataMail['dataMembers']['last_name'];
                                    } else {
                                        $receiverName = $dataMail['dataMembers']['first_name_en']." ".$dataMail['dataMembers']['last_name_en'];
                                    }
                                    $mail->to($dataMail['dataMembers']['email'], $receiverName)->subject('แจ้งการเปิดใช้งานสมาชิก '.$dataMail['dataGroups']['subject']);
                                    $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                });
                                /* END E-MAIL FUNCTION */
                            }
                        } else if (is_null($dataExist->sub_groups_id)) {
                            if ($dataGroups->internal == 1) {
                                if (empty($dataExist->last_login)) {
                                    $dataUpdate = $this->updateMembers($dataCourse, $dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 0);
                                    $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "This member has been updated."];
                                } else {
                                    $dataReject = $this->handleRejected($dataGroups, $dataReject, $arrDetail, $rowRejected, $row, "Duplicated Member.");
                                    $rowRejected++;
                                }
                            } else {
                                $dataUpdate = $this->updateMembersSSO($dataCourse, $dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 1);
                                $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "This member has been updated."];
                            }
                        } else {
                            $countNewEmailExist = Members::where("email", $arrDetail[4])->where('groups_id', $dataGroups->id)->where('id', '!=', $dataExist->id)->count();
                            if ($countNewEmailExist > 0) {
                                $dataReject = $this->handleRejected($dataGroups, $dataReject, $arrDetail, $rowRejected, $row, "Duplicated new email.");
                                $rowRejected++;
                            } else {
                                if (empty($dataExist->last_login)) {
                                    $dataUpdate = $this->updateMembers($dataCourse, $dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 2, null, false);
                                    $dataMember = $dataUpdate;
                                } else {
                                    $dataExist->active = 1;
                                    $dataExist->active_remark = 2;
                                    $dataExist->save();
                                    $dataExist->level_groups()->syncWithoutDetaching([$arrDetail[11]]);
                                    $dataExist->courses()->syncWithoutDetaching([$dataCourse->id]);
                                    $dataMember = $dataExist;
                                }

                                if ($dataGroups->internal == 1) {
                                    $dataMember->sub_groupsList()->syncWithoutDetaching([$arrDetail[9] => ['active' => 2, 'email' => $arrDetail[4]]]);
                                    $dataUpdated[] = ["row" => $row] + $dataMember->toArray() + ["message" => "This member has changing of subgroup."];

                                    /* BEGIN E-MAIL FUNCTION */
                                    // Notify Mail (Member's Group Changed)
                                    $url_login = config('constants._BASE_URL').$dataGroups->key."/login";
                                    $url_courses = config('constants._BASE_URL').$dataGroups->key."/courses/".$dataCourse->id."/info";
                                    $dataMail = array(
                                        'dataMember'=>$dataExist,
                                        'dataGroups'=> $dataGroups,
                                        'dataOldSubGroups' => $dataExist->sub_groups()->first(),
                                        'dataNewSubGroups' => $dataExist->sub_groupsList()->where('active', 2)->orderBy('id', 'desc')->first(),
                                        'dataCourses'=>$dataCourse,
                                        'url_login' => $url_login,
                                        'url_courses' => $url_courses,
                                        'newEmail' => $arrDetail[4]
                                    );
                                    Mail::send('courses-change-subgroups-alert-mail', $dataMail, function($mail) use ($dataMail) {
                                        if ($dataMail['dataMember']['is_foreign'] != 1) {
                                            $receiverName = $dataMail['dataMember']['first_name']." ".$dataMail['dataMember']['last_name'];
                                        } else {
                                            $receiverName = $dataMail['dataMember']['first_name_en']." ".$dataMail['dataMember']['last_name_en'];
                                        }
                                        $mail->to([$dataMail['dataMember']['email'], $dataMail['newEmail']], $receiverName)->subject('แจ้งเปลี่ยนสถานะการเป็นสมาชิกจาก '.$dataMail['dataOldSubGroups']['title'].' เป็น '.$dataMail['dataNewSubGroups']['title']);
                                        $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                    });
                                    /* END E-MAIL FUNCTION */
                                } else {
                                    $dataMember->sub_groupsList()->where('active', 1)->update(['active' => 3]);
                                    $dataMember->sub_groupsList()->where('active', 2)->update(['active' => 4]);
                                    $dataMember->sub_groupsList()->syncWithoutDetaching([$arrDetail[9] => ['active' => 1, 'email' => $arrDetail[4]]]);
                                    $dataUpdated[] = ["row" => $row] + $dataMember->toArray() + ["message" => "This member changed subgroup."];
                                }
                            }
                        }
                    } else {
                        if ($dataGroups->field_approval == "full_name") {
                            $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where('first_name', $arrDetail[2])->where('last_name', $arrDetail[3])->first();
                        } else if ($dataGroups->field_approval == "id_card") {
                            $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[6])->first();
                        } else if ($dataGroups->field_approval == "occupation_id") {
                            $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where('sub_groups_id', $arrDetail[9])->where($dataGroups->field_approval, $arrDetail[10])->first();
                        } else {
                            $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where('email', $arrDetail[4])->first();
                        }

                        if ($dataPreApprovedExist) {
                            $dataUpdate = $this->updateMembersPreApproved($dataCourse, $dataGroups, $dataLevelGroups, $dataPreApprovedExist, $arrDetail);
                            if ($dataUpdate) {
                                $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "This member has been updated."];
                            }
                        } else {
                            $dataCreate = $this->createMembersPreApproved($dataCourse, $dataGroups, $dataLevelGroups, $arrDetail);
                            if ($dataCreate) {
                                $dataInserted[] = ["row" => $row] + $dataCreate->toArray();
                            }
                        }
                    }
                }
            }

            $row++;
            // if ( $row == 100 ) break;
        }

        fclose($resourceFile);

        // dd('end fn.');

        return ["dataInsert" => $dataInsert, "dataInserted" => $dataInserted, "dataReject" => $dataReject, "dataUpdated" => $dataUpdated];
    }

    private function readMembersExcel($fileExcel, $countHeader, $dataCourse, $dataGroups)
    {
        $oFunc = new _FunctionsController;
        $authSession = Auth::user();
        $dataInsert = array();
        $dataInserted = array();
        $dataUpdated = array();
        $dataReject = array();
        $resourceFile = fopen($fileExcel, "r");

        $countHeader = (int)$countHeader;
        if (count($countHeader) > 0) {
            for ($i=0; $i < $countHeader; $i++) {
                $arrHeader[] = fgetcsv($resourceFile);
            }
        }

        $row = 1;
        $rowInserted = 0;
        $rowRejected = 0;

        while (($arrDetail = fgetcsv($resourceFile)) !== false) {
            $dataFormValidations = $this->handleValidations($dataGroups, $arrDetail, true);

            if (!is_null($dataFormValidations['errorMessage'])) {
                if ($dataGroups) {
                    $dataReject = $this->handleRejected($dataGroups, $dataReject, $arrDetail, $rowRejected, $row, $dataFormValidations['errorMessage']);
                } else {
                    $dataReject = $this->handleCommonRejected($dataReject, $arrDetail, $rowRejected, $row, $dataFormValidations['errorMessage']);
                }
                $rowRejected++;
            } else {

                if ($dataFormValidations['dataLevelGroupsExist']) {
                    $dataLevelGroups = $dataFormValidations['dataLevelGroupsExist'];
                } else {
                    $dataLevelGroups = $dataFormValidations['dataLevelGroupsHasPerm'];
                }

                if ($dataGroups->id == 3) {
                    if ($dataGroups->field_approval == "full_name") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where('first_name', $arrDetail[2])->where('last_name', $arrDetail[3])->first();
                    } else if ($dataGroups->field_approval == "id_card") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[6])->first();
                    } else if ($dataGroups->field_approval == "occupation_id") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[10])->first();
                    } else if ($dataGroups->field_approval == "license_id") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[12])->first();
                    } else {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where('email', $arrDetail[4])->first();
                    }

                    if ($dataExist) {
                        if ($dataExist->sub_groups_id == $arrDetail[9]) {
                            if ($dataExist->active == 1) {
                                $dataCourseExist = $dataExist->courses()->where('courses_id', $dataCourse->id)->first();
                                if (!$dataCourseExist) {

                                    if (empty($dataExist->last_login)) {
                                        $dataUpdate = $this->updateMembers($dataCourse, $dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 0);
                                        $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "This member has been updated. (Added member to course)"];
                                        $dataMember = $dataUpdate;
                                    } else {
                                        if (!$dataExist->level_groups()->where('level_groups.id', $arrDetail[11])->first()) {
                                            $dataExist->level_groups()->syncWithoutDetaching([$arrDetail[11]]);
                                            // $dataUpdated[] = ["row" => $row] + $dataExist->toArray() + ["message" => "Added member to unit."];
                                        }

                                        $dataExist->courses()->syncWithoutDetaching([$dataCourse->id]);
                                        $dataUpdated[] = ["row" => $row] + $dataExist->toArray() + ["message" => "This member already exists. (Added member to course)"];
                                        $dataMember = $dataExist;
                                    }

                                    /* BEGIN E-MAIL FUNCTION */
                                    // Notify Mail (Confirm Register Course *use old member)
                                    $url = config('constants._BASE_URL').$dataGroups->key."/courses/".$dataCourse->id."/info";
                                    $dataMail = array(
                                        'dataMembers'=>$dataMember,
                                        'dataGroups'=> $dataGroups,
                                        'dataCourses'=>$dataCourse,
                                        'url' => $url
                                    );
                                    Mail::send('courses-register-oldmembers-mail', $dataMail, function($mail) use ($dataMail) {
                                        if ($dataMail['dataMembers']['is_foreign'] != 1) {
                                            $receiverName = $dataMail['dataMembers']['first_name']." ".$dataMail['dataMembers']['last_name'];
                                        } else {
                                            $receiverName = $dataMail['dataMembers']['first_name_en']." ".$dataMail['dataMembers']['last_name_en'];
                                        }
                                        $mail->to($dataMail['dataMembers']['email'], $receiverName)->subject('แจ้งยืนยันการลงทะเบียนหลักสูตร '.$dataMail['dataCourses']['title']);
                                        $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                    });
                                    /* END E-MAIL FUNCTION */
                                } else {
                                    if (empty($dataExist->last_login)) {
                                        $dataUpdate = $this->updateMembers($dataCourse, $dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 0);
                                        $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "This member has been updated."];
                                    } else {
                                        if (!$dataExist->level_groups()->where('level_groups.id', $arrDetail[11])->first()) {
                                            $dataExist->level_groups()->syncWithoutDetaching([$arrDetail[11]]);
                                            $dataUpdated[] = ["row" => $row] + $dataExist->toArray() + ["message" => "Added member to unit."];
                                        } else {
                                            $dataUpdated[] = ["row" => $row] + $dataExist->toArray() + ["message" => "This member already exists in this course."];
                                        }
                                    }
                                }
                            } else {
                                $dataUpdate = $this->updateMembers($dataCourse, $dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 3, 1);
                                $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "Activated member."];
                                /* BEGIN E-MAIL FUNCTION */
                                // Notify Mail (Member's reactivated)
                                $url_login = config('constants._BASE_URL').$dataGroups->key."/login";
                                $url_courses = config('constants._BASE_URL').$dataGroups->key."/courses/".$dataCourse['id']."/info";
                                $dataMail = array(
                                    'dataMembers'=>$dataExist,
                                    'dataGroups'=> $dataGroups,
                                    'dataSubGroups' => $dataExist->sub_groups()->first(),
                                    'dataLevelGroups' => $dataExist->level_groups()->first(),
                                    'dataCourses'=>$dataCourse,
                                    'url_login' => $url_login,
                                    'url_courses' => $url_courses
                                );
                                Mail::send('courses-reactivated-mail', $dataMail, function($mail) use ($dataMail) {
                                    if ($dataMail['dataMembers']['is_foreign'] != 1) {
                                        $receiverName = $dataMail['dataMembers']['first_name']." ".$dataMail['dataMembers']['last_name'];
                                    } else {
                                        $receiverName = $dataMail['dataMembers']['first_name_en']." ".$dataMail['dataMembers']['last_name_en'];
                                    }
                                    $mail->to($dataMail['dataMembers']['email'], $receiverName)->subject('แจ้งการเปิดใช้งานสมาชิก '.$dataMail['dataGroups']['subject']);
                                    $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                });
                                /* END E-MAIL FUNCTION */
                            }
                        } else {
                            $countNewEmailExist = Members::where("email", $arrDetail[4])->where('groups_id', $dataGroups->id)->where('id', '!=', $dataExist->id)->count();
                            if ($countNewEmailExist > 0) {
                                $dataReject = $this->handleRejected($dataGroups, $dataReject, $arrDetail, $rowRejected, $row, "Duplicated new email.");
                                $rowRejected++;
                            } else {
                                if (empty($dataExist->last_login)) {
                                    $dataUpdate = $this->updateMembers($dataCourse, $dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 2, null, false);
                                    $dataMember = $dataUpdate;
                                } else {
                                    $dataExist->active = 1;
                                    $dataExist->active_remark = 2;
                                    $dataExist->save();
                                    $dataExist->level_groups()->syncWithoutDetaching([$arrDetail[11]]);
                                    $dataExist->courses()->syncWithoutDetaching([$dataCourse->id]);
                                    $dataMember = $dataExist;
                                }

                                $dataMember->sub_groupsList()->syncWithoutDetaching([$arrDetail[9] => ['active' => 2, 'email' => $arrDetail[4]]]);
                                $dataUpdated[] = ["row" => $row] + $dataMember->toArray() + ["message" => "This member has changing of subgroup."];

                                /* BEGIN E-MAIL FUNCTION */
                                // Notify Mail (Member's Group Changed)
                                $url_login = config('constants._BASE_URL').$dataGroups->key."/login";
                                $url_courses = config('constants._BASE_URL').$dataGroups->key."/courses/".$dataCourse->id."/info";
                                $dataMail = array(
                                    'dataMember'=>$dataMember,
                                    'dataGroups'=> $dataGroups,
                                    'dataOldSubGroups' => $dataMember->sub_groups()->first(),
                                    'dataNewSubGroups' => $dataMember->sub_groupsList()->where('active', 2)->orderBy('id', 'desc')->first(),
                                    'dataCourses'=>$dataCourse,
                                    'url_login' => $url_login,
                                    'url_courses' => $url_courses,
                                    'newEmail' => $arrDetail[4]
                                );
                                Mail::send('courses-change-subgroups-alert-mail', $dataMail, function($mail) use ($dataMail) {
                                    if ($dataMail['dataMember']['is_foreign'] != 1) {
                                        $receiverName = $dataMail['dataMember']['first_name']." ".$dataMail['dataMember']['last_name'];
                                    } else {
                                        $receiverName = $dataMail['dataMember']['first_name_en']." ".$dataMail['dataMember']['last_name_en'];
                                    }
                                    $mail->to([$dataMail['dataMember']['email'], $dataMail['newEmail']], $receiverName)->subject('แจ้งเปลี่ยนสถานะการเป็นสมาชิกจาก '.$dataMail['dataOldSubGroups']['title'].' เป็น '.$dataMail['dataNewSubGroups']['title']);
                                    $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                });
                                /* END E-MAIL FUNCTION */
                            }
                        }
                    } else {
                        // Add New Member
                        $dataCreate = $this->createMembers($dataCourse, $dataGroups, $dataLevelGroups, $arrDetail);
                        if ($dataCreate) {
                            $dataInserted[] = ["row" => $row] + $dataCreate->toArray();

                            // Remove Pre-Approved
                            if ($dataGroups->field_approval == "full_name") {
                                $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where('first_name', $arrDetail[2])->where('last_name', $arrDetail[3])->first();
                            } else if ($dataGroups->field_approval == "id_card") {
                                $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[6])->first();
                            } else if ($dataGroups->field_approval == "occupation_id") {
                                $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[10])->first();
                            } else if ($dataGroups->field_approval == "license_id") {
                                $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[12])->first();
                            } else {
                                $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where('email', $arrDetail[4])->first();
                            }

                            if ($dataPreApprovedExist) {
                                if ($dataPreApprovedExist->courses) {
                                    $dataCreate->courses()->syncWithoutDetaching(array_pluck($dataPreApprovedExist->courses, 'id'));
                                    $dataPreApprovedExist->courses()->detach();
                                }

                                if ($dataPreApprovedExist->classrooms) {
                                    $dataCreate->classrooms()->syncWithoutDetaching(array_pluck($dataPreApprovedExist->classrooms, 'id'));
                                    $dataPreApprovedExist->classrooms()->detach();
                                }

                                $dataPreApprovedExist->delete();
                                $dataPreApprovedExist->level_groups()->detach();
                            }

                            /* BEGIN E-MAIL FUNCTION */
                            // Notify Mail (Confirm Register Course *use new member)
                            $dataMembers = Members::where('email', $arrDetail[4])->where('groups_id', $dataGroups->id)->first();
                            $url_login = config('constants._BASE_URL').$dataGroups->key."/login";
                            $url_courses = config('constants._BASE_URL').$dataGroups->key."/courses/".$dataCourse->id."/info";
                            $dataMail = array(
                                'dataMembers'=>$dataMembers,
                                'dataGroups'=> $dataGroups,
                                'dataSubGroups' => $dataMembers->sub_groups()->first(),
                                'dataLevelGroups' => $dataMembers->level_groups()->first(),
                                'dataCourses'=>$dataCourse,
                                'url_courses' => $url_courses,
                                'url_login' => $url_login
                            );
                            Mail::send('courses-register-newmembers-mail', $dataMail, function($mail) use ($dataMail) {
                                if ($dataMail['dataMembers']['is_foreign'] != 1) {
                                    $receiverName = $dataMail['dataMembers']['first_name']." ".$dataMail['dataMembers']['last_name'];
                                } else {
                                    $receiverName = $dataMail['dataMembers']['first_name_en']." ".$dataMail['dataMembers']['last_name_en'];
                                }
                                $mail->to($dataMail['dataMembers']['email'], $receiverName)->subject('แจ้งยืนยันการเป็นสมาชิก '.$dataMail['dataGroups']['subject']);
                                $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                            });
                            /* END E-MAIL FUNCTION */
                        }
                    }
                } else {
                    if ($dataGroups->field_approval == "full_name") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where('first_name', $arrDetail[2])->where('last_name', $arrDetail[3])->first();
                    } else if ($dataGroups->field_approval == "id_card") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[6])->first();
                    } else if ($dataGroups->field_approval == "occupation_id") {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where('sub_groups_id', $arrDetail[9])->where($dataGroups->field_approval, $arrDetail[10])->first();
                    } else {
                        $dataExist = Members::where('groups_id', $dataGroups->id)->where('email', $arrDetail[4])->first();
                    }

                    if ($dataExist) {
                        if ($dataExist->sub_groups_id == $arrDetail[9]) {
                            if ($dataExist->active == 1) {
                                $dataCourseExist = $dataExist->courses()->where('courses_id', $dataCourse->id)->first();
                                if (!$dataCourseExist) {

                                    if (empty($dataExist->last_login)) {
                                        $dataUpdate = $this->updateMembers($dataCourse, $dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 0);
                                        $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "This member has been updated. (Added member to course)"];
                                        $dataMember = $dataUpdate;
                                    } else {
                                        if (!$dataExist->level_groups()->where('level_groups.id', $arrDetail[11])->first()) {
                                            $dataExist->level_groups()->syncWithoutDetaching([$arrDetail[11]]);
                                            // $dataUpdated[] = ["row" => $row] + $dataExist->toArray() + ["message" => "Added member to unit."];
                                        }

                                        $dataExist->courses()->syncWithoutDetaching([$dataCourse->id]);
                                        $dataUpdated[] = ["row" => $row] + $dataExist->toArray() + ["message" => "This member already exists. (Added member to course)"];
                                        $dataMember = $dataExist;
                                    }

                                    /* BEGIN E-MAIL FUNCTION */
                                    // Notify Mail (Confirm Register Course *use old member)
                                    $url = config('constants._BASE_URL').$dataGroups->key."/courses/".$dataCourse->id."/info";
                                    $dataMail = array(
                                        'dataMembers'=>$dataMember,
                                        'dataGroups'=> $dataGroups,
                                        'dataCourses'=>$dataCourse,
                                        'url' => $url
                                    );
                                    Mail::send('courses-register-oldmembers-mail', $dataMail, function($mail) use ($dataMail) {
                                        if ($dataMail['dataMembers']['is_foreign'] != 1) {
                                            $receiverName = $dataMail['dataMembers']['first_name']." ".$dataMail['dataMembers']['last_name'];
                                        } else {
                                            $receiverName = $dataMail['dataMembers']['first_name_en']." ".$dataMail['dataMembers']['last_name_en'];
                                        }
                                        $mail->to($dataMail['dataMembers']['email'], $receiverName)->subject('แจ้งยืนยันการลงทะเบียนหลักสูตร '.$dataMail['dataCourses']['title']);
                                        $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                    });
                                    /* END E-MAIL FUNCTION */
                                } else {
                                    if (empty($dataExist->last_login)) {
                                        $dataUpdate = $this->updateMembers($dataCourse, $dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 0);
                                        $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "This member has been updated."];
                                    } else {
                                        if (!$dataExist->level_groups()->where('level_groups.id', $arrDetail[11])->first()) {
                                            $dataExist->level_groups()->syncWithoutDetaching([$arrDetail[11]]);
                                            $dataUpdated[] = ["row" => $row] + $dataExist->toArray() + ["message" => "Added member to unit."];
                                        } else {
                                            $dataUpdated[] = ["row" => $row] + $dataExist->toArray() + ["message" => "This member already exists in this course."];
                                        }
                                    }
                                }
                            } else {
                                $dataUpdate = $this->updateMembers($dataCourse, $dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 3, 1);
                                $dataUpdated[] = ["row" => $row] + $dataUpdate->toArray() + ["message" => "Activated member."];
                                /* BEGIN E-MAIL FUNCTION */
                                // Notify Mail (Member's reactivated)
                                $url_login = config('constants._BASE_URL').$dataGroups->key."/login";
                                $url_courses = config('constants._BASE_URL').$dataGroups->key."/courses/".$dataCourse['id']."/info";
                                $dataMail = array(
                                    'dataMembers'=>$dataExist,
                                    'dataGroups'=> $dataGroups,
                                    'dataSubGroups' => $dataExist->sub_groups()->first(),
                                    'dataLevelGroups' => $dataExist->level_groups()->first(),
                                    'dataCourses'=>$dataCourse,
                                    'url_login' => $url_login,
                                    'url_courses' => $url_courses
                                );
                                Mail::send('courses-reactivated-mail', $dataMail, function($mail) use ($dataMail) {
                                    if ($dataMail['dataMembers']['is_foreign'] != 1) {
                                        $receiverName = $dataMail['dataMembers']['first_name']." ".$dataMail['dataMembers']['last_name'];
                                    } else {
                                        $receiverName = $dataMail['dataMembers']['first_name_en']." ".$dataMail['dataMembers']['last_name_en'];
                                    }
                                    $mail->to($dataMail['dataMembers']['email'], $receiverName)->subject('แจ้งการเปิดใช้งานสมาชิก '.$dataMail['dataGroups']['subject']);
                                    $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                });
                                /* END E-MAIL FUNCTION */
                            }
                        } else {
                            $countNewEmailExist = Members::where("email", $arrDetail[4])->where('groups_id', $dataGroups->id)->where('id', '!=', $dataExist->id)->count();
                            if ($countNewEmailExist > 0) {
                                $dataReject = $this->handleRejected($dataGroups, $dataReject, $arrDetail, $rowRejected, $row, "Duplicated new email.");
                                $rowRejected++;
                            } else {
                                if (empty($dataExist->last_login)) {
                                    $dataUpdate = $this->updateMembers($dataCourse, $dataGroups, $dataLevelGroups, $dataExist, $arrDetail, 1, 2, null, false);
                                    $dataMember = $dataUpdate;
                                } else {
                                    $dataExist->active = 1;
                                    $dataExist->active_remark = 2;
                                    $dataExist->save();
                                    $dataExist->level_groups()->syncWithoutDetaching([$arrDetail[11]]);
                                    $dataExist->courses()->syncWithoutDetaching([$dataCourse->id]);
                                    $dataMember = $dataExist;
                                }

                                $dataMember->sub_groupsList()->syncWithoutDetaching([$arrDetail[9] => ['active' => 2, 'email' => $arrDetail[4]]]);
                                $dataUpdated[] = ["row" => $row] + $dataMember->toArray() + ["message" => "This member has changing of subgroup."];

                                /* BEGIN E-MAIL FUNCTION */
                                // Notify Mail (Member's Group Changed)
                                $url_login = config('constants._BASE_URL').$dataGroups->key."/login";
                                $url_courses = config('constants._BASE_URL').$dataGroups->key."/courses/".$dataCourse->id."/info";
                                $dataMail = array(
                                    'dataMember'=>$dataMember,
                                    'dataGroups'=> $dataGroups,
                                    'dataOldSubGroups' => $dataMember->sub_groups()->first(),
                                    'dataNewSubGroups' => $dataMember->sub_groupsList()->where('active', 2)->orderBy('id', 'desc')->first(),
                                    'dataCourses'=>$dataCourse,
                                    'url_login' => $url_login,
                                    'url_courses' => $url_courses,
                                    'newEmail' => $arrDetail[4]
                                );
                                Mail::send('courses-change-subgroups-alert-mail', $dataMail, function($mail) use ($dataMail) {
                                    if ($dataMail['dataMember']['is_foreign'] != 1) {
                                        $receiverName = $dataMail['dataMember']['first_name']." ".$dataMail['dataMember']['last_name'];
                                    } else {
                                        $receiverName = $dataMail['dataMember']['first_name_en']." ".$dataMail['dataMember']['last_name_en'];
                                    }
                                    $mail->to([$dataMail['dataMember']['email'], $dataMail['newEmail']], $receiverName)->subject('แจ้งเปลี่ยนสถานะการเป็นสมาชิกจาก '.$dataMail['dataOldSubGroups']['title'].' เป็น '.$dataMail['dataNewSubGroups']['title']);
                                    $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                                });
                                /* END E-MAIL FUNCTION */
                            }
                        }
                    } else {
                        // Add New Member
                        $dataCreate = $this->createMembers($dataCourse, $dataGroups, $dataLevelGroups, $arrDetail);
                        if ($dataCreate) {
                            $dataInserted[] = ["row" => $row] + $dataCreate->toArray();

                            // Remove Pre-Approved
                            if ($dataGroups->field_approval == "full_name") {
                                $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where('first_name', $arrDetail[2])->where('last_name', $arrDetail[3])->first();
                            } else if ($dataGroups->field_approval == "id_card") {
                                $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where($dataGroups->field_approval, $arrDetail[6])->first();
                            } else if ($dataGroups->field_approval == "occupation_id") {
                                $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where('sub_groups_id', $arrDetail[9])->where($dataGroups->field_approval, $arrDetail[10])->first();
                            } else {
                                $dataPreApprovedExist = MembersPreApproved::where('groups_id', $dataGroups->id)->where('email', $arrDetail[4])->first();
                            }

                            if ($dataPreApprovedExist) {
                                if ($dataPreApprovedExist->courses) {
                                    $dataCreate->courses()->syncWithoutDetaching(array_pluck($dataPreApprovedExist->courses, 'id'));
                                    $dataPreApprovedExist->courses()->detach();
                                }

                                if ($dataPreApprovedExist->classrooms) {
                                    $dataCreate->classrooms()->syncWithoutDetaching(array_pluck($dataPreApprovedExist->classrooms, 'id'));
                                    $dataPreApprovedExist->classrooms()->detach();
                                }

                                $dataPreApprovedExist->delete();
                                $dataPreApprovedExist->level_groups()->detach();
                            }

                            /* BEGIN E-MAIL FUNCTION */
                            // Notify Mail (Confirm Register Course *use new member)
                            $dataMembers = Members::where('email', $arrDetail[4])->where('groups_id', $dataGroups->id)->first();
                            $url_login = config('constants._BASE_URL').$dataGroups->key."/login";
                            $url_courses = config('constants._BASE_URL').$dataGroups->key."/courses/".$dataCourse->id."/info";
                            $dataMail = array(
                                'dataMembers'=>$dataMembers,
                                'dataGroups'=> $dataGroups,
                                'dataSubGroups' => $dataMembers->sub_groups()->first(),
                                'dataLevelGroups' => $dataMembers->level_groups()->first(),
                                'dataCourses'=>$dataCourse,
                                'url_courses' => $url_courses,
                                'url_login' => $url_login
                            );
                            Mail::send('courses-register-newmembers-mail', $dataMail, function($mail) use ($dataMail) {
                                if ($dataMail['dataMembers']['is_foreign'] != 1) {
                                    $receiverName = $dataMail['dataMembers']['first_name']." ".$dataMail['dataMembers']['last_name'];
                                } else {
                                    $receiverName = $dataMail['dataMembers']['first_name_en']." ".$dataMail['dataMembers']['last_name_en'];
                                }
                                $mail->to($dataMail['dataMembers']['email'], $receiverName)->subject('แจ้งยืนยันการเป็นสมาชิก '.$dataMail['dataGroups']['subject']);
                                $mail->from(config('constants.EMAIL.USERNAME'), config('constants.EMAIL.NAME'));
                            });
                            /* END E-MAIL FUNCTION */
                        }
                    }
                }
            }

            $row++;
            // if ( $row == 100 ) break;
        }

        fclose($resourceFile);

        return ["dataInsert" => $dataInsert, "dataInserted" => $dataInserted, "dataUpdated" => $dataUpdated, "dataReject" => $dataReject];
    }

    private function checkPermission($case)
    {
        $authSession = Auth::user();

        switch (strtolower($case)) {
            case 'upload':
                return $authSession->upload_status == 1;
                break;

            default:
                return 'failed';
                break;
        }

        return false;

    }

    public function importPreApprovedMembers(Request $request, _FunctionsController $oFunc, $id)
    {
        ini_set('max_execution_time', 300);

        if ($this->checkPermission('upload') === false) {
            return response()->json(array('message' => config('constants._errorMessage._403')), 404);
        }

        $dataCourse = Courses::find($id);

        if (!$dataCourse) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "'Course'", config('constants._errorMessage._404'))), 404);
        }

        if (!$request->hasFile('file')) {
            $excelExtension = "";
        } else {
            $fileExcel = $request->file('file');
            $excelExtension = strtolower($fileExcel->getClientOriginalExtension());
        }

        $validator = Validator::make(
            [
                'file'   => $excelExtension,
                'groupId'=> $request['groupId'],
            ],
            [
                'file'   => 'required|in:csv',
                'groupId'=> 'required',
            ],
            [
                'file.in' => 'The :attribute must be one of the following types: .csv',
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $fileConverted = $oFunc->convertFileUTF8($fileExcel);
        if ($fileConverted['file'] === false) {
            if (!empty($fileConverted['encoding'])) {
                $message = "The encoding '".$fileConverted['encoding']."' is not supported.";
            } else {
                $message = "This file encoding not supported.";
            }

            return response()->json(['message' => $message], 422);
        }

        $dataGroups = Groups::find($request['groupId']);
        // $dataGroups = $dataCourse->sub_groups()->with('groups')->first()->groups;

        // $dataExcel = $this->readMembersPreApprovedExcel($fileExcel, 1, $dataCourse, $dataGroups);

        $dataExcel = $this->readMembersPreApprovedExcel($fileExcel, 1, $dataCourse, $dataGroups);

        $is_success = true;

        if (!empty($dataExcel['dataInserted']) && !empty($dataExcel['dataUpdated'])) {
            $message = "Successfully imported ".count($dataExcel['dataInserted'])." row(s) and updated ".count($dataExcel['dataUpdated'])." row(s)";
        } else if (!empty($dataExcel['dataInserted'])) {
            $message = "Successfully imported ".count($dataExcel['dataInserted'])." row(s).";
        } else if (!empty($dataExcel['dataUpdated'])) {
            $message = "Successfully updated ".count($dataExcel['dataUpdated'])." row(s).";
        } else {
            $is_success = false;
            $message = "Nothing members pre-approved was imported or updated.";
        }

        if (!$is_success) {
            $message = isset($message) ? $message : "Import failed.";
            return response()->json(array('is_error' => !$is_success, 'message' => $message, 'uploaded_members' => $dataExcel['dataInserted'], 'rejected_members' => $dataExcel['dataReject'], 'updated_members' => $dataExcel['dataUpdated']), 500);
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message, 'uploaded_members' => $dataExcel['dataInserted'], 'rejected_members' => $dataExcel['dataReject'], 'updated_members' => $dataExcel['dataUpdated']), 200);

    }

    public function importMembers(Request $request, _FunctionsController $oFunc, $id)
    {
        ini_set('max_execution_time', 300);

        if ($this->checkPermission('upload') === false) {
            return response()->json(array('message' => config('constants._errorMessage._403')), 404);
        }

        $dataCourse = Courses::find($id);

        if (!$dataCourse) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "'Course'", config('constants._errorMessage._404'))), 404);
        }

        if (!$request->hasFile('file')) {
            $excelExtension = "";
        } else {
            $fileExcel = $request->file('file');
            $excelExtension = strtolower($fileExcel->getClientOriginalExtension());
        }

        $validator = Validator::make(
            [
                'file'   => $excelExtension,
                'groupId'=> $request['groupId'],
            ],
            [
                'file'   => 'required|in:csv',
                'groupId'=> 'required',
            ],
            [
                'file.in' => 'The :attribute must be one of the following types: .csv',
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $fileConverted = $oFunc->convertFileUTF8($fileExcel);
        if ($fileConverted['file'] === false) {
            if (!empty($fileConverted['encoding'])) {
                $message = "The encoding '".$fileConverted['encoding']."' is not supported.";
            } else {
                $message = "This file encoding not supported.";
            }

            return response()->json(['message' => $message], 422);
        }

        $dataGroups = Groups::find($request['groupId']);
        // $dataGroups = $dataCourse->sub_groups()->with('groups')->first()->groups;

        $dataExcel = $this->readMembersExcel($fileExcel, 1, $dataCourse, $dataGroups);

        $is_success = true;

        if (!empty($dataExcel['dataInserted']) && !empty($dataExcel['dataUpdated'])) {
            $message = "Successfully imported ".count($dataExcel['dataInserted'])." row(s) and updated ".count($dataExcel['dataUpdated'])." row(s)";
        } else if (!empty($dataExcel['dataInserted'])) {
            $message = "Successfully imported ".count($dataExcel['dataInserted'])." row(s).";
        } else if (!empty($dataExcel['dataUpdated'])) {
            $message = "Successfully updated ".count($dataExcel['dataUpdated'])." row(s).";
        } else {
            $is_success = false;
            $message = "Nothing members was imported or updated.";
        }

        if (!$is_success) {
            $message = isset($message) ? $message : "Import failed.";
            return response()->json(array('is_error' => !$is_success, 'message' => $message, 'uploaded_members' => $dataExcel['dataInserted'], 'rejected_members' => $dataExcel['dataReject'], 'updated_members' => $dataExcel['dataUpdated']), 500);
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message, 'uploaded_members' => $dataExcel['dataInserted'], 'rejected_members' => $dataExcel['dataReject'], 'updated_members' => $dataExcel['dataUpdated']), 200);

    }

    public function detachMembers($id, Request $request)
    {
        //
        $data = Courses::find($id);

        if ($data) {
            $input = $request->json()->all();
            $membersIDs = array_pluck($input['members'], 'id');
            $is_success = $data->members()->detach($membersIDs);
        } else {
            $is_success = false;
        }

        if ($is_success) {
            $message = "The members has been detached from this course.";
        } else {
            $message = "Failed to detach the members from this course.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function getMembers(Request $request, _RolesController $oRole, $id)
    {
        // Check Permission Acces
        if (!$oRole->haveAccess($id, "courses")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $per_page = $request->input('per_page', 999);
        $order_by = $request->input('order_by', 'approved_datetime');
        $order_direction = $request->input('order_direction', 'DESC');

        $data = Courses::find($id);

        if (!$data) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "'Course'", config('constants._errorMessage._404'))), 404);
        }

        $dataMembers = $data->members()->whereNotNull('approved_type');

        if($request->has('search')){
            $dataMembers = $dataMembers->where(function ($query) use ($request) {
                $query->where('members.email', 'like', '%'.$request['search'].'%')
                      ->orWhere('members.first_name', 'like', '%'.$request['search'].'%')
                      ->orWhere('members.first_name_en', 'like', '%'.$request['search'].'%')
                      ->orWhere('members.last_name', 'like', '%'.$request['search'].'%')
                      ->orWhere('members.last_name_en', 'like', '%'.$request['search'].'%');
            });
        }

        $dataMembers = $dataMembers->orderBy($order_by, $order_direction)->paginate($per_page);

        for($i=0; $i<count($dataMembers); $i++) {
            $dataMembers[$i]->num = $dataMembers->lastItem() - $i;
            $dataMembers[$i]->approved_admin = Admins::select('first_name')->find($dataMembers[$i]->approved_by);
            $dataMembers[$i]->created_admin = Admins::select('first_name')->find($dataMembers[$i]->created_by);
        }

        return $dataMembers;
    }

    public function getMembersPreApproved(Request $request, _RolesController $oRole, $id)
    {
        // Check Permission Acces
        if (!$oRole->haveAccess($id, "courses")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $per_page = $request->input('per_page', 999);
        $order_by = $request->input('order_by', 'id');
        $order_direction = $request->input('order_direction', 'DESC');

        $data = Courses::find($id);

        if (!$data) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "'Course'", config('constants._errorMessage._404'))), 404);
        }

        $dataMembersPreApproved = $data->members_pre_approved();

        if($request->has('search')){
            $dataMembersPreApproved = $dataMembersPreApproved->where(function ($query) use ($request) {
                $query->where('members_pre_approved.email', 'like', '%'.$request['search'].'%')
                      ->orWhere('members_pre_approved.first_name', 'like', '%'.$request['search'].'%')
                      ->orWhere('members_pre_approved.first_name_en', 'like', '%'.$request['search'].'%')
                      ->orWhere('members_pre_approved.last_name', 'like', '%'.$request['search'].'%')
                      ->orWhere('members_pre_approved.last_name_en', 'like', '%'.$request['search'].'%');
            });
        }

        $dataMembersPreApproved = $dataMembersPreApproved->orderBy($order_by, $order_direction)->paginate($per_page);

        for($i=0; $i<count($dataMembersPreApproved); $i++) {
            $dataMembersPreApproved[$i]->num = $dataMembersPreApproved->lastItem() - $i;
            $dataMembersPreApproved[$i]->created_admin = Admins::select('first_name')->find($dataMembersPreApproved[$i]->created_by);
        }

        return $dataMembersPreApproved;
    }

    // public function create_live_event()
    // {
    //     $oFunc = new _FunctionsController;

    //     $authentication_data = $oFunc->liveTranscodeAuthentication('/live_events');

    //     $body = $oFunc->createXMLLiveEvent($data->streaming_prefix_streamname, $data->streaming_streamname);

    //     $client = new httpClient();

    //     try {
    //         $response = $client->request('POST', env('LIVE_TRANSCODE_SERVER').'/api/live_events', [
    //             'headers' => [
    //                 'Accept' => 'application/xml',
    //                 'X-Auth-User' => 'admin',
    //                 'X-Auth-Expires' => $authentication_data['expires'],
    //                 'X-Auth-Key' => $authentication_data['key'],
    //                 'Content-Type' => 'application/xml'
    //             ],
    //             'body' => $body
    //         ]);
    //     } catch(RequestException $e) {
    //         if ($e->hasResponse()) {
    //             return response($this->namespacedXMLToArray($e->getResponse()->getBody()), $e->getResponse()->getStatusCode());
    //         }
    //     }

    //     $dataXML = $this->namespacedXMLToArray($response->getBody());

    //     $event_name = 'DooTV_'.$data->streaming_prefix_streamname;
    //     if ($dataXML['name'] != $event_name) {

    //     }

    //     return response()->json($dataXML, 200);
    // }

    public function live_event_status($id)
    {
        $oFunc = new _FunctionsController;
        $client = new httpClient();

        $url_start_live_event = '/live_events/'.$id.'/status';
        $authentication_data = $oFunc->liveTranscodeAuthentication($url_start_live_event);

        try {
            $response = $client->request('GET', env('LIVE_TRANSCODE_API').$url_start_live_event, [
                'headers' => [
                    'Accept' => 'application/xml',
                    'X-Auth-User' => 'admin',
                    'X-Auth-Expires' => $authentication_data['expires'],
                    'X-Auth-Key' => $authentication_data['key'],
                    'Content-Type' => 'application/xml'
                ]
            ]);
        } catch(RequestException $e) {
            if ($e->hasResponse()) {
                return response($this->namespacedXMLToArray($e->getResponse()->getBody()), $e->getResponse()->getStatusCode());
            }
        }

        $dataXML = $this->namespacedXMLToArray($response->getBody());

        return response()->json($dataXML, 200);
    }

    public function create_smil(Request $request)
    {
        $client = new httpClient();
        $params = array(
            "streaming_prefix_streamname" => $request['streaming_prefix_streamname'],
        );

        try {
            $response = $client->request('POST', env('BASE_URL_API_MEDIA').'ffmpeg/create-smil-live.php', [
                'json' => $params
            ]);
        } catch(RequestException $e) {
            if ($e->hasResponse()) {
                $respData['statusCode'] = $e->getResponse()->getStatusCode();
                $respData['errorInfo'] = json_decode($e->getResponse()->getBody());
            }
        }

        $respData = json_decode($response->getBody(), true);

        return response()->json($respData, 200);
    }

    public function getBroadcastSignal($id)
    {
        $oFunc = new _FunctionsController;
        $client = new httpClient();

        $topic = Topics::find($id);

        $url_get_status = '/live_events/'.$topic->live_event_id.'/status';
        $authentication_data = $oFunc->liveTranscodeAuthentication($url_get_status);

        try {
            $response = $client->request('GET', env('LIVE_TRANSCODE_API').$url_get_status, [
                'headers' => [
                    'Accept' => 'application/xml',
                    'X-Auth-User' => 'admin',
                    'X-Auth-Expires' => $authentication_data['expires'],
                    'X-Auth-Key' => $authentication_data['key'],
                ]
            ]);
        } catch(RequestException $e) {
            
            if ($e->hasResponse()) {
                $message = "Failed to get live event status.";

                return response()->json(array('is_error' => true, 'message' => $message), 200);
                return response()->json(array('hasSignal' => false), 200);
                // return response($this->namespacedXMLToArray($e->getResponse()->getBody()), $e->getResponse()->getStatusCode());
            }
        }

        $dataStatusXML = $this->namespacedXMLToArray($response->getBody());

        // return response()->json($dataStatusXML, 200);

        if ($dataStatusXML['status'] == 'complete' || $dataStatusXML['status'] == 'cancelled') {
            $url_reset_event = '/live_events/'.$topic->live_event_id.'/reset';
            $authentication_data = $oFunc->liveTranscodeAuthentication($url_reset_event);

            try {
                $response = $client->request('POST', env('LIVE_TRANSCODE_API').$url_reset_event, [
                    'headers' => [
                        'Accept' => 'application/xml',
                        'X-Auth-User' => 'admin',
                        'X-Auth-Expires' => $authentication_data['expires'],
                        'X-Auth-Key' => $authentication_data['key'],
                    ]
                ]);
            } catch(RequestException $e) {
                
                if ($e->hasResponse()) {
                    $message = "Failed to reset live event.";

                    return response()->json(array('is_error' => true, 'message' => $message), 200);
                    // return response($this->namespacedXMLToArray($e->getResponse()->getBody()), $e->getResponse()->getStatusCode());
                }
            }

            $dataResetXML = $this->namespacedXMLToArray($response->getBody());

            if ($dataResetXML['input']['status'] == 'pending') {
                $url_start_live_event = '/live_events/'.$topic->live_event_id.'/start';
                $authentication_data = $oFunc->liveTranscodeAuthentication($url_start_live_event);

                try {
                    $response = $client->request('POST', env('LIVE_TRANSCODE_API').$url_start_live_event, [
                        'headers' => [
                            'Accept' => 'application/xml',
                            'X-Auth-User' => 'admin',
                            'X-Auth-Expires' => $authentication_data['expires'],
                            'X-Auth-Key' => $authentication_data['key'],
                            'Content-Type' => 'application/xml'
                        ]
                    ]);
                } catch(RequestException $e) {
                    if ($e->hasResponse()) {
                        return response($this->namespacedXMLToArray($e->getResponse()->getBody()), $e->getResponse()->getStatusCode());
                    }
                }

                $dataStartXML = $this->namespacedXMLToArray($response->getBody());

                if ($dataStartXML['id'] != $topic->live_event_id) {
                    $message = "Failed to start live event.";

                    return response()->json(array('is_error' => true, 'message' => $message), 200);
                } else {
                    sleep(5);
                }
            }
        }


        $live_event = 'DooTV_' . $topic->streaming_prefix_streamname;

        $url_get_alerts = '/alerts';
        $authentication_data = $oFunc->liveTranscodeAuthentication($url_get_alerts);

        try {
            $response = $client->request('GET', env('LIVE_TRANSCODE_API').$url_get_alerts, [
                'headers' => [
                    'Accept' => 'application/xml',
                    'X-Auth-User' => 'admin',
                    'X-Auth-Expires' => $authentication_data['expires'],
                    'X-Auth-Key' => $authentication_data['key'],
                ]
            ]);
        } catch(RequestException $e) {
            
            if ($e->hasResponse()) {
                return response()->json(array('hasSignal' => false), 200);
                // return response($this->namespacedXMLToArray($e->getResponse()->getBody()), $e->getResponse()->getStatusCode());
            }
        }

        $dataXML = $this->namespacedXMLToArray($response->getBody());

        if ($dataXML) {
            $noSignal = array();
            $error_message = array(
                'video' => '['.$topic->live_event_id.'] Video not detected: Check input signal',
                'audio' => '['.$topic->live_event_id.'] Audio not detected: Check input signal',
                'waiting_input' => 'Waiting for RTMP input',
            );

            $hasSignal = true;
            if (isset($dataXML['input_alert'])) {
                foreach ($dataXML['input_alert'] as $key => $value) {
                    if ($value['live_event'] == $live_event) {
                        $hasSignal = false;
                        break;
                    }
                }
            }

            return response()->json(array('hasSignal' => $hasSignal), 200);
        } else {
            return response()->json(array('hasSignal' => false), 200);
        }
    }

    public function updateLiveResults($topic_id, Request $request)
    {
        $data = LiveResults::where('topic_id', $topic_id)->first();
        $input = $request->json()->all();
        $data->fill($input);

        if (isset($input['streaming_status']) && $input['streaming_status'] == 1) {
            $data->live_start_datetime = date('Y-m-d H:i:s');
        } else if (isset($input['streaming_status']) && $input['streaming_status'] == 0) {
            $data->live_end_datetime = date('Y-m-d H:i:s');
        }

        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The live results has been updated.";
        } else {
            $message = "Failed to update the live results.";
        }
        
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function getLiveResults($topic_id, Request $request)
    {
        $data = LiveResults::where('topic_id', $topic_id)->first();

        return response()->json($data, 200);
    }

    public function resetLiveControl(Request $request)
    {
        $data = Topics::find('440');

        // $data->state = 'live';
        // $data->streaming_status = 0;
        // $data->streaming_url = 'https://setlive-stream-cdn.open-cdn.com/live/smil:t_440UgI4WUnr.smil/playlist.m3u8';
        // $data->is_stop_record = 0;
        // $data->is_stop_stream = 0;
        // $data->is_auto_convert = 0;

        $prefix = '';
        if ($request['action'] == 'reset') {
            $prefix = 't_440UgI4WUnr';
        } else if ($request['action'] == 'change') {
            $prefix = 't_444fAEG7IM9';
        }

        $data->streaming_prefix_streamname = $prefix;

        $data->save();
    }

}
