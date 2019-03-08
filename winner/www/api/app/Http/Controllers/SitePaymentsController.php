<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Validator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Orders;
use App\Models\Methods;
use App\Models\Payments;
use App\Models\Members;

use Auth;
use Hash;
use Input;
use GeoIP;
use Mail;
use Omise;
use OmiseCharge;

class SitePaymentsController extends Controller
{
    public function token($token)
    {
        $data = Orders::where('token', '=', $token)->first();
        return response()->json($data, 200);
    }

    public function orders_update($id, Request $request)
    {
        //
        $data = Orders::find($id);
        $input = $request->json()->all();
        $data->methods_id = $input['methods_id'];
        $is_success = $data->save();
        return response()->json(array('is_error' => !$is_success), 200);
    }

    public function methods()
    {
        $data = Methods::where('status', '=', '1')->orderBy('order','asc')->get();
        return response()->json($data, 200);
    }

    public function result2c2p(Request $request, _SecurityController $_security, _FunctionsController $oFunc, SiteController $_site)
    {
        $input = $request->all();

        $dataOrder = Orders::find($input['order_id']);
        if (!$dataOrder) {
            $message = "ไม่พบรายการสั่งซื้อดังกล่าว";
            $_site->logs('payments', '{"alert_msg":"'.$message.'"}', 404, '', '', json_encode($input, JSON_UNESCAPED_UNICODE), '', '', '', '');
            return response()->json(array('message' => str_replace("{{_RESOURCE_}}", "'Order'", config('constants._errorMessage._404'))), 404);
        }

        $secret_key = env('2C2P_'.strtoupper($dataOrder->groups->key).'_SECRET_KEY');

        if (!$oFunc->_2c2pCheckHash($input, $secret_key)) {
            $message = "การชำระเงินไม่ถูกต้อง";
            $_site->logs('payments', '{"alert_msg":"'.$message.'"}', 400, '', $dataOrder->members->id, json_encode($input, JSON_UNESCAPED_UNICODE), $dataOrder->members->groups_id, $dataOrder->members->sub_groups_id, '', '');
            return response()->json(['message' => config('constants._errorMessage._400')], 400);
        }

        $payments = Payments::where('orders_id', $dataOrder->id)->first();
        if (!$payments) {
            $payments = new Payments;
            $payments->create_datetime = date('Y-m-d H:i:s');
        }

        $payments->orders_id = $dataOrder->id;
        $payments->methods = $dataOrder->methods->title;

        switch ($input['payment_channel']) {
            case '001': $payments->methods_type  = "Credit and debit cards"; break;
            case '002': $payments->methods_type  = "Cash payment channel"; break;
            case '003': $payments->methods_type  = "Direct debit"; break;
            case '004': $payments->methods_type  = "Others"; break;
            case '005': $payments->methods_type  = "IPP transaction"; break;
            default: $payments->methods_type = null; break;
        }

        $payments->merchant_id = $input['merchant_id'];
        $payments->amount = (int)$input['amount'] / 100;
        $payments->currency = empty($input['currency']) ? "THB" : $oFunc->getCodeCurrency($input['currency']);
        $payments->approval_code = $input['approval_code'];
        $payments->txn = $input['transaction_ref'];
        $payments->txn_datetime = $input['transaction_datetime'];
        $payments->paid_channel = $input['paid_channel'];

        switch ($input['payment_status']) {
            case '000':
                $payments->payment_status  = "successful";
                // $payments->approve_status = 1;
                $payments->approve_datetime = date('Y-m-d H:i:s');
                break;
            case '001': $payments->payment_status  = "pending"; break;
            case '002': $payments->payment_status  = "rejected"; break;
            case '003': $payments->payment_status  = "canceled_by_user"; break;
            default: $payments->payment_status = "failed"; break;
        }

        $payments->payment_code = $input['payment_status'];
        $payments->payment_message = $input['channel_response_desc'];
        $payments->raw_data = json_encode($input, JSON_UNESCAPED_UNICODE);
        $payments->modify_datetime = date('Y-m-d H:i:s');
        $is_success = $payments->save();

        if ($is_success) {
            // Mail::send('mail-billing', ['data' => $payments, 'webs' => $webs, 'orders' => $data], function($message) use ($payments)
            // {
            //     $message->from(env('MAIL_USERNAME'), 'Thailivestream Platforms');
            //     $message->to($payments->members_email, $payments->members_email)->subject('Thank you for payments #'.$payments->txn);
            // });

            $_site->logs('payments', '{"alert_msg":"success"}', 200, '', $dataOrder->members->id, json_encode($request->json()->all(), JSON_UNESCAPED_UNICODE), $dataOrder->members->groups_id, $dataOrder->members->sub_groups_id, '', '');
        }

        $_user = session()->get('_user');

        if ($_user) {
            $url = str_replace("{GROUP_KEY}", $_user->groups->key, config('constants.URL_GROUP.MY_ORDERS'));
            return redirect($url);
        }

        // return response()->json(array('is_error' => !$is_success, 'message' => $message, 'createdId' => $payments->id), 200);
    }

    // public function createPayments(Request $request, _SecurityController $_security, _FunctionsController $oFunc, SiteController $_site)
    // {
    //     if (!$oFunc->checkSession()) {
    //         return response()->json(array('message' => config('constants._errorMessage._401')), 401);
    //     }

