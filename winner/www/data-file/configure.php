<?php

$http_origin = "";
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $http_origin = $_SERVER['HTTP_ORIGIN'];
}

$allowed_http_origins = array(
    "http://winner.open-cdn.com",
);
if (in_array($http_origin, $allowed_http_origins)) {
    header('Access-Control-Allow-Origin: ' . $http_origin);
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, X-CSRF-Token, Content-Type, Accept");
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Credentials: true');
}

define("BASE_IMG_DIR", "/usr/share/nginx/html/data-file/");
define("BASE_IMG_PATH", "/data-file/");
define("CALLBACK_CKEDITOR_PATH", "/backend/assets/global/plugins/cke-editor/ckCallbackUploadImage.php");

define("ADMIN_AVATAR_DIR", BASE_IMG_DIR."admins/");
define("CONFIGURATION_LOGO_DIR", BASE_IMG_DIR."configuration/logo/");
define("GROUPS_THUMBNAIL_DIR", BASE_IMG_DIR."groups/thumbnail/");
define("HIGHLIGHTS_PICTURE_DIR", BASE_IMG_DIR."highlights/");
define("CATEGORIES_ICON_DIR", BASE_IMG_DIR."categories/icon/");
define("COURSES_THUMBNAIL_DIR", BASE_IMG_DIR."courses/thumbnail/");
define("CONTENTS_PICTURE_DIR", BASE_IMG_DIR."contents/picture/");
define("DOCUMENTS_FILE_DIR", BASE_IMG_DIR."file/");
define("SLIDES_PICTURE_DIR", BASE_IMG_DIR."slides/picture/");
define("SLIDES_PDF_DIR", BASE_IMG_DIR."slides/pdf/");
define("IMAGES_LOGO_DIR", BASE_IMG_DIR."images/logo/");
define("IMAGES_SIGNATURE_DIR", BASE_IMG_DIR."images/signature/");
define("INSTRUCTORS_PDF_DIR", BASE_IMG_DIR."instructors/pdf/");
define("METHODS_PICTURE_DIR", BASE_IMG_DIR."methods/picture/");
define("DISCUSSION_PICTURE_DIR", BASE_IMG_DIR."discussion/");