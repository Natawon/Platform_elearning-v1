<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;

use App\Models\AdminsGroups;
use App\Models\Enroll;
use App\Models\Quiz;
use App\Models\Topics;
use App\Models\Questions2Answer;
use App\Models\ClassRooms;

use Input;
use Auth;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Rap2hpoutre\FastExcel\SheetCollection;
use FastExcel;

class UsageStatisticController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request, _RolesController $oRole)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 3600);

        $search = $request['search'];

        $data = new Enroll;

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
        $data = $data->with([
            'members',
            'groups',
            'courses.topics' => function($query) {
                $query->whereNull('parent')->orderBy('order','asc');
            },
            'courses.topics.sub_topics' => function($query) {
                $query->orderBy('order','asc');
            },
            'latest_pre_test',
            'latest_post_test',
            'latest_exam',
            'latest_survey',
            'enroll2topic'
        ]);
        $data = $data->whereHas('members', function($query) use ($search) {
            $query->where('email', 'like' , "%".$search."%");
            $query->orWhere('first_name', 'like' , "%".$search."%");
        });
        $data = $data->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);

        $timeToday = strtotime('TODAY');

        for($i=0; $i<count($data); $i++) {
            // $data[$i]->members = $data[$i]->members()->first();
            // $data[$i]->groups = $data[$i]->members->groups()->first();
            // $data[$i]->courses = $data[$i]->courses()->first();

            // $data[$i]->topics = $data[$i]->courses->topics()->whereNull('parent')->orderBy('order','asc')->get();
            for($a=0; $a<count($data[$i]->courses->topics); $a++) {
                // $data[$i]->courses->topics[$a]->parent = Topics::where('parent', $data[$i]->courses->topics[$a]->id)->orderBy('order','asc')->get();
                for($x=0; $x<count($data[$i]->courses->topics[$a]->sub_topics); $x++) {

                    // $data[$i]->courses->topics[$a]->sub_topics[$x]->enroll2topic = $data[$i]->courses->topics[$a]->sub_topics[$x]->enroll2topic()->where('enroll_id', $data[$i]->id)->first();

                    $data[$i]->courses->topics[$a]->sub_topics[$x]->enroll2topic = array_first($data[$i]->enroll2topic, function ($enroll2topic, $key) use ($data, $i, $a, $x) {
                        return $enroll2topic['topics_id'] == $data[$i]->courses->topics[$a]->sub_topics[$x]->id;
                    });

                    $data[$i]->courses->topics[$a]->sub_topics[$x]->duration = (strtotime($data[$i]->courses->topics[$a]->sub_topics[$x]->end_time) - $timeToday) - (strtotime($data[$i]->courses->topics[$a]->sub_topics[$x]->start_time) - $timeToday);

                    if($data[$i]->courses->topics[$a]->sub_topics[$x]->enroll2topic){
                        if($data[$i]->courses->topics[$a]->sub_topics[$x]->enroll2topic->status){
                            $data[$i]->courses->topics[$a]->sub_topics[$x]->duration_enroll = $data[$i]->courses->topics[$a]->sub_topics[$x]->duration;
                        }else{
                            $data[$i]->courses->topics[$a]->sub_topics[$x]->duration_enroll = $data[$i]->courses->topics[$a]->sub_topics[$x]->enroll2topic->duration;
                        }
                    }else{
                        $data[$i]->courses->topics[$a]->sub_topics[$x]->duration_enroll = 0;
                    }

                    if($data[$i]->courses->topics[$a]->sub_topics[$x]->duration){
                        $data[$i]->courses->topics[$a]->sub_topics[$x]->progress = $data[$i]->courses->topics[$a]->sub_topics[$x]->duration_enroll/$data[$i]->courses->topics[$a]->sub_topics[$x]->duration;
                        $data[$i]->courses->topics[$a]->sub_topics[$x]->percentage = number_format($data[$i]->courses->topics[$a]->sub_topics[$x]->progress * 100);

                        $data[$i]->duration2topic += $data[$i]->courses->topics[$a]->sub_topics[$x]->duration;
                        $data[$i]->duration2enroll += $data[$i]->courses->topics[$a]->sub_topics[$x]->duration_enroll;

                        $data[$i]->duration2progress = $data[$i]->duration2enroll/$data[$i]->duration2topic;
                        $data[$i]->duration2percentage = number_format($data[$i]->duration2progress * 100);
                    }

                }
            }

            if($data[$i]->duration2percentage >= $data[$i]->courses->percentage){
                $data[$i]->courses->learning = true;
            }else{
                $data[$i]->courses->learning = false;
            }

            // $data[$i]->pre_test = $data[$i]->enroll2quiz()->where('type', 1)->orderBy('id', 'desc')->first();
            $data[$i]->pre_test = $data[$i]->latest_pre_test;
            unset($data[$i]->latest_pre_test);
            if($data[$i]->pre_test){
                if($data[$i]->pre_test->score){
                    $data[$i]->pre_test->progress = $data[$i]->pre_test->score/$data[$i]->pre_test->count;
                }
                $data[$i]->pre_test->percentage = number_format($data[$i]->pre_test->progress * 100);
                // $data[$i]->pre_test->quiz = Quiz::find($data[$i]->pre_test->quiz_id);
                if($data[$i]->pre_test->percentage >= $data[$i]->pre_test->quiz->passing_score){
                    $data[$i]->pre_test->learning = true;
                }else{
                    $data[$i]->pre_test->learning = false;
                }

            }

            // $data[$i]->post_test = $data[$i]->enroll2quiz()->where('type', 4)->orderBy('id', 'desc')->first();
            $data[$i]->post_test = $data[$i]->latest_post_test;
            unset($data[$i]->latest_post_test);
            if($data[$i]->post_test){
                if($data[$i]->post_test->score){
                    $data[$i]->post_test->progress = $data[$i]->post_test->score/$data[$i]->post_test->count;
                }
                $data[$i]->post_test->percentage = number_format($data[$i]->post_test->progress * 100);
                // $data[$i]->post_test->quiz = Quiz::find($data[$i]->post_test->quiz_id);
                if($data[$i]->post_test->percentage >= $data[$i]->post_test->quiz->passing_score){
                    $data[$i]->post_test->learning = true;
                }else{
                    $data[$i]->post_test->learning = false;
                }

            }

            // $data[$i]->exam = $data[$i]->enroll2quiz()->where('type', 3)->orderBy('id', 'desc')->first();
            $data[$i]->exam = $data[$i]->latest_exam;
            unset($data[$i]->latest_exam);
            if($data[$i]->exam){
                if($data[$i]->exam->score){
                    $data[$i]->exam->progress = $data[$i]->exam->score/$data[$i]->exam->count;
                }
                $data[$i]->exam->percentage = number_format($data[$i]->exam->progress * 100);
                // $data[$i]->exam->quiz = Quiz::find($data[$i]->exam->quiz_id);
                if($data[$i]->exam->percentage >= $data[$i]->exam->quiz->passing_score){
                    $data[$i]->exam->learning = true;
                }else{
                    $data[$i]->exam->learning = false;
                }

            }

            // $data[$i]->survey = $data[$i]->enroll2quiz()->where('type', 5)->orderBy('id', 'desc')->first();
            $data[$i]->survey = $data[$i]->latest_survey;
            unset($data[$i]->latest_survey);

            unset($data[$i]->enroll2topic);

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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 3600);

        $data = Enroll::with([
            'members',
            'groups',
            'courses.topics' => function($query) {
                $query->whereNull('parent')->orderBy('order','asc');
            },
            'courses.topics.sub_topics' => function($query) {
                $query->orderBy('order','asc');
            },
            'latest_pre_test',
            'latest_post_test',
            'latest_exam',
            'latest_survey',
            'enroll2topic'
        ])->find($id);

            // $data->members = $data->members()->first();
            // $data->groups = $data->members->groups()->first();
            // $data->courses = $data->courses()->first();

            $timeToday = strtotime('TODAY');
            // $data->topics = $data->courses->topics()->whereNull('parent')->orderBy('order','asc')->get();
            for($a=0; $a<count($data->courses->topics); $a++) {
                // $data->courses->topics[$a]->parent = Topics::where('parent', $data->courses->topics[$a]->id)->orderBy('order','asc')->get();
                for($x=0; $x<count($data->courses->topics[$a]->sub_topics); $x++) {

                    // $data->courses->topics[$a]->sub_topics[$x]->enroll2topic = $data->courses->topics[$a]->sub_topics[$x]->enroll2topic()->where('enroll_id', $data->id)->first();

                    $data->courses->topics[$a]->sub_topics[$x]->enroll2topic = array_first($data->enroll2topic, function ($enroll2topic, $key) use ($data, $a, $x) {
                        return $enroll2topic['topics_id'] == $data->courses->topics[$a]->sub_topics[$x]->id;
                    });

                    $data->courses->topics[$a]->sub_topics[$x]->duration = (strtotime($data->courses->topics[$a]->sub_topics[$x]->end_time) - $timeToday) - (strtotime($data->courses->topics[$a]->sub_topics[$x]->start_time) - $timeToday);

                    if($data->courses->topics[$a]->sub_topics[$x]->enroll2topic){
                        if($data->courses->topics[$a]->sub_topics[$x]->enroll2topic->status){
                            $data->courses->topics[$a]->sub_topics[$x]->duration_enroll = $data->courses->topics[$a]->sub_topics[$x]->duration;
                        }else{
                            $data->courses->topics[$a]->sub_topics[$x]->duration_enroll = $data->courses->topics[$a]->sub_topics[$x]->enroll2topic->duration;
                        }
                    }else{
                        $data->courses->topics[$a]->sub_topics[$x]->duration_enroll = 0;
                    }

                    if($data->courses->topics[$a]->sub_topics[$x]->duration){
                        $data->courses->topics[$a]->sub_topics[$x]->progress = $data->courses->topics[$a]->sub_topics[$x]->duration_enroll/$data->courses->topics[$a]->sub_topics[$x]->duration;
                        $data->courses->topics[$a]->sub_topics[$x]->percentage = number_format($data->courses->topics[$a]->sub_topics[$x]->progress * 100);

                        $data->duration2topic += $data->courses->topics[$a]->sub_topics[$x]->duration;
                        $data->duration2enroll += $data->courses->topics[$a]->sub_topics[$x]->duration_enroll;

                        $data->duration2progress = $data->duration2enroll/$data->duration2topic;
                        $data->duration2percentage = number_format($data->duration2progress * 100);
                    }

                }
            }

            if($data->duration2percentage >= $data->courses->percentage){
                $data->courses->learning = true;
            }else{
                $data->courses->learning = false;
            }


            // $data->pre_test = $data->enroll2quiz()->where('type', 1)->orderBy('id', 'desc')->first();
            $data->pre_test = $data->latest_pre_test;
            unset($data->latest_pre_test);
            if($data->pre_test){
                // $data->pre_test->quiz = Quiz::find($data->pre_test->quiz_id);
                $data->pre_test->quiz->questions2answer = Questions2Answer::with('questions.answer')->where('enroll2quiz_id', $data->pre_test->id)->get();
                for($i=0; $i<count($data->pre_test->quiz->questions2answer); $i++) {
                        // $data->pre_test->quiz->questions2answer[$i]->questions = $data->pre_test->quiz->questions2answer[$i]->questions()->first();
                        // $data->pre_test->quiz->questions2answer[$i]->questions->answer = $data->pre_test->quiz->questions2answer[$i]->questions->answer()->get();
                        if($data->pre_test->quiz->questions2answer[$i]->questions->answer->count()){
                            for($a=0; $a<count($data->pre_test->quiz->questions2answer[$i]->questions->answer); $a++) {
                                $data->pre_test->quiz->questions2answer[$i]->questions->answer[$a]->questions2answer = $data->pre_test->quiz->questions2answer[$i]->questions->answer[$a]->questions2answer()->where('enroll2quiz_id', $data->pre_test->id)->first();
                            }
                        }else{
                            $data->pre_test->quiz->questions2answer[$i]->questions->answer = $data->pre_test->quiz->questions2answer[$i]->questions->questions2answer()->where('enroll2quiz_id', $data->pre_test->id)->first();
                        }
                }
                if($data->pre_test->score){
                    $data->pre_test->progress = $data->pre_test->score/$data->pre_test->count;
                }
                $data->pre_test->percentage = number_format($data->pre_test->progress * 100);

                if($data->pre_test->percentage >= $data->pre_test->quiz->passing_score){
                    $data->pre_test->learning = true;
                }else{
                    $data->pre_test->learning = false;
                }
            }

            // $data->post_test = $data->enroll2quiz()->where('type', 4)->orderBy('id', 'desc')->first();
            $data->post_test = $data->latest_post_test;
            unset($data->latest_post_test);
            if($data->post_test){
                // $data->post_test->quiz = Quiz::find($data->post_test->quiz_id);
                $data->post_test->quiz->questions2answer = Questions2Answer::with('questions.answer')->where('enroll2quiz_id', $data->post_test->id)->get();
                for($i=0; $i<count($data->post_test->quiz->questions2answer); $i++) {
                    // $data->post_test->quiz->questions2answer[$i]->questions = $data->post_test->quiz->questions2answer[$i]->questions()->first();
                    // $data->post_test->quiz->questions2answer[$i]->questions->answer = $data->post_test->quiz->questions2answer[$i]->questions->answer()->get();
                    if($data->post_test->quiz->questions2answer[$i]->questions->answer->count()){
                        for($a=0; $a<count($data->post_test->quiz->questions2answer[$i]->questions->answer); $a++) {
                            $data->post_test->quiz->questions2answer[$i]->questions->answer[$a]->questions2answer = $data->post_test->quiz->questions2answer[$i]->questions->answer[$a]->questions2answer()->where('enroll2quiz_id', $data->post_test->id)->first();
                        }
                    }else{
                        $data->post_test->quiz->questions2answer[$i]->questions->answer = $data->post_test->quiz->questions2answer[$i]->questions->questions2answer()->where('enroll2quiz_id', $data->post_test->id)->first();
                    }
                }
                if($data->post_test->score){
                    $data->post_test->progress = $data->post_test->score/$data->post_test->count;
                }
                $data->post_test->percentage = number_format($data->post_test->progress * 100);

                if($data->post_test->percentage >= $data->post_test->quiz->passing_score){
                    $data->post_test->learning = true;
                }else{
                    $data->post_test->learning = false;
                }
            }

            // $data->exam = $data->enroll2quiz()->where('type', 3)->orderBy('id', 'desc')->first();
            $data->exam = $data->latest_exam;
            unset($data->latest_exam);
            if($data->exam){
                // $data->exam->quiz = Quiz::find($data->exam->quiz_id);
                $data->exam->quiz->questions2answer = Questions2Answer::with('questions.answer')->where('enroll2quiz_id', $data->exam->id)->get();
                for($i=0; $i<count($data->exam->quiz->questions2answer); $i++) {
                    // $data->exam->quiz->questions2answer[$i]->questions = $data->exam->quiz->questions2answer[$i]->questions()->first();
                    // $data->exam->quiz->questions2answer[$i]->questions->answer = $data->exam->quiz->questions2answer[$i]->questions->answer()->get();
                    if($data->exam->quiz->questions2answer[$i]->questions->answer->count()){
                        for($a=0; $a<count($data->exam->quiz->questions2answer[$i]->questions->answer); $a++) {
                            $data->exam->quiz->questions2answer[$i]->questions->answer[$a]->questions2answer = $data->exam->quiz->questions2answer[$i]->questions->answer[$a]->questions2answer()->where('enroll2quiz_id', $data->exam->id)->first();
                        }
                    }else{
                        $data->exam->quiz->questions2answer[$i]->questions->answer = $data->exam->quiz->questions2answer[$i]->questions->questions2answer()->where('enroll2quiz_id', $data->exam->id)->first();
                    }
                }
                if($data->exam->score){
                    $data->exam->progress = $data->exam->score/$data->exam->count;
                }
                $data->exam->percentage = number_format($data->exam->progress * 100);

                if($data->exam->percentage >= $data->exam->quiz->passing_score){
                    $data->exam->learning = true;
                }else{
                    $data->exam->learning = false;
                }
            }

        // $data->survey = $data->enroll2quiz()->where('type', 5)->orderBy('id', 'desc')->first();
        $data->survey = $data->latest_survey;
        unset($data->latest_survey);
        if($data->survey){
            // $data->survey->quiz = Quiz::find($data->survey->quiz_id);
            $data->survey->quiz->questions2answer = Questions2Answer::with('questions.answer')->where('enroll2quiz_id', $data->survey->id)->get();
            for($i=0; $i<count($data->survey->quiz->questions2answer); $i++) {
                // $data->survey->quiz->questions2answer[$i]->questions = $data->survey->quiz->questions2answer[$i]->questions()->first();
                // $data->survey->quiz->questions2answer[$i]->questions->answer = $data->survey->quiz->questions2answer[$i]->questions->answer()->get();
                if($data->survey->quiz->questions2answer[$i]->questions->answer->count()){
                    for($a=0; $a<count($data->survey->quiz->questions2answer[$i]->questions->answer); $a++) {
                        $data->survey->quiz->questions2answer[$i]->questions->answer[$a]->questions2answer = $data->survey->quiz->questions2answer[$i]->questions->answer[$a]->questions2answer()->where('enroll2quiz_id', $data->survey->id)->first();
                    }
                }else{
                    $data->survey->quiz->questions2answer[$i]->questions->answer = $data->survey->quiz->questions2answer[$i]->questions->questions2answer()->where('enroll2quiz_id', $data->survey->id)->first();
                }
            }
        }

        unset($data->enroll2topic);

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
    public function update($id, Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function export_bak(Request $request, _RolesController $oRole)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 3600);

        $search = $request['search'];
        $courses_id = $request['courses_id'];

        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        Excel::create('ข้อมูลการเข้าเรียน '.date('Y-m-d H:i:s'), function($excel) use($search, $courses_id, $authSessionGroups, $authSession, $oRole) {
            $excel->sheet('ข้อมูลการเข้าเรียน' , function($sheet) use ($search, $courses_id, $authSessionGroups, $authSession, $oRole) {
                $sheet->row(1, array('Enroll ID.', 'Groups', 'Sub Groups', 'ชื่อ - สกุล', 'อีเมล์', 'รหัสหลักสูตร', 'ชื่อหลักสูตร', 'ประเภทการลงทะเบียน', 'Class Room', 'วันที่ลงทะเบียน', 'เข้าใช้งานระบบล่าสุด', 'สถานะการเรียน', 'สถานะการเรียน (%)', 'แบบทดสอบก่อนเรียน', 'แบบทดสอบก่อนเรียน (คะแนน)', 'แบบทดสอบก่อนเรียน (%)', 'แบบทดสอบหลังเรียน', 'แบบทดสอบหลังเรียน (คะแนน)', 'แบบทดสอบหลังเรียน (%)', 'แบบทดสอบเพื่อวัดความรู้', 'แบบทดสอบเพื่อวัดความรู้ (คะแนน)', 'แบบทดสอบเพื่อวัดความรู้ (%)', 'สถานะ', 'แบบสอบถาม', 'วุฒิบัตร', 'หมายเลขวุฒิบัตร', 'วันที่ออกวุฒิบัตร'));

                $data = new Enroll;
                if($courses_id){
                    $data = $data->where('courses_id', $courses_id);
                    $data = $data->whereHas('courses', function($query) use ($authSessionGroups) {
                        $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                    });
                }else{
                    if ($authSession->super_users) {
                        $data = $data->whereHas('courses', function($query) use ($authSession) {
                            $query->where('admins_id', $authSession->id);
                        });
                    } else if (!$oRole->isSuper()) {
                        $data = $data->whereHas('courses', function($query) use ($authSessionGroups) {
                            $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                        });
                    }
                }

                $data = $data->with('members');
                $data = $data->whereHas('members', function($query) use ($search) {
                    $query->where('email', 'like' , "%".$search."%");
                    $query->orWhere('first_name', 'like' , "%".$search."%");
                });

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

    public function export(Request $request, _RolesController $oRole, _FunctionsController $oFunc)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 3600);

        $search = $request['search'];
        $courses_id = $request['courses_id'];

        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        // Excel::create('ข้อมูลการเข้าเรียน '.date('Y-m-d H:i:s'), function($excel) use($search, $courses_id, $authSessionGroups, $authSession, $oRole) {
            // $excel->sheet('ข้อมูลการเข้าเรียน' , function($sheet) use ($search, $courses_id, $authSessionGroups, $authSession, $oRole) {
                // $sheet->row(1, array('Enroll ID.', 'Groups', 'Sub Groups', 'ชื่อ - สกุล', 'อีเมล์', 'รหัสหลักสูตร', 'ชื่อหลักสูตร', 'ประเภทการลงทะเบียน', 'Class Room', 'วันที่ลงทะเบียน', 'เข้าใช้งานระบบล่าสุด', 'สถานะการเรียน', 'สถานะการเรียน (%)', 'แบบทดสอบก่อนเรียน', 'แบบทดสอบก่อนเรียน (คะแนน)', 'แบบทดสอบก่อนเรียน (%)', 'แบบทดสอบหลังเรียน', 'แบบทดสอบหลังเรียน (คะแนน)', 'แบบทดสอบหลังเรียน (%)', 'แบบทดสอบเพื่อวัดความรู้', 'แบบทดสอบเพื่อวัดความรู้ (คะแนน)', 'แบบทดสอบเพื่อวัดความรู้ (%)', 'สถานะ', 'แบบสอบถาม', 'วุฒิบัตร', 'หมายเลขวุฒิบัตร', 'วันที่ออกวุฒิบัตร'));

                $data = new Enroll;
                if($courses_id){
                    $data = $data->where('courses_id', $courses_id);
                    $data = $data->whereHas('courses', function($query) use ($authSessionGroups) {
                        $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                    });
                }else{
                    if ($authSession->super_users) {
                        $data = $data->whereHas('courses', function($query) use ($authSession) {
                            $query->where('admins_id', $authSession->id);
                        });
                    } else if (!$oRole->isSuper()) {
                        $data = $data->whereHas('courses', function($query) use ($authSessionGroups) {
                            $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                        });
                    }
                }

                $data = $data->with([
                    'members',
                    'groups',
                    'sub_groups',
                    'courses.topics' => function($query) {
                        $query->whereNull('parent')->orderBy('order','asc');
                    },
                    'courses.topics.sub_topics' => function($query) {
                        $query->orderBy('order','asc');
                    },
                    'latest_pre_test',
                    'latest_post_test',
                    'latest_exam',
                    'latest_survey',
                    'enroll2topic'
                ]);
                $data = $data->whereHas('members', function($query) use ($search) {
                    $query->where('email', 'like' , "%".$search."%");
                    $query->orWhere('first_name', 'like' , "%".$search."%");
                });

                $data = $data->orderBy('id', 'desc');
                // $data = $data->with('courses');
                // $data = $data->get();

                $countChunk = $oFunc->cal_chunk($data->count());

                if ($countChunk < 100) {
                    $data = $data->get();
                } else {
                    $dataEnroll = collect([]);
                    $data->chunk($countChunk, function($enrolls) use (&$dataEnroll) {
                        $dataEnroll = $dataEnroll->merge($enrolls);
                    });

                    $data = $dataEnroll;
                }

                $timeToday = strtotime('TODAY');
                $list = [];

                for($i=0; $i<count($data); $i++) {
                    if (empty($data[$i]->courses->topics)) {
                        continue;
                    }

                    // $data[$i]->groups = $data[$i]->groups()->first();
                    if($data[$i]->sub_groups_id){
                        // $data[$i]->sub_groups = $data[$i]->sub_groups()->first();
                        $data[$i]->sub_groups_title = $data[$i]->sub_groups->title;
                    }else{
                        $data[$i]->sub_groups_title = '';
                    }
                    // $data[$i]->members = $data[$i]->members()->first();
                    // $data[$i]->courses = $data[$i]->courses()->first();

                    if($data[$i]->enroll_type == 1){
                        $data[$i]->type = 'ClassRoom (Targets)';
                        $data[$i]->classrooms = ClassRooms::select('title')->find($data[$i]->enroll_type_id);
                        $data[$i]->classrooms = $data[$i]->classrooms->title;
                    }
                    elseif($data[$i]->enroll_type == 2){
                        $data[$i]->type = 'ClassRoom (Groups)';
                        $data[$i]->classrooms = ClassRooms::select('title')->find($data[$i]->enroll_type_id);
                        $data[$i]->classrooms = $data[$i]->classrooms->title;
                    }
                    elseif($data[$i]->enroll_type == 3){
                        $data[$i]->type = 'Targets';
                        $data[$i]->classrooms = '';
                    }
                    elseif($data[$i]->enroll_type == 4){
                        $data[$i]->type = 'Level Groups';
                        $data[$i]->classrooms = '';
                    }
                    else{
                        $data[$i]->type = 'Public';
                        $data[$i]->classrooms = '';
                    }

                    // $data[$i]->topics = $data[$i]->courses->topics()->whereNull('parent')->orderBy('order','asc')->get();
                    for($a=0; $a<count($data[$i]->courses->topics); $a++) {
                        // $data[$i]->courses->topics[$a]->parent = Topics::where('parent', $data[$i]->courses->topics[$a]->id)->orderBy('order','asc')->get();
                        for($x=0; $x<count($data[$i]->courses->topics[$a]->sub_topics); $x++) {

                            // $data[$i]->courses->topics[$a]->sub_topics[$x]->enroll2topic = $data[$i]->courses->topics[$a]->sub_topics[$x]->enroll2topic()->where('enroll_id', $data[$i]->id)->first();

                            $data[$i]->courses->topics[$a]->sub_topics[$x]->enroll2topic = array_first($data[$i]->enroll2topic, function ($enroll2topic, $key) use ($data, $i, $a, $x) {
                                return $enroll2topic['topics_id'] == $data[$i]->courses->topics[$a]->sub_topics[$x]->id;
                            });

                            $data[$i]->courses->topics[$a]->sub_topics[$x]->duration = (strtotime($data[$i]->courses->topics[$a]->sub_topics[$x]->end_time) - $timeToday) - (strtotime($data[$i]->courses->topics[$a]->sub_topics[$x]->start_time) - $timeToday);

                            if($data[$i]->courses->topics[$a]->sub_topics[$x]->enroll2topic){
                                if($data[$i]->courses->topics[$a]->sub_topics[$x]->enroll2topic->status){
                                    $data[$i]->courses->topics[$a]->sub_topics[$x]->duration_enroll = $data[$i]->courses->topics[$a]->sub_topics[$x]->duration;
                                }else{
                                    $data[$i]->courses->topics[$a]->sub_topics[$x]->duration_enroll = $data[$i]->courses->topics[$a]->sub_topics[$x]->enroll2topic->duration;
                                }
                            }else{
                                $data[$i]->courses->topics[$a]->sub_topics[$x]->duration_enroll = 0;
                            }

                            if($data[$i]->courses->topics[$a]->sub_topics[$x]->duration){
                                $data[$i]->courses->topics[$a]->sub_topics[$x]->progress = $data[$i]->courses->topics[$a]->sub_topics[$x]->duration_enroll/$data[$i]->courses->topics[$a]->sub_topics[$x]->duration;
                                $data[$i]->courses->topics[$a]->sub_topics[$x]->percentage = number_format($data[$i]->courses->topics[$a]->sub_topics[$x]->progress * 100);

                                $data[$i]->duration2topic += $data[$i]->courses->topics[$a]->sub_topics[$x]->duration;
                                $data[$i]->duration2enroll += $data[$i]->courses->topics[$a]->sub_topics[$x]->duration_enroll;
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

                    // $data[$i]->pre_test = $data[$i]->enroll2quiz()->where('type', 1)->orderBy('id', 'desc')->first();
                    $data[$i]->pre_test = $data[$i]->latest_pre_test;
                    unset($data[$i]->latest_pre_test);
                    if($data[$i]->pre_test){
                        // $data[$i]->pre_test->quiz = Quiz::find($data[$i]->pre_test->quiz_id);
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

                    // $data[$i]->post_test = $data[$i]->enroll2quiz()->where('type', 4)->orderBy('id', 'desc')->first();
                    $data[$i]->post_test = $data[$i]->latest_post_test;
                    unset($data[$i]->latest_post_test);
                    if($data[$i]->post_test){
                        // $data[$i]->post_test->quiz = Quiz::find($data[$i]->post_test->quiz_id);
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

                    // $data[$i]->exam = $data[$i]->enroll2quiz()->where('type', 3)->orderBy('id', 'desc')->first();
                    $data[$i]->exam = $data[$i]->latest_exam;
                    unset($data[$i]->latest_exam);
                    if($data[$i]->exam){
                        // $data[$i]->exam->quiz = Quiz::find($data[$i]->exam->quiz_id);
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

                    // $data[$i]->survey = $data[$i]->enroll2quiz()->where('type', 5)->orderBy('id', 'desc')->first();
                    $data[$i]->survey = $data[$i]->latest_survey;
                    unset($data[$i]->latest_survey);
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

                    unset($data[$i]->enroll2topic);

                    // $sheet->row($i+2, array($data[$i]->id, $data[$i]->groups->title, $data[$i]->sub_groups, $data[$i]->members->first_name.' '.$data[$i]->members->last_name , $data[$i]->members->email, $data[$i]->courses->code, $data[$i]->courses->title, $data[$i]->type, $data[$i]->classrooms, $data[$i]->enroll_datetime, $data[$i]->last_datetime, $data[$i]->learning_status, $data[$i]->learning_percentage, $data[$i]->pre_test_status, $data[$i]->pre_test_score, $data[$i]->pre_test_percentage, $data[$i]->post_test_status, $data[$i]->post_test_score, $data[$i]->post_test_percentage, $data[$i]->exam_status, $data[$i]->exam_score, $data[$i]->exam_percentage, $data[$i]->course_status, $data[$i]->survey_status, $data[$i]->certificate, $data[$i]->certificate_reference_number, $data[$i]->certificate_datetime));

                    $list[] = [
                        'Enroll ID.' => $data[$i]->id,
                        'Groups' => $data[$i]->groups->title,
                        'Sub Groups' => $data[$i]->sub_groups_title,
                        'ชื่อ - สกุล' => $data[$i]->members->first_name.' '.$data[$i]->members->last_name,
                        'อีเมล์' => $data[$i]->members->email,
                        'รหัสหลักสูตร' => $data[$i]->courses->code,
                        'ชื่อหลักสูตร' => $data[$i]->courses->title,
                        'ประเภทการลงทะเบียน' => $data[$i]->type,
                        'Class Room' => $data[$i]->classrooms,
                        'วันที่ลงทะเบียน' => $data[$i]->enroll_datetime,
                        'เข้าใช้งานระบบล่าสุด' => $data[$i]->last_datetime,
                        'สถานะการเรียน' => $data[$i]->learning_status,
                        'สถานะการเรียน (%)' => $data[$i]->learning_percentage,
                        'แบบทดสอบก่อนเรียน' => $data[$i]->pre_test_status,
                        'แบบทดสอบก่อนเรียน (คะแนน)' => $data[$i]->pre_test_score,
                        'แบบทดสอบก่อนเรียน (%)' => $data[$i]->pre_test_percentage,
                        'แบบทดสอบหลังเรียน' => $data[$i]->post_test_status,
                        'แบบทดสอบหลังเรียน (คะแนน)' => $data[$i]->post_test_score,
                        'แบบทดสอบหลังเรียน (%)' => $data[$i]->post_test_percentage,
                        'แบบทดสอบเพื่อวัดความรู้' => $data[$i]->exam_status,
                        'แบบทดสอบเพื่อวัดความรู้ (คะแนน)' => $data[$i]->exam_score,
                        'แบบทดสอบเพื่อวัดความรู้ (%)' => $data[$i]->exam_percentage,
                        'สถานะ' => $data[$i]->course_status,
                        'แบบสอบถาม' => $data[$i]->survey_status,
                        'วุฒิบัตร' => $data[$i]->certificate,
                        'หมายเลขวุฒิบัตร' => $data[$i]->certificate_reference_number,
                        'วันที่ออกวุฒิบัตร' => $data[$i]->certificate_datetime,
                    ];
                }

                if (empty($list)) {
                    return response()->json(['message' => "No data requested."], 200);
                }

                $headerStyle = (new StyleBuilder())
                   ->setFontSize(11)
                   ->build();
                $dataStyle = $headerStyle;

                $list = collect($list);
                $sheets = new SheetCollection([
                    'ข้อมูลการเข้าเรียน' => $list
                ]);

                $filename = '_ข้อมูลการเข้าเรียน '.date('Y-m-d H:i:s').'.xlsx';
                return FastExcel::data($sheets)->headerStyle($headerStyle)->dataStyle($dataStyle)->download($filename);
            // });
        // })->download('xls');

    }

}
