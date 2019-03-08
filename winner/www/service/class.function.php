<?php
/**
* Name : Helper Functions
* Created By : Nawee (nawee.ku.dootvmedia@gmail.com)
* Created Date : 25/03/2016
* Updated Date : 25/03/2016
* Updated By : Nawee (nawee.ku.dootvmedia@gmail.com)
*/

class HelperFunctions {
    // var $variables;
    var $thai_day_arr=array("อาทิตย์","จันทร์","อังคาร","พุธ","พฤหัสบดี","ศุกร์","เสาร์");
    var $thai_month_arr=array(
        "0"=>"",
        "1"=>"มกราคม",
        "2"=>"กุมภาพันธ์",
        "3"=>"มีนาคม",
        "4"=>"เมษายน",
        "5"=>"พฤษภาคม",
        "6"=>"มิถุนายน",
        "7"=>"กรกฎาคม",
        "8"=>"สิงหาคม",
        "9"=>"กันยายน",
        "10"=>"ตุลาคม",
        "11"=>"พฤศจิกายน",
        "12"=>"ธันวาคม"
    );
    var $thai_month_arr_short=array(
        "0"=>"",
        "1"=>"ม.ค.",
        "2"=>"ก.พ.",
        "3"=>"มี.ค.",
        "4"=>"เม.ย.",
        "5"=>"พ.ค.",
        "6"=>"มิ.ย.",
        "7"=>"ก.ค.",
        "8"=>"ส.ค.",
        "9"=>"ก.ย.",
        "10"=>"ต.ค.",
        "11"=>"พ.ย.",
        "12"=>"ธ.ค."
    );

    public function __construct(){
        # do something
    }

    public function thai_date_and_time($time){   // 19 ธันวาคม 2556 เวลา 10:10:43
        $thai_date_return =date("j",$time);
        $thai_date_return.=" ".$this->thai_month_arr[date("n",$time)];
        $thai_date_return.= " ".(date("Y",$time)+543);
        $thai_date_return.= " เวลา ".date("H:i:s",$time);
        return $thai_date_return;
    }
    public function thai_date_and_time_short($time){   // 19  ธ.ค. 2556 10:10:4
        $thai_date_return =date("j",$time);
        $thai_date_return.="&nbsp;&nbsp;".$this->thai_month_arr_short[date("n",$time)];
        $thai_date_return.= " ".(date("Y",$time)+543);
        $thai_date_return.= " ".date("H:i:s",$time);
        return $thai_date_return;
    }
    public function thai_date_short($time){   // 19  ธ.ค. 2556
        $thai_date_return =date("j",$time);
        $thai_date_return.="&nbsp;&nbsp;".$this->thai_month_arr_short[date("n",$time)];
        $thai_date_return.= " ".(date("Y",$time)+543);
        return $thai_date_return;
    }
    public function thai_date_fullmonth($time){   // 19 ธันวาคม 2556
        $thai_date_return =date("j",$time);
        $thai_date_return.=" ".$this->thai_month_arr[date("n",$time)];
        $thai_date_return.= " ".(date("Y",$time)+543);
        return $thai_date_return;
    }
    public function thai_date_short_number($time){   // 19-12-56
        $thai_date_return =date("d",$time);
        $thai_date_return.="-".date("m",$time);
        $thai_date_return.= "-".substr((date("Y",$time)+543),-2);
        return $thai_date_return;
    }

    // Clean Quote
    function cleanQoute($text) {
        //$text=strtolower($text);
        $code_entities_match = array('"',"'");
        $code_entities_replace = array('','');
        $text = str_replace($code_entities_match, $code_entities_replace, $text);
        return $text;
    }

    // Check length of string (utf8)
    function utf8_strlen($s) {
        $c = strlen($s); $l = 0;
        for ($i = 0; $i < $c; ++$i)
        if ((ord($s[$i]) & 0xC0) != 0x80) ++$l;
        return $l;
    }

