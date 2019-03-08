<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Admins;
use App\Models\AdminsGroups;
use App\Models\Groups;
use App\Models\Courses;
use App\Models\Certificates;

use Auth;
use PDF;
use Storage;
use DB;
use Carbon\Carbon;

class CertificatesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request, _RolesController $oRole)
    {
        //
        $data = new Certificates;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if($request->has('search')){
            $data = $data->where(function ($query) use ($request) {
                $query->where('title', 'like', '%'.$request['search'].'%');
            });
        }

        $data = $data->with('groups');
        // $data = $data->whereHas('groups', function($query) use ($authSessionGroups) {
        //     $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
        // });

        if (!$oRole->isSuper()) {
            if ($request->has('groups_id')) {
                $groupsQuery = array_where($authSessionGroups->toArray(), function ($authSessionGroup) use ($request) {
                    return $authSessionGroup['id'] == $request['groups_id'];
                });
            } else {
                $groupsQuery = $authSessionGroups;
            }
            // $data = $data->with('groups');
            $data = $data->whereHas('groups', function($query) use ($groupsQuery) {
                $query->whereIn('groups_id', array_pluck($groupsQuery, 'id'));
            });
        } else {
            if ($request->has('groups_id')) {
                $data = $data->where('groups_id', $request['groups_id']);
            }
        }

        $data = $data->with('courses');

        $data = $data->orderBy($request['order_by'],$request['order_direction'])->paginate($request['per_page']);

        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->modify_by = $admins->username;
            $data[$i]->groups = $data[$i]->groups()->get();
            $data[$i]->file_preview_image = route('certificates-preview-image', ['filename' => $data[$i]->file_preview]);
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
        $error = array();
        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'groups_id' => 'required|numeric',
            'title' => 'required|max:255',
            'body_text_1' => 'required|max:255',
            'body_text_2' => 'required|max:255',
            'logo_align' => 'required_unless:number_of_logo,|max:2',
            'signature_align' => 'required_unless:number_of_signature,|max:2',
            'background_color' => 'required|max:255',
            'text_color' => 'required|max:255',
            'border_color' => 'required|max:255',
            'border_style' => 'required|in:normal,radius|max:255',
            'name_of_signature_1' => 'required_with:signature_1|max:255',
            'position_of_signature_1' => 'required_with:signature_1|max:255',
            'name_of_signature_2' => 'required_with:signature_2|max:255',
            'position_of_signature_2' => 'required_with:signature_2|max:255',
            'name_of_signature_3' => 'required_with:signature_3|max:255',
            'position_of_signature_3' => 'required_with:signature_3|max:255',
            'name_of_signature_1_en' => 'required_with:signature_1_en|max:255',
            'position_of_signature_1_en' => 'required_with:signature_1_en|max:255',
            'name_of_signature_2_en' => 'required_with:signature_2_en|max:255',
            'position_of_signature_2_en' => 'required_with:signature_2_en|max:255',
            'name_of_signature_3_en' => 'required_with:signature_3_en|max:255',
            'position_of_signature_3_en' => 'required_with:signature_3_en|max:255',
        ]);

        // $validator->sometimes('logo_1', 'required|max:255', function ($input) { return $input->number_of_logo >= 1; });
        // $validator->sometimes('logo_2', 'required|max:255', function ($input) { return $input->number_of_logo >= 2; });
        // $validator->sometimes('logo_3', 'required|max:255', function ($input) { return $input->number_of_logo >= 3; });
        // $validator->sometimes('signature_1', 'required|max:255', function ($input) { return $input->number_of_signature >= 1; });
        // $validator->sometimes('name_of_signature_1', 'required|max:255', function ($input) { return $input->number_of_signature >= 1; });
        // $validator->sometimes('position_of_signature_1', 'required|max:255', function ($input) { return $input->number_of_signature >= 1; });
        // $validator->sometimes('signature_2', 'required|max:255', function ($input) { return $input->number_of_signature >= 2; });
        // $validator->sometimes('name_of_signature_2', 'required|max:255', function ($input) { return $input->number_of_signature >= 2; });
        // $validator->sometimes('position_of_signature_2', 'required|max:255', function ($input) { return $input->number_of_signature >= 2; });
        // $validator->sometimes('signature_3', 'required|max:255', function ($input) { return $input->number_of_signature >= 3; });
        // $validator->sometimes('name_of_signature_3', 'required|max:255', function ($input) { return $input->number_of_signature >= 3; });
        // $validator->sometimes('position_of_signature_3', 'required|max:255', function ($input) { return $input->number_of_signature >= 3; });

        if (empty($input['groups_id']) || !is_numeric($input['groups_id'])) {
            $error['groups_id'][] = "The groups id field is required.";
        } else {
            $dataGroup = Groups::find($input['groups_id']);
            if (!$dataGroup) {
                $error['groups_id'][] = "The groups id field is required.";
            }

            $validator->sometimes('body_text_1_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1; });
            $validator->sometimes('body_text_2_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1; });

            // $validator->sometimes('logo_1_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_logo >= 1; });
            // $validator->sometimes('logo_2_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_logo >= 2; });
            // $validator->sometimes('logo_3_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_logo >= 3; });
            // $validator->sometimes('signature_1_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_signature >= 1; });
            // $validator->sometimes('name_of_signature_1_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_signature >= 1; });
            // $validator->sometimes('position_of_signature_1_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_signature >= 1; });
            // $validator->sometimes('signature_2_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_signature >= 2; });
            // $validator->sometimes('name_of_signature_2_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_signature >= 2; });
            // $validator->sometimes('position_of_signature_2_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_signature >= 2; });
            // $validator->sometimes('signature_3_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_signature >= 3; });
            // $validator->sometimes('name_of_signature_3_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_signature >= 3; });
            // $validator->sometimes('position_of_signature_3_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_signature >= 3; });

        }

        if ($validator->fails() || !empty($error)) {
            $data = array_merge($validator->errors()->toArray(), $error);
            return response()->json($data, 422);
        }

        $authSession = Auth::user();
        $data = new Certificates;
        $data->fill($input);
        $data->create_datetime = date('Y-m-d H:i:s');
        $data->created_by = $authSession->id;
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();
        if ($is_success) {
            $message = "The certificates has been created.";

            if (!empty($data->file_preview)) {
                $pathOldFile = storage_path('app/certificates/pdf/').$data->file_preview;
                if (file_exists($pathOldFile)) {
                    unlink($pathOldFile);
                }
            }

            // Gen Cert. PDF
            $pdf = PDF::setOptions([
                'defaultFont' => 'thsarabunnew',
                'isRemoteEnabled' => true,
            ]);

            $pdf->loadView('certificate-preview', ['data' => $data, 'lang' => null]);

            $pathPreview = storage_path('app/certificates/pdf/');
            $pathFile = 'Certificate-Preview-'.time().'-'.mt_rand(0, 100).'.pdf';
            $pdf->setPaper('a3', 'landscape')->setWarnings(true)->save($pathPreview.$pathFile);

            $data->file_preview = $pathFile;
            $data->save();
        } else {
            $message = "Failed to create the certificates.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message, 'createdId' => $data->id), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
        $data = Certificates::find($id);
        return response()->json($data->toArray(), 200);
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
        $error = array();
        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'groups_id' => 'required|numeric',
            'title' => 'required|max:255',
            'body_text_1' => 'required|max:255',
            'body_text_2' => 'required|max:255',
            'logo_align' => 'required_unless:number_of_logo,|max:2',
            'signature_align' => 'required_unless:number_of_signature,|max:2',
            'background_color' => 'required|max:255',
            'text_color' => 'required|max:255',
            'border_color' => 'required|max:255',
            'border_style' => 'required|in:normal,radius|max:255',
            'name_of_signature_1' => 'required_with:signature_1|max:255',
            'position_of_signature_1' => 'required_with:signature_1|max:255',
            'name_of_signature_2' => 'required_with:signature_2|max:255',
            'position_of_signature_2' => 'required_with:signature_2|max:255',
            'name_of_signature_3' => 'required_with:signature_3|max:255',
            'position_of_signature_3' => 'required_with:signature_3|max:255',
            'name_of_signature_1_en' => 'required_with:signature_1_en|max:255',
            'position_of_signature_1_en' => 'required_with:signature_1_en|max:255',
            'name_of_signature_2_en' => 'required_with:signature_2_en|max:255',
            'position_of_signature_2_en' => 'required_with:signature_2_en|max:255',
            'name_of_signature_3_en' => 'required_with:signature_3_en|max:255',
            'position_of_signature_3_en' => 'required_with:signature_3_en|max:255',
        ]);

        // $validator->sometimes('logo_1', 'required|max:255', function ($input) { return $input->number_of_logo >= 1; });
        // $validator->sometimes('logo_2', 'required|max:255', function ($input) { return $input->number_of_logo >= 2; });
        // $validator->sometimes('logo_3', 'required|max:255', function ($input) { return $input->number_of_logo >= 3; });
        // $validator->sometimes('signature_1', 'required|max:255', function ($input) { return $input->number_of_signature >= 1; });
        // $validator->sometimes('name_of_signature_1', 'required|max:255', function ($input) { return $input->number_of_signature >= 1; });
        // $validator->sometimes('position_of_signature_1', 'required|max:255', function ($input) { return $input->number_of_signature >= 1; });
        // $validator->sometimes('signature_2', 'required|max:255', function ($input) { return $input->number_of_signature >= 2; });
        // $validator->sometimes('name_of_signature_2', 'required|max:255', function ($input) { return $input->number_of_signature >= 2; });
        // $validator->sometimes('position_of_signature_2', 'required|max:255', function ($input) { return $input->number_of_signature >= 2; });
        // $validator->sometimes('signature_3', 'required|max:255', function ($input) { return $input->number_of_signature >= 3; });
        // $validator->sometimes('name_of_signature_3', 'required|max:255', function ($input) { return $input->number_of_signature >= 3; });
        // $validator->sometimes('position_of_signature_3', 'required|max:255', function ($input) { return $input->number_of_signature >= 3; });

        if (empty($input['groups_id']) || !is_numeric($input['groups_id'])) {
            $error['groups_id'][] = "The groups id field is required.";
        } else {
            $dataGroup = Groups::find($input['groups_id']);
            if (!$dataGroup) {
                $error['groups_id'][] = "The groups id field is required.";
            }

            $validator->sometimes('body_text_1_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1; });
            $validator->sometimes('body_text_2_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1; });

            // $validator->sometimes('logo_1_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_logo >= 1; });
            // $validator->sometimes('logo_2_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_logo >= 2; });
            // $validator->sometimes('logo_3_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_logo >= 3; });
            // $validator->sometimes('signature_1_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_signature >= 1; });
            // $validator->sometimes('name_of_signature_1_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_signature >= 1; });
            // $validator->sometimes('position_of_signature_1_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_signature >= 1; });
            // $validator->sometimes('signature_2_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_signature >= 2; });
            // $validator->sometimes('name_of_signature_2_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_signature >= 2; });
            // $validator->sometimes('position_of_signature_2_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_signature >= 2; });
            // $validator->sometimes('signature_3_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_signature >= 3; });
            // $validator->sometimes('name_of_signature_3_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_signature >= 3; });
            // $validator->sometimes('position_of_signature_3_en', 'required|max:255', function ($input) use ($dataGroup) { return $dataGroup->multi_lang_certificate == 1 && $input->number_of_signature >= 3; });

        }

        if ($validator->fails() || !empty($error)) {
            $data = array_merge($validator->errors()->toArray(), $error);
            return response()->json($data, 422);
        }

        $authSession = Auth::user();
        $data = Certificates::find($id);
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();
        if ($is_success) {
            $message = "The certificates has been updated.";

            if (!empty($data->file_preview)) {
                $pathOldFile = storage_path('app/certificates/pdf/').$data->file_preview;
                if (file_exists($pathOldFile)) {
                    unlink($pathOldFile);
                }
            }

            // Gen Cert. PDF
            $pdf = PDF::setOptions([
                'defaultFont' => 'thsarabunnew',
                'isRemoteEnabled' => true,
            ]);

            $pdf->loadView('certificate-preview', ['data' => $data, 'lang' => null]);

            $pathPreview = storage_path('app/certificates/pdf/');
            $pathFile = 'Certificate-Preview-'.time().'-'.mt_rand(0, 100).'.pdf';
            $pdf->setPaper('a3', 'landscape')->setWarnings(true)->save($pathPreview.$pathFile);

            $data->file_preview = $pathFile;
            $data->save();
        } else {
            $message = "Failed to update the certificates.";
        }
        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
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
        $data = Certificates::find($id);
        $is_success = $data->delete();
        if ($is_success) {
            $message = "The certificates has been deleted.";
        } else {
            $message = "Failed to delete the certificates.";
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

        $data = Certificates::find($id);
        $data->status = $input['status'];
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The certificate has been updated.";
        } else {
            $message = "Failed to update the certificate.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function parent()
    {
        //
        $data = Certificates::where('parent','0')->get();
        return response()->json($data, 200);
    }

    public function all(Request $request, _RolesController $oRole)
    {
        //
        $order_by = $request->input('order_by', 'id');
        $order_direction = $request->input('order_direction', 'ASC');

        $data = new Certificates;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        $data = $data->with('groups')->with('courses');

        if ($request->has('courses_id')) {
            if ($authSession->super_users) {
                $data = $data->where('groups_id', $authSession->groups_id);
            } else {
                $dataCourse = Courses::find($request['courses_id']);

                $data = $data->whereHas('groups', function($query) use ($authSessionGroups, $dataCourse) {
                    $query->whereIn('groups_id', array_pluck($authSessionGroups->intersect($dataCourse->groups), 'id'));
                });
            }

            $data = $data->whereNull('courses_id')->orWhereHas('courses', function($query) use ($request, $order_direction) {
                $query->where('courses_id', $request['courses_id']);
            });
        } else {
            if ($authSession->super_users) {
                $data = $data->where('groups_id', $authSession->groups_id);
            } else if (!$oRole->isSuper()) {
                $data = $data->whereHas('groups', function($query) use ($authSessionGroups) {
                    $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
                });
            }
        }

        // return response()->json(['debug' => $data->toSql()], 404);
        $data = $data->where('status', 1)->orderBy($order_by,$order_direction)->get();


        for($i=0; $i<count($data); $i++) {
            $admins = Admins::find($data[$i]->created_by);
            $data[$i]->created_by = $admins->username;
            $admins = Admins::find($data[$i]->modify_by);
            $data[$i]->modify_by = $admins->username;
            // $data[$i]->groups = $data[$i]->groups()->get();
            $data[$i]->title_with_detail = $data[$i]->title." (".$data[$i]->groups->title.")".(!is_null($data[$i]->courses) ? ' <span class="c-red">**เฉพาะคอร์ส '.$data[$i]->courses->code.'**</span>' : '');
            // $data[$i]->title_with_detail = $data[$i]->title.(!is_null($data[$i]->courses) ? ' <span class="c-red">**เฉพาะคอร์ส '.$data[$i]->courses->code.'**</span>' : '');
        }

        return response()->json($data, 200);
    }

    public function orders(Request $request)
    {
        $input = $request->json()->all();
        for($i=0; $i<count($input); $i++) {
            $data[$i] = Certificates::find($input[$i]['id']);
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

        $entity = Certificates::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = Certificates::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = Certificates::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = Certificates::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = Certificates::where('order', '>', $request['order'])->min('id');
                    Certificates::find($next)->decrement('order');
                    $entity->moveBefore(Certificates::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The certificates has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

    public function createPreview(Request $request, _SecurityController $_security, _FunctionsController $oFunc, SiteController $_site){
        $input = $request->json()->all();

        if (!empty($input['old_file'])) {
            $pathOldFile = storage_path('app/certificates/pdf/').$input['old_file'];
            if (file_exists($pathOldFile)) {
                unlink(storage_path('app/certificates/pdf/').$input['old_file']);
            }
        }

        // Gen Cert. PDF
        $pdf = PDF::setOptions([
            'defaultFont' => 'thsarabunnew',
            'isRemoteEnabled' => true,
        ]);

        $pdf->loadView('certificate-preview', ['data' => (object)$input, 'lang' => $input['lang']]);

        $pathPreview = storage_path('app/certificates/pdf/');
        $pathFile = 'Certificate-Preview-'.time().'-'.mt_rand(0, 100).'.pdf';
        $pdf->setPaper('a3', 'landscape')->setWarnings(true)->save($pathPreview.$pathFile);

        return response()->json(["urlPreview" => route('certificates-preview', ['filename' => $pathFile]), "filePreview" => $pathFile], 200);
    }

    public function createPreviewByCourse(Request $request, _SecurityController $_security, _FunctionsController $oFunc, SiteController $_site){
        $input = $request->json()->all();

        if (!empty($input['old_file'])) {
            $pathOldFile = storage_path('app/certificates/pdf/').$input['old_file'];
            if (file_exists($pathOldFile)) {
                unlink(storage_path('app/certificates/pdf/').$input['old_file']);
            }
        }

        // Gen Cert. PDF
        $pdf = PDF::setOptions([
            'defaultFont' => 'thsarabunnew',
            'isRemoteEnabled' => true,
        ]);

        if (empty($input)) {
            return response()->json(array('message' => config('constants._errorMessage.__404')), _404);
        }

        $dataCertificate = Certificates::find($input['certificates_id']);

        $pdf->loadView('certificate-course-preview', ['data' => $dataCertificate, 'course' => (object)$input, 'lang' => $input['lang']]);

        $pathPreview = storage_path('app/certificates/pdf/');
        $pathFile = 'Certificate-Preview-'.time().'-'.mt_rand(0, 100).'.pdf';
        $pdf->setPaper('a3', 'landscape')->setWarnings(true)->save($pathPreview.$pathFile);

        return response()->json(["urlPreview" => route('certificates-preview', ['filename' => $pathFile]), "filePreview" => $pathFile], 200);
    }

    public function preview($filename) {
        return response()->file(storage_path('app/certificates/pdf/').$filename);
    }

    public function deletePreview($filename) {
        $pathOldFile = storage_path('app/certificates/pdf/').$filename;
        if (file_exists($pathOldFile)) {
            if (unlink($pathOldFile)) {
                $isSuccess = true;
            } else {
                $isSuccess = false;
            }
        } else {
            $isSuccess = false;
        }

        return response()->json(['isSuccess' => $isSuccess], 200);
    }

    public function getPreviewImage($filename) {
        $imagick = new \imagick(storage_path('app/certificates/pdf/').$filename.'[0]');
        $imagick->setImageFormat('jpg');

        return response($imagick)->header('Content-Type', 'image/jpeg');
    }

}
