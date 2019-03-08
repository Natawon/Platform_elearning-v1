<?php
//--------------------------------------------------
// Written By Bobby  @ Snatch-Icon
//--------------------------------------------------
function DateTime_TH($datetime_go){
    $datetimeT = explode(" ",$datetime_go);
    $dateT = explode("-",$datetimeT[0]);
    $date_y = $dateT[0]+543;
    $date_mm = $dateT[1];

	switch ($date_mm) {
		case "01" :
	$date_m = "มกราคม";
		break;

		case "02" :
	$date_m = "กุมภาพันธ์";
		break;
				case "03" :
	$date_m = "มีนาคม";
		break;
				case "04" :
	$date_m = "เมษายน";
		break;
				case "05" :
	$date_m = "พฤษภาคม";
		break;
				case "06" :
	$date_m = "มิถุนายน";
		break;
				case "07" :
	$date_m = "กรกฎาคม";
		break;
				case "08" :
	$date_m = "สิงหาคม";
		break;
				case "09" :
	$date_m = "กันยายน";
		break;
				case "10" :
	$date_m = "ตุลาคม";
		break;
				case "11" :
	$date_m = "พฤศจิกายน";
		break;
				case "12" :
	$date_m = "ธันวาคม";
		break;
	}

    $date_d = intval($dateT[2]);
    $date_TH = $date_d." ".$date_m." ".$date_y;

    $timeT = explode(":",$datetimeT[1]);
    $time_h = $timeT[0];
    $time_m = $timeT[1];

    $time_short = $time_h.":".$time_m." ";

    return $date_TH." ".$time_short." น.";
}

function DateTime_EN($datetime_go){
    $datetimeT = explode(" ",$datetime_go);
    $dateT = explode("-",$datetimeT[0]);
    $date_y = $dateT[0]+543;
    $date_mm = $dateT[1];

	switch ($date_mm) {
		case "01" :
	$date_m = "January";
		break;

		case "02" :
	$date_m = "February";
		break;
				case "03" :
	$date_m = "March";
		break;
				case "04" :
	$date_m = "April";
		break;
				case "05" :
	$date_m = "May";
		break;
				case "06" :
	$date_m = "June";
		break;
				case "07" :
	$date_m = "July";
		break;
				case "08" :
	$date_m = "August";
		break;
				case "09" :
	$date_m = "September";
		break;
				case "10" :
	$date_m = "October";
		break;
				case "11" :
	$date_m = "November";
		break;
				case "12" :
	$date_m = "December";
		break;
	}

    $date_d = intval($dateT[2]);
    $date_TH = $date_d." ".$date_m." ".$date_y;

    $timeT = explode(":",$datetimeT[1]);
    $time_h = $timeT[0];
    $time_m = $timeT[1];

    $time_short = $time_h.":".$time_m." ";

    return $date_TH." at ".$time_short." GMT";
}

function Date_Shot_EN($date_go){
    $dateT = explode("-",$date_go);
    $date_y = $dateT[0];
    $date_mm = $dateT[1];

    switch ($date_mm) {
        case "01" :
            $date_m = "Jan";
            break;

        case "02" :
            $date_m = "Feb";
            break;
        case "03" :
            $date_m = "Mar";
            break;
        case "04" :
            $date_m = "Apr";
            break;
        case "05" :
            $date_m = "May";
            break;
        case "06" :
            $date_m = "Jun";
            break;
        case "07" :
            $date_m = "Jul";
            break;
        case "08" :
            $date_m = "Aug";
            break;
        case "09" :
            $date_m = "Sep";
            break;
        case "10" :
            $date_m = "Oct";
            break;
        case "11" :
            $date_m = "Nov";
            break;
        case "12" :
            $date_m = "Dec";
            break;
    }

    $date_d = intval($dateT[2]);
    $dateTd = explode(" ",$date_d);
    $dateTd = $dateTd[0];
    $date_EN = "".$dateTd."<br>".$date_m;

    return $date_EN;
}