    // Generate Date Format
    public function genDate($timestamp = "", $type = 1) {
        $textDate = "";

        if (isset($_COOKIE['_lang']) && $_COOKIE['_lang'] == "th") {
            switch ((int)$type) {
                case 1:
                    $textDate = date("M d, Y", $timestamp);
                break;
                case 2:
                    $textDate = date("d/m/Y", $timestamp);
                break;
            }
        } else {
            switch ((int)$type) {
                case 1: $textDate = date("M d, Y", $timestamp); break;
                case 2: $textDate = date("d/m/Y", $timestamp); break;
            }
        }

        return $textDate;
    }

    // Convert String to Array
    public function convertToArray($string, $delimiter = ",", $limit = PHP_INT_MAX) {
        $arr = explode($delimiter, $string, $limit);
        $responseArr = array();
        foreach ($arr as $ele) {
            if ($ele != "") {
                $responseArr[] = $ele;
            }
        }
        return $responseArr;
    }

    // Generate Tags Array
    public function genAllTags($datas) {
        $arrTags = array();
        foreach ($datas as $data) {
            $arrTags = array_unique(array_merge($arrTags, $this->convertToArray($data['tag'])));
        }

        return $arrTags;
    }

    // Cut String
    public function cutStr($string, $length = 50, $holder = "...") {
        if ($this->utf8_strlen($string) > $length ){
            return mb_substr($string, 0, $length, 'UTF-8').$holder;
        }

        return $string;
    }

    // Convert timestamp to elapsed time ago
    public function elapsed_time_ago($past_time, $display_unit = 10, $lan = 'th') {
        $arr_text_th = array(
            'year'      => 'ปี',
            'month'     => 'เดือน',
            // 'week'   => 'สัปดาห์',
            'day'       => 'วัน',
            'hour'      => 'ชั่วโมง',
            'minute'    => 'นาที',
            // 'second' => 'วินาที',
            'justnow'   => 'เมื่อสักครู่',
            'ago'       => ' ที่แล้ว',
            'plural'    => ''
        );

        $arr_text_en = array(
            'year'      => 'year',
            'month'     => 'month',
            // 'week'   => 'week',
            'day'       => 'day',
            'hour'      => 'hour',
            'minute'    => 'minute',
            // 'second' => 'second',
            'justnow'   => 'Just now',
            'ago'       => ' ago',
            'plural'    => 's'
        );

        $arr_text = ($lan == 'th') ? $arr_text_th : $arr_text_en;

        $elapsed_time_str = '';
        $elapsed_time = time() - (int)$past_time;

        if ($elapsed_time < 60) {
            return $arr_text['justnow'];
        }

        $arr_intervals = array (
            'year'   => 31560192,
            'month'  => 2592000, //2592000(30), //2627424(30.41) ,//2630016(30.44),
            // 'week'   => 604800,
            'day'    => 86400,
            'hour'   => 3600,
            'minute' => 60,
            // 'second' => 1,
        );

        $count_interval = 0;

        foreach ($arr_intervals as $interval => $timestamp) {
            $diff_time_interval = floor($elapsed_time / $timestamp);

            if ($diff_time_interval >= 1) {
                if ($count_interval < $display_unit) {
                    $elapsed_time = $elapsed_time % $timestamp;
                    $elapsed_time_str .= $diff_time_interval.' '.$arr_text[$interval].(($diff_time_interval > 1) ? $arr_text['plural'].' ' : ' ');
                    $count_interval++;
                } else {
                    break;
                }
            }
        }

        return $elapsed_time_str.$arr_text['ago'];
    }

