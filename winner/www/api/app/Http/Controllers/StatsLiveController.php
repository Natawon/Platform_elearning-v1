<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;

use App\Models\Admins;
use App\Models\Logs;
use App\Models\Enroll;
use App\Models\Courses;
use App\Models\Topics;
use App\Models\Groups;
use App\Models\SubGroups;
use App\Models\LevelGroups;
use App\Models\ClassRooms;
use App\Models\Enroll2Quiz;
use App\Models\Quiz;
use App\Models\Questions2Answer;
use App\Models\Members;
use App\Models\LiveResults;
use App\Models\Enroll2TopicLive;
use App\Models\Member2Live;

use Input;
use DB;
use GeoIP;
use Auth;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class StatsLiveController extends Controller
{

    public function course($id)
    {
        $data = Courses::find($id);
        return response()->json($data, 200);
    }

    public function info(Request $request, _RolesController $oRole)
    {
        if (!$request['from_date']) {
            $fromDate = date('Y-m-d', strtotime("-1 month"));
        } else {
            $fromDate = date("Y-m-d", strtotime($request['from_date']));
        }

        if (!$request['to_date']) {
            $toDate = date('Y-m-d');
        } else {
            $toDate = date("Y-m-d", strtotime($request['to_date']));
        }

        if (!$request['from_time']) {
            $fromTime = "00:00";
        } else {
            $fromTime = date("H:i", strtotime($request['from_time']));
        }

        if (!$request['to_time']) {
            $toTime = "23:59";
        } else {
            $toTime = date("H:i", strtotime($request['to_time']));
        }

        $from_datetime = date("Y-m-d H:i:s", strtotime($fromDate." ".$fromTime));
        $to_datetime = date("Y-m-d H:i:s", strtotime($toDate." ".$toTime));

        $authSession = Auth::user();
        if($authSession->super_users){
            $authSessionGroups = $authSession->groups()->get();
        }else{
            $authSessionGroups = $authSession->admins_groups()->first();
            $authSessionGroups = $authSessionGroups->groups()->get();
        }

        $sub_groups = '';
        $level_groups = '';
        $classrooms = '';

        if($request['courses_id']){
            $courses = new Courses();
            $courses = $courses->where('id', $request['courses_id']);
            $courses = $courses->get();
        }else{
            $courses = new Courses();
            if ($authSession->super_users) {
                $courses = $courses->where('admins_id', $authSession->id);
                $courses = $courses->orWhere('level_public', 1);
                $courses = $courses->whereHas('groups', function($query) use ($authSessionGroups) {
                    $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            } else if (!$oRole->isSuper()) {
                $courses = $courses->whereHas('groups', function($query) use ($authSessionGroups) {
                    $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            }
            $courses = $courses->get();
        }

        if($request['groups_id']){
            $groups = new Groups();
            $groups = $groups->where('id', $request['groups_id']);
            $groups = $groups->get();
        }else{
            $groups = $authSessionGroups;
        }

        if($request['sub_groups_id']){
            $sub_groups = new SubGroups();
            $sub_groups = $sub_groups->find($request['sub_groups_id']);
        }else if ($authSession->super_users) {
            $sub_groups = new SubGroups();
            $sub_groups = $sub_groups->find($authSession->sub_groups_id);
        }

        if($request['level_groups_id']){
            $level_groups = new LevelGroups();
            $level_groups = array($level_groups->find($request['level_groups_id']));
        }else if ($authSession->super_users) {
            $level_groups_owner = $authSession->level_groups()->get();
            $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
            $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());
        }

        if($request['classrooms_id']){
            $classrooms = new ClassRooms();
            $classrooms = $classrooms->find($request['classrooms_id']);
        }

        return response()->json(array('courses' => $courses, 'groups' => $groups, 'sub_groups' => $sub_groups, 'level_groups' => $level_groups, 'classrooms' => $classrooms, 'from_datetime' => $from_datetime, 'to_datetime' => $to_datetime), 200);

    }

    public function enroll(Request $request, _RolesController $oRole, _FunctionsController $oFunc)
    {

        if (!$request['from_date']) {
            $fromDate = date('Y-m-d', strtotime("-1 month"));
        } else {
            $fromDate = date("Y-m-d", strtotime($request['from_date']));
        }

        if (!$request['to_date']) {
            $toDate = date('Y-m-d');
        } else {
            $toDate = date("Y-m-d", strtotime($request['to_date']));
        }

        if (!$request['from_time']) {
            $fromTime = "00:00";
        } else {
            $fromTime = date("H:i", strtotime($request['from_time']));
        }

        if (!$request['to_time']) {
            $toTime = "23:59";
        } else {
            $toTime = date("H:i", strtotime($request['to_time']));
        }

        $from_datetime = date("Y-m-d H:i:s", strtotime($fromDate." ".$fromTime));
        $to_datetime = date("Y-m-d H:i:s", strtotime($toDate." ".$toTime));

        $authSession = Auth::user();
        if($authSession->super_users){
            $authSessionGroups = $authSession->groups()->get();
        }else{
            $authSessionGroups = $authSession->admins_groups()->first();
            $authSessionGroups = $authSessionGroups->groups()->get();
        }

        //All Enroll
        $enroll = new Enroll;
        if ($request['courses_id']) {

            $enroll = $enroll->where('courses_id', $request['courses_id']);
            $enroll = $enroll->whereHas('courses', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });

        } else {
            if ($authSession->super_users) {
                $enroll = $enroll->whereHas('courses', function($query) use ($authSession, $authSessionGroups) {
                    $query->where('admins_id', $authSession->id);
                    $query->orWhere('level_public', 1);
                    $query->whereHas('groups', function($sub_query) use ($authSessionGroups) {
                        $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                    });
                });
            } else if (!$oRole->isSuper()) {

                $enroll = $enroll->whereHas('courses', function($query) use ($authSessionGroups) {
                    $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });

            }
        }

        if ($request['groups_id']) {
            $enroll = $enroll->where('groups_id', $request['groups_id']);
        }

        if ($request['sub_groups_id']) {
            $enroll = $enroll->where('sub_groups_id', $request['sub_groups_id']);
        } else if ($authSession->super_users) {
            $enroll = $enroll->where('sub_groups_id', $authSession->sub_groups_id);
        }

        if ($request['level_groups_id']) {

            $level_groups = $request['level_groups_id'];

            $enroll = $enroll->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->where('enroll_type_id', $level_groups);
            });

        } else if ($authSession->super_users) {
            $level_groups_owner = $authSession->level_groups()->get();
            $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
            $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

            $enroll = $enroll->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
            });
        }

        if($request['classrooms_id']){
            $enroll = $enroll->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $request['classrooms_id']);
        }
        // $enroll = $enroll->whereBetween('enroll_datetime', array($from_datetime, $to_datetime));
        // $enroll = $enroll->get();
        $enroll = $enroll->orderBy('id', 'asc')->get();

        $c_enroll = count($enroll);
        $enroll_data = [];

        for ($i=0; $i < $c_enroll; $i++) {
            $enroll_data[] = $enroll[$i]->id;
        }

        $enroll_live = new Enroll2TopicLive;
        $enroll_live = $enroll_live->select('topics_id', 'enroll_id', 'enter_datetime')
                    ->whereIn('enroll_id', $enroll_data);

        if ($request['topics_id']) {
            $enroll_live = $enroll_live->where('topics_id', $request['topics_id']);
        }

        $enroll_live = $enroll_live->groupBy('topics_id');
        $enroll_live = $enroll_live->groupBy('enroll_id');
        $enroll_live = $enroll_live->whereBetween('enter_datetime', array($from_datetime, $to_datetime))->get();

        //Page Views
        $page_views = new Member2Live;
        if ($request['courses_id']) {
            $page_views = $page_views->where('courses_id', $request['courses_id']);
            $page_views = $page_views->whereHas('courses', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });
        } else {
            if ($authSession->super_users) {
                $page_views = $page_views->whereHas('courses', function($query) use ($authSession, $authSessionGroups) {
                    $query->where('admins_id', $authSession->id);
                    $query->orWhere('level_public', 1);
                    $query->whereHas('groups', function($sub_query) use ($authSessionGroups) {
                        $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                    });
                });
            } else if (!$oRole->isSuper()) {
                $page_views = $page_views->whereHas('courses', function($query) use ($authSessionGroups) {
                    $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            }
        }

        if ($request['topics_id']) {
            $page_views = $page_views->where('topics_id', $request['topics_id']);
        }

        if ($request['groups_id']) {
            $page_views = $page_views->where('groups_id', $request['groups_id']);
        }

        if ($request['sub_groups_id']) {
            $page_views = $page_views->where('sub_groups_id', $request['sub_groups_id']);
        } else if ($authSession->super_users) {
            $page_views = $page_views->where('sub_groups_id', $authSession->sub_groups_id);
        }

        if ($request['level_groups_id']) {

            $level_groups = $request['level_groups_id'];

            $page_views = $page_views->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->where('enroll_type_id', $level_groups);
            });

        } else if ($authSession->super_users) {
            $level_groups_owner = $authSession->level_groups()->get();
            $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
            $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

            $page_views = $page_views->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
            });
        }

        if ($request['classrooms_id']) {
            $page_views = $page_views->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $request['classrooms_id']);

        }
        // $page_views = $page_views->whereIn('type', ['เข้าเรียน', 'ลงทะเบียน']);
        $page_views = $page_views->whereBetween('datetime', array($from_datetime, $to_datetime));
        $page_views = $page_views->get();

        //UIP
        $uip = new Member2Live;
        if($request['courses_id']){
            $uip = $uip->where('courses_id', $request['courses_id']);
            $uip = $uip->whereHas('courses', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });
        }else{
            if ($authSession->super_users) {
                $uip = $uip->whereHas('courses', function($query) use ($authSession, $authSessionGroups) {
                    $query->where('admins_id', $authSession->id);
                    $query->orWhere('level_public', 1);
                    $query->whereHas('groups', function($sub_query) use ($authSessionGroups) {
                        $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                    });
                });
            } else if (!$oRole->isSuper()) {
                $uip = $uip->whereHas('courses', function($query) use ($authSessionGroups) {
                    $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            }
        }

        if($request['groups_id']){
            $uip = $uip->where('groups_id', $request['groups_id']);
        }

        if($request['sub_groups_id']){
            $uip = $uip->where('sub_groups_id', $request['sub_groups_id']);
        }else if ($authSession->super_users) {
            $uip = $uip->where('sub_groups_id', $authSession->sub_groups_id);
        }

        if($request['level_groups_id']){

            $level_groups = $request['level_groups_id'];

            $uip = $uip->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->where('enroll_type_id', $level_groups);
            });

        }else if ($authSession->super_users) {
            $level_groups_owner = $authSession->level_groups()->get();
            $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
            $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

            $uip = $uip->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
            });
        }

        if($request['classrooms_id']){
            $uip = $uip->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $request['classrooms_id']);
        }

        // $uip = $uip->whereIn('type', ['เข้าเรียน', 'ลงทะเบียน']);
        $uip = $uip->whereBetween('datetime',array($from_datetime, $to_datetime));
        $uip = $uip->groupBy('ip');
        $uip = $uip->get();

        // Average study duration
        $avg_data = new Enroll2TopicLive;
        $avg_data = $avg_data->select(DB::raw('sum(duration) as duration, topics_id, enroll_id'))
                    ->whereIn('enroll_id', $enroll_data);

        if ($request['topics_id']) {
            $avg_data = $avg_data->where('topics_id', $request['topics_id']);
            $avg_data = $avg_data->groupBy('topics_id');
        }

        $avg_data = $avg_data->first();
        if ($avg_data) {
            $avg_data->duration = (int) $avg_data->duration;

            if ($avg_data->duration != 0) {
                $d1 = Carbon::now();
                $d2 = Carbon::now();
                $d2->addSeconds($avg_data->duration);

                $d_diff = $d2->diff($d1);
                $d_diff_format = '';
                if ($d_diff->y > 0) {
                    $d_diff_format .= '%y ปี ';
                }

                if ($d_diff->m > 0) {
                    $d_diff_format .= '%m เดือน ';
                }

                if ($d_diff->d > 0) {
                    $d_diff_format .= '%a วัน ';
                }

                if ($d_diff->h > 0) {
                    $d_diff_format .= '%h ชั่วโมง ';
                }

                if ($d_diff->i > 0) {
                    $d_diff_format .= '%i นาที ';
                }

                if ($d_diff->s > 0) {
                    $d_diff_format .= '%s วินาที';
                }

                $avg_duration = $d_diff->format($d_diff_format);
            } else {
                $avg_duration = '0 วินาที';
            }
        } else {
            $avg_duration = '0 วินาที';
        }


        // Most enter class
        $most_data = new Enroll2TopicLive;
        // $most_data = $most_data->select(DB::raw('DATE_FORMAT(enter_datetime, "%Y-%m-%d %H:%i") as most_enter_class, topics_id, enroll_id'))
        //             ->whereIn('enroll_id', $enroll_data);

        $most_data = $most_data->select(DB::raw('DATE_FORMAT(enter_datetime, "%Y-%m-%d %H:%i") as most_enter_class, topics_id, enroll_id, COUNT(*) as views'))
                    ->whereIn('enroll_id', $enroll_data);

        if ($request['topics_id']) {
            $most_data = $most_data->where('topics_id', $request['topics_id']);
        }

        $most_data = $most_data->groupBy('most_enter_class');
        $most_data = $most_data->groupBy('topics_id');
        $most_data = $most_data->orderBy('views', 'desc');


        $most_data = $most_data->first();
        if ($most_data) {
            $most_enter_class = $oFunc->thai_date_and_time_human_full(strtotime($most_data->most_enter_class));
        } else {
            $most_enter_class = '-';
        }


        return response()->json(
            array(
                'enroll' => count($enroll_live),
                'page_views' => count($page_views),
                'uip' => count($uip),
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'avg_duration' => $avg_duration,
                'most_enter_class' => $most_enter_class,
            ), 200);

    }

    public function log(Request $request, _RolesController $oRole)
    {
        if (!$request['from_date']) {
            $fromDate = date('Y-m-d', strtotime("-1 month"));
        } else {
            $fromDate = date("Y-m-d", strtotime($request['from_date']));
        }

        if (!$request['to_date']) {
            $toDate = date('Y-m-d');
        } else {
            $toDate = date("Y-m-d", strtotime($request['to_date']));
        }

        if (!$request['from_time']) {
            $fromTime = "0";
        } else {
            $fromTime = date("H", strtotime($request['from_time']));
        }

        if (!$request['to_time']) {
            $toTime = "23";
            $toTimeMinute = "00";
        } else {
            $toTime = date("H", strtotime($request['to_time']));
            $toTimeMinute = date("i", strtotime($request['to_time']));
        }

        $authSession = Auth::user();
        if($authSession->super_users){
            $authSessionGroups = $authSession->groups()->get();
        }else{
            $authSessionGroups = $authSession->admins_groups()->first();
            $authSessionGroups = $authSessionGroups->groups()->get();
        }

        $respData = array();
        $data = array();
        $data_chart = array();

        $viewData = Enroll::select('id', 'courses_id', 'groups_id', 'sub_groups_id', 'enroll_type', 'enroll_type_id');
        if($request['courses_id']){
            $viewData = $viewData->where('courses_id', $request['courses_id']);
            $viewData = $viewData->whereHas('courses', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });
        }else{
            if ($authSession->super_users) {
                $viewData = $viewData->whereHas('courses', function($query) use ($authSession, $authSessionGroups) {
                    $query->where('admins_id', $authSession->id);
                    $query->orWhere('level_public', 1);
                    $query->whereHas('groups', function($sub_query) use ($authSessionGroups) {
                        $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                    });
                });
            } else if (!$oRole->isSuper()) {
                $viewData = $viewData->whereHas('courses', function($query) use ($authSessionGroups) {
                    $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            }
        }

        if($request['groups_id']){
            $viewData = $viewData->where('groups_id', $request['groups_id']);
        }

        if($request['sub_groups_id']){
            $viewData = $viewData->where('sub_groups_id', $request['sub_groups_id']);
        }else if ($authSession->super_users) {
            $viewData = $viewData->where('sub_groups_id', $authSession->sub_groups_id);
        }

        if($request['level_groups_id']){

            $viewData = $viewData->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->where('enroll_type_id', $level_groups);
            });

        }else if ($authSession->super_users) {
            $level_groups_owner = $authSession->level_groups()->get();
            $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
            $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

            $viewData = $viewData->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
            });
        }

        if($request['classrooms_id']){
            $viewData = $viewData->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $request['classrooms_id']);
        }

        $viewData = $viewData->get();
        $c_enroll = count($viewData);
        $enroll_data = [];

        for ($i=0; $i < $c_enroll; $i++) {
            $enroll_data[] = $viewData[$i]->id;
        }

        if ($fromDate == $toDate) {
            $countMinute = 0;
            for ($i=$fromTime; $i<=$toTime; $i++) {
                if ($i == $toTime) {
                    $allMinutes = $toTimeMinute + 1;
                } else {
                    $allMinutes = 60;
                }
                for ($j=0; $j<$allMinutes; $j++) {
                    $enroll_live = new Enroll2TopicLive;
                    $enroll_live = $enroll_live->select('id', 'enroll_id', 'topics_id', 'enter_datetime')
                                                ->whereIn('enroll_id', $enroll_data);
                    if ($request['topics_id']) {
                        $enroll_live = $enroll_live->where('topics_id', $request['topics_id']);
                    }
                    $enroll_live = $enroll_live->where('enter_datetime', 'like', '%'.$fromDate.' '.str_pad($i, 2, "0", STR_PAD_LEFT).':'.str_pad($j, 2, "0", STR_PAD_LEFT).'%');
                    $enroll_live = $enroll_live->groupBy('topics_id');
                    $enroll_live = $enroll_live->groupBy('enroll_id');
                    $views = $enroll_live->get();
                    $data[$countMinute][] = str_pad($i, 2, "0", STR_PAD_LEFT).":".str_pad($j, 2, "0", STR_PAD_LEFT);
                    $data[$countMinute][] = count($views);
                    $data_chart[$countMinute][] = (int)(strtotime($fromDate.' '.str_pad($i, 2, "0", STR_PAD_LEFT).':'.str_pad($j, 2, "0", STR_PAD_LEFT).':00')."000");
                    $data_chart[$countMinute][] = count($views);
                    $countMinute++;
                }
            }
            $respData["type"] = 'hour';
            $respData["data"] = $data;
            $respData["data_chart"] = $data_chart;
        } else {
            $dataDates = $this->dateRange($fromDate, $toDate);
            $count = 0;
            // $viewData = new Enroll();
            foreach ($dataDates as $dataDate) {

                $enroll_live = new Enroll2TopicLive;
                $enroll_live = $enroll_live->select('id', 'enroll_id', 'topics_id', 'enter_datetime')
                    ->whereIn('enroll_id', $enroll_data);

                if ($request['topics_id']) {
                    $enroll_live = $enroll_live->where('topics_id', $request['topics_id']);
                }

                $enroll_live = $enroll_live->whereDate('enter_datetime', '=', $dataDate);
                $enroll_live = $enroll_live->groupBy('enroll_id');
                $enroll_live = $enroll_live->groupBy('topics_id');
                $enroll_live = $enroll_live->get()->count();

                $data[$count][] = $dataDate;
                $data[$count][] = $enroll_live;
                $data_chart[$count][] = (int)(strtotime($dataDate)."000");
                $data_chart[$count][] = $enroll_live;
                $count++;
            }
            // $respData["dataDates"] = $dataDates;
            $respData["type"] = 'day';
            $respData["data"] = $data;
            $respData["data_chart"] = $data_chart;
        }
        return response()->json($respData, 200);
    }

    public function device(Request $request, _RolesController $oRole)
    {

        if (!$request['from_date']) {
            $fromDate = date('Y-m-d', strtotime("-1 month"));
        } else {
            $fromDate = date("Y-m-d", strtotime($request['from_date']));
        }

        if (!$request['to_date']) {
            $toDate = date('Y-m-d');
        } else {
            $toDate = date("Y-m-d", strtotime($request['to_date']));
        }

        if (!$request['from_time']) {
            $fromTime = "00:00";
        } else {
            $fromTime = date("H:i", strtotime($request['from_time']));
        }

        if (!$request['to_time']) {
            $toTime = "23:59";
        } else {
            $toTime = date("H:i", strtotime($request['to_time']));
        }

        $authSession = Auth::user();
        if($authSession->super_users){
            $authSessionGroups = $authSession->groups()->get();
        }else{
            $authSessionGroups = $authSession->admins_groups()->first();
            $authSessionGroups = $authSessionGroups->groups()->get();
        }

        $data_all_windows = new Member2Live;
        $data_all_osx = new Member2Live;
        $data_all_linux = new Member2Live;
        $data_all_ios = new Member2Live;
        $data_all_android = new Member2Live;

        if($request['courses_id']){
            $data_all_windows = $data_all_windows->where('courses_id', $request['courses_id']);
            $data_all_windows = $data_all_windows->whereHas('courses', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });

            $data_all_osx = $data_all_osx->where('courses_id', $request['courses_id']);
            $data_all_osx = $data_all_osx->whereHas('courses', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });

            $data_all_linux = $data_all_linux->where('courses_id', $request['courses_id']);
            $data_all_linux = $data_all_linux->whereHas('courses', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });

            $data_all_ios = $data_all_ios->where('courses_id', $request['courses_id']);
            $data_all_ios = $data_all_ios->whereHas('courses', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });

            $data_all_android = $data_all_android->where('courses_id', $request['courses_id']);
            $data_all_android = $data_all_android->whereHas('courses', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });
        }else{
            if ($authSession->super_users) {
                $data_all_windows = $data_all_windows->whereHas('courses', function($query) use ($authSession, $authSessionGroups) {
                    $query->where('admins_id', $authSession->id);
                    $query->orWhere('level_public', 1);
                    $query->whereHas('groups', function($sub_query) use ($authSessionGroups) {
                        $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                    });
                });

                $data_all_osx = $data_all_osx->whereHas('courses', function($query) use ($authSession) {
                    $query->where('admins_id', $authSession->id);
                });

                $data_all_linux = $data_all_linux->whereHas('courses', function($query) use ($authSession) {
                    $query->where('admins_id', $authSession->id);
                });

                $data_all_ios = $data_all_ios->whereHas('courses', function($query) use ($authSession) {
                    $query->where('admins_id', $authSession->id);
                });

                $data_all_android = $data_all_android->whereHas('courses', function($query) use ($authSession) {
                    $query->where('admins_id', $authSession->id);
                });
            } else if (!$oRole->isSuper()) {
                $data_all_windows = $data_all_windows->whereHas('courses', function($query) use ($authSessionGroups) {
                    $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });

                $data_all_osx = $data_all_osx->whereHas('courses', function($query) use ($authSessionGroups) {
                    $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });

                $data_all_linux = $data_all_linux->whereHas('courses', function($query) use ($authSessionGroups) {
                    $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });

                $data_all_ios = $data_all_ios->whereHas('courses', function($query) use ($authSessionGroups) {
                    $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });

                $data_all_android = $data_all_android->whereHas('courses', function($query) use ($authSessionGroups) {
                    $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            }
        }

        if ($request['topics_id']) {
            $data_all_windows = $data_all_windows->where('topics_id', $request['topics_id']);
            // $data_all_windows = $data_all_windows->whereHas('courses', function($query) use ($authSessionGroups) {
            //     $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            // });

            $data_all_osx = $data_all_osx->where('topics_id', $request['topics_id']);
            // $data_all_osx = $data_all_osx->whereHas('courses', function($query) use ($authSessionGroups) {
            //     $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            // });

            $data_all_linux = $data_all_linux->where('topics_id', $request['topics_id']);
            // $data_all_linux = $data_all_linux->whereHas('courses', function($query) use ($authSessionGroups) {
            //     $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            // });

            $data_all_ios = $data_all_ios->where('topics_id', $request['topics_id']);
            // $data_all_ios = $data_all_ios->whereHas('courses', function($query) use ($authSessionGroups) {
            //     $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            // });

            $data_all_android = $data_all_android->where('topics_id', $request['topics_id']);
            // $data_all_android = $data_all_android->whereHas('courses', function($query) use ($authSessionGroups) {
            //     $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            // });
        }

        if($request['groups_id']){
            $data_all_windows = $data_all_windows->where('groups_id', $request['groups_id']);
            $data_all_osx = $data_all_osx->where('groups_id', $request['groups_id']);
            $data_all_linux = $data_all_linux->where('groups_id', $request['groups_id']);
            $data_all_ios = $data_all_ios->where('groups_id', $request['groups_id']);
            $data_all_android = $data_all_android->where('groups_id', $request['groups_id']);
        }

        if($request['sub_groups_id']){
            $data_all_windows = $data_all_windows->where('sub_groups_id', $request['sub_groups_id']);
            $data_all_osx = $data_all_osx->where('sub_groups_id', $request['sub_groups_id']);
            $data_all_linux = $data_all_linux->where('sub_groups_id', $request['sub_groups_id']);
            $data_all_ios = $data_all_ios->where('sub_groups_id', $request['sub_groups_id']);
            $data_all_android = $data_all_android->where('sub_groups_id', $request['sub_groups_id']);
        }else if ($authSession->super_users) {
            $data_all_windows = $data_all_windows->where('sub_groups_id', $authSession->sub_groups_id);
            $data_all_osx = $data_all_osx->where('sub_groups_id', $authSession->sub_groups_id);
            $data_all_linux = $data_all_linux->where('sub_groups_id', $authSession->sub_groups_id);
            $data_all_ios = $data_all_ios->where('sub_groups_id', $authSession->sub_groups_id);
            $data_all_android = $data_all_android->where('sub_groups_id', $authSession->sub_groups_id);
        }

        if($request['level_groups_id']){

            $level_groups = $request['level_groups_id'];

            $data_all_windows = $data_all_windows->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->where('enroll_type_id', $level_groups);
            });

            $data_all_osx = $data_all_osx->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->where('enroll_type_id', $level_groups);
            });

            $data_all_linux = $data_all_linux->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->where('enroll_type_id', $level_groups);
            });

            $data_all_ios = $data_all_ios->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->where('enroll_type_id', $level_groups);
            });

            $data_all_android = $data_all_android->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->where('enroll_type_id', $level_groups);
            });

        }else if ($authSession->super_users) {
            $level_groups_owner = $authSession->level_groups()->get();
            $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
            $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

            $data_all_windows = $data_all_windows->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
            });

            $data_all_osx = $data_all_osx->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
            });

            $data_all_linux = $data_all_linux->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
            });

            $data_all_ios = $data_all_ios->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
            });

            $data_all_android = $data_all_android->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
            });
        }

        if($request['classrooms_id']){
            $data_all_windows = $data_all_windows->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $request['classrooms_id']);
            $data_all_osx = $data_all_osx->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $request['classrooms_id']);
            $data_all_linux = $data_all_linux->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $request['classrooms_id']);
            $data_all_ios = $data_all_ios->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $request['classrooms_id']);
            $data_all_android = $data_all_android->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $request['classrooms_id']);
        }

        $from_datetime = date("Y-m-d H:i:s", strtotime($fromDate." ".$fromTime));
        $to_datetime = date("Y-m-d H:i:s", strtotime($toDate." ".$toTime));

        // $data_all_windows = $data_all_windows->whereIn('type', ['เข้าเรียน', 'ลงทะเบียน']);
        $data_all_windows = $data_all_windows->whereBetween('datetime',array($from_datetime, $to_datetime));
        $all_windows = $data_all_windows->where('platform', 'like', '%Windows%')->get()->count();

        // $data_all_osx = $data_all_osx->whereIn('type', ['เข้าเรียน', 'ลงทะเบียน']);
        $data_all_osx = $data_all_osx->whereBetween('datetime',array($from_datetime, $to_datetime));
        $all_osx = $data_all_osx->where('platform', 'like', '%OS X%')->get()->count();

        // $data_all_linux = $data_all_linux->whereIn('type', ['เข้าเรียน', 'ลงทะเบียน']);
        $data_all_linux = $data_all_linux->whereBetween('datetime',array($from_datetime, $to_datetime));
        $all_linux = $data_all_linux->where('platform', 'like', '%linux%')->get()->count();

        // $data_all_ios = $data_all_ios->whereIn('type', ['เข้าเรียน', 'ลงทะเบียน']);
        $data_all_ios = $data_all_ios->whereBetween('datetime',array($from_datetime, $to_datetime));
        $all_ios = $data_all_ios->where('platform', 'like', '%iOS%')->get()->count();

        // $data_all_android = $data_all_android->whereIn('type', ['เข้าเรียน', 'ลงทะเบียน']);
        $data_all_android = $data_all_android->whereBetween('datetime',array($from_datetime, $to_datetime));
        $all_android = $data_all_android->where('platform', 'like', '%AndroidOS%')->get()->count();

        $respData = array(
            "all_desktops" => ($all_windows+$all_osx+$all_linux),
            "all_mobiles" => ($all_ios+$all_android),
            "all_ios" => $all_ios,
            "all_android" => $all_android,
            "all_windows" => $all_windows,
            "all_osx" => $all_osx,
            "all_linux" => $all_linux
        );

        return response()->json($respData, 200);
    }

    public function country(Request $request, _RolesController $oRole)
    {
        if (!$request['from_date']) {
            $fromDate = date('Y-m-d', strtotime("-1 month"));
        } else {
            $fromDate = date("Y-m-d", strtotime($request['from_date']));
        }

        if (!$request['to_date']) {
            $toDate = date('Y-m-d');
        } else {
            $toDate = date("Y-m-d", strtotime($request['to_date']));
        }

        if (!$request['from_time']) {
            $fromTime = "00:00";
        } else {
            $fromTime = date("H:i", strtotime($request['from_time']));
        }

        if (!$request['to_time']) {
            $toTime = "23:59";
        } else {
            $toTime = date("H:i", strtotime($request['to_time']));
        }

        $authSession = Auth::user();
        if($authSession->super_users){
            $authSessionGroups = $authSession->groups()->get();
        }else{
            $authSessionGroups = $authSession->admins_groups()->first();
            $authSessionGroups = $authSessionGroups->groups()->get();
        }

        $data = new Member2Live;

        if($request['courses_id']){
            $data = $data->where('courses_id', $request['courses_id']);
            $data = $data->whereHas('courses', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });
        }else{
            if ($authSession->super_users) {
                $data = $data->whereHas('courses', function($query) use ($authSession, $authSessionGroups) {
                    $query->where('admins_id', $authSession->id);
                    $query->orWhere('level_public', 1);
                    $query->whereHas('groups', function($sub_query) use ($authSessionGroups) {
                        $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                    });
                });
            } else if (!$oRole->isSuper()) {
                $data = $data->whereHas('courses', function($query) use ($authSessionGroups) {
                    $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            }
        }

        if ($request['topics_id']) {
            $data = $data->where('topics_id', $request['topics_id']);
        }

        if ($request['groups_id']) {
            $data = $data->where('groups_id', $request['groups_id']);
        }

        if($request['sub_groups_id']){
            $data = $data->where('sub_groups_id', $request['sub_groups_id']);
        }else if ($authSession->super_users) {
            $data = $data->where('sub_groups_id', $authSession->sub_groups_id);
        }

        if($request['level_groups_id']){

            $level_groups = $request['level_groups_id'];

            $data = $data->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->where('enroll_type_id', $level_groups);
            });

        }else if ($authSession->super_users) {
            $level_groups_owner = $authSession->level_groups()->get();
            $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
            $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

            $data = $data->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
            });
        }

        if($request['classrooms_id']){
            $data = $data->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $request['classrooms_id']);
        }

        $from_datetime = date("Y-m-d H:i:s", strtotime($fromDate." ".$fromTime));
        $to_datetime = date("Y-m-d H:i:s", strtotime($toDate." ".$toTime));

        // $data = $data->whereIn('type', ['เข้าเรียน', 'ลงทะเบียน']);
        $data = $data->whereBetween('datetime',array($from_datetime, $to_datetime));
        $data = $data->whereNotNull('isoCode');
        $data = $data->selectRaw('ANY_VALUE(isoCode) as isoCode, ANY_VALUE(country) as country, SUM(isoCode = isoCode) as total_views')
            ->groupBy('isoCode')
            ->orderBy('total_views', 'DESC')
            ->get();

        return response()->json($data, 200);
    }

    public function state(Request $request, _RolesController $oRole)
    {
        if (!$request['from_date']) {
            $fromDate = date('Y-m-d', strtotime("-1 month"));
        } else {
            $fromDate = date("Y-m-d", strtotime($request['from_date']));
        }

        if (!$request['to_date']) {
            $toDate = date('Y-m-d');
        } else {
            $toDate = date("Y-m-d", strtotime($request['to_date']));
        }

        if (!$request['from_time']) {
            $fromTime = "00:00";
        } else {
            $fromTime = date("H:i", strtotime($request['from_time']));
        }

        if (!$request['to_time']) {
            $toTime = "23:59";
        } else {
            $toTime = date("H:i", strtotime($request['to_time']));
        }

        $authSession = Auth::user();
        if($authSession->super_users){
            $authSessionGroups = $authSession->groups()->get();
        }else{
            $authSessionGroups = $authSession->admins_groups()->first();
            $authSessionGroups = $authSessionGroups->groups()->get();
        }

        $data = new Member2Live;
        if($request['courses_id']){
            $data = $data->where('courses_id', $request['courses_id']);
            $data = $data->whereHas('courses', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });
        }else{
            if ($authSession->super_users) {
                $data = $data->whereHas('courses', function($query) use ($authSession, $authSessionGroups) {
                    $query->where('admins_id', $authSession->id);
                    $query->orWhere('level_public', 1);
                    $query->whereHas('groups', function($sub_query) use ($authSessionGroups) {
                        $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                    });
                });
            } else if (!$oRole->isSuper()) {
                $data = $data->whereHas('courses', function($query) use ($authSessionGroups) {
                    $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            }
        }

        if ($request['topics_id']) {
            $data = $data->where('topics_id', $request['topics_id']);
        }

        if($request['groups_id']){
            $data = $data->where('groups_id', $request['groups_id']);
        }

        if($request['sub_groups_id']){
            $data = $data->where('sub_groups_id', $request['sub_groups_id']);
        }else if ($authSession->super_users) {
            $data = $data->where('sub_groups_id', $authSession->sub_groups_id);
        }

        if($request['level_groups_id']){

            $level_groups = $request['level_groups_id'];

            $data = $data->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->where('enroll_type_id', $level_groups);
            });

        }else if ($authSession->super_users) {

            $level_groups_owner = $authSession->level_groups()->get();
            $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
            $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

            $data = $data->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
            });

        }

        if($request['classrooms_id']){
            $data = $data->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $request['classrooms_id']);
        }

        $from_datetime = date("Y-m-d H:i:s", strtotime($fromDate." ".$fromTime));
        $to_datetime = date("Y-m-d H:i:s", strtotime($toDate." ".$toTime));

        // $data = $data->whereIn('type', ['เข้าเรียน', 'ลงทะเบียน']);
        $data = $data->whereBetween('datetime',array($from_datetime, $to_datetime));
        $data = $data->whereNotNull('state');
        $data = $data->selectRaw('ANY_VALUE(state) as state, ANY_VALUE(country) as country, SUM(state = state) as total_views')
            ->groupBy('state')
            ->orderBy('total_views', 'DESC')
            ->get();

        return response()->json($data, 200);
    }

    public function stats_live(Request $request, _RolesController $oRole, _FunctionsController $oFunc)
    {
        //
        $per_page = $request->input('per_page', 30);
        $order_by = $request->input('order_by', 'id');
        $order_direction = $request->input('order_direction', 'asc');

        if (!$request['from_date']) {
            $fromDate = date('Y-m-d', strtotime("-1 month"));
        } else {
            $fromDate = date("Y-m-d", strtotime($request['from_date']));
        }

        if (!$request['to_date']) {
            $toDate = date('Y-m-d');
        } else {
            $toDate = date("Y-m-d", strtotime($request['to_date']));
        }

        if (!$request['from_time']) {
            $fromTime = "00:00";
        } else {
            $fromTime = date("H:i", strtotime($request['from_time']));
        }

        if (!$request['to_time']) {
            $toTime = "23:59";
        } else {
            $toTime = date("H:i", strtotime($request['to_time']));
        }

        $authSession = Auth::user();
        if($authSession->super_users){
            $authSessionGroups = $authSession->groups()->get();
        }else{
            $authSessionGroups = $authSession->admins_groups()->first();
            $authSessionGroups = $authSessionGroups->groups()->get();
        }

        // $enroll = Enroll::select('id', 'courses_id')->where('courses_id', $request['courses_id']);
        $enroll = Enroll::select('id', 'courses_id');

        if ($request['courses_id']) {
            $enroll = $enroll->where('courses_id', $request['courses_id']);
            $enroll = $enroll->whereHas('courses', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });
        } else {
            if ($authSession->super_users) {
                $enroll = $enroll->whereHas('courses', function($query) use ($authSession, $authSessionGroups) {
                    $query->where('admins_id', $authSession->id);
                    $query->orWhere('level_public', 1);
                    $query->whereHas('groups', function($sub_query) use ($authSessionGroups) {
                        $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                    });
                });
            } else if (!$oRole->isSuper()) {
                $enroll = $enroll->whereHas('courses', function($query) use ($authSessionGroups) {
                    $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            }
        }

        if ($request['groups_id']) {
            $enroll = $enroll->where('groups_id', $request['groups_id']);
        }

        if ($request['sub_groups_id']) {
            $enroll = $enroll->where('sub_groups_id', $request['sub_groups_id']);
        } else if ($authSession->super_users) {
                $enroll = $enroll->where('sub_groups_id', $authSession->sub_groups_id);
        }

        if ($request['level_groups_id']) {
            $level_groups = $request['level_groups_id'];

            $enroll = $enroll->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->where('enroll_type_id', $level_groups);
            });
        } else if ($authSession->super_users) {
            $level_groups_owner = $authSession->level_groups()->get();
            $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
            $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

            $enroll = $enroll->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
            });
        }

        if ($request['classrooms_id']) {
            $enroll = $enroll->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $request['classrooms_id']);
        }

        if ($request['search']) {
            $search = $request['search'];
            $enroll = $enroll->whereHas('members', function($query) use ($search) {
                $query->where('email', 'like' , "%".$search."%");
                $query->orWhere('first_name', 'like' , "%".$search."%");
            });
        }

        $enroll = $enroll->orderBy('id', 'asc')->get();

        $c_enroll = count($enroll);
        $enroll_data = [];

        for ($i=0; $i < $c_enroll; $i++) {
            $enroll_data[] = $enroll[$i]->id;
        }

        // $data = Enroll2TopicLive::whereIn('enroll_id', $enroll_data)->sum('duration')->groupBy('topics_id');
        $data = new Enroll2TopicLive;
        $data = $data->select(DB::raw('sum(duration) as duration, topics_id, enroll_id, enter_datetime'))
                    ->whereIn('enroll_id', $enroll_data);

        if ($request['topics_id']) {
            $data = $data->where('topics_id', $request['topics_id']);
        }

        $from_datetime = date("Y-m-d H:i:s", strtotime($fromDate." ".$fromTime));
        $to_datetime = date("Y-m-d H:i:s", strtotime($toDate." ".$toTime));

        $data = $data->whereBetween('enter_datetime',array($from_datetime, $to_datetime));

        $data = $data->groupBy('enroll_id');
        $data = $data->groupBy('topics_id');

        if ($request['topics_id']) {
            $data = $data->orderByRaw('enroll_id asc');
        } else {
            $data = $data->orderByRaw('topics_id asc');

        }

        $data = $data->paginate($per_page);

        $c_data = count($data);
        for ($i=0; $i < $c_data; $i++) {
            $data[$i]->duration = (int) $data[$i]->duration;
            $data[$i]->topics = $data[$i]->topics()->select('id', 'title')->first();
            $enroll = $data[$i]->enroll()->select('id', 'groups_id', 'members_id')->first();
            $data[$i]->groups = $enroll->groups()->select('id', 'title')->first();
            $data[$i]->enter_datetime = $oFunc->thai_date_and_time_human_full(strtotime($data[$i]->enter_datetime));

            $data[$i]->members = Members::select('id', 'first_name', 'last_name', 'email')->where('id', $enroll->members_id)->first();

            if (!$data[$i]->topics) {
                $data[$i] = null;
                continue;
            }

            $live_result = LiveResults::select('id', 'topic_id', 'live_start_datetime', 'live_end_datetime')->where('topic_id', $data[$i]->topics->id)->first();

            if (isset($live_result->live_start_datetime) && isset($live_result->live_end_datetime)) {
                $live_result_start_datetime = Carbon::parse($live_result->live_start_datetime);
                $live_result_end_datetime = Carbon::parse($live_result->live_end_datetime);

                $diff = $live_result_start_datetime->diff($live_result_end_datetime);

                $diff_format = '';
                if ($diff->y > 0) {
                    $diff_format .= '%y ปี ';
                }

                if ($diff->m > 0) {
                    $diff_format .= '%m เดือน ';
                }

                if ($diff->d > 0) {
                    $diff_format .= '%a วัน ';
                }

                if ($diff->h > 0) {
                    $diff_format .= '%h ชั่วโมง ';
                }

                if ($diff->i > 0) {
                    $diff_format .= '%i นาที ';
                }

                if ($diff->s > 0) {
                    $diff_format .= '%s วินาที';
                }

                $data[$i]->live_duration = $diff->format($diff_format);

            } else {
                $data[$i]->live_duration = 'กำลังถ่ายทอดสด';
                if (empty($live_result->live_start_datetime) && empty($live_result->live_end_datetime)) {
                    $data[$i]->live_duration = 'รอการถ่ายทอดสด';
                }
            }

            if ($data[$i]->duration != 0) {
                $d1 = Carbon::now();
                $d2 = Carbon::now();
                $d2->addSeconds($data[$i]->duration);

                $d_diff = $d2->diff($d1);
                $d_diff_format = '';
                if ($d_diff->y > 0) {
                    $d_diff_format .= '%y ปี ';
                }

                if ($d_diff->m > 0) {
                    $d_diff_format .= '%m เดือน ';
                }

                if ($d_diff->d > 0) {
                    $d_diff_format .= '%a วัน ';
                }

                if ($d_diff->h > 0) {
                    $d_diff_format .= '%h ชั่วโมง ';
                }

                if ($d_diff->i > 0) {
                    $d_diff_format .= '%i นาที ';
                }

                if ($d_diff->s > 0) {
                    $d_diff_format .= '%s วินาที';
                }

                $data[$i]->study_duration = $d_diff->format($d_diff_format);
            } else {
                $data[$i]->study_duration = '0 วินาที';
            }
        }

        return response()->json($data, 200);
    }

    public function learning(Request $request, _RolesController $oRole)
    {
        //
        if (!$request['from_date']) {
            $fromDate = date('Y-m-d', strtotime("-1 month"));
        } else {
            $fromDate = date("Y-m-d", strtotime($request['from_date']));
        }

        if (!$request['to_date']) {
            $toDate = date('Y-m-d');
        } else {
            $toDate = date("Y-m-d", strtotime($request['to_date']));
        }

        if (!$request['from_time']) {
            $fromTime = "00:00";
        } else {
            $fromTime = date("H:i", strtotime($request['from_time']));
        }

        if (!$request['to_time']) {
            $toTime = "23:59";
        } else {
            $toTime = date("H:i", strtotime($request['to_time']));
        }

        $from_datetime = date("Y-m-d H:i:s", strtotime($fromDate." ".$fromTime));
        $to_datetime = date("Y-m-d H:i:s", strtotime($toDate." ".$toTime));

        $authSession = Auth::user();
        if($authSession->super_users){
            $authSessionGroups = $authSession->groups()->get();
        }else{
            $authSessionGroups = $authSession->admins_groups()->first();
            $authSessionGroups = $authSessionGroups->groups()->get();
        }

        $data = new Enroll();
        $data->enroll = $data->with('courses');

        if($request['courses_id']){
            $data->enroll = $data->enroll->where('courses_id', $request['courses_id']);
            $data->enroll = $data->enroll->whereHas('courses', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });

            $data->courses = Courses::find($request['courses_id']);
            $data->quiz = $data->courses->quiz()->where('type', 3)->where('status', 1)->count();
            if($data->quiz){ $data->quiz_process = true; }else{ $data->quiz_process = false; }

        }else{
            if ($authSession->super_users) {
                $data->enroll = $data->enroll->whereHas('courses', function($query) use ($authSession, $authSessionGroups) {
                    $query->where('admins_id', $authSession->id);
                    $query->orWhere('level_public', 1);
                    $query->whereHas('groups', function($sub_query) use ($authSessionGroups) {
                        $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                    });
                });
            } else if (!$oRole->isSuper()) {
                $data->enroll = $data->enroll->whereHas('courses', function($query) use ($authSessionGroups) {
                    $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            }

            $data->quiz_process = true;
        }

        if($request['groups_id']){
            $data->enroll = $data->enroll->where('groups_id', $request['groups_id']);
        }

        if($request['sub_groups_id']){
            $data->enroll = $data->enroll->where('sub_groups_id', $request['sub_groups_id']);
        }else if ($authSession->super_users) {
            $data->enroll = $data->enroll->where('sub_groups_id', $authSession->sub_groups_id);
        }

        if($request['level_groups_id']){

            $level_groups = $request['level_groups_id'];

            $data->enroll = $data->enroll->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->where('enroll_type_id', $level_groups);
            });

        }else if ($authSession->super_users) {

            $data->level_groups_owner = $authSession->level_groups()->get();
            $data->level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
            $data->level_groups = array_merge($data->level_groups_owner->toArray(), $data->level_groups_access->toArray());

            $data->enroll = $data->enroll->where('enroll_type', 3)->orWhere(function($query) use ($data) {
                $query->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($data->level_groups, 'id'));
            });
        }

        if($request['classrooms_id']){
            $data->enroll = $data->enroll->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $request['classrooms_id']);
        }

        $data->enroll = $data->enroll->whereBetween('enroll_datetime',array($from_datetime, $to_datetime));
        $data->enroll = $data->enroll->get();
        for($i=0; $i<count($data->enroll); $i++) {

            $data->enroll[$i]->courses->topics = $data->enroll[$i]->courses->topics()->whereNotNull('parent')->orderBy('order','asc')->get();
            for($a=0; $a<count($data->enroll[$i]->courses->topics); $a++) {
                $data->enroll[$i]->courses->topics[$a]->duration = (strtotime($data->enroll[$i]->courses->topics[$a]->end_time) - strtotime('TODAY')) - (strtotime($data->enroll[$i]->courses->topics[$a]->start_time) - strtotime('TODAY'));
                $data->enroll[$i]->full_duration += $data->enroll[$i]->courses->topics[$a]->duration;
            }

            // $data->enroll[$i]->duration = $data->enroll[$i]->enroll2topic()->sum('duration');
            // bug duration
            $data->enroll[$i]->enroll2topic = $data->enroll[$i]->enroll2topic()->get();
            for($q=0; $q<count($data->enroll[$i]->enroll2topic); $q++) {
                if($data->enroll[$i]->enroll2topic[$q]->status == 1){
                    $data->enroll[$i]->enroll2topic[$q]->topics = $data->enroll[$i]->enroll2topic[$q]->topics()->first();
                    $data->enroll[$i]->duration += (strtotime($data->enroll[$i]->enroll2topic[$q]->topics->end_time) - strtotime('TODAY')) - (strtotime($data->enroll[$i]->enroll2topic[$q]->topics->start_time) - strtotime('TODAY'));
                }else{
                    $data->enroll[$i]->duration += $data->enroll[$i]->enroll2topic[$q]->duration;
                }
            }


            if($data->enroll[$i]->duration){
                $data->enroll[$i]->progress = number_format(($data->enroll[$i]->duration / $data->enroll[$i]->full_duration)*100);
                if($data->enroll[$i]->progress >= $data->enroll[$i]->courses->percentage){
                    $data->learning_pass += 1;
                }else if($data->enroll[$i]->progress){
                    $data->learning_not_pass += 1;
                }else{
                    $data->not_learning += 1;
                }
            }else{
                $data->not_learning += 1;
            }

            $data->enroll[$i]->exam = $data->enroll[$i]->enroll2quiz()->where('type', 3)->whereNotNull('score')->orderBy('id','desc')->first();
            if($data->enroll[$i]->exam){
                $data->exam += 1;
                $data->enroll[$i]->exam->percentage = number_format(($data->enroll[$i]->exam->score/$data->enroll[$i]->exam->count)*100);
                $data->enroll[$i]->exam->quiz = $data->enroll[$i]->exam->quiz()->first();
                if(number_format($data->enroll[$i]->exam->percentage) >= $data->enroll[$i]->exam->quiz->passing_score){
                    $data->exam_pass += 1;
                }else{
                    $data->exam_not_pass += 1;
                }
            }

            if($data->enroll[$i]->certificate_reference_number){
                $data->certificate += 1;
            }

        }

        if($data->quiz_process == true){
            if($data->certificate > $data->exam_pass){
                $data->not_certificate_over = $data->certificate - $data->exam_pass;
                $data->certificate = $data->certificate - $data->not_certificate_over;
                $data->not_certificate = 0;
            }else{
                $data->not_certificate = $data->exam_pass - $data->certificate;
            }
            $data->enroll_all = $data->enroll->count();
            $data->learning_all = $data->enroll_all - $data->exam;
            $data->learning_pass_not_exam = $data->learning_pass - $data->exam;
            $data->not_pass = ($data->not_learning + $data->learning_not_pass + $data->learning_pass_not_exam + $data->exam_not_pass);
        }else{
            $data->enroll_all = $data->enroll->count();
            $data->not_certificate = $data->certificate;
            $data->learning_all = $data->enroll_all;
            $data->learning_pass = $data->learning_pass;
            $data->not_pass = ($data->not_learning + $data->learning_not_pass);
        }

        return response()->json($data, 200);
    }

    public function courses(Request $request)
    {

        if(!$request['courses_id']){
            return null;
        }else{
        //
        if (!$request['from_date']) {
            $fromDate = date('Y-m-d', strtotime("-1 month"));
        } else {
            $fromDate = date("Y-m-d", strtotime($request['from_date']));
        }

        if (!$request['to_date']) {
            $toDate = date('Y-m-d');
        } else {
            $toDate = date("Y-m-d", strtotime($request['to_date']));
        }

        if (!$request['from_time']) {
            $fromTime = "00:00";
        } else {
            $fromTime = date("H:i", strtotime($request['from_time']));
        }

        if (!$request['to_time']) {
            $toTime = "23:59";
        } else {
            $toTime = date("H:i", strtotime($request['to_time']));
        }

        $from_datetime = date("Y-m-d H:i:s", strtotime($fromDate." ".$fromTime));
        $to_datetime = date("Y-m-d H:i:s", strtotime($toDate." ".$toTime));

        $groups_id = $request['groups_id'];
        $sub_groups_id = $request['sub_groups_id'];
        $level_groups_id = $request['level_groups_id'];
        $classrooms_id = $request['classrooms_id'];

        $authSession = Auth::user();
        if($authSession->super_users){
            $authSessionGroups = $authSession->groups()->get();
        }else{
            $authSessionGroups = $authSession->admins_groups()->first();
            $authSessionGroups = $authSessionGroups->groups()->get();
        }

        $data = new Courses;
        $data->courses = $data->find($request['courses_id']);
        $data->topics = $data->courses->topics()->whereNull('parent')->orderBy('order','asc')->get();
        for($i=0; $i<count($data->topics); $i++) {
            $data->topics[$i]->parent = Topics::where('parent', $data->topics[$i]->id)->orderBy('order','asc')->get();
            for($a=0; $a<count($data->topics[$i]->parent); $a++) {
                $data->topics[$i]->parent[$a]->duration = (strtotime($data->topics[$i]->parent[$a]->end_time) - strtotime('TODAY')) - (strtotime($data->topics[$i]->parent[$a]->start_time) - strtotime('TODAY'));
                $data->topics[$i]->parent[$a]->enroll2topic = $data->topics[$i]->parent[$a]->enroll2topic();
                $data->topics[$i]->parent[$a]->enroll2topic = $data->topics[$i]->parent[$a]->enroll2topic->with('enroll');
                $data->topics[$i]->parent[$a]->enroll2topic = $data->topics[$i]->parent[$a]->enroll2topic->whereHas('enroll', function($query) use ($groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $from_datetime, $to_datetime, $authSession, $authSessionGroups) {

                    if($groups_id){
                        $query->where('groups_id', $groups_id);
                    }

                    if($sub_groups_id){
                        $query->where('sub_groups_id', $sub_groups_id);
                    }else if ($authSession->super_users) {
                        $query->where('sub_groups_id', $authSession->sub_groups_id);
                    }

                    if($level_groups_id){

                        $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups_id) {
                            $query_sub->where('enroll_type', 4)->where('enroll_type_id', $level_groups_id);
                        });

                    }else if ($authSession->super_users) {
                        $level_groups_owner = $authSession->level_groups()->get();
                        $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
                        $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

                        $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups) {
                            $query_sub->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
                        });

                    }

                    if($classrooms_id){
                        $query->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $classrooms_id);
                    }

                    $query->whereBetween('enroll_datetime',array($from_datetime, $to_datetime));
                });
                $data->topics[$i]->parent[$a]->enroll2topic = $data->topics[$i]->parent[$a]->enroll2topic->get();
                $data->topics[$i]->parent[$a]->enroll2topic_count = $data->topics[$i]->parent[$a]->enroll2topic->count();

                for($t=0; $t<count($data->topics[$i]->parent[$a]->enroll2topic); $t++) {
                    if($data->topics[$i]->parent[$a]->enroll2topic[$t]->status){

                        $data->topics[$i]->parent[$a]->duration2enroll += $data->topics[$i]->parent[$a]->duration;
                        $data->topics[$i]->parent[$a]->duration2topic = $data->topics[$i]->parent[$a]->duration;
                        $data->topics[$i]->parent[$a]->duration2average = $data->topics[$i]->parent[$a]->duration2enroll / $data->topics[$i]->parent[$a]->enroll2topic_count;
                        $data->topics[$i]->parent[$a]->duration2percentage = ($data->topics[$i]->parent[$a]->duration2average / $data->topics[$i]->parent[$a]->duration2topic)*100;
                        $data->topics[$i]->parent[$a]->duration2gmdate = gmdate("H:i:s", $data->topics[$i]->parent[$a]->duration2average);

                        $data->topics[$i]->parent[$a]->enroll2topic_success += 1;

                    }else{

                        $data->topics[$i]->parent[$a]->duration2enroll += $data->topics[$i]->parent[$a]->enroll2topic[$t]->duration;
                        $data->topics[$i]->parent[$a]->duration2topic = $data->topics[$i]->parent[$a]->duration;
                        $data->topics[$i]->parent[$a]->duration2average = $data->topics[$i]->parent[$a]->duration2enroll / $data->topics[$i]->parent[$a]->enroll2topic_count;
                        $data->topics[$i]->parent[$a]->duration2percentage = ($data->topics[$i]->parent[$a]->duration2average / $data->topics[$i]->parent[$a]->duration2topic)*100;
                        $data->topics[$i]->parent[$a]->duration2gmdate = gmdate("H:i:s", $data->topics[$i]->parent[$a]->duration2average);

                        $data->topics[$i]->parent[$a]->enroll2topic_process += 1;

                    }
                }
            }
        }

        return response()->json($data, 200);
        }
    }

    public function quiz(Request $request, _RolesController $oRole)
    {

        //
        if (!$request['from_date']) {
            $fromDate = date('Y-m-d', strtotime("-1 month"));
        } else {
            $fromDate = date("Y-m-d", strtotime($request['from_date']));
        }

        if (!$request['to_date']) {
            $toDate = date('Y-m-d');
        } else {
            $toDate = date("Y-m-d", strtotime($request['to_date']));
        }

        if (!$request['from_time']) {
            $fromTime = "00:00";
        } else {
            $fromTime = date("H:i", strtotime($request['from_time']));
        }

        if (!$request['to_time']) {
            $toTime = "23:59";
        } else {
            $toTime = date("H:i", strtotime($request['to_time']));
        }

        $from_datetime = date("Y-m-d H:i:s", strtotime($fromDate." ".$fromTime));
        $to_datetime = date("Y-m-d H:i:s", strtotime($toDate." ".$toTime));

        $courses_id = $request['courses_id'];
        $groups_id = $request['groups_id'];
        $sub_groups_id = $request['sub_groups_id'];
        $level_groups_id = $request['level_groups_id'];
        $classrooms_id = $request['classrooms_id'];

        $authSession = Auth::user();
        if($authSession->super_users){
            $authSessionGroups = $authSession->groups()->get();
        }else{
            $authSessionGroups = $authSession->admins_groups()->first();
            $authSessionGroups = $authSessionGroups->groups()->get();
        }

        //Pre Test
        $pre_test = new Enroll2Quiz();
        $pre_test->data = $pre_test->with('enroll');
        $pre_test->data = $pre_test->data->where('type', 1)->whereNotNull('score');
        $pre_test->data = $pre_test->data->whereHas('enroll', function($query) use ($courses_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime) {
            if($courses_id){
                $query->where('courses_id', $courses_id);
                $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                    $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            }else{
                if ($authSession->super_users) {
                    $query->whereHas('courses', function($sub_query) use ($authSession, $authSessionGroups) {
                        $sub_query->where('admins_id', $authSession->id);
                        $sub_query->orWhere('level_public', 1);
                        $sub_query->whereHas('groups', function($unit_query) use ($authSessionGroups) {
                            $unit_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                        });
                    });
                } else if (!$oRole->isSuper()) {
                    $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                        $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                    });
                }
            }

            if($groups_id){
                $query->where('groups_id', $groups_id);
            }

            if($sub_groups_id){
                $query->where('sub_groups_id', $sub_groups_id);
            }else if ($authSession->super_users) {
                $query->where('sub_groups_id', $authSession->sub_groups_id);
            }

            if($level_groups_id){

                $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups_id) {
                    $query_sub->where('enroll_type', 4)->where('enroll_type_id', $level_groups_id);
                });

            }else if ($authSession->super_users) {
                $level_groups_owner = $authSession->level_groups()->get();
                $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
                $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

                $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups) {
                    $query_sub->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
                });
            }

            if($classrooms_id){
                $query->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $classrooms_id);
            }

            $query->whereBetween('enroll_datetime',array($from_datetime, $to_datetime));
        });
        $pre_test->data = $pre_test->data->orderBy('id', 'desc');
        $pre_test->data = $pre_test->data->get();
        $pre_test->count = $pre_test->data->count();


        if($request['courses_id']){
            $pre_test->questions = Quiz::where('courses_id', $request['courses_id'])->where('type', 1)->where('status', 1)->first();
            $pre_test->quiz_id = $pre_test->questions->id;
            $pre_test->questions = $pre_test->questions->questions()->where('status', 1)->orderBy('order', 'asc')->get();
            for($i=0; $i<count($pre_test->questions); $i++) {
                $pre_test->questions[$i]->answer = $pre_test->questions[$i]->answer()->where('status', 1)->orderBy('order', 'asc')->get();
                for($a=0; $a<count($pre_test->questions[$i]->answer); $a++) {
                    $pre_test->questions[$i]->answer[$a]->passing_score = $pre_test->questions[$i]->answer[$a]->questions2answer()->where('answer_id', $pre_test->questions[$i]->answer[$a]->id);
                    $pre_test->questions[$i]->answer[$a]->passing_score = $pre_test->questions[$i]->answer[$a]->passing_score->with('enroll2quiz');
                    $pre_test->questions[$i]->answer[$a]->passing_score = $pre_test->questions[$i]->answer[$a]->passing_score->whereHas('enroll2quiz.enroll', function($query) use ($courses_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime) {
                        if($courses_id){
                            $query->where('courses_id', $courses_id);
                            $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                                $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                            });
                        }else{
                            if ($authSession->super_users) {
                                $query->whereHas('courses', function($sub_query) use ($authSession, $authSessionGroups) {
                                    $sub_query->where('admins_id', $authSession->id);
                                    $sub_query->orWhere('level_public', 1);
                                    $sub_query->whereHas('groups', function($unit_query) use ($authSessionGroups) {
                                        $unit_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                                    });
                                });
                            } else if (!$oRole->isSuper()) {
                                $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                                    $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                                });
                            }
                        }

                        if($groups_id){
                            $query->where('groups_id', $groups_id);
                        }

                        if($sub_groups_id){
                            $query->where('sub_groups_id', $sub_groups_id);
                        }else if ($authSession->super_users) {
                            $query->where('sub_groups_id', $authSession->sub_groups_id);
                        }

                        if($level_groups_id){

                            $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups_id) {
                                $query_sub->where('enroll_type', 4)->where('enroll_type_id', $level_groups_id);
                            });

                        }else if ($authSession->super_users) {
                            $level_groups_owner = $authSession->level_groups()->get();
                            $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
                            $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

                            $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups) {
                                $query_sub->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
                            });
                        }

                        if($classrooms_id){
                            $query->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $classrooms_id);
                        }

                        $query->whereBetween('enroll_datetime',array($from_datetime, $to_datetime));
                    });
                    $pre_test->questions[$i]->answer[$a]->passing_score = $pre_test->questions[$i]->answer[$a]->passing_score->orderBy('id', 'desc');
                    $pre_test->questions[$i]->answer[$a]->passing_score = $pre_test->questions[$i]->answer[$a]->passing_score->count();

                }
            }
        }

        //Post Test
        $post_test = new Enroll2Quiz();
        $post_test->data = $post_test->with('enroll');
        $post_test->data = $post_test->data->where('type', 4)->whereNotNull('score');
        $post_test->data = $post_test->data->whereHas('enroll', function($query) use ($courses_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime) {
            if($courses_id){
                $query->where('courses_id', $courses_id);
                $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                    $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            }else{
                if ($authSession->super_users) {
                    $query->whereHas('courses', function($sub_query) use ($authSession, $authSessionGroups) {
                        $sub_query->where('admins_id', $authSession->id);
                        $sub_query->orWhere('level_public', 1);
                        $sub_query->whereHas('groups', function($unit_query) use ($authSessionGroups) {
                            $unit_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                        });
                    });
                } else if (!$oRole->isSuper()) {
                    $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                        $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                    });
                }
            }
            if($groups_id){
                $query->where('groups_id', $groups_id);
            }

            if($sub_groups_id){
                $query->where('sub_groups_id', $sub_groups_id);
            }else if ($authSession->super_users) {
                $query->where('sub_groups_id', $authSession->sub_groups_id);
            }

            if($level_groups_id){

                $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups_id) {
                    $query_sub->where('enroll_type', 4)->where('enroll_type_id', $level_groups_id);
                });

            }else if ($authSession->super_users) {
                $level_groups_owner = $authSession->level_groups()->get();
                $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
                $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

                $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups) {
                    $query_sub->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
                });
            }

            if($classrooms_id){
                $query->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $classrooms_id);
            }

            $query->whereBetween('enroll_datetime',array($from_datetime, $to_datetime));
        });
        $post_test->data = $post_test->data->orderBy('id', 'desc');
        $post_test->data = $post_test->data->get();
        $post_test->count = $post_test->data->count();

        if($request['courses_id']){
            $post_test->questions = Quiz::where('courses_id', $request['courses_id'])->where('type', 4)->where('status', 1)->first();
            $post_test->quiz_id = $post_test->questions->id;
            $post_test->questions = $post_test->questions->questions()->where('status', 1)->orderBy('order', 'asc')->get();
            for($i=0; $i<count($post_test->questions); $i++) {
                $post_test->questions[$i]->answer = $post_test->questions[$i]->answer()->where('status', 1)->orderBy('order', 'asc')->get();
                for($a=0; $a<count($post_test->questions[$i]->answer); $a++) {
                    $post_test->questions[$i]->answer[$a]->passing_score = $post_test->questions[$i]->answer[$a]->questions2answer()->where('answer_id', $post_test->questions[$i]->answer[$a]->id);
                    $post_test->questions[$i]->answer[$a]->passing_score = $post_test->questions[$i]->answer[$a]->passing_score->with('enroll2quiz');
                    $post_test->questions[$i]->answer[$a]->passing_score = $post_test->questions[$i]->answer[$a]->passing_score->whereHas('enroll2quiz.enroll', function($query) use ($courses_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime) {
                        if($courses_id){
                            $query->where('courses_id', $courses_id);
                            $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                                $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                            });
                        }else{
                            if ($authSession->super_users) {
                                $query->whereHas('courses', function($sub_query) use ($authSession, $authSessionGroups) {
                                    $sub_query->where('admins_id', $authSession->id);
                                    $sub_query->orWhere('level_public', 1);
                                    $sub_query->whereHas('groups', function($unit_query) use ($authSessionGroups) {
                                        $unit_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                                    });
                                });
                            } else if (!$oRole->isSuper()) {
                                $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                                    $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                                });
                            }
                        }

                        if($groups_id){
                            $query->where('groups_id', $groups_id);
                        }

                        if($sub_groups_id){
                            $query->where('sub_groups_id', $sub_groups_id);
                        }else if ($authSession->super_users) {
                            $query->where('sub_groups_id', $authSession->sub_groups_id);
                        }

                        if($level_groups_id){

                            $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups_id) {
                                $query_sub->where('enroll_type', 4)->where('enroll_type_id', $level_groups_id);
                            });

                        }else if ($authSession->super_users) {
                            $level_groups_owner = $authSession->level_groups()->get();
                            $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
                            $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

                            $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups) {
                                $query_sub->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
                            });
                        }

                        if($classrooms_id){
                            $query->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $classrooms_id);
                        }

                        $query->whereBetween('enroll_datetime',array($from_datetime, $to_datetime));
                    });
                    $post_test->questions[$i]->answer[$a]->passing_score = $post_test->questions[$i]->answer[$a]->passing_score->orderBy('id', 'desc');
                    $post_test->questions[$i]->answer[$a]->passing_score = $post_test->questions[$i]->answer[$a]->passing_score->count();

                }
            }
        }

        $compare = new Enroll2Quiz();
        $compare->data = $compare->with('enroll');
        $compare->data = $compare->data->where('type', 4)->whereNotNull('score');
        $compare->data = $compare->data->whereHas('enroll', function($query) use ($courses_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime) {
            if($courses_id){
                $query->where('courses_id', $courses_id);
                $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                    $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            }else{
                if ($authSession->super_users) {
                    $query->whereHas('courses', function($sub_query) use ($authSession, $authSessionGroups) {
                        $sub_query->where('admins_id', $authSession->id);
                        $sub_query->orWhere('level_public', 1);
                        $sub_query->whereHas('groups', function($unit_query) use ($authSessionGroups) {
                            $unit_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                        });
                    });
                } else if (!$oRole->isSuper()) {
                    $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                        $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                    });
                }
            }

            if($groups_id){
                $query->where('groups_id', $groups_id);
            }

            if($sub_groups_id){
                $query->where('sub_groups_id', $sub_groups_id);
            }else if ($authSession->super_users) {
                $query->where('sub_groups_id', $authSession->sub_groups_id);
            }

            if($level_groups_id){

                $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups_id) {
                    $query_sub->where('enroll_type', 4)->where('enroll_type_id', $level_groups_id);
                });

            }else if ($authSession->super_users) {
                $level_groups_owner = $authSession->level_groups()->get();
                $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
                $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

                $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups) {
                    $query_sub->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
                });
            }

            if($classrooms_id){
                $query->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $classrooms_id);
            }

            $query->whereBetween('enroll_datetime',array($from_datetime, $to_datetime));
        });
        $compare->data = $compare->data->orderBy('id', 'desc');
        $compare->data = $compare->data->get();
        for($i=0; $i<count($compare->data); $i++) {
            $compare->data[$i]->to_pre_test = new Enroll2Quiz();
            $compare->data[$i]->to_pre_test = $compare->data[$i]->to_pre_test->where('enroll_id', $compare->data[$i]->enroll_id);
            $compare->data[$i]->to_pre_test = $compare->data[$i]->to_pre_test->where('type', 1)->whereNotNull('score')->orderBy('id', 'desc');
            $compare->data[$i]->to_pre_test = $compare->data[$i]->to_pre_test->first();
            if($compare->data[$i]->to_pre_test){
                if($compare->data[$i]->score > $compare->data[$i]->to_pre_test->score){
                    $compare->over += 1;
                }else{
                    $compare->under += 1;
                }
            }else{
                $compare->under += 1;
            }
        }

        if($post_test->count){
            $compare->over_percentage = number_format(($compare->over/$post_test->count)*100);
            $compare->under_percentage = number_format(($compare->under/$post_test->count)*100);
        }

        //Exam
        $exam = new Enroll2Quiz();
        $exam->data = $exam->with('enroll');
        $exam->data = $exam->data->where('type', 3)->whereNotNull('score');
        $exam->data = $exam->data->whereHas('enroll', function($query) use ($courses_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime) {
            if($courses_id){
                $query->where('courses_id', $courses_id);
                $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                    $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            }else{
                if ($authSession->super_users) {
                    $query->whereHas('courses', function($sub_query) use ($authSession, $authSessionGroups) {
                        $sub_query->where('admins_id', $authSession->id);
                        $sub_query->orWhere('level_public', 1);
                        $sub_query->whereHas('groups', function($unit_query) use ($authSessionGroups) {
                            $unit_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                        });
                    });
                } else if (!$oRole->isSuper()) {
                    $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                        $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                    });
                }
            }

            if($groups_id){
                $query->where('groups_id', $groups_id);
            }

            if($sub_groups_id){
                $query->where('sub_groups_id', $sub_groups_id);
            }else if ($authSession->super_users) {
                $query->where('sub_groups_id', $authSession->sub_groups_id);
            }

            if($level_groups_id){

                $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups_id) {
                    $query_sub->where('enroll_type', 4)->where('enroll_type_id', $level_groups_id);
                });

            }else if ($authSession->super_users) {
                $level_groups_owner = $authSession->level_groups()->get();
                $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
                $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

                $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups) {
                    $query_sub->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
                });
            }

            if($classrooms_id){
                $query->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $classrooms_id);
            }

            $query->whereBetween('enroll_datetime',array($from_datetime, $to_datetime));
        });
        $exam->data = $exam->data->orderBy('id', 'desc');
        $exam->data = $exam->data->get();
        for($i=0; $i<count($exam->data); $i++) {
            $exam->data[$i]->percentage = number_format(($exam->data[$i]->score/$exam->data[$i]->count)*100);
            $exam->data[$i]->quiz = $exam->data[$i]->quiz()->first();
            if($exam->data[$i]->percentage >= $exam->data[$i]->quiz->passing_score){
                $exam->exam_pass += 1;
                $exam->last_score += $exam->data[$i]->score;
            }else{
                $exam->exam_not_pass += 1;
                $exam->last_score += $exam->data[$i]->score;
            }
        }
        $exam->count = $exam->data->count();
        if($exam->exam_pass){
            $exam->average_to_quiz = number_format($exam->count / $exam->exam_pass);
        }

        $exam->first_last_score = new Enroll();
        if($request['courses_id']){
            $exam->first_last_score = $exam->first_last_score->where('courses_id', $request['courses_id']);
            $exam->first_last_score = $exam->first_last_score->whereHas('courses', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });
        }else{
            if ($authSession->super_users) {
                $exam->first_last_score = $exam->first_last_score->whereHas('courses', function($query) use ($authSession, $authSessionGroups) {
                    $query->where('admins_id', $authSession->id);
                    $query->orWhere('level_public', 1);
                    $query->whereHas('groups', function($sub_query) use ($authSessionGroups) {
                        $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                    });
                });
            } else if (!$oRole->isSuper()) {
                $exam->first_last_score = $exam->first_last_score->whereHas('courses', function($query) use ($authSessionGroups) {
                    $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            }
        }
        if($request['groups_id']){
            $exam->first_last_score = $exam->first_last_score->where('groups_id', $request['groups_id']);
        }

        if($request['sub_groups_id']){
            $exam->first_last_score = $exam->first_last_score->where('sub_groups_id', $request['sub_groups_id']);
        }else if ($authSession->super_users) {
            $exam->first_last_score = $exam->first_last_score->where('sub_groups_id', $authSession->sub_groups_id);
        }

        if($request['level_groups_id']){

            $level_groups = $request['level_groups_id'];
            $exam->first_last_score = $exam->first_last_score->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->where('enroll_type_id', $level_groups);
            });

        }else if ($authSession->super_users) {
            $exam->level_groups_owner = $authSession->level_groups()->get();
            $exam->level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
            $exam->level_groups = array_merge($exam->level_groups_owner->toArray(), $exam->level_groups_access->toArray());

            $exam->first_last_score = $exam->first_last_score->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($exam->level_groups, 'id'));
        }

        if($request['classrooms_id']){
            $exam->first_last_score = $exam->first_last_score->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $request['classrooms_id']);
        }
        $exam->first_last_score = $exam->first_last_score->whereBetween('enroll_datetime',array($from_datetime, $to_datetime));
        $exam->first_last_score = $exam->first_last_score->get();
        for($i=0; $i<count($exam->first_last_score); $i++) {
            $exam->first_last_score[$i]->asc = $exam->first_last_score[$i]->enroll2quiz()->where('type', 3)->whereNotNull('score')->orderBy('id', 'asc')->first();
            $exam->first_last_score[$i]->desc = $exam->first_last_score[$i]->enroll2quiz()->where('type', 3)->whereNotNull('score')->orderBy('id', 'desc')->first();

            $exam->first_score += $exam->first_last_score[$i]->asc['score'];
            $exam->last_score += $exam->first_last_score[$i]->desc['score'];
        }

        if($exam->first_score){
            $exam->first_score_average = number_format($exam->first_score / $exam->count, 3);
        }

        if($exam->last_score){
            $exam->last_score_average = number_format($exam->last_score / $exam->count, 3);
        }


        $exam->min = new Enroll2Quiz();
        $exam->min = $exam->min->with('enroll');
        $exam->min = $exam->min->where('type', 3)->whereNotNull('score');
        $exam->min = $exam->min->whereHas('enroll', function($query) use ($courses_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime) {
            if($courses_id){
                $query->where('courses_id', $courses_id);
                $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                    $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            }else{
                if ($authSession->super_users) {
                    $query->whereHas('courses', function($sub_query) use ($authSession, $authSessionGroups) {
                        $sub_query->where('admins_id', $authSession->id);
                        $sub_query->orWhere('level_public', 1);
                        $sub_query->whereHas('groups', function($unit_query) use ($authSessionGroups) {
                            $unit_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                        });
                    });
                } else if (!$oRole->isSuper()) {
                    $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                        $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                    });
                }
            }

            if($groups_id){
                $query->where('groups_id', $groups_id);
            }

            if($sub_groups_id){
                $query->where('sub_groups_id', $sub_groups_id);
            }else if ($authSession->super_users) {
                $query->where('sub_groups_id', $authSession->sub_groups_id);
            }

            if($level_groups_id){

                $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups_id) {
                    $query_sub->where('enroll_type', 4)->where('enroll_type_id', $level_groups_id);
                });

            }else if ($authSession->super_users) {
                $level_groups_owner = $authSession->level_groups()->get();
                $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
                $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

                $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups) {
                    $query_sub->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
                });
            }

            if($classrooms_id){
                $query->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $classrooms_id);
            }

            $query->whereBetween('enroll_datetime',array($from_datetime, $to_datetime));
        });
        $exam->min = $exam->min->orderBy('id', 'desc');
        $exam->min = $exam->min->min('score');

        $exam->max = new Enroll2Quiz();
        $exam->max = $exam->max->with('enroll');
        $exam->max = $exam->max->where('type', 3)->whereNotNull('score');
        $exam->max = $exam->max->whereHas('enroll', function($query) use ($courses_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime) {
            if($courses_id){
                $query->where('courses_id', $courses_id);
                $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                    $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            }else{
                if ($authSession->super_users) {
                    $query->whereHas('courses', function($sub_query) use ($authSession, $authSessionGroups) {
                        $sub_query->where('admins_id', $authSession->id);
                        $sub_query->orWhere('level_public', 1);
                        $sub_query->whereHas('groups', function($unit_query) use ($authSessionGroups) {
                            $unit_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                        });
                    });
                } else if (!$oRole->isSuper()) {
                    $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                        $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                    });
                }
            }

            if($groups_id){
                $query->where('groups_id', $groups_id);
            }

            if($sub_groups_id){
                $query->where('sub_groups_id', $sub_groups_id);
            }else if ($authSession->super_users) {
                $query->where('sub_groups_id', $authSession->sub_groups_id);
            }

            if($level_groups_id){
                $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups_id) {
                    $query_sub->where('enroll_type', 4)->where('enroll_type_id', $level_groups_id);
                });
            }else if ($authSession->super_users) {
                $level_groups_owner = $authSession->level_groups()->get();
                $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
                $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

                $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups) {
                    $query_sub->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
                });
            }

            if($classrooms_id){
                $query->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $classrooms_id);
            }

            $query->whereBetween('enroll_datetime',array($from_datetime, $to_datetime));
        });
        $exam->max = $exam->max->orderBy('id', 'desc');
        $exam->max = $exam->max->max('score');


        if($request['courses_id']){
            $exam->questions = Quiz::where('courses_id', $request['courses_id'])->where('type', 3)->where('status', 1)->first();
            $exam->quiz_id = $exam->questions->id;
            $exam->questions = $exam->questions->questions()->where('status', 1)->orderBy('order', 'asc')->get();
            for($i=0; $i<count($exam->questions); $i++) {
                $exam->questions[$i]->answer = $exam->questions[$i]->answer()->where('status', 1)->orderBy('order', 'asc')->get();
                for($a=0; $a<count($exam->questions[$i]->answer); $a++) {
                    $exam->questions[$i]->answer[$a]->passing_score = $exam->questions[$i]->answer[$a]->questions2answer()->where('answer_id', $exam->questions[$i]->answer[$a]->id);
                    $exam->questions[$i]->answer[$a]->passing_score = $exam->questions[$i]->answer[$a]->passing_score->with('enroll2quiz');
                    $exam->questions[$i]->answer[$a]->passing_score = $exam->questions[$i]->answer[$a]->passing_score->whereHas('enroll2quiz.enroll', function($query) use ($courses_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime) {
                        if($courses_id){
                            $query->where('courses_id', $courses_id);
                            $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                                $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                            });
                        }else{
                            if ($authSession->super_users) {
                                $query->whereHas('courses', function($sub_query) use ($authSession, $authSessionGroups) {
                                    $sub_query->where('admins_id', $authSession->id);
                                    $sub_query->orWhere('level_public', 1);
                                    $sub_query->whereHas('groups', function($unit_query) use ($authSessionGroups) {
                                        $unit_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                                    });
                                });
                            } else if (!$oRole->isSuper()) {
                                $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                                    $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                                });
                            }
                        }
                        if($groups_id){
                            $query->where('groups_id', $groups_id);
                        }

                        if($sub_groups_id){
                            $query->where('sub_groups_id', $sub_groups_id);
                        }else if ($authSession->super_users) {
                            $query->where('sub_groups_id', $authSession->sub_groups_id);
                        }

                        if($level_groups_id){
                            $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups_id) {
                                $query_sub->where('enroll_type', 4)->where('enroll_type_id', $level_groups_id);
                            });
                        }else if ($authSession->super_users) {
                            $level_groups_owner = $authSession->level_groups()->get();
                            $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
                            $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

                            $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups) {
                                $query_sub->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
                            });
                        }

                        if($classrooms_id){
                            $query->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $classrooms_id);
                        }

                        $query->whereBetween('enroll_datetime',array($from_datetime, $to_datetime));
                    });
                    $exam->questions[$i]->answer[$a]->passing_score = $exam->questions[$i]->answer[$a]->passing_score->orderBy('id', 'desc');
                    $exam->questions[$i]->answer[$a]->passing_score = $exam->questions[$i]->answer[$a]->passing_score->count();
                }
            }
        }

        $survey = '';
        if($request['courses_id']){
            $survey = new Quiz();
            $survey = $survey->where('courses_id', $request['courses_id'])->where('type', 5)->where('status', 1)->first();
            $survey->quiz_id = $survey->id;
            $survey->count = Enroll2quiz::where('quiz_id', $survey->id)->whereHas('enroll', function($query) use ($courses_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime) {
                if($courses_id){
                    $query->where('courses_id', $courses_id);
                    $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                        $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                    });
                }else{
                    if ($authSession->super_users) {
                        $query->whereHas('courses', function($sub_query) use ($authSession, $authSessionGroups) {
                            $sub_query->where('admins_id', $authSession->id);
                            $sub_query->orWhere('level_public', 1);
                            $sub_query->whereHas('groups', function($unit_query) use ($authSessionGroups) {
                                $unit_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                            });
                        });
                    } else if (!$oRole->isSuper()) {
                        $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                            $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                        });
                    }
                }

                if($groups_id){
                    $query->where('groups_id', $groups_id);
                }

                if($sub_groups_id){
                    $query->where('sub_groups_id', $sub_groups_id);
                }else if ($authSession->super_users) {
                    $query->where('sub_groups_id', $authSession->sub_groups_id);
                }

                if($level_groups_id){
                    $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups_id) {
                        $query_sub->where('enroll_type', 4)->where('enroll_type_id', $level_groups_id);
                    });
                }else if ($authSession->super_users) {
                    $level_groups_owner = $authSession->level_groups()->get();
                    $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
                    $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

                    $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups) {
                        $query_sub->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
                    });
                }

                if($classrooms_id){
                    $query->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $classrooms_id);
                }

                $query->whereBetween('enroll_datetime',array($from_datetime, $to_datetime));
            })->count();
            $survey->questions = $survey->questions()->where('status', 1)->orderBy('order', 'asc')->get();
            for($i=0; $i<count($survey->questions); $i++) {
                $survey->questions[$i]->answer = $survey->questions[$i]->answer()->where('status', 1)->orderBy('order', 'asc')->get();
                for($a=0; $a<count($survey->questions[$i]->answer); $a++) {
                    $survey->questions[$i]->answer[$a]->passing_score = $survey->questions[$i]->answer[$a]->questions2answer()->where('answer_id', $survey->questions[$i]->answer[$a]->id);
                    $survey->questions[$i]->answer[$a]->passing_score = $survey->questions[$i]->answer[$a]->passing_score->with('enroll2quiz');
                    $survey->questions[$i]->answer[$a]->passing_score = $survey->questions[$i]->answer[$a]->passing_score->whereHas('enroll2quiz.enroll', function($query) use ($courses_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime) {
                        if($courses_id){
                            $query->where('courses_id', $courses_id);
                            $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                                $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                            });
                        }else{
                            if ($authSession->super_users) {
                                $query->whereHas('courses', function($sub_query) use ($authSession, $authSessionGroups) {
                                    $sub_query->where('admins_id', $authSession->id);
                                    $sub_query->orWhere('level_public', 1);
                                    $sub_query->whereHas('groups', function($unit_query) use ($authSessionGroups) {
                                        $unit_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                                    });
                                });
                            } else if (!$oRole->isSuper()) {
                                $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                                    $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                                });
                            }
                        }
                        if($groups_id){
                            $query->where('groups_id', $groups_id);
                        }

                        if($sub_groups_id){
                            $query->where('sub_groups_id', $sub_groups_id);
                        }else if ($authSession->super_users) {
                            $query->where('sub_groups_id', $authSession->sub_groups_id);
                        }

                        if($level_groups_id){
                            $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups_id) {
                                $query_sub->where('enroll_type', 4)->where('enroll_type_id', $level_groups_id);
                            });
                        }else if ($authSession->super_users) {
                            $level_groups_owner = $authSession->level_groups()->get();
                            $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
                            $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

                            $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups) {
                                $query_sub->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
                            });
                        }

                        if($classrooms_id){
                            $query->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $classrooms_id);
                        }
                            $query->whereBetween('enroll_datetime',array($from_datetime, $to_datetime));
                    });
                    $survey->questions[$i]->answer[$a]->passing_score = $survey->questions[$i]->answer[$a]->passing_score->orderBy('id', 'desc');
                    $survey->questions[$i]->answer[$a]->passing_score = $survey->questions[$i]->answer[$a]->passing_score->count();
                }
            }
        }

        $data = array(
            'pre_test' => $pre_test
           ,'post_test' => $post_test
           ,'compare' => $compare
           ,'exam' => $exam
           ,'survey' => $survey
        );

        return response()->json($data, 200);

    }

    public function export_enroll(Request $request, _RolesController $oRole)
    {
        if (!$request['from_date']) {
            $fromDate = date('Y-m-d', strtotime("-1 month"));
        } else {
            $fromDate = date("Y-m-d", strtotime($request['from_date']));
        }

        if (!$request['to_date']) {
            $toDate = date('Y-m-d');
        } else {
            $toDate = date("Y-m-d", strtotime($request['to_date']));
        }

        if (!$request['from_time']) {
            $fromTime = "00:00";
        } else {
            $fromTime = date("H:i", strtotime($request['from_time']));
        }

        if (!$request['to_time']) {
            $toTime = "23:59";
        } else {
            $toTime = date("H:i", strtotime($request['to_time']));
        }

        $from_datetime = date("Y-m-d H:i:s", strtotime($fromDate." ".$fromTime));
        $to_datetime = date("Y-m-d H:i:s", strtotime($toDate." ".$toTime));

        $courses_id = $request['courses_id'];
        $groups_id = $request['groups_id'];
        $sub_groups_id = $request['sub_groups_id'];
        $level_groups_id = $request['level_groups_id'];
        $classrooms_id = $request['classrooms_id'];

        $authSession = Auth::user();
        if($authSession->super_users){
            $authSessionGroups = $authSession->groups()->get();
        }else{
            $authSessionGroups = $authSession->admins_groups()->first();
            $authSessionGroups = $authSessionGroups->groups()->get();
        }

        Excel::create('ข้อมูลการเข้าเรียน '.date('Y-m-d H:i:s'), function($excel) use($courses_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime) {
            $excel->sheet('ข้อมูลการเข้าเรียน' , function($sheet) use ($courses_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime) {
                $sheet->row(1, array('Enroll ID.', 'Groups', 'Sub Groups', 'ชื่อ - สกุล', 'อีเมล์', 'รหัสหลักสูตร', 'ชื่อหลักสูตร', 'ประเภทการลงทะเบียน', 'Class Room', 'วันที่ลงทะเบียน', 'เข้าใช้งานระบบล่าสุด', 'สถานะการเรียน', 'สถานะการเรียน (%)', 'แบบทดสอบก่อนเรียน', 'แบบทดสอบก่อนเรียน (คะแนน)', 'แบบทดสอบก่อนเรียน (%)', 'แบบทดสอบหลังเรียน', 'แบบทดสอบหลังเรียน (คะแนน)', 'แบบทดสอบหลังเรียน (%)', 'แบบทดสอบเพื่อวัดความรู้', 'แบบทดสอบเพื่อวัดความรู้ (คะแนน)', 'แบบทดสอบเพื่อวัดความรู้ (%)', 'สถานะ', 'แบบสอบถาม', 'วุฒิบัตร', 'หมายเลขวุฒิบัตร', 'วันที่ออกวุฒิบัตร'));

                $data = new Enroll;
                if($courses_id){
                    $data = $data->where('courses_id', $courses_id);
                    $data = $data->whereHas('courses', function($query) use ($authSessionGroups) {
                        $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                    });
                }else{
                    if ($authSession->super_users) {
                        $data = $data->whereHas('courses', function($query) use ($authSession, $authSessionGroups) {
                            $query->where('admins_id', $authSession->id);
                            $query->orWhere('level_public', 1);
                            $query->whereHas('groups', function($sub_query) use ($authSessionGroups) {
                                $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                            });
                        });
                    } else if (!$oRole->isSuper()) {
                        $data = $data->whereHas('courses', function($query) use ($authSessionGroups) {
                            $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                        });
                    }
                }

                if($groups_id){
                    $data = $data->where('groups_id', $groups_id);
                }

                if($sub_groups_id){
                    $data = $data->where('sub_groups_id', $sub_groups_id);
                }else if ($authSession->super_users) {
                    $data = $data->where('sub_groups_id', $authSession->sub_groups_id);
                }

                if($level_groups_id){
                    $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups_id) {
                        $query_sub->where('enroll_type', 4)->where('enroll_type_id', $level_groups_id);
                    });
                }else if ($authSession->super_users) {
                    $level_groups_owner = $authSession->level_groups()->get();
                    $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
                    $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

                    $data = $data->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                        $query->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
                    });
                }

                if($classrooms_id){
                    $data = $data->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $classrooms_id);
                }
                $data = $data->whereBetween('enroll_datetime',array($from_datetime, $to_datetime));
                $data = $data->orderBy('id', 'desc');
                $data = $data->with('courses');
                $data = $data->get();
                for($i=0; $i<count($data); $i++) {
                    $data[$i]->groups = $data[$i]->groups()->first();
                    if($data[$i]->sub_groups_id){
                        $data[$i]->sub_groups = $data[$i]->sub_groups()->first();
                        $data[$i]->sub_groups = $data[$i]->sub_groups->title;
                    }else{
                        $data[$i]->sub_groups = '';
                    }
                    $data[$i]->members = $data[$i]->members()->first();
                    $data[$i]->courses = $data[$i]->courses()->first();

                    if($data[$i]->type == 1){
                        $data[$i]->type = 'ClassRoom (Targets)';
                        $data[$i]->classrooms = new ClassRooms();
                        $data[$i]->classrooms = $data[$i]->classrooms->find($data[$i]->type_id)->first();
                        $data[$i]->classrooms = $data[$i]->classrooms->title;
                    }
                    elseif($data[$i]->type == 2){
                        $data[$i]->type = 'ClassRoom (Groups)';
                        $data[$i]->classrooms = new ClassRooms();
                        $data[$i]->classrooms = $data[$i]->classrooms->find($data[$i]->type_id)->first();
                        $data[$i]->classrooms = $data[$i]->classrooms->title;
                    }
                    elseif($data[$i]->type == 3){
                        $data[$i]->type = 'Targets';
                        $data[$i]->classrooms = '';
                    }
                    elseif($data[$i]->type == 4){
                        $data[$i]->type = 'Level Groups';
                        $data[$i]->classrooms = '';
                    }
                    else{
                        $data[$i]->type = 'Public';
                        $data[$i]->classrooms = '';
                    }

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
                                $data[$i]->learning_percentage = number_format($data[$i]->duration2enroll/$data[$i]->duration2topic * 100);
                            }
                        }
                    }
                    if($data[$i]->learning_percentage){
                        if($data[$i]->learning_percentage >= $data[$i]->courses->percentage){
                            $data[$i]->learning_status = 'ผ่าน';
                        }else{
                            $data[$i]->learning_status = 'กำลังเรียน';
                        }
                    }else{
                        $data[$i]->learning_percentage = null;
                        $data[$i]->learning_status = 'ยังไม่เรียน';
                    }

                    $data[$i]->pre_test = $data[$i]->enroll2quiz()->where('type', 1)->orderBy('id', 'desc')->first();
                    if($data[$i]->pre_test){
                        $data[$i]->pre_test->quiz = Quiz::find($data[$i]->pre_test->quiz_id);
                        if($data[$i]->pre_test->score){
                            $data[$i]->pre_test_score = $data[$i]->pre_test->score;
                            $data[$i]->pre_test_count = $data[$i]->pre_test->count;
                            $data[$i]->pre_test_percentage = number_format($data[$i]->pre_test->score/$data[$i]->pre_test->count * 100);
                            $data[$i]->pre_test_status = 'ทำแล้ว';
                        }
                    }else{
                        $data[$i]->pre_test_score = null;
                        $data[$i]->pre_test_count = null;
                        $data[$i]->pre_test_percentage = null;
                        $data[$i]->pre_test_status = 'ยังไม่ได้ทำ';
                    }

                    $data[$i]->post_test = $data[$i]->enroll2quiz()->where('type', 4)->orderBy('id', 'desc')->first();
                    if($data[$i]->post_test){
                        $data[$i]->post_test->quiz = Quiz::find($data[$i]->post_test->quiz_id);
                        if($data[$i]->post_test->score){
                            $data[$i]->post_test_score = $data[$i]->post_test->score;
                            $data[$i]->post_test_count = $data[$i]->post_test->count;
                            $data[$i]->post_test_percentage = number_format($data[$i]->post_test->score/$data[$i]->post_test->count * 100);
                            $data[$i]->post_test_status = 'ทำแล้ว';
                        }
                    }else{
                        $data[$i]->post_test_score = null;
                        $data[$i]->post_test_count = null;
                        $data[$i]->post_test_percentage = null;
                        $data[$i]->post_test_status = 'ยังไม่ได้ทำ';
                    }

                    $data[$i]->exam = $data[$i]->enroll2quiz()->where('type', 3)->orderBy('id', 'desc')->first();
                    if($data[$i]->exam){
                        $data[$i]->exam->quiz = Quiz::find($data[$i]->exam->quiz_id);
                        if($data[$i]->exam->score){
                            $data[$i]->exam_percentage = number_format($data[$i]->exam->score/$data[$i]->exam->count * 100);
                            if($data[$i]->exam_percentage >= $data[$i]->exam->quiz->passing_score){
                                $data[$i]->exam_score = $data[$i]->exam->score;
                                $data[$i]->exam_count = $data[$i]->exam->count;
                                $data[$i]->exam_status = 'ผ่าน';
                                $data[$i]->course_status = 'ผ่าน';
                            }else{
                                $data[$i]->exam_score = $data[$i]->exam->score;
                                $data[$i]->exam_count = $data[$i]->exam->count;
                                $data[$i]->exam_status = 'ยังไม่ผ่าน';
                                $data[$i]->course_status = 'ยังไม่ผ่าน';
                            }
                        }
                    }else{
                        $data[$i]->exam_score = null;
                        $data[$i]->exam_count = null;
                        $data[$i]->exam_percentage = null;
                        $data[$i]->exam_status = 'ยังไม่ได้ทำ';
                        $data[$i]->course_status = 'ยังไม่ผ่าน';
                    }

                    $data[$i]->survey = $data[$i]->enroll2quiz()->where('type', 5)->orderBy('id', 'desc')->first();
                    if($data[$i]->survey){
                        $data[$i]->survey_status = 'ทำแล้ว';
                    }else{
                        $data[$i]->survey_status = 'ยังไม่ได้ทำ';
                    }

                    if($data[$i]->certificate_reference_number){
                        $data[$i]->certificate = 'พิมพ์วุฒิบัตร';
                    }else{
                        $data[$i]->certificate = 'ไม่พิมพ์วุฒิบัตร';
                    }


                    $sheet->row($i+2, array($data[$i]->id, $data[$i]->groups->title, $data[$i]->sub_groups, $data[$i]->members->first_name.' '.$data[$i]->members->last_name , $data[$i]->members->email, $data[$i]->courses->code, $data[$i]->courses->title, $data[$i]->type, $data[$i]->classrooms, $data[$i]->enroll_datetime, $data[$i]->last_datetime, $data[$i]->learning_status, $data[$i]->learning_percentage, $data[$i]->pre_test_status, $data[$i]->pre_test_score, $data[$i]->pre_test_percentage, $data[$i]->post_test_status, $data[$i]->post_test_score, $data[$i]->post_test_percentage, $data[$i]->exam_status, $data[$i]->exam_score, $data[$i]->exam_percentage, $data[$i]->course_status, $data[$i]->survey_status, $data[$i]->certificate, $data[$i]->certificate_reference_number, $data[$i]->certificate_datetime));
                }
            });
        })->download('xls');

    }

    public function export_quiz(Request $request, _RolesController $oRole){

        if (!$request['from_date']) {
            $fromDate = date('Y-m-d', strtotime("-1 month"));
        } else {
            $fromDate = date("Y-m-d", strtotime($request['from_date']));
        }

        if (!$request['to_date']) {
            $toDate = date('Y-m-d');
        } else {
            $toDate = date("Y-m-d", strtotime($request['to_date']));
        }

        if (!$request['from_time']) {
            $fromTime = "00:00";
        } else {
            $fromTime = date("H:i", strtotime($request['from_time']));
        }

        if (!$request['to_time']) {
            $toTime = "23:59";
        } else {
            $toTime = date("H:i", strtotime($request['to_time']));
        }

        $from_datetime = date("Y-m-d H:i:s", strtotime($fromDate." ".$fromTime));
        $to_datetime = date("Y-m-d H:i:s", strtotime($toDate." ".$toTime));

        $courses_id = $request['courses_id'];
        $groups_id = $request['groups_id'];
        $sub_groups_id = $request['sub_groups_id'];
        $level_groups_id = $request['level_groups_id'];
        $classrooms_id = $request['classrooms_id'];

        $authSession = Auth::user();
        if($authSession->super_users){
            $authSessionGroups = $authSession->groups()->get();
        }else{
            $authSessionGroups = $authSession->admins_groups()->first();
            $authSessionGroups = $authSessionGroups->groups()->get();
        }

        Excel::create('ข้อมูลการทำข้อสอบ '.date('Y-m-d H:i:s'), function($excel) use($courses_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime) {
            $excel->sheet('ข้อมูลการทำข้อสอบ' , function($sheet) use ($courses_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime) {
                $sheet->row(1, array('Enroll ID.', 'Groups', 'Sub Groups', 'ชื่อ - สกุล', 'อีเมล์', 'รหัสหลักสูตร', 'ชื่อหลักสูตร', 'ชุดคำถาม', 'ประเภท', 'คะแนน', 'คะแนนเต็ม', 'คิดเป็น​ (%)', 'สถานะ', 'วันเวลาทำข้อสอบ'));

                $data = new Enroll2Quiz();
                $data = $data->with('enroll');
                $data = $data->whereIn('type',  [1, 2, 3, 4]);
                $data = $data->whereNotNull('score');
                $data = $data->whereHas('enroll', function($query) use ($courses_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime) {
                    if($courses_id){
                        $query->where('courses_id', $courses_id);
                        $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                            $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                        });
                    }else{
                        if ($authSession->super_users) {
                            $query->whereHas('courses', function($sub_query) use ($authSession, $authSessionGroups) {
                                $sub_query->where('admins_id', $authSession->id);
                                $sub_query->orWhere('level_public', 1);
                                $sub_query->whereHas('groups', function($unit_query) use ($authSessionGroups) {
                                    $unit_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                                });
                            });
                        } else if (!$oRole->isSuper()) {
                            $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                                $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                            });
                        }
                    }

                    if($groups_id){
                        $query->where('groups_id', $groups_id);
                    }

                    if($sub_groups_id){
                        $query->where('sub_groups_id', $sub_groups_id);
                    }else if ($authSession->super_users) {
                        $query->where('sub_groups_id', $authSession->sub_groups_id);
                    }

                    if($level_groups_id){
                        $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups_id) {
                            $query_sub->where('enroll_type', 4)->where('enroll_type_id', $level_groups_id);
                        });
                    }else if ($authSession->super_users) {
                        $level_groups_owner = $authSession->level_groups()->get();
                        $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
                        $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

                        $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups) {
                            $query_sub->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
                        });
                    }

                    if($classrooms_id){
                        $query->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $classrooms_id);
                    }
                        $query->whereBetween('enroll_datetime',array($from_datetime, $to_datetime));
                });
                $data = $data->orderBy('id', 'desc');
                $data = $data->get();
                for($i=0; $i<count($data); $i++) {
                    $data[$i]->enroll = $data[$i]->enroll()->first();
                    $data[$i]->enroll->groups = $data[$i]->enroll->groups()->first();
                    if($data[$i]->enroll->sub_groups_id){
                        $data[$i]->enroll->sub_groups = $data[$i]->enroll->sub_groups()->first();
                        $data[$i]->enroll->sub_groups = $data[$i]->enroll->sub_groups->title;
                    }else{
                        $data[$i]->enroll->sub_groups = '';
                    }
                    $data[$i]->enroll->members = $data[$i]->enroll->members()->first();
                    $data[$i]->enroll->courses = $data[$i]->enroll->courses()->first();
                    $data[$i]->quiz = $data[$i]->quiz()->first();
                    if($data[$i]->quiz->type == 1){ $data[$i]->quiz->quiz_type = 'Pre-Test'; }
                    if($data[$i]->quiz->type == 2){ $data[$i]->quiz->quiz_type = 'Quiz'; }
                    if($data[$i]->quiz->type == 3){ $data[$i]->quiz->quiz_type = 'Exam'; }
                    if($data[$i]->quiz->type == 4){ $data[$i]->quiz->quiz_type = 'Post-Test'; }

                    if($data[$i]->quiz->type == 3){
                        $data[$i]->percentage = number_format($data[$i]->score/$data[$i]->count * 100);
                        if($data[$i]->percentage >= $data[$i]->quiz->passing_score){
                            $data[$i]->quiz_status = 'ผ่าน';
                        }else{
                            $data[$i]->quiz_status = 'ยังไม่ผ่าน';
                        }
                    }else{
                        $data[$i]->percentage = null;
                        $data[$i]->quiz_status = null;
                    }


                    $sheet->row($i+2, array($data[$i]->enroll->id, $data[$i]->enroll->groups->title, $data[$i]->enroll->sub_groups, $data[$i]->enroll->members->first_name.' '.$data[$i]->enroll->members->last_name, $data[$i]->enroll->members->email, $data[$i]->enroll->courses->code, $data[$i]->enroll->courses->title, $data[$i]->quiz->title, $data[$i]->quiz->quiz_type, $data[$i]->score, $data[$i]->count, $data[$i]->percentage, $data[$i]->quiz_status, $data[$i]->datetime));
                }
            });
        })->download('xls');

    }

    public function export_course(Request $request, _RolesController $oRole){

        if (!$request['from_date']) {
            $fromDate = date('Y-m-d', strtotime("-1 month"));
        } else {
            $fromDate = date("Y-m-d", strtotime($request['from_date']));
        }

        if (!$request['to_date']) {
            $toDate = date('Y-m-d');
        } else {
            $toDate = date("Y-m-d", strtotime($request['to_date']));
        }

        if (!$request['from_time']) {
            $fromTime = "00:00";
        } else {
            $fromTime = date("H:i", strtotime($request['from_time']));
        }

        if (!$request['to_time']) {
            $toTime = "23:59";
        } else {
            $toTime = date("H:i", strtotime($request['to_time']));
        }

        $from_datetime = date("Y-m-d H:i:s", strtotime($fromDate." ".$fromTime));
        $to_datetime = date("Y-m-d H:i:s", strtotime($toDate." ".$toTime));

        $courses_id = $request['courses_id'];
        $topics_id = $request['topics_id'];
        $groups_id = $request['groups_id'];
        $sub_groups_id = $request['sub_groups_id'];
        $level_groups_id = $request['level_groups_id'];
        $classrooms_id = $request['classrooms_id'];

        $authSession = Auth::user();
        if($authSession->super_users){
            $authSessionGroups = $authSession->groups()->get();
        }else{
            $authSessionGroups = $authSession->admins_groups()->first();
            $authSessionGroups = $authSessionGroups->groups()->get();
        }

        $course_data = Courses::find($courses_id);
        $course_data->topics = $course_data->topics()->whereNotNull('parent')->orderBy('order','asc')->get();

        $enroll = Enroll::select('id', 'members_id', 'courses_id');
        if($courses_id){
            $enroll = $enroll->where('courses_id', $courses_id);
            $enroll = $enroll->whereHas('courses', function($query) use ($authSessionGroups) {
                $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
            });
        }else{
            if ($authSession->super_users) {
                $enroll = $enroll->whereHas('courses', function($query) use ($authSession, $authSessionGroups) {
                    $query->where('admins_id', $authSession->id);
                    $query->orWhere('level_public', 1);
                    $query->whereHas('groups', function($sub_query) use ($authSessionGroups) {
                        $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                    });
                });
            } else if (!$oRole->isSuper()) {
                $enroll = $enroll->whereHas('courses', function($query) use ($authSessionGroups) {
                    $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            }
        }

        if($groups_id){
            $enroll = $enroll->where('groups_id', $groups_id);
        }

        if($sub_groups_id){
            $enroll = $enroll->where('sub_groups_id', $sub_groups_id);
        }else if ($authSession->super_users) {
            $enroll = $enroll->where('sub_groups_id', $authSession->sub_groups_id);
        }

        if($level_groups_id){
            $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups_id) {
                $query_sub->where('enroll_type', 4)->where('enroll_type_id', $level_groups_id);
            });
        }else if ($authSession->super_users) {
            $level_groups_owner = $authSession->level_groups()->get();
            $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
            $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

            $enroll = $enroll->where('enroll_type', 3)->orWhere(function($query) use ($level_groups) {
                $query->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
            });
        }

        if($classrooms_id){
            $enroll = $enroll->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $classrooms_id);
        }

        $enroll = $enroll->where('courses_id', $course_data->id)->orderBy('id', 'asc')->get();

        $c_enroll = count($enroll);
        $enroll_data = [];
        $enroll_data_member = [];

        for ($i=0; $i < $c_enroll; $i++) {
            $enroll_data[] = $enroll[$i]->id;
            $enroll_data_member[] = $enroll[$i]->members_id;
        }

        // Excel::create('ข้อมูลภาพรวมหลักสูตร '.date('Y-m-d H:i:s'), function($excel) use($courses_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime) {
        Excel::create('ข้อมูลการเข้าเรียนถ่ายทอดสดหลักสูตร '.$course_data->code.'_'.$course_data->title, function($excel) use($courses_id, $topics_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime, $course_data, $enroll_data_member, $enroll_data) {
            $excel->sheet('ข้อมูลการเข้าเรียน' , function($sheet) use ($courses_id, $topics_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime, $course_data, $enroll_data_member, $enroll_data) {

                $data = Member2Live::where('courses_id', $course_data->id)->orderBy('datetime', 'asc')
                        ->whereBetween('datetime', array($from_datetime, $to_datetime))
                        ->whereIn('members_id', $enroll_data_member)
                        ->get();

                $sheet->row(1, array('วันที่เริ่มต้น', '', $from_datetime));
                $sheet->row(2, array('วันที่สิ้นสุด', '', $to_datetime));
                $sheet->row(3, array('รหัสหลักสูตร', '', $course_data->code));
                $sheet->row(4, array('ชื่อหลักสูตร', '', $course_data->title));
                // $sheet->row(5, array('จำนวนคนที่เข้าเรียนหลักสูตรถ่ายทอดสดทั้งหมด (คน)'));
                $sheet->row(5, array('จำนวนการรับชมทั้งหมด (ครั้ง)', '', count($data)));
                $sheet->row(6, '');
                $sheet->row(7, array(
                    'ลำดับ',
                    'วันที่รับชม',
                    'หัวข้อ',
                    'ชื่อ - สกุล',
                    'อีเมล์',
                    'IP Address',
                    'City',
                    'Country',
                    'Device',
                    'Browser'
                ));

                for ($i=0; $i < count($data); $i++) {
                    $data[$i]->members = $data[$i]->members()->first();
                    $data[$i]->topics = $data[$i]->topics()->first();
                    $index = $i + 1;
                    $name = $data[$i]->members->first_name.' '.$data[$i]->members->last_name;

                    if ($data[$i]->is_foreign == 1) {
                        $name = $data[$i]->members->first_name_en.' '.$data[$i]->members->last_name_en;
                    }

                    $sheet->row(7 + $index, array(
                        $index,
                        $data[$i]->datetime,
                        $data[$i]->topics->title,
                        $name,
                        $data[$i]->members->email,
                        $data[$i]->ip,
                        $data[$i]->city,
                        $data[$i]->country.' ('.$data[$i]->isoCode.')',
                        $data[$i]->device.' ('.$data[$i]->platform.')',
                        $data[$i]->browser.' ('.$data[$i]->browser_version.')'
                    ));
                }

                $sheet->mergeCells('A1:B1');
                $sheet->mergeCells('A2:B2');
                $sheet->mergeCells('A3:B3');
                $sheet->mergeCells('A4:B4');
                $sheet->mergeCells('A5:B5');
                $sheet->mergeCells('A6:I6');

                $sheet->mergeCells('C1:D1');
                $sheet->mergeCells('C2:D2');
                $sheet->mergeCells('C3:D3');
                $sheet->mergeCells('C4:D4');
                $sheet->mergeCells('C5:D5');

                $sheet->setAutoSize(true);
                $sheet->setWidth('B', 25);
                $sheet->setWidth('C', 30);

                $sheet->cells('C5:D5', function($cells) {
                    $cells->setAlignment('left');
                });
            });
            $excel->sheet('ข้อมูลระยะเวลาการเข้าเรียน' , function($sheet) use ($courses_id, $topics_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime, $course_data, $enroll_data_member, $enroll_data) {
                // $data = Enroll2TopicLive::whereIn('enroll_id', $enroll_data)->get();
                $data = new Enroll2TopicLive;
                $data = $data->select(DB::raw('sum(duration) as duration, topics_id, enroll_id, enter_datetime'))
                            ->whereIn('enroll_id', $enroll_data);

                if ($topics_id) {
                    $data = $data->where('topics_id', $topics_id);
                }

                $data = $data->whereBetween('enter_datetime',array($from_datetime, $to_datetime));

                $data = $data->groupBy('enroll_id');
                $data = $data->groupBy('topics_id');

                $data = $data->orderByRaw('enter_datetime asc');

                $data = $data->get();

                $sheet->row(1, array('วันที่เริ่มต้น', '', $from_datetime));
                $sheet->row(2, array('วันที่สิ้นสุด', '', $to_datetime));
                $sheet->row(3, array('รหัสหลักสูตร', '', $course_data->code));
                $sheet->row(4, array('ชื่อหลักสูตร', '', $course_data->title));
                // $sheet->row(5, array('จำนวนคนที่เข้าเรียนหลักสูตรถ่ายทอดสดทั้งหมด (คน)'));
                $sheet->row(5, array('จำนวนการเข้าเรียนทั้งหมด (ครั้ง)', '', count($data)));
                $sheet->row(6, '');
                $sheet->row(7, array(
                    'ลำดับ',
                    'Enroll ID',
                    'วันที่เข้าเรียน',
                    'หัวข้อ',
                    'ชื่อ - สกุล',
                    'อีเมล์',
                    'จำนวนเวลาที่เข้าเรียน',
                    'จำนวนเวลาถ่ายทอดสดทั้งหมด'
                ));

                for ($i=0; $i < count($data); $i++) {
                    // $data[$i]->members = $data[$i]->members()->first();
                    $enroll = $data[$i]->enroll()->select('id', 'groups_id', 'members_id')->first();
                    $data[$i]->members = Members::select('id', 'first_name', 'last_name', 'email')->where('id', $enroll->members_id)->first();
                    $data[$i]->topics = $data[$i]->topics()->first();
                    $index = $i + 1;
                    $name = $data[$i]->members->first_name.' '.$data[$i]->members->last_name;

                    if ($data[$i]->is_foreign == 1) {
                        $name = $data[$i]->members->first_name_en.' '.$data[$i]->members->last_name_en;
                    }

                    $live_result = LiveResults::select('id', 'topic_id', 'live_start_datetime', 'live_end_datetime')->where('topic_id', $data[$i]->topics->id)->first();

                    if (isset($live_result->live_start_datetime) && isset($live_result->live_end_datetime)) {
                        $live_result_start_datetime = Carbon::parse($live_result->live_start_datetime);
                        $live_result_end_datetime = Carbon::parse($live_result->live_end_datetime);

                        $diff = $live_result_start_datetime->diff($live_result_end_datetime);

                        $diff_format = '';
                        if ($diff->y > 0) {
                            $diff_format .= '%y ปี ';
                        }

                        if ($diff->m > 0) {
                            $diff_format .= '%m เดือน ';
                        }

                        if ($diff->d > 0) {
                            $diff_format .= '%a วัน ';
                        }

                        if ($diff->h > 0) {
                            $diff_format .= '%h ชั่วโมง ';
                        }

                        if ($diff->i > 0) {
                            $diff_format .= '%i นาที ';
                        }

                        if ($diff->s > 0) {
                            $diff_format .= '%s วินาที';
                        }

                        $data[$i]->live_duration = $diff->format($diff_format);

                    } else {
                        $data[$i]->live_duration = 'กำลังถ่ายทอดสด';
                        if (empty($live_result->live_start_datetime) && empty($live_result->live_end_datetime)) {
                            $data[$i]->live_duration = 'รอการถ่ายทอดสด';
                        }
                    }

                    if ($data[$i]->duration != 0) {
                        $d1 = Carbon::now();
                        $d2 = Carbon::now();
                        $d2->addSeconds($data[$i]->duration);

                        $d_diff = $d2->diff($d1);
                        $d_diff_format = '';
                        if ($d_diff->y > 0) {
                            $d_diff_format .= '%y ปี ';
                        }

                        if ($d_diff->m > 0) {
                            $d_diff_format .= '%m เดือน ';
                        }

                        if ($d_diff->d > 0) {
                            $d_diff_format .= '%a วัน ';
                        }

                        if ($d_diff->h > 0) {
                            $d_diff_format .= '%h ชั่วโมง ';
                        }

                        if ($d_diff->i > 0) {
                            $d_diff_format .= '%i นาที ';
                        }

                        if ($d_diff->s > 0) {
                            $d_diff_format .= '%s วินาที';
                        }

                        $data[$i]->study_duration = $d_diff->format($d_diff_format);
                    } else {
                        $data[$i]->study_duration = '0 วินาที';
                    }

                    $sheet->row(7 + $index, array(
                        $index,
                        $data[$i]->enroll_id,
                        $data[$i]->enter_datetime,
                        $data[$i]->topics->title,
                        $name,
                        $data[$i]->members->email,
                        $data[$i]->study_duration,
                        $data[$i]->live_duration
                    ));
                }

                $sheet->mergeCells('A1:B1');
                $sheet->mergeCells('A2:B2');
                $sheet->mergeCells('A3:B3');
                $sheet->mergeCells('A4:B4');
                $sheet->mergeCells('A5:B5');
                $sheet->mergeCells('A6:H6');

                $sheet->mergeCells('C1:D1');
                $sheet->mergeCells('C2:D2');
                $sheet->mergeCells('C3:D3');
                $sheet->mergeCells('C4:D4');
                $sheet->mergeCells('C5:D5');

                $sheet->setAutoSize(true);
                $sheet->setWidth('B', 25);
                $sheet->setWidth('D', 25);

                $sheet->cells('C5:D5', function($cells) {
                    $cells->setAlignment('left');
                });
            });
            $excel->setActiveSheetIndex(0);
        })->download('xls');

    }

    public function export_questions(Request $request, _RolesController $oRole){

        //
        if (!$request['from_date']) {
            $fromDate = date('Y-m-d', strtotime("-1 month"));
        } else {
            $fromDate = date("Y-m-d", strtotime($request['from_date']));
        }

        if (!$request['to_date']) {
            $toDate = date('Y-m-d');
        } else {
            $toDate = date("Y-m-d", strtotime($request['to_date']));
        }

        if (!$request['from_time']) {
            $fromTime = "00:00";
        } else {
            $fromTime = date("H:i", strtotime($request['from_time']));
        }

        if (!$request['to_time']) {
            $toTime = "23:59";
        } else {
            $toTime = date("H:i", strtotime($request['to_time']));
        }

        $from_datetime = date("Y-m-d H:i:s", strtotime($fromDate." ".$fromTime));
        $to_datetime = date("Y-m-d H:i:s", strtotime($toDate." ".$toTime));

        $quiz_id = $request['quiz_id'];
        $courses_id = $request['courses_id'];
        $groups_id = $request['groups_id'];
        $sub_groups_id = $request['sub_groups_id'];
        $level_groups_id = $request['level_groups_id'];
        $classrooms_id = $request['classrooms_id'];

        $authSession = Auth::user();
        if($authSession->super_users){
            $authSessionGroups = $authSession->groups()->get();
        }else{
            $authSessionGroups = $authSession->admins_groups()->first();
            $authSessionGroups = $authSessionGroups->groups()->get();
        }

        $data = new Quiz();
        $data = $data->find($quiz_id);
        $data->courses = $data->courses()->first();

        Excel::create($data->title.' ('.$data->courses->code.' '.$data->courses->title.') '.date('Y-m-d H:i:s'), function($excel) use($quiz_id, $courses_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime) {
            $excel->sheet('ข้อมูลการทำข้อสอบ' , function($sheet) use ($quiz_id, $courses_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime) {
                $sheet->row(1, array('ข้อคำถาม', 'คำถาม', 'ข้อคำตอบ', 'คำตอบ', 'ตอบ', 'เฉลยข้อที่ถูก'));

                $data = new Quiz();
                $data = $data->find($quiz_id);
                $data->questions = $data->questions()->where('status', 1)->orderBy('order', 'asc')->get();
                for($i=0; $i<count($data->questions); $i++) {
                    $data->questions[$i]->answer = $data->questions[$i]->answer()->where('status', 1)->orderBy('order', 'asc')->get();

                    if($i == 0){
                        $data->questions[$i]->row = $i+2;
                    }else{
                        $data->questions[$i]->row = ($i*$data->questions[$i]->answer->count())+2;
                    }

                    for($a=0; $a<count($data->questions[$i]->answer); $a++) {
                        $data->questions[$i]->answer[$a]->passing_score = $data->questions[$i]->answer[$a]->questions2answer()->where('answer_id', $data->questions[$i]->answer[$a]->id);
                        $data->questions[$i]->answer[$a]->passing_score = $data->questions[$i]->answer[$a]->passing_score->with('enroll2quiz');
                        $data->questions[$i]->answer[$a]->passing_score = $data->questions[$i]->answer[$a]->passing_score->whereHas('enroll2quiz.enroll', function($query) use ($courses_id, $groups_id, $sub_groups_id, $level_groups_id, $classrooms_id, $authSessionGroups, $authSession, $oRole, $from_datetime, $to_datetime) {
                            if($courses_id){
                                $query->where('courses_id', $courses_id);
                                $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                                    $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                                });
                            }else{
                                if ($authSession->super_users) {
                                    $query->whereHas('courses', function($sub_query) use ($authSession, $authSessionGroups) {
                                        $sub_query->where('admins_id', $authSession->id);
                                        $sub_query->orWhere('level_public', 1);
                                        $sub_query->whereHas('groups', function($unit_query) use ($authSessionGroups) {
                                            $unit_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                                        });
                                    });
                                } else if (!$oRole->isSuper()) {
                                    $query->whereHas('courses', function($sub_query) use ($authSessionGroups) {
                                        $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                                    });
                                }
                            }

                            if($groups_id){
                                $query->where('groups_id', $groups_id);
                            }

                            if($sub_groups_id){
                                $query->where('sub_groups_id', $sub_groups_id);
                            }else if ($authSession->super_users) {
                                $query->where('sub_groups_id', $authSession->sub_groups_id);
                            }

                            if($level_groups_id){
                                $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups_id) {
                                    $query_sub->where('enroll_type', 4)->where('enroll_type_id', $level_groups_id);
                                });
                            }else if ($authSession->super_users) {
                                $level_groups_owner = $authSession->level_groups()->get();
                                $level_groups_access = $authSession->admin2level_group()->where('level_groups.approve',1)->get();
                                $level_groups = array_merge($level_groups_owner->toArray(), $level_groups_access->toArray());

                                $query->where('enroll_type', 3)->orWhere(function($query_sub) use ($level_groups) {
                                    $query_sub->where('enroll_type', 4)->whereIn('enroll_type_id', array_pluck($level_groups, 'id'));
                                });
                            }

                            if($classrooms_id){
                                $query->where('enroll_type', 1)->orWhere('enroll_type', 2)->where('enroll_type_id', $classrooms_id);
                            }

                            $query->whereBetween('enroll_datetime',array($from_datetime, $to_datetime));
                        });
                        $data->questions[$i]->answer[$a]->passing_score = $data->questions[$i]->answer[$a]->passing_score->orderBy('id', 'desc');
                        $data->questions[$i]->answer[$a]->passing_score = $data->questions[$i]->answer[$a]->passing_score->count();

                        if($data->questions[$i]->answer[$a]->correct){
                            $data->questions[$i]->answer[$a]->correct = $a+1;
                        }else{
                            $data->questions[$i]->answer[$a]->correct = null;
                        }

                        if($i == 0){
                            $data->questions[$i]->answer[$a]->row = $a+2;
                            if($a == 0){
                                $sheet->row($data->questions[$i]->answer[$a]->row, array($i+1, strip_tags($data->questions[$i]->questions), $a+1, strip_tags($data->questions[$i]->answer[$a]->answer), $data->questions[$i]->answer[$a]->passing_score, $data->questions[$i]->answer[$a]->correct));
                            }else{
                                $sheet->row($data->questions[$i]->answer[$a]->row, array('', '', $a+1, strip_tags($data->questions[$i]->answer[$a]->answer), $data->questions[$i]->answer[$a]->passing_score, $data->questions[$i]->answer[$a]->correct));
                            }
                        }else{
                            $data->questions[$i]->answer[$a]->row = $a+$data->questions[$i]->row;
                            if($a == 0){
                                $sheet->row($data->questions[$i]->answer[$a]->row, array($i+1, strip_tags($data->questions[$i]->questions), $a+1, strip_tags($data->questions[$i]->answer[$a]->answer), $data->questions[$i]->answer[$a]->passing_score, $data->questions[$i]->answer[$a]->correct));
                            }else{
                                $sheet->row($data->questions[$i]->answer[$a]->row, array('', '', $a+1, strip_tags($data->questions[$i]->answer[$a]->answer), $data->questions[$i]->answer[$a]->passing_score, $data->questions[$i]->answer[$a]->correct));
                            }
                        }

                    }

                }
            });
        })->download('xls');
    }

    private function dateRange( $first, $last, $step = '+1 day', $format = 'Y-m-d' )
    {
        $dates = array();
        $current = strtotime( $first );
        $last = strtotime( $last );

        while( $current <= $last ) {

            $dates[] = date( $format, $current );
            $current = strtotime( $step, $current );
        }

        return $dates;
    }

}
