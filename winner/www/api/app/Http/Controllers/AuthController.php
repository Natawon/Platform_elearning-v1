<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17/7/2558
 * Time: 10:59 น.
 */

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Admins;
use App\Models\AdminsGroups;
use App\Models\AdminsMenu;
use Auth;
use Hash;
use Carbon\Carbon;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->guard = 'admin';
    }

    public function login (Request $request) {

        if ($request->has('forceLogin') && $request->forceLogin == true) {
            $tempAdmin = session()->get('temp_admin');
            $requestData = $tempAdmin;
        } else {
            $requestData = $request->json()->all();
        }

        $username = $requestData['username'];
        $password = $requestData['password'];
        $remember_me = $requestData['remember'];

        $data = Admins::where("username", "=", $username)->where("status", "=", "1")->first();
        if ($data) {
            if ($data->active != 1) {
                $message = 'บัญชีผู้ใช้ของท่านได้ยังไม่ได้เปิดใช้งาน <br>** กรุณาติดต่อผู้ดูแลระบบ **';
                return response()->json(array('is_error' => true, 'message'=>$message), 500);
            }

            if ($data->admins_groups->incorrect_password_limit > 0 && $data->incorrect_password >= $data->admins_groups->incorrect_password_limit) {
                $data->active = 2;
                $data->suspended_datetime = date('Y-m-d H:i:s');
                $data->save();

                $message = 'บัญชีผู้ใช้ของท่านได้ถูกระงับการใช้งาน เนื่องจากท่านใส่รหัสผ่านผิดเกินจำนวนที่กำหนด <br>** กรุณาติดต่อผู้ดูแลระบบ **';
                return response()->json(array('is_error' => true, 'message'=>$message), 500);
            }

            if (Hash::check($password, $data->encode_password)) {

                if ($data->admins_groups->max_account_age > 0) {
                    $dateNow = Carbon::now();
                    $dateLastLogin = Carbon::parse($data->last_login);

                    if ($data->active_remark != 3 && $dateNow->diffInDays($dateLastLogin) >= $data->admins_groups->max_account_age) {
                        $data->active = 2;
                        $data->suspended_datetime = date('Y-m-d H:i:s');
                        $data->save();

                        $message = 'บัญชีผู้ใช้ของท่านได้ถูกระงับการใช้งาน เนื่องจากท่านไม่ได้ใช้งานเกินระยเวลาที่กำหนด <br>** กรุณาติดต่อผู้ดูแลระบบ **';
                        return response()->json(array('is_error' => true, 'message'=>$message), 500);
                    }
                }

                $_user = Auth::guard($this->guard)->user();

                if ($data->my_session_id && (!$request->has('forceLogin') || $request->forceLogin === false)) {
                    if (is_null($_user) || (!is_null($_user) && $_user['my_session_id'] != $data->my_session_id)) {
                        $dataMySessionId = unserialize(session()->getHandler()->read($data->my_session_id));
                        if (!empty($dataMySessionId['_admin_session']) && (is_object($dataMySessionId['_admin_session']) || is_array($dataMySessionId['_admin_session']))) {
                            $request->session()->put('temp_admin', $request->all());
                            $message = 'บัญชีผู้ใช้ของท่านได้มีการใช้งานอยู่ในขณะนี้';
                            return response()->json(array('message' => $message, 'option' => 'session-exists'), 200);
                        }
                    }
                }

                $remember = false;
                if ($remember_me) {
                    $remember = true;
                }

                $data->incorrect_password = 0;
                $data->last_login = date('Y-m-d H:i:s');
                // $data->save();

                Auth::guard($this->guard)->login($data, $remember);

                $data->my_session_id = session()->getId();
                $data->save();

                $request->session()->put('_admin_session', $data);

                $responseData = array();
                $responseData = Auth::guard($this->guard)->user()->toArray();

                // Start Menu Permission
                $dataGroupMenu = AdminsGroups::find(Auth::guard($this->guard)->user()->admins_groups_id);

                $dataAllMenu = AdminsMenu::where('status', 1)->get();
                $menus = array();
                $count = 0;

                foreach ($dataGroupMenu->admins_menu()->orderBy('admins_menu_id','asc')->get()->toArray() as $groupMenu) {
                    foreach ($dataAllMenu as $dataMenu) {
                        if ($dataMenu["id"] == $groupMenu["id"]) {

                            if ($groupMenu["parent_id"] == 0) {
                                $menus[$groupMenu["title"]] = $groupMenu;
                            } else {
                                $dataParent = AdminsMenu::find($groupMenu["parent_id"])->toArray();

                                if (!isset($menus[$dataParent["title"]])) {
                                    $menus[$dataParent["title"]] = $dataParent;
                                }
                                $menus[$dataParent["title"]]["sub_menu"][] = $groupMenu;
                            }

                            break;
                        }
                    }

                    $count++;
                }

                // $menusPermission = array();
                $menus = array_values(array_sort($menus, function ($value) {
                    return $value['order'];
                }));

                // foreach ($menus as $menu) {
                //     $menusPermission[] = $menu;
                // }

                $dataGroupMenu->groups = $data->admins_groups->groups;
                $dataGroupMenu->admins_groups_menu = $menus;
                $responseData["admins_groups"] = $dataGroupMenu;
                $responseData["groups"] = Auth::guard($this->guard)->user()->groups;
                // End Menu Permission

                $responseData['option'] = null;
                // return response()->json(["debug" => $data->admins_groups->max_password_age], 500);
                if ($data->admins_groups->max_password_age > 0) {
                    $dateNow = Carbon::now();
                    $dateLastChangedPassword = Carbon::parse($data->last_changed_password);
                    $responseData['option'] = $dateNow->diffInDays($dateLastChangedPassword) >= $data->admins_groups->max_password_age ? 'change-password' : null;
                }

                return response()->json($responseData, 200);
            } else {
                $data->increment('incorrect_password');
                return response()->json(array('is_error' => true, 'message'=>'Login failed the password do not match.'), 500);
            }
        } else {
            return response()->json(array('is_error' => true, 'message'=>'Login failed '.$username.' cannot access.'), 500);
        }
    }

    public function logout()
    {
        $_auth = Auth::guard($this->guard);

        if ($_auth->user()) {
            $data = Admins::find($_auth->user()->id);
            $data->my_session_id = null;
            // $data->last_logout = date('Y-m-d H:i:s');
            $data->save();
            $_auth->logout();
        }

        session()->forget('_admin_session');
        return response()->json(array('is_error' => true, 'message' => 'Logged Out!!'));
    }

    public function forgetTempSession(Request $request)
    {
        session()->forget('temp_admin');
        return response()->json([], 200);
    }

    public function updateUser()
    {
        $authSession = Auth::guard($this->guard)->user();
        $responseData = array();
        $responseData = $authSession->toArray();

        // Start Menu Permission
        $dataGroupMenu = AdminsGroups::find($authSession->admins_groups_id);

        $dataAllMenu = AdminsMenu::where('status', 1)->get();
        $menus = array();
        $count = 0;

        foreach ($dataGroupMenu->admins_menu()->orderBy('admins_menu_id','asc')->get()->toArray() as $groupMenu) {
            foreach ($dataAllMenu as $dataMenu) {
                if ($dataMenu["id"] == $groupMenu["id"]) {

                    if ($groupMenu["parent_id"] == 0) {
                        $menus[$groupMenu["title"]] = $groupMenu;
                    } else {
                        $dataParent = AdminsMenu::find($groupMenu["parent_id"])->toArray();

                        if (!isset($menus[$dataParent["title"]])) {
                            $menus[$dataParent["title"]] = $dataParent;
                        }
                        $menus[$dataParent["title"]]["sub_menu"][] = $groupMenu;
                    }

                    break;
                }
            }

            $count++;
        }

        // $menusPermission = array();
        $menus = array_values(array_sort($menus, function ($value) {
            return $value['order'];
        }));

        // foreach ($menus as $menu) {
        //     $menusPermission[] = $menu;
        // }

        $dataGroupMenu->groups = $authSession->admins_groups->groups;
        $dataGroupMenu->admins_groups_menu = $menus;
        $responseData["admins_groups"] = $dataGroupMenu;
        $responseData["groups"] = $authSession->groups;
        // End Menu Permission

        return response()->json($responseData, 200);
    }

}


