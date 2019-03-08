<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Orders;
use App\Models\Admins;
use App\Models\Payments;
use App\Models\Methods;
use App\Models\Members;

use Auth;
use Torann\GeoIP\Facades\GeoIP as GeoIP;
use Jenssegers\Agent\Agent;
use Mail;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $per_page = $request->input('per_page', 10);
        $order_by = $request->input('order_by', 'id');
        $order_direction = $request->input('order_direction', 'DESC');

        $data = new Orders;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if($request->has('search')){
            $data = $data->where(function ($query) use ($request) {
                $query->where('id', 'like', '%'.$request['search'].'%')
                      ->orWhere('courses_code', 'like', '%'.$request['search'].'%')
                      ->orWhere('courses_title', 'like', '%'.$request['search'].'%')
                      ->orWhereHas('members', function($query) use ($request) {
                            $query->where('email', 'like', '%'.$request['search'].'%')
                                  ->orWhere('first_name', 'like', '%'.$request['search'].'%')
                                  ->orWhere('first_name_en', 'like', '%'.$request['search'].'%')
                                  ->orWhere('last_name', 'like', '%'.$request['search'].'%')
                                  ->orWhere('last_name_en', 'like', '%'.$request['search'].'%');
                        });
            });
        }

        $from_date = $request['from_date'];
        $to_date = $request['to_date'];

        if($from_date && $to_date){
            $from_date = date("Y-m-d", strtotime($from_date));
            $to_date = date("Y-m-d", strtotime($to_date."+1 day"));
            $data = $data->whereBetween('orders.create_datetime',array($from_date,$to_date));
        }

        $data = $data->orderBy($order_by, $order_direction)->paginate($per_page);
        for($i=0; $i<count($data); $i++) {
            if ($data[$i]->create_by) {
                $admins = Admins::find($data[$i]->create_by);
                $data[$i]->create_by = $admins->username;
            }

            if ($data[$i]->modify_by) {
                $admins = Admins::find($data[$i]->modify_by);
                $data[$i]->modify_by = $admins->username;
            }

            $data[$i]->members = $data[$i]->members()->first();
            $data[$i]->methods = $data[$i]->methods()->first();
            $data[$i]->payments = $data[$i]->payments()->first();
            // $data[$i]->payments = $data[$i]->payments()->where('payment_status', 'successful')->where('approve_status', 1)->orderBy('approve_datetime', 'DESC')->first();
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
            'methods_id' => 'required|numeric',
            'members_id' => 'required|numeric',
            'courses_id' => 'required|numeric',
            'courses_code' => 'required|max:255',
            'courses_title' => 'required|max:255',
            'courses_price' => 'required|numeric',
            'currency' => 'required|max:3',
            'type_tax_invoice' => 'required|in:personal,corporate',
            'inv_name' => 'required|max:255',
            'inv_branch' => 'required_if:type_tax_invoice,corporate|max:1',
            'inv_branch_no' => 'required_if:inv_branch,1|digits:5',
            'inv_tax_id' => 'required|max:20',
            'inv_email' => 'required|email|max:255',
            'inv_tel' => 'required|max:10',
            'inv_address' => 'required|max:220',
            'inv_zip_code' => 'required|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $authSession = Auth::user();
        $dataGeoIP = GeoIP::getLocation();
        $agent = new Agent();

        $data = new Orders;
        $data->fill($input);

        do {
            $token = str_random(32);
            $dataOrders = Orders::where('token', '=', $token)->first();
        } while ($token == $dataOrders['token']);

        $data->token = $token;

        $data->ip = ($dataGeoIP['ip'] != "Unknown") ? $dataGeoIP['ip'] : $_SERVER['REMOTE_ADDR'];
        $data->user_agent = $_SERVER['HTTP_USER_AGENT'];
        $data->isoCode = $dataGeoIP['iso_code'];
        $data->country = $dataGeoIP['country'];
        $data->city = $dataGeoIP['city'];
        $data->timezone = $dataGeoIP['timezone'];
        $data->continent = $dataGeoIP['continent'];
        $data->device = $agent->device();
        $data->platform = $agent->platform();
        $data->platform_version = $agent->version($data->platform);
        $data->browser = $agent->browser();
        $data->browser_version = $agent->version($data->browser);

        $data->create_datetime = date('Y-m-d H:i:s');
        $data->create_by = $authSession->id;
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The orders has been created.";
        } else {
            $message = "Failed to create the orders.";
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
        if (!$oRole->haveAccess($id, "orders")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = Orders::find($id);

        if ($data->create_by) {
            $admins = Admins::find($data->create_by);
            $data->create_by = $admins->username;
        }

        $data->members = $data->members()->with('groups')->first();
        $data->methods = $data->methods()->first();

        switch ($data->methods->type) {
            case 1: $data->methods->type_title  = "บัตรเครดิต/เดบิต"; break;
            case 2: $data->methods->type_title  = "โอนเงิน"; break;
            default: $data->methods->type_title = ""; break;
        }

        $data->payments = $data->payments()->get();

        for($i=0; $i<count($data->payments); $i++) {
            if ($data->payments[$i]->approve_by) {
                $admins = Admins::find($data->payments[$i]->approve_by);
                $data->payments[$i]->approve_by = $admins->username;
            }

            if ($data->payments[$i]->create_by) {
                $admins = Admins::find($data->payments[$i]->create_by);
                $data->payments[$i]->create_by = $admins->username;
            }

            if ($data->payments[$i]->modify_by) {
                $admins = Admins::find($data->payments[$i]->modify_by);
                $data->payments[$i]->modify_by = $admins->username;
            }
        }

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
    public function update($id, Request $request, _RolesController $oRole)
    {
        // Check Permission Acces
        if (!$oRole->haveAccess($id, "orders")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'methods_id' => 'required|numeric',
            'members_id' => 'required|numeric',
            'courses_id' => 'required|numeric',
            'courses_code' => 'required|max:255',
            'courses_title' => 'required|max:255',
            'courses_price' => 'required|numeric',
            'currency' => 'required|max:3',
            'type_tax_invoice' => 'required|in:personal,corporate',
            'inv_name' => 'required|max:255',
            'inv_branch' => 'required_if:type_tax_invoice,corporate|max:1',
            'inv_branch_no' => 'required_if:inv_branch,1|digits:5',
            'inv_tax_id' => 'required|max:20',
            'inv_email' => 'required|email|max:255',
            'inv_tel' => 'required|max:10',
            'inv_address' => 'required|max:220',
            'inv_zip_code' => 'required|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $authSession = Auth::user();

        $data = Orders::find($id);
        $data->fill($input);
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The orders has been updated.";
        } else {
            $message = "Failed to update the orders.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
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
        if (!$oRole->haveAccess($id, "orders")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        $data = Orders::find($id);

        $countPayments = Payments::where('orders_id', $id)->count();

        if ($countPayments > 0) {
            $is_success = false;
            $message = "Failed to delete the orders because the relevant payment information is available.";
        } else {
            $is_success = $data->delete();
            if ($is_success) {
                $message = "The orders has been deleted.";
            } else {
                $message = "Failed to delete the orders.";
            }
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

        $data = Orders::find($id);
        $data->status = $input['status'];
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The order has been updated.";
        } else {
            $message = "Failed to update the order.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }

    public function all()
    {
        //
        $data = new Orders;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();
        // if($authSession->super_users){
        //     $data = $data->where('sub_groups_id', $authSession->sub_groups_id);
        // }else{
        //     $data = $data->with('groups');
        //     $data = $data->whereHas('groups', function($query) use ($authSessionGroups) {
        //         $query->whereIn('groups_id', array_pluck($authSessionGroups, 'id'));
        //     });
        // }

        $data = $data->get();
        for($i=0; $i<count($data); $i++) {
            $data[$i]->title = $data[$i]->id.' ('.$data[$i]->courses_code.' - '.$data[$i]->courses_title.')';
            $data[$i]->members = $data[$i]->members()->first();
            $data[$i]->methods = $data[$i]->methods()->first();
            $data[$i]->payments = $data[$i]->payments()->get();
        }
        return response()->json($data, 200);
    }

    public function orders(Request $request)
    {
        $input = $request->json()->all();
        for($i=0; $i<count($input); $i++) {
            $data[$i] = Orders::find($input[$i]['id']);
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

        $entity = Orders::find($request['id']);

        if (is_numeric($request['positionEntityId'])) {
            $positionEntity = Orders::find($request['positionEntityId']);

            if ($request['type'] == "moveAfter") {
                $entity->moveAfter($positionEntity);
            } else {
                $entity->moveBefore($positionEntity);
            }
        } else if (is_numeric($request['order'])) {
            $data = Orders::where('order', '=', $request['order'])->first();

            if ($data) {
                if ($data->order > $entity['order']) {
                    $entity->moveAfter($data);
                } else if ($data->order < $entity['order']) {
                    $entity->moveBefore($data);
                }
            } else {
                $last = Orders::orderBy('order', 'desc')->first();
                if ($request['order'] > $last->order) {
                    $entity->moveAfter($last);
                } else {
                    $next = Orders::where('order', '>', $request['order'])->min('id');
                    Orders::find($next)->decrement('order');
                    $entity->moveBefore(Orders::find($next));
                }
            }
        } else {
            $message = "Failed to sort.";
            return response()->json(array('message' => $message), 500);
        }

        $message = "The orders has been sorted.";
        return response()->json(array('message' => $message), 200);
    }

}