    // Check Role User
    public function checkRole($role) {
        if (is_array($role)) {
            if (is_array($_SESSION['user']['role'])) {
                $arrRoleMe = array_map('strtolower', $_SESSION['user']['role']);
                $arrRoleAccess = array_intersect($role, $arrRoleMe);
                if (!empty($arrRoleAccess)) {
                    return true;
                } else {
                    return false;
                }
            } else if (is_string($_SESSION['user']['role'])) {
                if (in_array(strtolower($_SESSION['user']['role']), $role)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    // Check value null for "-"
    public function isNull($str) {
        return ($str != "" && $str != null) ? $str : '-';
    }

    // Check job quick for icon
    public function isQuick($is_quick) {
        return ((int)$is_quick == 1) ? '<i class="ace-icon fa fa-check bigger-120"></i>' : '<i class="ace-icon fa fa-times bigger-120"></i>';
    }

    // Check Status for label
    public function genLabelStatus($status) {
        $symbol = "";

        switch (strtolower($status)) {
            case 'in progress':
                $symbol = '<span class="label label-sm label-default">'.$status.'</span>';
                break;

            case 'approved':
                $symbol = '<span class="label label-sm label-success">'.$status.'</span>';
                break;

            case 'rejected':
                $symbol = '<span class="label label-sm label-danger">'.$status.'</span>';
                break;

            case 'waiting':
                $symbol = '<span class="label label-sm label-warning">'.$status.'</span>';
                break;

            case 'verify':
                $symbol = '<span class="label label-sm label-warning">'.$status.'</span>';
                break;

            case 'recheck':
                $symbol = '<span class="label label-sm label-warning">'.$status.'</span>';
                break;
        }

        return $symbol;
    }

    // Check Status for label
    public function genTextStatus($status) {
        $classCss = "";

        switch (strtolower($status)) {
            case 'in progress':
                $classCss = '';
                break;

            case 'approved':
                $classCss = 'text-success';
                break;

            case 'rejected':
                $classCss = 'text-danger';
                break;

            case 'verify':
                $classCss = 'text-warning';
                break;

            case 'recheck':
                $classCss = 'text-warning';
                break;

            case 'waiting':
                $classCss = '';
                break;
        }

        return $classCss;
    }

    // Generate Row color
    public function genBgColor($status) {
        switch (strtolower($status)) {
            case 'danger':
                $classStatusColor = "bg-red";
                break;
            case 'warning':
                $classStatusColor = "bg-yellow";
                break;

            default:
                $classStatusColor = "";
                break;
        }

        return $classStatusColor;
    }

    // Create Pagination
    public function createPagination($current, $total, $limit = 10, $classPagination = 'pagination', $classButton = '', $htmlBtnPrev = '<span aria-hidden="true">&laquo;</span>', $htmlBtnNext = '<span aria-hidden="true">&raquo;</span>', $is_rewrite = true) {
        $queryStr = $this->getQueryString('page', $current);
        $targetPage = $this->getPath($current).($queryStr == "?" ? $queryStr : $queryStr."&" );

        if ($is_rewrite) {
            if (strpos($targetPage, "/page/") !== false) {
                $targetPage = substr($targetPage, 0 ,strpos($targetPage, "/page/"));
            } else {
                $targetPage = substr($targetPage, 0 ,strpos($targetPage, "?"));
            }
        }

        // $page = $this->getParam('page', $current);
        $page = $this->getpara('page');
        $stages = 3;

        // Initial page num setup
        if ($page == 0) {
            $page = 1;
        }
        $prev = $page - 1;
        $next = $page + 1;
        $lastPage = ceil($total / $limit);
        $lastPagem1 = $lastPage - 1;

        $paginate = '';
        if($lastPage > 1) {

            $paginate .= '<ul class="'.$classPagination.'">';

            // Previous
            if ($page > 1) {
                $paginate .= '<li>';
                $paginate .= '<a href="'.$targetPage.'/page/'.$prev.'" class="'.$classButton.'">'.$htmlBtnPrev.'</a>';
                $paginate .= '</li>';
            } else {
                $paginate .= '<li class="disabled">';
                $paginate .= '<span> '.$htmlBtnPrev.' </span>';
                $paginate .= '</li>';
            }

            // Pages
            if ($lastPage < 7 + ($stages * 2)) { // Not enough pages to breaking it up
                for ($counter = 1; $counter <= $lastPage; $counter++) {
                    if ($counter == $page) {
                     $paginate .= '<li class="active">';
                     // $paginate .= '<a href="'.$targetPage.'/page/'.$counter.'" class="'.$classButton.'">'.$counter.'</a>';
                     $paginate .= '<span>'.$counter.' <span class="sr-only">(current)</span></span>';
                     $paginate .= '</li>';
                    } else {
                        $paginate .= '<li>';
                        $paginate .= '<a href="'.$targetPage.'/page/'.$counter.'" class="'.$classButton.'">'.$counter.'</a>';
                        $paginate .= '</li>';
                    }
                }
            } else if ($lastPage > 5 + ($stages * 2)) { // Enough pages to hide a few?
                // Beginning only hide later pages
                if($page < 1 + ($stages * 2)) {
                    for ($counter = 1; $counter < 4 + ($stages * 2); $counter++) {
                        if ($counter == $page) {
                            $paginate .= '<li class="active">';
                            // $paginate .= '<a href="'.$targetPage.'/page/'.$counter.'" class="'.$classButton.'">'.$counter.'</a>';
                            $paginate .= '<span>'.$counter.' <span class="sr-only">(current)</span></span>';
                            $paginate .= '</li>';
                        } else {
                            $paginate .= '<li>';
                            $paginate .= '<a href="'.$targetPage.'/page/'.$counter.'" class="'.$classButton.'">'.$counter.'</a>';
                            $paginate .= '</li>';
                        }
                    }
                    $paginate .= '<li>';
                    $paginate .= '<span>...</span>';
                    $paginate .= '</li>';

                    $paginate .= '<li>';
                    $paginate .= '<a href="'.$targetPage.'/page/'.$lastPagem1.'" class="'.$classButton.'">'.$lastPagem1.'</a>';
                    $paginate .= '</li>';

                    $paginate .= '<li>';
                    $paginate .= '<a href="'.$targetPage.'/page/'.$lastPage.'" class="'.$classButton.'">'.$lastPage.'</a>';
                    $paginate .= '</li>';
                } else if ($lastPage - ($stages * 2) > $page && $page > ($stages * 2)) { // Middle hide some front and some back
                    $paginate .= '<li>';
                    $paginate .= '<a href="'.$targetPage.'/page/1" class="'.$classButton.'">1</a>';
                    $paginate .= '</li>';

                    $paginate .= '<li>';
                    $paginate .= '<a href="'.$targetPage.'/page/2" class="'.$classButton.'">2</a>';
                    $paginate .= '</li>';
                    $paginate .= '<li>';
                    $paginate .= '<span>...</span>';
                    $paginate .= '</li>';
                    for ($counter = $page - $stages; $counter <= $page + $stages; $counter++) {
                        if ($counter == $page) {
                            $paginate .= '<li class="active">';
                            // $paginate .= '<a href="'.$targetPage.'/page/'.$counter.'" class="'.$classButton.'">'.$counter.'</a>';
                            $paginate .= '<span>'.$counter.' <span class="sr-only">(current)</span></span>';
                            $paginate .= '</li>';
                        } else {
                            $paginate .= '<li>';
                            $paginate .= '<a href="'.$targetPage.'/page/'.$counter.'" class="'.$classButton.'">'.$counter.'</a>';
                            $paginate .= '</li>';
                        }
                    }
                    $paginate .= '<li>';
                    $paginate .= '<span>...</span>';
                    $paginate .= '</li>';

                    $paginate .= '<li>';
                    $paginate .= '<a href="'.$targetPage.'/page/'.$lastPagem1.'" class="'.$classButton.'">'.$lastPagem1.'</a>';
                    $paginate .= '</li>';

                    $paginate .= '<li>';
                    $paginate .= '<a href="'.$targetPage.'/page/'.$lastPage.'" class="'.$classButton.'">'.$lastPage.'</a>';
                    $paginate .= '</li>';
                } else { // End only hide early pages
                    $paginate .= '<li>';
                    $paginate .= '<a href="'.$targetPage.'/page/1" class="'.$classButton.'">1</a>';
                    $paginate .= '</li>';

                    $paginate .= '<li>';
                    $paginate .= '<a href="'.$targetPage.'/page/2" class="'.$classButton.'">2</a>';
                    $paginate .= '</li>';
                    $paginate .= '<li>';
                    $paginate .= '<span>...</span>';
                    $paginate .= '</li>';
                    for ($counter = $lastPage - (2 + ($stages * 2)); $counter <= $lastPage; $counter++) {
                        if ($counter == $page) {
                            $paginate .= '<li class="active">';
                            // $paginate .= '<a href="'.$targetPage.'/page/'.$counter.'" class="'.$classButton.'">'.$counter.'</a>';
                            $paginate .= '<span>'.$counter.' <span class="sr-only">(current)</span></span>';
                            $paginate .= '</li>';
                        } else {
                            $paginate .= '<li>';
                            $paginate .= '<a href="'.$targetPage.'/page/'.$counter.'" class="'.$classButton.'">'.$counter.'</a>';
                            $paginate .= '</li>';
                        }
                    }
                }
            }

            // Next
            if ($page < $counter - 1) {
                $paginate .= '<li>';
                $paginate .= '<a href="'.$targetPage.'/page/'.$next.'" class="'.$classButton.'"><span> '.$htmlBtnNext.' </span></a>';
                $paginate .= '</li>';
            } else {
                $paginate .= '<li>';
                $paginate .= '<span> '.$htmlBtnNext.' </span>';
                $paginate .= '</li>';
            }

            $paginate .= '</div>';

        }

        return $paginate;
    }

    // Get query string
    public function getQueryString($unset = null, $url = null) {
        if ($url != null) {
            $parts = parse_url($url);
            parse_str($parts['query'], $arrayGet);
        } else {
            $arrayGet = $_GET;
        }

        $unset = explode(",",$unset);

        if ($unset != null) {
            foreach ($unset as $val) {
                unset($arrayGet[$val]);
            }
        }

        $para = '?'.http_build_query($arrayGet);

        return $para;
    }

    // Get Parameter
    public function getParam($param, $url = null) {
        $result = "";

        if ($url != null) {
            $parts = parse_url($url);
            parse_str($parts['query'], $aParam);
        } else {
            $aParam = $_GET;
        }

        if (!empty($aParam)) {
            foreach ($aParam as $key => $value) {
                if ($key == $param) {
                    $result = urlencode($value);
                    break;
                }
            }
        }

        return $result;
    }

    // Get Parameter 2
    public function getpara($param = NULL, $unset = NULL) {
        $array_g = $_GET;
        $unset = explode(",",$unset);

        if($unset!=NULL) {
            foreach($unset as $val) { unset($array_g[$val]); }
        }
        $i=1;
        $para = '?';
        foreach($array_g as $key=>$val) {
            if ($param != NULL) {
                if ($key == $param) {
                    $para = urlencode($val);
                    break;
                }
            } else {
                $para .= "$key=$val";
                if($i!=count($array_g)) {$para .= "&";}
                $i++;
            }
        }
        return $para;
    }

    // Get Path Without Query String
    public function getPath($url = null) {
        if ($url != null) {
            $parts = parse_url($url);
            parse_str($parts['query'], $aParam);
            return $parts['scheme']."://".$parts['host'].$parts['path'];
        } else {
            if (isset($_SERVER['REQUEST_SCHEME'])) {
                $requestScheme = $_SERVER['REQUEST_SCHEME'];
            } else {
                $requestScheme = "http";
            }

            return $requestScheme."://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
        }
    }

    // Get Path Without Query String
    public function getCurrentURL() {
        if (isset($_SERVER['REQUEST_SCHEME'])) {
            $requestScheme = $_SERVER['REQUEST_SCHEME'];
        } else {
            $requestScheme = "http";
        }

        return $requestScheme."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }

    // Calculate VAT
    public function calVAT($price, $percent = 7) {
        return ((int)$price * $percent) / 100;
    }

    // Random String
    public function randString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    // Generate Number Format
    public function numFormat($number) {
        return number_format($number, 2, '.', ',');
    }

    // Get unit text
    public function getUnitText($val) {
        switch ((int)$val) {
            case 1:
                $unitText = "PCS";
                break;
            case 2:
                $unitText = "MUL";
                break;
            case 3:
                $unitText = "SET";
                break;

            default:
                $unitText = "";
                break;
        }

        return $unitText;
    }

    public function getSlug($str, $options = array()) {
        // Make sure string is in UTF-8 and strip invalid UTF-8 characters
        $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());
        $defaults = array(
            'delimiter'     => '-',
            'limit'         => null,
            'lowercase'     => true,
            'replacements'  => array(),
            'transliterate' => false,
        );
        // Merge options
        $options = array_merge($defaults, $options);
        $char_map = array(
            // Latin
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
            'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
            'ß' => 'ss',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
            'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
            'ÿ' => 'y',
            // Latin symbols
            '©' => '(c)',
            // Greek
            'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
            'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
            'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
            'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
            'Ϋ' => 'Y',
            'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
            'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
            'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
            'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
            'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
            // Turkish
            'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
            'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g',
            // Russian
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
            'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
            'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
            'Я' => 'Ya',
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
            'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
            'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
            'я' => 'ya',
            // Ukrainian
            'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
            'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
            // Czech
            'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U',
            'Ž' => 'Z',
            'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
            'ž' => 'z',
            // Polish
            'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z',
            'Ż' => 'Z',
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
            'ż' => 'z',
            // Latvian
            'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N',
            'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
            'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
            'š' => 's', 'ū' => 'u', 'ž' => 'z'
        );
        // Make custom replacements
        $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);
        // Transliterate characters to ASCII
        if ($options['transliterate']) {
            $str = str_replace(array_keys($char_map), $char_map, $str);
        }
        // Replace non-alphanumeric characters with our delimiter
        $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);
        // Remove duplicate delimiters
        $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);
        // Truncate slug to max. characters
        $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');
        // Remove delimiter from ends
        $str = trim($str, $options['delimiter']);
        return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
    }

    public function get_slug($txt){
        // $badword = array("\"", "/");
        $badword = array('-','&quot;','!','@','#','$','%','^','&','*','(',')','_','+','{','}','|',':','"','<','>','?','[',']','\\',';',"'",',','.','/','*','+','~','`','=');
        $slug = $txt;
        $slug = str_replace($badword, "-",$slug );
        $slug = str_replace(" ","-",$slug );

        return $slug;
    }

    public function set_cookie($name,$value,$exp,$path = "/") {
        $ck_expire = time()+($exp*60*60*24); // calculate to timestamp
        setcookie($name,$value,$ck_expire,$path);
    }

    public function getCookieString() {
        $cookie = array();

        foreach ($_COOKIE as $key => $value) {
            $cookie[] = "{$key}={$value}";
        }

        return implode('; ', $cookie);
    }

    public function escapeString($string) {
        return mysqli_real_escape_string($string);
    }
    public function setString($string) {
        return  htmlentities($string,ENT_QUOTES,'UTF-8');
    }
    public function getString($string) {
        return  html_entity_decode($string,ENT_QUOTES,'UTF-8');
    }
    public function setSpecialString($string) {
        return  htmlspecialchars($string,ENT_QUOTES,'UTF-8');
    }
    public function getSpecialString($string) {
        return  htmlspecialchars_decode($string,ENT_QUOTES,'UTF-8');
    }
    public function setInt($int) {
        return (int)addslashes($int);
    }
    public function setFloat($float) {
        return (float)addslashes($float);
    }
    public function validateMatch($val1, $val2) {
        return ($val1 == $val2);
    }
    public function validateUsername($val) {
        $pattern = '/^(?=^.{2,30}$)([\da-zA-Z_])*$/';
        return (preg_match($pattern, $val) == true);
    }
    public function validatePassword($val) {
        $pattern = '/^(?=^.{8,30}$)([\da-zA-Z!@#$%\-_])*$/'; // !@#$%\^&*()+\-_,\.
        return (preg_match($pattern, $val) == true);
    }
    public function validateInt($val) {
        return (filter_var(trim($val), FILTER_VALIDATE_INT) !== false);
    }
    public function validateFloat($val) {
        return (filter_var(trim($val), FILTER_VALIDATE_FLOAT) !== false);
    }
    public function validateEmail($val) {
        return (filter_var(trim($val), FILTER_VALIDATE_EMAIL) !== false);
    }
    public function sanitizeInt($val) {
        return (int)filter_var(trim($val), FILTER_SANITIZE_NUMBER_INT);
    }
    public function sanitizeFloat($val) {
        return (float)filter_var(trim($val), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }
    public function sanitizeEmail($val) {
        return (string)filter_var(trim($val), FILTER_SANITIZE_EMAIL);
    }
    public function sanitizeString($val) {
        return (string)filter_var(trim($val), FILTER_SANITIZE_STRING);
    }

    public function hasCertLang($member, $lang = "foreign") {
        if ($lang == "foreign" && $member['first_name_en'] != "" && $member['last_name_en'] != "") {
            return true;
        } else if ($lang == "thai" && $member['first_name'] != "" && $member['last_name'] != "") {
            return true;
        } else {
            return false;
        }
    }

    public function valueExists($value, $choiceValue, $type) {
        $attr = "";

        if ($value == $choiceValue) {
            switch (strtolower($type)) {
                case 'radio':
                case 'checkbox':
                    $attr = 'checked';
                    break;
                case 'select':
                    $attr = 'selected';
                    break;
                default:
                    $attr = 'checked';
                    break;
            }
        }

        return $attr;

    }

    public function isOtherValue($value, $choiceValue = [], $type) {
        $results = [
            "attr" => ""
        ];

        if (is_array($choiceValue)) {
            if (is_array($value)) {
                $arrRoleMe = array_map('strtolower', $value);
                $arrRoleAccess = array_intersect($choiceValue, $arrRoleMe);
                if (empty($arrRoleAccess)) {
                    $results["value"] = true;
                    $results["attr"] = $this->valueExists($value, $value, $type);
                } else {
                    $results["value"] = false;
                }
            } else if (is_string($value) || is_null($value)) {
                if (!in_array(strtolower($value), $choiceValue)) {
                    $results["value"] = true;
                    $results["attr"] = $this->valueExists($value, $value, $type);
                } else {
                    $results["value"] = false;
                }
            } else {
                $results["value"] = false;
            }
        } else {
            $results["value"] = false;
        }

        return $results;

    }

    public function constArr($variableDefined)
    {
        if (!defined($variableDefined)) {
            return [];
        }

        $resultArr = unserialize(constant($variableDefined));

        if ($resultArr === false || !is_array($resultArr)) {
            return [];
        }

        return $resultArr;
    }

    public function isYouTube($url)
    {
        $rx = '~
          ^(?:https?://)?                           # Optional protocol
           (?:www[.])?                              # Optional sub-domain
           (?:youtube[.]com/watch[?]v=|youtu[.]be/) # Mandatory domain name (w/ query string in .com)
           ([^&]{11})                               # Video id of 11 characters as capture group 1
            ~x';

        $has_match = preg_match($rx, $url, $matches);

        return ['has_match' => $has_match, 'matches' => $matches];
    }

}



