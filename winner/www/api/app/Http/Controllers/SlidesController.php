<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Slides;
use App\Models\Admins;
use App\Models\SlidesTimes;
use App\Models\Courses;
use App\Models\Topics;
use Auth;
use Input;
use DB;

class SlidesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request, _RolesController $oRole)
    {
        //
        // $data = Slides::orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);
        $data = new Slides;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if($authSession->super_users){
            $data = $data->whereHas('courses',function ($query){
                $authSession = Auth::user();
                $query->where('admins_id', $authSession->id);
            });
        } else if (!$oRole->isSuper()) {
            $data = $data->whereHas('courses',function ($query) use ($authSessionGroups){
                $query->whereHas('groups', function ($sub_query) use ($authSessionGroups){
                    $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            });
        }

        if ($request['courses_id'] && $request['topics_id']) {
            $data = $data->select('slides.*');
            $data = $data->leftJoin('slides_times', 'slides.id', '=', 'slides_times.slides_id');
            $data = $data->where('slides_times.topics_id', '=', $request['topics_id'])->groupBy('slides_times.slides_id');
        } else if ($request['courses_id']) {
            $data = $data->where('courses_id', '=', $request['courses_id']);
        }


        $data = $data->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);
        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->modify_by = $admins->username;
            $data[$i]->courses = $data[$i]->courses()->first();
            $data[$i]->courses->first_topic = $data[$i]->courses->topics()->whereNotNull('parent')->orderBy('order', 'ASC')->first();
            $data[$i]->topics = $data[$i]->topics()->first();
            $data[$i]->slides_times = $data[$i]->slides_times()->orderBy('topics_id', 'ASC')->orderBy('time', 'ASC')->get();
            for($t=0; $t<count($data[$i]->slides_times); $t++) {
                $data[$i]->slides_times[$t]->topics = $data[$i]->slides_times[$t]->topics()->first();

            }
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
        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'picture' => 'required|max:255',
            'courses_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = new Slides;
        $data->fill($input);
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();

        if ($is_success) {
            if ($input['slides_times']) {
                foreach ($input['slides_times'] as $slides_times) {
                    if (isset($slides_times['id'])) {
                        $dataSlides_times = SlidesTimes::find($slides_times['id']);
                        if ($dataSlides_times) {
                            $dataSlides_times->fill($slides_times);
                            // $dataSlides_times->courses_id = $input['courses_id'];
                            // $dataSlides_times->topics_id = $input['topics_id'];
                            $dataSlides_times->create_datetime = date('Y-m-d H:i:s');
                            $dataSlides_times->modify_datetime = date('Y-m-d H:i:s');
                            $dataSlides_times->modify_by = $input['admin_id'];
                            $is_success = $dataSlides_times->save();
                        } else {
                            $dataSlides_times = new SlidesTimes;
                            $dataSlides_times->fill($slides_times);
                            $dataSlides_times->courses_id = $input['courses_id'];
                            $dataSlides_times->topics_id = $input['topics']['id'];
                            $dataSlides_times->create_datetime = date('Y-m-d H:i:s');
                            $dataSlides_times->modify_datetime = date('Y-m-d H:i:s');
                            $dataSlides_times->modify_by = $input['admin_id'];
                            $is_success = $dataSlides_times->save();
                        }
                    } else {
                        $dataSlides_times = new SlidesTimes;
                        $dataSlides_times->fill($slides_times);
                        $dataSlides_times->courses_id = $input['courses_id'];
                        $dataSlides_times->slides_id = $data->id;
                        $dataSlides_times->topics_id = $input['topics']['id'];
                        $dataSlides_times->create_datetime = date('Y-m-d H:i:s');
                        $dataSlides_times->modify_datetime = date('Y-m-d H:i:s');
                        $dataSlides_times->modify_by = $input['admin_id'];
                        $is_success = $dataSlides_times->save();
                    }

                    if (!$is_success) {
                        $is_success_all = false;
                        break;
                    }
                }
            } else {
                $is_success = true;
            }
        }

        if ($is_success) {
            $message = "The slides has been created.";
        } else {
            $message = "Failed to create the slides.";
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
        if (!$oRole->haveAccess($id, "slides")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = Slides::find($id);
        $data->courses = $data->courses()->first();
        $data->slides_times = $data->slides_times()->whereNull('topics_id')->get();
        $data->slides = Slides::where('courses_id', $data->courses_id)->get();
        for($i=0; $i<count($data->slides); $i++) {
            $data->slides[$i]->slides_times = $data->slides[$i]->slides_times()->get();
            for($t=0; $t<count($data->slides[$i]->slides_times); $t++) {
                $data->slides[$i]->slides_times[$t]->topics = $data->slides[$i]->slides_times[$t]->topics()->first();

            }
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
    public function update($id, Request $request, _RolesController $oRole)
    {
        // Check Permission Acces
        if (!$oRole->haveAccess($id, "slides")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'picture' => 'required|max:255',
            'courses_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        if ($input['slides_times']) {
            foreach ($input['slides_times'] as $slides_times) {
                if (isset($slides_times['id'])) {
                    $dataSlides_times = SlidesTimes::find($slides_times['id']);
                    if ($dataSlides_times) {
                        $dataSlides_times->fill($slides_times);
                        // $dataSlides_times->courses_id = $input['courses_id'];
                        // $dataSlides_times->topics_id = $input['topics_id'];
                        $dataSlides_times->create_datetime = date('Y-m-d H:i:s');
                        $dataSlides_times->modify_datetime = date('Y-m-d H:i:s');
                        $dataSlides_times->modify_by = $input['admin_id'];
                        $is_success = $dataSlides_times->save();
                    } else {
                        $dataSlides_times = new SlidesTimes;
                        $dataSlides_times->fill($slides_times);
                        $dataSlides_times->courses_id = $input['courses_id'];
                        $dataSlides_times->topics_id = $input['topics']['id'];
                        $dataSlides_times->create_datetime = date('Y-m-d H:i:s');
                        $dataSlides_times->modify_datetime = date('Y-m-d H:i:s');
                        $dataSlides_times->modify_by = $input['admin_id'];
                        $is_success = $dataSlides_times->save();
                    }
                } else {
                    $dataSlides_times = new SlidesTimes;
                    $dataSlides_times->fill($slides_times);
                    $dataSlides_times->courses_id = $input['courses_id'];
                    $dataSlides_times->topics_id = $input['topics']['id'];
                    $dataSlides_times->create_datetime = date('Y-m-d H:i:s');
                    $dataSlides_times->modify_datetime = date('Y-m-d H:i:s');
                    $dataSlides_times->modify_by = $input['admin_id'];
                    $is_success = $dataSlides_times->save();
                }

                if (!$is_success) {
                    $is_success_all = false;
                    break;
                }
            }
        } else {
            $is_success = true;
        }

        if ($input['picture']) {
            $dataSlides = Slides::find($id);
            $dataSlides->picture = $input['picture'];
            $is_success = $dataSlides->save();
        }

        if ($is_success) {
            $message = "The slides has been updated.";
        } else {
            $message = "Failed to update the slides.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);

        /*$data = Slides::find($id);
        $input = $request->json()->all();
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();

        if ($is_success) {
            $message = "The slides has been updated.";
        } else {
            $message = "Failed to update the slides.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);*/
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id, _RolesController $oRole)
    {
        // Check Permission Acces
        if (!$oRole->haveAccess($id, "slides")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = Slides::find($id);
        $data->slides_times()->delete();
        $data->delete();
        $is_success = $data;

        if ($is_success) {
            $message = "The slides has been deleted.";
        } else {
            $message = "Failed to delete the slides.";
        }
        return response()->json(array('is_error' => !$is_success, 'message'=>$message), 200);
    }

    public function updateStatus($id, Request $request)
    {
        //
        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'status' => 'required|max:1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = Slides::find($id);
        $data->status = $input['status'];
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The slide has been updated.";
        } else {
            $message = "Failed to update the slide.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function parents($id, _RolesController $oRole)
    {
        //
        $data = new Slides;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if($authSession->super_users){
            $data = $data->with('courses');
            $data = $data->whereHas('courses',function ($query){
                $authSession = Auth::user();
                $query->where('admins_id', $authSession->id);
            });
        } else if (!$oRole->isSuper()) {
            $data = $data->with('courses');
            $data = $data->whereHas('courses',function ($query) use ($authSessionGroups){
                $query->whereHas('groups', function ($sub_query) use ($authSessionGroups){
                    $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            });
        }
        $data = $data->where('courses_id', $id)->get();
        return response()->json($data, 200);
    }

    public function all(_RolesController $oRole)
    {
        //
        $data = new Slides;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if($authSession->super_users){
            $data = $data->with('courses');
            $data = $data->whereHas('courses',function ($query){
                $authSession = Auth::user();
                $query->where('admins_id', $authSession->id);
            });
        } else if (!$oRole->isSuper()) {
            $data = $data->with('courses');
            $data = $data->whereHas('courses',function ($query) use ($authSessionGroups){
                $query->whereHas('groups', function ($sub_query) use ($authSessionGroups){
                    $sub_query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            });
        }
        $data = $data->get();
        return response()->json($data, 200);
    }


    public function orders(Request $request)
    {
        $input = $request->json()->all();
        for($i=0; $i<count($input); $i++) {
            $data[$i] = Slides::find($input[$i]['id']);
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

        $entity = Slides::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = Slides::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = Slides::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = Slides::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = Slides::where('order', '>', $request['order'])->min('id');
                    Slides::find($next)->decrement('order');
                    $entity->moveBefore(Slides::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The slides has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

    public function slidesConvertCreate(Request $request)
    {
        //
        $validator = Validator::make($request->json()->all(), [
            'courses_id' => 'required|numeric',
            'files' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        DB::beginTransaction();
        $is_success_all = true;

        $input = $request->json()->all();

        $dataCourses = Courses::find($input['courses_id']);

        if (!$dataCourses) {
            return response()->json(array('message' => 'The Courses not found.'), 404);
        }

        if (empty($input['files'])) {
            return response()->json(array('message' => 'The files is empty.'), 400);
        }

        foreach ($input['files'] as $index => $slide) {
            if (!$slide['is_success']) {
                continue;
            }

            $pageSlide = $index + 1;

            $dataSlide = new Slides;
            $dataSlide->courses_id = $input['courses_id'];
            $dataSlide->picture = $slide['file_name'];
            $dataSlide->title = "Slide ".$pageSlide;

            // if ($pageSlide == 1) {
            //     $dataSlide->slide_active = 1;
            // }

            $dataSlide->create_datetime = date('Y-m-d H:i:s');
            $dataSlide->modify_datetime = date('Y-m-d H:i:s');
            $dataSlide->modify_by = Auth::user()->id;
            $is_success = $dataSlide->save();

            if (!$is_success) {
                $is_success_all = false;
                break;
            }
        }

        // $deleteFilesInfo = false;

        if ($is_success_all) {
            DB::commit();
            $message = "The slides has been created.";

            // if ($rowAffected > 1) {
            //     $paramFilesDel = array(
            //         "action" => "delete",
            //         "files" => $dataOldSlidesGroups
            //     );

            //     $deleteFilesInfo = array('statusCode' => 200);

            //     $httpClient = new httpClient();

            //     try {
            //         $responseDelFiles = $httpClient->request('DELETE', env('BASE_FILE_URL').'slides_pdf_upload.php', [
            //             'json' => $paramFilesDel
            //         ]);

            //         $deleteFilesInfo['data'] = json_decode($responseDelFiles->getBody());

            //     } catch(RequestException $e) {
            //         if ($e->hasResponse()) {
            //             $deleteFilesInfo['statusCode'] = $e->getResponse()->getStatusCode();
            //             $deleteFilesInfo['errorInfo'] = json_decode($e->getResponse()->getBody());
            //         }
            //     }
            // }

        } else {
            DB::rollBack();
            $message = "Failed to create the new slides.";
        }

        // return response()->json(array('is_error' => !$is_success_all, 'message' => $message, 'deleteFilesInfo' => $deleteFilesInfo), 200);
        return response()->json(array('is_error' => !$is_success_all, 'message' => $message), 200);

    }

    public function getByTopics($id, Request $request)
    {
        //
        $data = Slides::find($id);
        $data->topics = Topics::where('id', '=', $request['topics_id'])->first();

        $data->topics->startTime = (strtotime($data->topics->start_time) - strtotime('TODAY')) * 1000;
        $data->topics->endTime = (strtotime($data->topics->end_time) - strtotime('TODAY')) * 1000;
        $data->topics->duration = $data->topics->endTime - $data->topics->startTime;

        if($data->topics->streaming_url){
            $data->topics->streaming_url = $data->topics->streaming_url;
            $data->topics->streaming_url_cut = $data->topics->streaming_url.'?wowzaplaystart='.$data->topics->startTime.'&wowzaplayduration='.$data->topics->duration;
        }else{
            $data->topics->streaming_url = $data->topics->courses['streaming_url'];
            $data->topics->streaming_url_cut = $data->topics->courses['streaming_url'].'?wowzaplaystart='.$data->topics->startTime.'&wowzaplayduration='.$data->topics->duration;
        }

        $data->slides_times = SlidesTimes::where('slides_id', '=', $data->id)->where('topics_id', '=', $request['topics_id'])->orderBy('time', 'asc')->get();

        return response()->json($data, 200);
    }

    public function updateSlideActive($id, Request $request)
    {
        //
        $data = Slides::find($id);
        $input = $request->json()->all();
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $input['admin_id'];
        $is_success = $data->save();

        if ($is_success) {
            if ($input['slide_active'] == 1) {

                $isOtherUpdateSuccess = Slides::where("id", "!=", $input['id'])->where("courses_id", "=", $input['courses_id'])->where("slide_active", "=", "1")->update(['slide_active' => 0]);

            } else {

                $dataSlidesActiveCheck = Slides::where('courses_id', $data->courses_id)->where('slide_active', 1)->get();

                if (!$dataSlidesActiveCheck->count()) {
                    $dataFirst = Slides::where('courses_id', $data->courses_id)->orderBy('order', 'asc')->first();

                    if ($dataFirst) {
                        $isDefaultActiveSuccess = $dataFirst->update(['slide_active' => 1]);
                    }

                }

            }

            $message = "The slide active has been updated.";
        } else {
            $message = "Failed to update the slide active.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }
}
