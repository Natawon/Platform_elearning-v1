<?php
require_once("setting.php");
require_once("function.php");
require_once("class.function.php");
require_once("class.http-client-call.php");

$oFunc = new HelperFunctions();

function configuration(){
    global $BASE_SERVICE_URL;
    $service = file_get_contents($BASE_SERVICE_URL."/site/configuration");
    $json = json_decode($service, true);
    return $json;
}

function highlights(){
    global $BASE_SERVICE_URL;
    $service = file_get_contents($BASE_SERVICE_URL."/site/highlights?&order_by=order&order_direction=asc");
    $json = json_decode($service, true);
    return $json;
}

function avatars_list(){
    global $BASE_SERVICE_URL;
    $service = file_get_contents($BASE_SERVICE_URL."/site/avatars_list?&order_by=order&order_direction=asc");
    $json = json_decode($service, true);
    return $json;
}

function avatars($id){
    global $BASE_SERVICE_URL;
    $service = file_get_contents($BASE_SERVICE_URL."/site/avatars/".$id);
    $json = json_decode($service, true);
    return $json;
}

function categories(){
    global $BASE_SERVICE_URL;
    $service = file_get_contents($BASE_SERVICE_URL."/site/categories?&order_by=order&order_direction=asc");
    $json = json_decode($service, true);
    return $json;
}

function qa(){
    global $BASE_SERVICE_URL;
    $service = file_get_contents($BASE_SERVICE_URL."/site/qa?&order_by=order&order_direction=asc");
    $json = json_decode($service, true);
    return $json;
}

function courses_list($groupsID){
    global $BASE_SERVICE_URL;
    $service = file_get_contents($BASE_SERVICE_URL."/site/groups/".$groupsID."/courses_list?&order_by=order&order_direction=asc");
    $json = json_decode($service, true);
    return $json;
}

function categories2courses($id, $groupsID){
    global $BASE_SERVICE_URL;
    $service = file_get_contents($BASE_SERVICE_URL."/site/groups/".$groupsID."/categories/".$id."/courses?&order_by=order&order_direction=asc");
    $json = json_decode($service, true);
    return $json;
}

function courses($id){
    global $BASE_SERVICE_URL, $oFunc;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl($BASE_SERVICE_URL."/site/courses/".$id, "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    return $responseData;
}

function courses_recommended($groupsID){
    global $BASE_SERVICE_URL;
    $service = file_get_contents($BASE_SERVICE_URL."/site/groups/".$groupsID."/courses_recommended?&order_by=order&order_direction=asc");
    $json = json_decode($service, true);
    return $json;
}

function session(){
    global $BASE_SERVICE_URL, $oFunc;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl($BASE_SERVICE_URL."/set/user/session", "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    // $json = json_decode($responseData, true);
    return $responseData['data'];
}

function session_require($action = 'HOME', $id = null){
    global $BASE_SERVICE_URL, $BASE_SET_SITE_URL, $base_set_login, $oFunc;

    $oClient = new HttpClientCall();
    $responseHttp = $oClient->curl($BASE_SERVICE_URL."/set/user/session", "GET", $oFunc->getCookieString());
    $responseData = $responseHttp['body'];
    // $json = json_decode($responseData, true);
    if (!isset($responseData['data'])) {
        switch (strtoupper($action)) {
            case 'HOME':
                header("location: ".$BASE_SET_SITE_URL.$base_set_login.'?redirectPage='.urlencode($BASE_SET_SITE_URL."/elearning/detail.do?action=home"));
                break;
            case 'INFO':
                header("location: ".$BASE_SET_SITE_URL.$base_set_login.'?redirectPage='.urlencode($BASE_SET_SITE_URL."/elearning/detail.do?courseid=".$id."&action=info"));
                break;
            case 'LEARNING':
                header("location: ".$BASE_SET_SITE_URL.$base_set_login.'?redirectPage='.urlencode($BASE_SET_SITE_URL."/elearning/detail.do?courseid=".$id."&action=learning"));
                break;

            default:
                header("location: ".$BASE_SET_SITE_URL.$base_set_login.'?redirectPage='.urlencode($BASE_SET_SITE_URL."/elearning/detail.do?action=home"));
                break;
        }

        exit();
    } else {
        return $responseData['data'];
    }
}

function members($id){
    global $BASE_SERVICE_URL;
    $service = file_get_contents($BASE_SERVICE_URL."/site/members/".$id);
    $json = json_decode($service, true);
    return $json;
}

function enroll($id){
    global $BASE_SERVICE_URL;
    $service = file_get_contents($BASE_SERVICE_URL."/site/enroll/".$id);
    $json = json_decode($service, true);
    return $json;
}

function exam2score($id){
    global $BASE_SERVICE_URL;
    $service = file_get_contents($BASE_SERVICE_URL."/site/exam2score/".$id);
    $json = json_decode($service, true);
    return $json;
}

function enroll2topic($id){
    global $BASE_SERVICE_URL;
    $service = file_get_contents($BASE_SERVICE_URL."/site/enroll2topic/".$id);
    $json = json_decode($service, true);
    return $json;
}

function enroll2topic_skip($id, $skip){
    global $BASE_SERVICE_URL;
    $service = file_get_contents($BASE_SERVICE_URL."/site/enroll2topic/".$id."/skip/".$skip);
    $json = json_decode($service, true);
    return $json;
}

function enroll2summary($id){
    global $BASE_SERVICE_URL;
    $service = file_get_contents($BASE_SERVICE_URL."/site/enroll2summary/".$id);
    $json = json_decode($service, true);
    return $json;
}

function mycourses($id){
    global $BASE_SERVICE_URL;
    $service = file_get_contents($BASE_SERVICE_URL."/site/my2enroll/".$id);
    $json = json_decode($service, true);
    return $json;
}


?>