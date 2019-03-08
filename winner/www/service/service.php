<?php
require_once("setting.php");
require_once("function.php");
require_once("class.function.php");
require_once("class.http-client-call.php");
require_once("class.underscore.php");

$oFunc = new HelperFunctions();
$oClient = new HttpClientCall();

function milliseconds() {
    $mt = explode(' ', microtime());
    return ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
}

function loadTest() {
    global $oFunc, $oClient;
    // $responseHttp = $oClient->curl(constant("_BASE_SITE_URL")."/load-test.php", "GET", $oFunc->getCookieString());
    $responseHttp = $oClient->curl(constant("_BASE_SITE_URL")."/load-test.php", "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function loadTestWithLaravel() {
    global $oFunc, $oClient;
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/load-test", "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function getcCsrfToken(){
    global $oFunc, $oClient;

    // $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/csrf/token", "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];

    // if (isset($responseData['csrf_token'])) {
    //     $oFunc->set_cookie('XSRF-TOKEN', $responseData['encrypted_csrf_token'], 1);
    // }

    return $responseData;
}

function configuration(){
    // $con=mysqli_connect("27.131.144.60","root","dootvazws3e","e_learning");
    // // Check connection
    // if (mysqli_connect_errno())
    //   {
    //   echo "Failed to connect to MySQL: " . mysqli_connect_error();
    //   }
    //   mysqli_set_charset($con,"utf8");

    // // Perform queries 
    // $rs = mysqli_query($con,"SELECT * FROM configuration WHERE id = 1");
    // $configuration = mysqli_fetch_assoc($rs);

    // mysqli_close($con);
    global $oFunc, $oClient;

    // $oClient = new HttpClientCall();
    // echo "configuration start:".date("H:i:s")."<br>";
    // $st = milliseconds();
    // echo "configuration start: ".$st."<br>";
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/configuration", "GET", $oFunc->getCookieString());
    // $responseHttp = $oClient->curl("https://www.mthai.com/", "GET");
    // echo "configuration ended:".date("H:i:s")."<br>";
    // sleep(2);
    // $en = milliseconds();
    // echo "configuration ended: ".$en."<br>".($en - $st)." ms. <br><br>";
    // echo "<pre>";
    // print_r($responseHttp);
    // echo "</pre>";
    $responseData = $responseHttp['body'];
    // $responseData = $responseHttp;
    return $responseData;
}

function highlights($groupsKey){
    global $oFunc, $oClient;

    // $oClient = new HttpClientCall();
    // echo "highlights start:".date("H:i:s")."<br>";
    // $st = milliseconds();
    // echo "highlights start: ".$st."<br>";
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/groups/".$groupsKey."/highlights?&order_by=order&order_direction=asc", "GET", $oFunc->getCookieString());
    // echo "highlights ended:".date("H:i:s")."<br>";
    // sleep(2);
    // $en = milliseconds();
    // echo "highlights ended: ".$en."<br>".($en - $st)." ms. <br><br>";
    $responseData = $responseHttp['body'];
    return $responseData;
}

function avatars_list(){
    global $oFunc;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/avatars_list?&order_by=order&order_direction=asc", "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function avatars($id){
    global $oFunc, $oClient;

    // $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/avatars/".$id, "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function categories($groupsKey){
    global $oFunc, $oClient;

    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/groups/".$groupsKey."/categories/?&order_by=order&order_direction=asc", "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function qa(){
    global $oFunc;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/qa?&order_by=order&order_direction=asc", "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function courses_list($groupsKey, $options = []){
    global $oFunc, $oClient;

    $queryString = http_build_query($options);

    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/groups/".$groupsKey."/courses_list?".$queryString, "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function search_courses($groupsKey, $keyword){
    global $oFunc, $oClient;

    $keyword = rawurlencode($keyword);

    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/groups/".$groupsKey."/courses/search/".$keyword."?order_by=order&order_direction=asc", "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function filter_course($groupsKey, $options = []){
    global $oFunc, $oClient;

    $queryString = http_build_query($options);

    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/groups/".$groupsKey."/courses/filter?".$queryString, "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function categories2courses($id, $groupsKey, $options = []){
    global $oFunc;

    $queryString = http_build_query($options);

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/groups/".$groupsKey."/categories/".$id."/courses?".$queryString, "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function courses($id, $groupsKey){
    global $oFunc;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/groups/".$groupsKey."/courses/".$id, "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function courses_recommended($groupsKey){
    global $oFunc;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/groups/".$groupsKey."/courses_recommended?&order_by=order&order_direction=asc", "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function session(){
    global $oFunc, $oClient;
    // echo date("Y-m-d H:i:s u")."<br>";
    // $oClient = new HttpClientCall();
    // echo "session start:".date("H:i:s")."<br>";
    // $st = milliseconds();
    // echo "session start: ".$st."<br>";
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/set/user/session", "GET", $oFunc->getCookieString());
    // echo "session ended:".date("H:i:s")."<br>";
    // sleep(2);
    // $en = milliseconds();
    // echo "session ended: ".$en."<br>".($en - $st)." ms. <br><br>";
    // echo date("Y-m-d H:i:s u");
    // echo "<pre>";
    // print_r($responseHttp);
    // echo "</pre>";
    $responseData = $responseHttp['body'];
    // $json = json_decode($responseData, true);
    return $responseData['data'];
}

function session_require($action = 'HOME', $id = null){
    global $oFunc, $groupKey;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/set/user/session", "GET", $oFunc->getCookieString());

    // echo "<pre>";
    // print_r($responseHttp);
    // echo "</pre>";
    // exit();
    $responseData = $responseHttp['body'];
    // $json = json_decode($responseData, true);
    if (!isset($responseData['data'])) {
        if (isset($oFunc->constArr("_BASE_SET")[$groupKey])) {
            $redirectTo = $oFunc->constArr("_BASE_SET")[$groupKey]['login'];
            $isRedirect = $oFunc->constArr("_BASE_SET")[$groupKey]['isRedirect'];
            $redirectPage = $oFunc->constArr("_BASE_SET")[$groupKey]['redirectPage'];
        } else {
            header("Location : ".constant("_PAGE_404"));
            exit();
        }

        if ($isRedirect && $redirectPage != null) {
            switch (strtoupper($action)) {
                case 'HOME':
                    $redirectTo .= '?redirectPage='.urlencode($redirectPage."?action=home");
                    break;
                case 'INFO':
                    $redirectTo .= '?redirectPage='.urlencode($redirectPage."?courseid=".$id."&action=info");
                    break;
                case 'LEARNING':
                    $redirectTo .= '?redirectPage='.urlencode($redirectPage."?courseid=".$id."&action=learning");
                    break;

                default:
                    $redirectTo .= '?redirectPage='.urlencode($redirectPage."?action=home");
                    break;
            }
        }

        header("location: ".$redirectTo);
        exit();
    } else {
        return $responseData['data'];
    }
}

function instructor_session_require(){
    global $oFunc, $groupKey;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/instructors/session", "GET", $oFunc->getCookieString());

    $responseData = $responseHttp['body'];
    return $responseData['data'];
}

function temp_session_require($action = 'HOME', $id = null){
    global $oFunc, $groupKey;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/set/user/temp/session", "GET", $oFunc->getCookieString());

    $responseData = $responseHttp['body'];
    if (empty($responseData['data'])) {
        if (isset($oFunc->constArr("_BASE_SET")[$groupKey])) {
            $redirectTo = $oFunc->constArr("_BASE_SET")[$groupKey]['login'];
            $isRedirect = $oFunc->constArr("_BASE_SET")[$groupKey]['isRedirect'];
            $redirectPage = $oFunc->constArr("_BASE_SET")[$groupKey]['redirectPage'];
        } else {
            header("Location : ".constant("_PAGE_404"));
            exit();
        }

        if ($isRedirect && $redirectPage != null) {
            switch (strtoupper($action)) {
                case 'HOME':
                    $redirectTo .= '?redirectPage='.urlencode($redirectPage."?action=home");
                    break;
                case 'INFO':
                    $redirectTo .= '?redirectPage='.urlencode($redirectPage."?courseid=".$id."&action=info");
                    break;
                case 'LEARNING':
                    $redirectTo .= '?redirectPage='.urlencode($redirectPage."?courseid=".$id."&action=learning");
                    break;

                default:
                    $redirectTo .= '?redirectPage='.urlencode($redirectPage."?action=home");
                    break;
            }
        }

        header("location: ".$redirectTo);
        exit();
    } else {
        return $responseData['data'];
    }
}

function groups($key){
     global $oFunc;

     $oClient = new HttpClientCall();
     $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/groups/".$key, "GET", $oFunc->getCookieString());
     $responseData = $responseHttp['body'];
     return $responseData;
}

function groups2id($id){
    global $oFunc;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/groups/".$id."/id", "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function groups404(){
    global $oFunc;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/groups404", "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function enroll($id){
    global $oFunc;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/enroll/".$id, "GET", $oFunc->getCookieString());

    if ($responseHttp['headers']['StatusCode'] == 404 && $responseHttp['headers']['Content-Type'] == "application/json") {
        header("Location: ".constant("_BASE_SITE_URL"));
        exit();
    }

    $responseData = $responseHttp['body'];
    return $responseData;
}

function exam2score($id){
    global $oFunc;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/exam2score/".$id, "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function enroll2topic($id){
    global $oFunc;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/enroll2topic/".$id, "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function enroll2topic_skip($id, $skip){
    global $oFunc;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/enroll2topic/".$id."/skip/".$skip, "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function enroll2topic_live_skip($id, $skip){
    global $oFunc;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/enroll2topic_live/".$id."/skip/".$skip, "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function enroll2summary($id){
    global $oFunc;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/enroll2summary/".$id, "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function mycourses(){
    global $oFunc;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/my2enroll", "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function mycourses_test(){
    global $oFunc;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl("https://elearning.set.or.th/api/site/my2enroll_test", "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function myorders(){
    global $oFunc;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/my2orders", "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function quiz($enroll_id, $quiz_id){
    global $oFunc;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/enroll/".$enroll_id."/quiz/".$quiz_id, "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function licenseTypes($id = null){
     global $oFunc;

     $oClient = new HttpClientCall();

     if (is_null($id)) {
        $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/license_types/", "GET", $oFunc->getCookieString());
     } else {
        $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/license_types/".$id, "GET", $oFunc->getCookieString());
     }

     $responseData = $responseHttp['body'];
     return $responseData;
}
function enrollByCourse($cid){
    global $oFunc;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/enroll/courses/".$cid, "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}


function discussion($key,$id){
    global $oFunc;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/discussion/groups/".$key."/courses/".$id, "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function live_courses_list($groupsKey, $options = []){
    global $oFunc, $oClient;

    $queryString = http_build_query($options);

    $responseHttp = $oClient->curl(constant("_BASE_SERVICE_URL")."/site/groups/".$groupsKey."/live_courses_list?".$queryString, "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}