    //     $_user = session()->get('_user');
    //     $input = $request->json()->all();

    //     $validator = Validator::make($input, [
    //         'omiseToken' => 'required|max:255',
    //         'omise_price' => 'required|numeric',
    //         'orders_id' => 'required|numeric',
    //         'courses_price' => 'required|numeric',
    //         'currency' => 'required|max:3',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->messages(), 422);
    //     }

    //     $dataOrder = Orders::find($input['orders_id']);
    //     if (!$dataOrder) {
    //         $message = "ไม่พบรายการสั่งซื้อดังกล่าว";
    //         $_site->logs('payments', '{"alert_msg":"'.$message.'"}', 404, '', $_user['id'], json_encode($request->json()->all(), JSON_UNESCAPED_UNICODE), $_user->groups_id, $_user->sub_groups_id, '', '');
    //         return response()->json(array('is_error' => true, 'message' => $message), 404);
    //     }

    //     define('OMISE_API_VERSION', env('OMISE_API_VERSION'));
    //     define('OMISE_PUBLIC_KEY', env('OMISE_PUBLIC_KEY'));
    //     define('OMISE_SECRET_KEY', env('OMISE_SECRET_KEY'));
    //     $charge = OmiseCharge::create(array(
    //         'amount' => (int)$input['omise_price'],
    //         'currency' => strtolower($input['currency']),
    //         'card' => $input['omiseToken']
    //     ));

    //     if ($charge['status'] == 'successful') {
    //         $payments = new Payments;
    //         $payments->orders_id = $dataOrder->id;
    //         $payments->methods = $dataOrder->methods->title;

    //         switch ($dataOrder->methods->type) {
    //             case 1: $payments->methods_type  = "บัตรเครดิต/เดบิต"; break;
    //             case 2: $payments->methods_type  = "โอนเงิน"; break;
    //             default: $payments->methods_type = ""; break;
    //         }

    //         $payments->amount = (int)$input['courses_price'];
    //         $payments->currency = strtoupper($input['currency']);
    //         $payments->txn = $charge['transaction'];
    //         $payments->payment_status = $charge['status'];
    //         // $payments->payment_code = $charge['failure_code'];
    //         // $payments->payment_message = $charge['failure_message'];
    //         $payments->approve_status = 1;
    //         $payments->approve_datetime = date('Y-m-d H:i:s');
    //         $payments->create_datetime = date('Y-m-d H:i:s');
    //         $payments->modify_datetime = date('Y-m-d H:i:s');
    //         $is_success = $payments->save();

    //         if ($is_success) {
    //             // Mail::send('mail-billing', ['data' => $payments, 'webs' => $webs, 'orders' => $data], function($message) use ($payments)
    //             // {
    //             //     $message->from(env('MAIL_USERNAME'), 'Thailivestream Platforms');
    //             //     $message->to($payments->members_email, $payments->members_email)->subject('Thank you for payments #'.$payments->txn);
    //             // });
    //             $dataOrder->order_status = 1;
    //             $dataOrder->save();

    //             $is_error = false;
    //             $message = "ดำเนินการซื้อเสร็จสมบูรณ์ ระบบกำลังโหลดหน้าใหม่";
    //             $_site->logs('payments', '{"alert_msg":"success"}', 200, '', $_user['id'], json_encode($request->json()->all(), JSON_UNESCAPED_UNICODE), $_user->groups_id, $_user->sub_groups_id, '', '');
    //         } else {
    //             $is_error = true;
    //             $message = "มีปัญหาในการสั่งซื้อ กรุณาติดต่อเจ้าหน้าที่";
    //             $_site->logs('payments', '{"alert_msg":"'.$message.'"}', 500, '', $_user['id'], json_encode($request->json()->all(), JSON_UNESCAPED_UNICODE), $_user->groups_id, $_user->sub_groups_id, '', '');
    //         }
    //     } else {
    //         $is_error = true;
    //         $message = "มีปัญหาในการสั่งซื้อ กรุณาติดต่อเจ้าหน้าที่";

    //         $payments = new Payments;
    //         $payments->orders_id = $dataOrder->id;
    //         $payments->methods = $dataOrder->methods->title;
    //         $payments->amount = (int)$input['courses_price'];
    //         $payments->currency = strtoupper($input['currency']);
    //         $payments->txn = $charge['transaction'];
    //         $payments->payment_status = $charge['status'];
    //         $payments->payment_code = $charge['failure_code'];
    //         $payments->payment_message = $charge['failure_message'];
    //         // $payments->approve_status = 1;
    //         // $payments->approve_datetime = date('Y-m-d H:i:s');
    //         $payments->create_datetime = date('Y-m-d H:i:s');
    //         $payments->modify_datetime = date('Y-m-d H:i:s');
    //         $is_success = $payments->save();

    //         $_site->logs('payments', '{"alert_msg":"'.$message.'"}', 500, '', $_user['id'], json_encode($request->json()->all(), JSON_UNESCAPED_UNICODE), $_user->groups_id, $_user->sub_groups_id, '', '');
    //     }

    //     return response()->json(array('is_error' => !$is_success, 'message' => $message, 'createdId' => $payments->id), 200);
    // }

}
