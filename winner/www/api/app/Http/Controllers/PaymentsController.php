<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Payments;
use App\Models\Orders;
use App\Models\Methods;
use App\Models\Admins;

use Auth;

class PaymentsController extends Controller
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

        $data = new Payments;
        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        if($request->has('search')){
            $data = $data->where(function ($query) use ($request) {
                $query->where('id', 'like', '%'.$request['search'].'%')
                      ->orWhere('orders_id', 'like', '%'.$request['search'].'%')
                      ->orWhere('amount', 'like', '%'.$request['search'].'%')
                      ->orWhere('txn', 'like', '%'.$request['search'].'%');
            });
        }

        $from_date = $request['from_date'];
        $to_date = $request['to_date'];

        if($from_date && $to_date){
            $from_date = date("Y-m-d", strtotime($from_date));
            $to_date = date("Y-m-d", strtotime($to_date."+1 day"));
            $data = $data->whereBetween('payments.create_datetime',array($from_date,$to_date));
        }

        $data = $data->orderBy($order_by, $order_direction)->paginate($per_page);
        for($i=0; $i<count($data); $i++) {
            if ($data[$i]->approve_by) {
                $admins = Admins::find($data[$i]->approve_by);
                $data[$i]->approve_by = $admins->username;
            }

            if ($data[$i]->create_by) {
                $admins = Admins::find($data[$i]->create_by);
                $data[$i]->create_by = $admins->username;
            }

            if ($data[$i]->modify_by) {
                $admins = Admins::find($data[$i]->modify_by);
                $data[$i]->modify_by = $admins->username;
            }

            $data[$i]->orders = $data[$i]->orders()->first();
            if ($data[$i]->orders->create_by) {
                $admins = Admins::find($data[$i]->orders->create_by);
                $data[$i]->orders->create_by = $admins->username;
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
            'orders_id' => 'required|numeric',
            'methods' => 'required|max:255',
            'methods_type' => 'required|max:255',
            'amount' => 'required|numeric',
            'currency' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $authSession = Auth::user();

        $data = new Payments;
        $data->fill($input);

        $uniqueOrdersId = Payments::where('orders_id', '=', $data->orders_id)->count();

        if ($uniqueOrdersId > 0) {
            $message = "The order '".$data->orders_id."' has already exist payment.";
            $is_success = false;

            return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
        }

        if ($input['payment_status'] == 'successful') {
            $data->approve_by = $authSession->id;
            $data->approve_datetime = date('Y-m-d H:i:s');
        }

        $data->create_datetime = date('Y-m-d H:i:s');
        $data->create_by = $authSession->id;
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The payment has been created.";
        } else {
            $message = "Failed to create the payment.";
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
        $data = Payments::find($id);
        $data->orders = $data->orders()->first();

        if ($data->approve_by) {
            $admins = Admins::find($data->approve_by);
            $data->approve_by = $admins->username;
        }

        if ($data->create_by) {
            $admins = Admins::find($data->create_by);
            $data->create_by = $admins->username;
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
    public function update($id, Request $request)
    {
        //
        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'orders_id' => 'required|numeric',
            'methods' => 'required|max:255',
            'methods_type' => 'required|max:255',
            'amount' => 'required|numeric',
            'currency' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $authSession = Auth::user();

        $data = Payments::find($id);

        if ($data->payment_status != 'successful' && $input['payment_status'] == 'successful') {
            $data->approve_by = $authSession->id;
            $data->approve_datetime = date('Y-m-d H:i:s');
        }

        $data->fill($input);

        $uniqueOrdersId = Payments::where('id', '!=', $data->id)->where('orders_id', '=', $data->orders_id)->count();

        if ($uniqueOrdersId > 0) {
            $message = "The order '".$data->orders_id."' has already exist payment.";
            $is_success = false;

            return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
        }

        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = $authSession->id;
        $is_success = $data->save();
        if ($is_success) {
            $message = "The payment has been updated.";
        } else {
            $message = "Failed to update the payment.";
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
        $data = Payments::find($id);
        $is_success = $data->delete();
        if ($is_success) {
            $message = "The payment has been deleted.";
        } else {
            $message = "Failed to delete the payment.";
        }
        return response()->json(array('is_error' => !$is_success, 'message'=>$message), 200);
    }

    public function downloadReconcileFile($id, _RolesController $oRole) {

        $authSession = Auth::user();
        $authSessionGroups = $authSession->admins_groups()->first();
        $authSessionGroups = $authSessionGroups->groups()->get();

        $data = Payments::find($id);

        if (!$data) {
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "'Payment'", config('constants._errorMessage._404'))), 404);
        }

        // Check Permission Acces
        if (!$oRole->haveAccess($data->orders_id, "orders")) {
            return response()->json(array('message' => config('constants._errorMessage._'.$oRole->_errorCode)), $oRole->_errorCode);
        }

        return response()->download(storage_path('app/reconcile-reports/').$data->validate_file_csv, $data->validate_file_csv);
    }

    public function updateIsCanceled($id, Request $request)
    {
        //
        $input = $request->json()->all();

        $validator = Validator::make($input, [
            'is_canceled' => 'required|max:1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $data = Payments::find($id);
        $data->is_canceled = $input['is_canceled'];
        $data->modify_datetime = date('Y-m-d H:i:s');
        $data->modify_by = Auth::user()->id;
        $is_success = $data->save();

        if ($is_success) {
            $message = "The payment has been updated.";
        } else {
            $message = "Failed to update the payment.";
        }

        return response()->json(array('is_error' => !$is_success, 'message' => $message), 200);
    }


}
