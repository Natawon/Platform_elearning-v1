<?php
session_start();

/* DEFINE CONSTANTS FOR SITE */
    define("_BASE_SITE_URL"    , "http://winner.open-cdn.com");
    define("_BASE_SERVICE_URL" , "http://127.0.0.1/api");
    define("_BASE_DIR_URL"     , "http://winner.open-cdn.com/data-file");
    define("_PAGE_404"         , constant("_BASE_SITE_URL")."/404");

    define("_BASE_DIR_AVATARS"           , constant("_BASE_DIR_URL")."/avatars/");
    define("_BASE_DIR_LOGO"              , constant("_BASE_DIR_URL")."/configuration/logo/");
    define("_BASE_DIR_HIGHLIGHTS"        , constant("_BASE_DIR_URL")."/highlights/");
    define("_BASE_DIR_COURSES_THUMBNAIL" , constant("_BASE_DIR_URL")."/courses/thumbnail/");
    define("_BASE_DIR_GROUPS_THUMBNAIL"  , constant("_BASE_DIR_URL")."/groups/thumbnail/");
    define("_BASE_DIR_CATEGORIES_ICON"   , constant("_BASE_DIR_URL")."/categories/icon/");
    define("_BASE_DIR_SLIDES"            , constant("_BASE_DIR_URL")."/slides/picture/");
    define("_BASE_DIR_FILE"              , constant("_BASE_DIR_URL")."/file/");
    define("_BASE_DIR_INSTRUCTORS_PDF"   , constant("_BASE_DIR_URL")."/instructors/pdf/");


/* DEFINE CONSTANTS FOR SET SITE */
    // define("_BASE_SET_SITE_URL", "https://test.set.or.th/set");
    define("_BASE_SET", serialize([
        "G-Education" => [
            "isRedirect"   => false,
            "redirectPage" => null,
            "login"        => constant("_BASE_SITE_URL")."/G-Education/login",
            "forgot"       => constant("_BASE_SITE_URL")."/G-Education/forgot-password",
        ],
    ]));

?>

<?php
$activeMenu = "";
$keyword = "";
$scriptSite = "";
// ============================================= //
// =============== Check WebView =============== //
// ============================================= //
if (isset($_GET['site']) && strtolower($_GET['site']) == "webview") {
    // var_dump($_COOKIE['site']);
    // $oFunc->set_cookie('site', 'webview', 1);
    $ck_expire = time()+(1*60*60*24); // calculate to timestamp
    setcookie('site','webview',$ck_expire,'/');
    $scriptSite .= '<script type="text/javascript">';
    $scriptSite .= 'document.getElementsByTagName("body")[0].className += "_webview";';
    $scriptSite .= '</script>';
} else if (isset($_COOKIE['site']) && $_COOKIE['site'] == "webview") {
    $scriptSite .= '<script type="text/javascript">';
    $scriptSite .= 'document.getElementsByTagName("body")[0].className += "_webview";';
    $scriptSite .= '</script>';
} else {
    $scriptSite .= '<script type="text/javascript">';
    $scriptSite .= 'document.getElementsByTagName("body")[0].className += "_normal";';
    $scriptSite .= '</script>';
}