function clean_url($text)
{
//$text=strtolower($text);
    $code_entities_match = array(' ','--','&quot;','!','@','#','$','%','^','&','*','(',')','_','+','{','}','|',':','"','<','>','?','[',']','\\',';',"'",',','.','/','*','+','~','`','=','fontstylecolor2660a1font-weightbold','font');
    $code_entities_replace = array('','','','','','','','','','','','','','','','','','','','','','','','','','','','');
    $text = str_replace($code_entities_match, $code_entities_replace, $text);
    return $text;
}

function clean_tag_p($text)
{
    $code_entities_match = array('<p>','</p>');
    $code_entities_replace = array('','');
    $text = str_replace($code_entities_match, $code_entities_replace, $text);
    return $text;
}

function cutStr($str, $maxChars='', $holder=''){
    if (strlen($str) > $maxChars ){
        $str = iconv_substr($str, 0, $maxChars,"UTF-8") . $holder;
    }
    return $str;
}

function humanTiming($datetime)
{
    $time = strtotime($datetime);
    $time = time() - $time; // to get the time since that moment
    $time = ($time<1)? 1 : $time;
    $tokens = array (
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );
    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s ago':'');
    }
}

function diffTiming($datetime){
    $now = new DateTime();
    $bangkokTZ = new DateTimeZone("Asia/Bangkok");
    $now->setTimezone($bangkokTZ);
    $datetime_live = $datetime;
    $datetime_stamp_live = new DateTime($datetime_live);
    $diff = strtotime($datetime_stamp_live->format("Y-m-d H:i:s")) - strtotime($now->format("Y-m-d H:i:s"));
    $tokens = array (
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );
    foreach ($tokens as $unit => $text) {
        if ($diff < $unit) continue;
        $numberOfUnits = floor($diff / $unit);
        return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
    }
}

function diff($datetime){
    $now = new DateTime();
    $bangkokTZ = new DateTimeZone("Asia/Bangkok");
    $now->setTimezone($bangkokTZ);
    $datetime_live = $datetime;
    $datetime_stamp_live = new DateTime($datetime_live);
    $diff = strtotime($datetime_stamp_live->format("Y-m-d H:i:s")) - strtotime($now->format("Y-m-d H:i:s"));
    return $diff;
}

function Time_Shot($time){

    $timeT = explode(":",$time);
    $time_h = number_format($timeT[0]);
    $time_m = number_format($timeT[1]);
    $time_s = number_format($timeT[2]);

    if($time_h > '0' and $time_m > '0' and $time_s > '0'){
        $time_short = $time_h.":".$time_m.":".$time_s;
    }else if($time_h > '0' and $time_m > '0' and $time_s == '0'){
        $time_short = $time_h.":".$time_m.":".$time_s."0";
    }else if($time_h > '0' and $time_m == '0' and $time_s >= '0'){
        $time_short = $time_h.":".$time_m."0:".$time_s;
    }else if($time_m > '0' and $time_s > '0'){
        $time_short = $time_m.".".$time_s;
    }else if($time_m > '0' and $time_s > '0'){
        $time_short = $time_m.".".$time_s;
    }

    return $time_short;
}

function getImage($dir,$images) {
    if($images){
        $path = $dir.$images;
    }else{
        $path = '/images/Default.jpg';
    }
    return $path;
}

function getImageDefault($type, $dir, $images) {
    $path = null;

    if (!empty($images)) {
        $path = $dir.$images;
    } else {
        switch (strtolower($type)) {
            case 'courses':
                $path = '/images/Default.jpg';
                break;

            case 'categories':
                $path = '/images/categories-icon-default.png';
                break;

            default:
                # code...
                break;
        }
    }

    return $path;
}

function groupKey($groupKey) {
    if ($groupKey) { $key = '/'.$groupKey; }
    return $key;
}

function cleanGroupKey($val) {
    return trim(filter_var($val, FILTER_SANITIZE_STRING), '/');
}






