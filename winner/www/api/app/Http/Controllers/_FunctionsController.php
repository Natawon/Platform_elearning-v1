<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\AdminsGroups;
use App\Models\Admins;

use Auth;
use DateTime;
use DateTimeZone;
use DateInterval;
use File;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SFTP;
use phpseclib\Net\SSH2;

class _FunctionsController extends Controller
{
    // public $xxx;
    // protected $xxx;
    // private $xxx;

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

    public function __construct()
    {
        // do something.
    }

    public function welcome()
    {
        return view('welcome');
    }

    public function checkSession()
    {
        $dataSession = session()->get('_user');

        if (!isset($dataSession)) {
            return false;
        }

        return true;
    }

    public function checkInstructorSession()
    {
        $dataSession = session()->get('_user_instructors');

        if (!isset($dataSession)) {
            return false;
        }

        return true;
    }

    public function thai_date_and_time($time){   // 19 ธันวาคม 2556 เวลา 10:10:43
        $thai_date_return = "";
        $thai_date_return.=date("j",$time);
        $thai_date_return.=" ".$this->thai_month_arr[date("n",$time)];
        $thai_date_return.= " ".(date("Y",$time)+543);
        $thai_date_return.= " เวลา ".date("H:i:s",$time);
        return $thai_date_return;
    }
    public function thai_date_and_time_short($time){   // 19  ธ.ค. 2556 10:10:4
        $thai_date_return = "";
        $thai_date_return.=date("j",$time);
        $thai_date_return.="&nbsp;&nbsp;".$this->thai_month_arr_short[date("n",$time)];
        $thai_date_return.= " ".(date("Y",$time)+543);
        $thai_date_return.= " ".date("H:i:s",$time);
        return $thai_date_return;
    }
    public function thai_date_and_time_human($time){   // 19 ธันวาคม 2556 10:10 น.
        $thai_date_return = "";
        $thai_date_return.=date("j",$time);
        $thai_date_return.=" ".$this->thai_month_arr[date("n",$time)];
        $thai_date_return.= " ".(date("Y",$time)+543);
        $thai_date_return.= " ".date("H:i",$time)." น.";
        return $thai_date_return;
    }
    public function thai_date_and_time_human_full($time){   // 19 ธันวาคม 2556 เวลา 10:10 น.
        $thai_date_return = "";
        $thai_date_return.=date("j",$time);
        $thai_date_return.=" ".$this->thai_month_arr[date("n",$time)];
        $thai_date_return.= " ".(date("Y",$time)+543);
        $thai_date_return.= " เวลา ".date("H:i",$time)." น.";
        return $thai_date_return;
    }
    public function thai_date_short($time){   // 19  ธ.ค. 2556
        $thai_date_return = "";
        $thai_date_return.=date("j",$time);
        $thai_date_return.="&nbsp;&nbsp;".$this->thai_month_arr_short[date("n",$time)];
        $thai_date_return.= " ".(date("Y",$time)+543);
        return $thai_date_return;
    }
    public function thai_date_fullmonth($time){   // 19 ธันวาคม 2556
        $thai_date_return = "";
        $thai_date_return.=date("j",$time);
        $thai_date_return.=" ".$this->thai_month_arr[date("n",$time)];
        $thai_date_return.= " ".(date("Y",$time)+543);
        return $thai_date_return;
    }
    public function thai_date_short_number($time){   // 19-12-56
        $thai_date_return = "";
        $thai_date_return.=date("d",$time);
        $thai_date_return.="-".date("m",$time);
        $thai_date_return.= "-".substr((date("Y",$time)+543),-2);
        return $thai_date_return;
    }

    private function removeNamespaceFromXML( $xml )
    {
        // Because I know all of the the namespaces that will possibly appear in
        // in the XML string I can just hard code them and check for
        // them to remove them
        $toRemove = ['rap', 'turss', 'crim', 'cred', 'j', 'rap-code', 'evic'];
        // This is part of a regex I will use to remove the namespace declaration from string
        $nameSpaceDefRegEx = '(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?';

        // Cycle through each namespace and remove it from the XML string
        foreach( $toRemove as $remove ) {
            // First remove the namespace from the opening of the tag
            $xml = str_replace('<' . $remove . ':', '<', $xml);
            // Now remove the namespace from the closing of the tag
            $xml = str_replace('</' . $remove . ':', '</', $xml);
            // This XML uses the name space with CommentText, so remove that too
            $xml = str_replace($remove . ':commentText', 'commentText', $xml);
            // Complete the pattern for RegEx to remove this namespace declaration
            $pattern = "/xmlns:{$remove}{$nameSpaceDefRegEx}/";
            // Remove the actual namespace declaration using the Pattern
            $xml = preg_replace($pattern, '', $xml, 1);
        }

        // Return sanitized and cleaned up XML with no namespaces
        return $xml;
    }

    public function namespacedXMLToArray($xml)
    {
        // One function to both clean the XML string and return an array
        return json_decode(json_encode(simplexml_load_string($this->removeNamespaceFromXML($xml))), true);
    }

    public function clearPivot($array = array())
    {
        return array_map(function($n) {
            unset($n['pivot']);
            return $n;
        }, $array);
    }

    public function validateDate($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') == $date;
    }

    public function generateRandomString($length = 13) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function checkIDCard($id_card) {
        if (strlen($id_card) != 13) {
            return false;
        }

        $sum=0;
        for ($i=0; $i<12; $i++) {
            $sum += (int)($id_card{$i})*(13-$i);
        }

        if ((11-($sum % 11)) % 10 == (int)($id_card{12})) {
            return true;
        } else {
            return false;
        }

    }

    public function generateSecurePassword($length = 8) {
        $alphabets = str_random((int)$length-2);
        $numeric = random_int(10, 99);
        $symbol = substr(str_shuffle("!@#$^"), 0, 1);

        return str_shuffle($alphabets.$numeric.$symbol);
    }

    public function hasCertLang($member, $lang) {
        if ($lang == "en" && $member['first_name_en'] != "" && $member['last_name_en'] != "") {
            return true;
        } else if ($lang == "th" && $member['first_name'] != "" && $member['last_name'] != "") {
            return true;
        } else {
            return false;
        }
    }

    public function utf8_strlen($s) {
        $c = strlen($s); $l = 0;
        for ($i = 0; $i < $c; ++$i)
        if ((ord($s[$i]) & 0xC0) != 0x80) ++$l;
        return $l;
    }

    public function convertFileUTF8($file) {
        $result = [];
        $encodingList = ["ASCII", "UTF-8", "UTF-7", "ISO-8859-1", "EUC-JP", "SJIS", "eucJP-win", "SJIS-win", "JIS", "ISO-2022-JP"];

        $str = file_get_contents($file);
        $currentEncoding = mb_detect_encoding($str, $encodingList);

        if ($currentEncoding === false) {
            $result['file'] = false;
            $result['encoding'] = null;
            return $result;
        }

        $currentEncoding = strtoupper($currentEncoding);

        if ($currentEncoding != "UTF-8") {
            if ($currentEncoding == "ISO-8859-1") {
                $str = iconv("TIS-620", "UTF-8", $str);
                file_put_contents($file, $str);
            } else {
                $result['file'] = false;
                $result['encoding'] = $currentEncoding;
                return $result;
            }
        }

        $result['file'] = $file;
        $result['encoding'] = $currentEncoding;
        return $result;
    }

    public function convertArrayUTF8($array) {
        $result = [];
        $encodingList = ["ASCII", "UTF-8", "UTF-7", "ISO-8859-1", "EUC-JP", "SJIS", "eucJP-win", "SJIS-win", "JIS", "ISO-2022-JP"];

        $str = json_encode($array, JSON_UNESCAPED_UNICODE);
        $currentEncoding = mb_detect_encoding($str, $encodingList);
        dd($currentEncoding);

        if ($currentEncoding === false) {
            $result['file'] = false;
            $result['encoding'] = null;
            return $result;
        }

        $currentEncoding = strtoupper($currentEncoding);

        if ($currentEncoding != "UTF-8") {
            if ($currentEncoding == "ISO-8859-1") {
                $str = iconv("TIS-620", "UTF-8", $str);
                $array = json_decode($str, true);
            } else {
                $result['file'] = false;
                $result['encoding'] = $currentEncoding;
                return $result;
            }
        }

        $result['file'] = $array;
        $result['encoding'] = $currentEncoding;
        return $result;
    }

    public function createXMLLiveEvent($streaming_prefix_streamname, $streaming_streamname) {
        return '<?xml version="1.0" encoding="UTF-8"?>
        <live_event>
            <name>DooTV_'.$streaming_prefix_streamname.'</name>
            <input>
                <deblock_selected>true</deblock_selected>
                <denoise_selected>false</denoise_selected>
                <error_clear_time nil="true"/>
                <failback_rule>immediately</failback_rule>
                <filter_enable>Auto</filter_enable>
                <filter_strength>1</filter_strength>
                <hot_backup_pair>false</hot_backup_pair>
                <input_label>Dootv_input</input_label>
                <loop_source>false</loop_source>
                <no_psi>false</no_psi>
                <order>1</order>
                <service_name nil="true"/>
                <service_provider_name nil="true"/>
                <timecode_source>embedded</timecode_source>
                <network_input>
                    <check_server_certificate>true</check_server_certificate>
                    <enable_fec_rx>false</enable_fec_rx>
                    <password>live</password>
                    <quad>false</quad>
                    <udp_igmp_source></udp_igmp_source>
                    <uri>rtmp://localhost/live/'.$streaming_prefix_streamname.'/'.$streaming_streamname.'</uri>
                    <username>live</username>
                </network_input>
                <name>input_1</name>
                <video_selector>
                    <color_space>follow</color_space>
                    <order>1</order>
                    <program_id nil="true"/>
                    <name>input_1_video_selector_0</name>
                </video_selector>
                <audio_selector>
                    <default_selection>true</default_selection>
                    <order>1</order>
                    <program_selection>1</program_selection>
                    <selector_type></selector_type>
                    <unwrap_smpte337>true</unwrap_smpte337>
                    <name>input_1_audio_selector_0</name>
                </audio_selector>
            </input>
            <loop_all_inputs>false</loop_all_inputs>
            <timecode_config>
                <require_initial_timecode>false</require_initial_timecode>
                <source>embedded</source>
                <sync_threshold nil="true"/>
            </timecode_config>
            <failure_rule>
                <priority>50</priority>
                <restart_on_failure>false</restart_on_failure>
            </failure_rule>
            <input_loss_behavior>
                <black_frame_msec>10000</black_frame_msec>
                <input_loss_image_color>000000</input_loss_image_color>
                <input_loss_image_type>color</input_loss_image_type>
                <repeat_frame_msec>1000</repeat_frame_msec>
            </input_loss_behavior>
            <ad_trigger>scte35_splice_insert</ad_trigger>
            <ad_avail_offset>0</ad_avail_offset>
            <ignore_web_delivery_allowed_flag>false</ignore_web_delivery_allowed_flag>
            <ignore_no_regional_blackout_flag>false</ignore_no_regional_blackout_flag>
            <low_latency_mode>false</low_latency_mode>
            <initial_audio_gain>0</initial_audio_gain>
            <avsync_enable>true</avsync_enable>
            <avsync_pad_trim_audio>true</avsync_pad_trim_audio>
            <user_data></user_data>
            <input_end_action>switch_input</input_end_action>
            <output_timing_source>input_clock</output_timing_source>
            <input_buffer_size>60</input_buffer_size>
            <resource_reservation>none</resource_reservation>
            <low_framerate_input>false</low_framerate_input>
            <extract_sdt>false</extract_sdt>
            <stream_assembly>
                <name>stream_assembly_0</name>
                <video_description>
                    <afd_signaling>None</afd_signaling>
                    <anti_alias>true</anti_alias>
                    <drop_frame_timecode>true</drop_frame_timecode>
                    <fixed_afd nil="true"/>
                    <force_cpu_encode>false</force_cpu_encode>
                    <height>1080</height>
                    <insert_color_metadata>true</insert_color_metadata>
                    <respond_to_afd>None</respond_to_afd>
                    <sharpness>50</sharpness>
                    <stretch_to_output>false</stretch_to_output>
                    <timecode_passthrough>false</timecode_passthrough>
                    <vbi_passthrough>false</vbi_passthrough>
                    <width>1920</width>
                    <h264_settings>
                        <adaptive_quantization>medium</adaptive_quantization>
                        <bitrate>6000000</bitrate>
                        <buf_fill_pct nil="true"/>
                        <buf_size>2000000</buf_size>
                        <cabac>true</cabac>
                        <dynamic_sub_gop>false</dynamic_sub_gop>
                        <flicker_aq>true</flicker_aq>
                        <force_field_pictures>false</force_field_pictures>
                        <framerate_denominator nil="true"/>
                        <framerate_follow_source>true</framerate_follow_source>
                        <framerate_numerator nil="true"/>
                        <gop_b_reference>false</gop_b_reference>
                        <gop_closed_cadence>1</gop_closed_cadence>
                        <gop_markers>false</gop_markers>
                        <gop_num_b_frames>2</gop_num_b_frames>
                        <gop_size>90.0</gop_size>
                        <gop_size_units>frames</gop_size_units>
                        <interpolate_frc>false</interpolate_frc>
                        <look_ahead_rate_control>medium</look_ahead_rate_control>
                        <max_bitrate nil="true"/>
                        <max_qp nil="true"/>
                        <min_bitrate nil="true"/>
                        <min_buf_occ>0</min_buf_occ>
                        <min_i_interval>0</min_i_interval>
                        <min_qp nil="true"/>
                        <motion_vector_direct_mode nil="true"/>
                        <num_ref_frames>1</num_ref_frames>
                        <par_denominator nil="true"/>
                        <par_follow_source>true</par_follow_source>
                        <par_numerator nil="true"/>
                        <passes>1</passes>
                        <progressive_references>false</progressive_references>
                        <qp nil="true"/>
                        <quality_level nil="true"/>
                        <repeat_pps>false</repeat_pps>
                        <rp2027_syntax>false</rp2027_syntax>
                        <scd>true</scd>
                        <sei_timecode>false</sei_timecode>
                        <slices>1</slices>
                        <slow_pal>false</slow_pal>
                        <softness nil="true"/>
                        <spatial_aq>true</spatial_aq>
                        <svq>0</svq>
                        <telecine>None</telecine>
                        <temporal_aq>true</temporal_aq>
                        <profile>Main</profile>
                        <rate_control_mode>CBR</rate_control_mode>
                        <interlace_mode>progressive</interlace_mode>
                    </h264_settings>
                    <selected_gpu nil="true"/>
                    <codec>h.264</codec>
                </video_description>
                <audio_description>
                    <audio_type>0</audio_type>
                    <follow_input_audio_type>true</follow_input_audio_type>
                    <follow_input_language_code>true</follow_input_language_code>
                    <language_code nil="true"/>
                    <nielsen_rtvod_watermark></nielsen_rtvod_watermark>
                    <order>1</order>
                    <stream_name nil="true"/>
                    <timecode_passthrough>false</timecode_passthrough>
                    <aac_settings>
                        <ad_broadcaster_mix>false</ad_broadcaster_mix>
                        <bitrate>128000</bitrate>
                        <coding_mode>2_0</coding_mode>
                        <latm_loas>false</latm_loas>
                        <mpeg2>false</mpeg2>
                        <sample_rate>48000</sample_rate>
                        <profile>LC</profile>
                        <rate_control_mode>CBR</rate_control_mode>
                    </aac_settings>
                    <codec>aac</codec>
                    <audio_source_name>Audio Selector 1</audio_source_name>
                </audio_description>
            </stream_assembly>
            <stream_assembly>
                <name>stream_assembly_1</name>
                <video_description>
                    <afd_signaling>None</afd_signaling>
                    <anti_alias>true</anti_alias>
                    <drop_frame_timecode>true</drop_frame_timecode>
                    <fixed_afd nil="true"/>
                    <force_cpu_encode>false</force_cpu_encode>
                    <height>720</height>
                    <insert_color_metadata>true</insert_color_metadata>
                    <respond_to_afd>None</respond_to_afd>
                    <sharpness>50</sharpness>
                    <stretch_to_output>false</stretch_to_output>
                    <timecode_passthrough>false</timecode_passthrough>
                    <vbi_passthrough>false</vbi_passthrough>
                    <width>1280</width>
                    <h264_settings>
                        <adaptive_quantization>medium</adaptive_quantization>
                        <bitrate>4000000</bitrate>
                        <buf_fill_pct nil="true"/>
                        <buf_size nil="true"/>
                        <cabac>true</cabac>
                        <dynamic_sub_gop>false</dynamic_sub_gop>
                        <flicker_aq>true</flicker_aq>
                        <force_field_pictures>false</force_field_pictures>
                        <framerate_denominator nil="true"/>
                        <framerate_follow_source>true</framerate_follow_source>
                        <framerate_numerator nil="true"/>
                        <gop_b_reference>false</gop_b_reference>
                        <gop_closed_cadence>1</gop_closed_cadence>
                        <gop_markers>false</gop_markers>
                        <gop_num_b_frames>2</gop_num_b_frames>
                        <gop_size>90.0</gop_size>
                        <gop_size_units>frames</gop_size_units>
                        <interpolate_frc>false</interpolate_frc>
                        <look_ahead_rate_control>medium</look_ahead_rate_control>
                        <max_bitrate nil="true"/>
                        <max_qp nil="true"/>
                        <min_bitrate nil="true"/>
                        <min_buf_occ nil="true"/>
                        <min_i_interval>0</min_i_interval>
                        <min_qp nil="true"/>
                        <motion_vector_direct_mode nil="true"/>
                        <num_ref_frames>1</num_ref_frames>
                        <par_denominator nil="true"/>
                        <par_follow_source>true</par_follow_source>
                        <par_numerator nil="true"/>
                        <passes>1</passes>
                        <progressive_references>false</progressive_references>
                        <qp nil="true"/>
                        <quality_level nil="true"/>
                        <repeat_pps>false</repeat_pps>
                        <rp2027_syntax>false</rp2027_syntax>
                        <scd>true</scd>
                        <sei_timecode>false</sei_timecode>
                        <slices>1</slices>
                        <slow_pal>false</slow_pal>
                        <softness nil="true"/>
                        <spatial_aq>true</spatial_aq>
                        <svq>0</svq>
                        <telecine>None</telecine>
                        <temporal_aq>true</temporal_aq>
                        <profile>Main</profile>
                        <rate_control_mode>CBR</rate_control_mode>
                        <interlace_mode>progressive</interlace_mode>
                    </h264_settings>
                    <selected_gpu nil="true"/>
                    <codec>h.264</codec>
                </video_description>
                <audio_description>
                    <audio_type>0</audio_type>
                    <follow_input_audio_type>true</follow_input_audio_type>
                    <follow_input_language_code>true</follow_input_language_code>
                    <language_code nil="true"/>
                    <nielsen_rtvod_watermark></nielsen_rtvod_watermark>
                    <order>1</order>
                    <stream_name nil="true"/>
                    <timecode_passthrough>false</timecode_passthrough>
                    <aac_settings>
                        <ad_broadcaster_mix>false</ad_broadcaster_mix>
                        <bitrate>128000</bitrate>
                        <coding_mode>2_0</coding_mode>
                        <latm_loas>false</latm_loas>
                        <mpeg2>false</mpeg2>
                        <sample_rate>48000</sample_rate>
                        <profile>LC</profile>
                        <rate_control_mode>CBR</rate_control_mode>
                    </aac_settings>
                    <codec>aac</codec>
                    <audio_source_name>Audio Selector 1</audio_source_name>
                </audio_description>
            </stream_assembly>
            <stream_assembly>
                <name>stream_assembly_2</name>
                <video_description>
                    <afd_signaling>None</afd_signaling>
                    <anti_alias>true</anti_alias>
                    <drop_frame_timecode>true</drop_frame_timecode>
                    <fixed_afd nil="true"/>
                    <force_cpu_encode>false</force_cpu_encode>
                    <height>360</height>
                    <insert_color_metadata>true</insert_color_metadata>
                    <respond_to_afd>None</respond_to_afd>
                    <sharpness>50</sharpness>
                    <stretch_to_output>false</stretch_to_output>
                    <timecode_passthrough>false</timecode_passthrough>
                    <vbi_passthrough>false</vbi_passthrough>
                    <width>640</width>
                    <h264_settings>
                        <adaptive_quantization>medium</adaptive_quantization>
                        <bitrate>1000000</bitrate>
                        <buf_fill_pct nil="true"/>
                        <buf_size nil="true"/>
                        <cabac>true</cabac>
                        <dynamic_sub_gop>false</dynamic_sub_gop>
                        <flicker_aq>true</flicker_aq>
                        <force_field_pictures>false</force_field_pictures>
                        <framerate_denominator nil="true"/>
                        <framerate_follow_source>true</framerate_follow_source>
                        <framerate_numerator nil="true"/>
                        <gop_b_reference>false</gop_b_reference>
                        <gop_closed_cadence>1</gop_closed_cadence>
                        <gop_markers>false</gop_markers>
                        <gop_num_b_frames>2</gop_num_b_frames>
                        <gop_size>90.0</gop_size>
                        <gop_size_units>frames</gop_size_units>
                        <interpolate_frc>false</interpolate_frc>
                        <look_ahead_rate_control>medium</look_ahead_rate_control>
                        <max_bitrate nil="true"/>
                        <max_qp nil="true"/>
                        <min_bitrate nil="true"/>
                        <min_buf_occ nil="true"/>
                        <min_i_interval>0</min_i_interval>
                        <min_qp nil="true"/>
                        <motion_vector_direct_mode nil="true"/>
                        <num_ref_frames>1</num_ref_frames>
                        <par_denominator nil="true"/>
                        <par_follow_source>true</par_follow_source>
                        <par_numerator nil="true"/>
                        <passes>1</passes>
                        <progressive_references>false</progressive_references>
                        <qp nil="true"/>
                        <quality_level nil="true"/>
                        <repeat_pps>false</repeat_pps>
                        <rp2027_syntax>false</rp2027_syntax>
                        <scd>true</scd>
                        <sei_timecode>false</sei_timecode>
                        <slices>1</slices>
                        <slow_pal>false</slow_pal>
                        <softness nil="true"/>
                        <spatial_aq>true</spatial_aq>
                        <svq>0</svq>
                        <telecine>None</telecine>
                        <temporal_aq>true</temporal_aq>
                        <profile>Main</profile>
                        <rate_control_mode>CBR</rate_control_mode>
                        <interlace_mode>progressive</interlace_mode>
                    </h264_settings>
                    <selected_gpu nil="true"/>
                    <codec>h.264</codec>
                </video_description>
                <audio_description>
                    <audio_type>0</audio_type>
                    <follow_input_audio_type>true</follow_input_audio_type>
                    <follow_input_language_code>true</follow_input_language_code>
                    <language_code nil="true"/>
                    <nielsen_rtvod_watermark></nielsen_rtvod_watermark>
                    <order>1</order>
                    <stream_name nil="true"/>
                    <timecode_passthrough>false</timecode_passthrough>
                    <aac_settings>
                        <ad_broadcaster_mix>false</ad_broadcaster_mix>
                        <bitrate>128000</bitrate>
                        <coding_mode>2_0</coding_mode>
                        <latm_loas>false</latm_loas>
                        <mpeg2>false</mpeg2>
                        <sample_rate>48000</sample_rate>
                        <profile>LC</profile>
                        <rate_control_mode>CBR</rate_control_mode>
                    </aac_settings>
                    <codec>aac</codec>
                    <audio_source_name>Audio Selector 1</audio_source_name>
                </audio_description>
            </stream_assembly>
            <stream_assembly>
                <name>stream_assembly_3</name>
                <video_description>
                    <afd_signaling>None</afd_signaling>
                    <anti_alias>true</anti_alias>
                    <drop_frame_timecode>true</drop_frame_timecode>
                    <fixed_afd nil="true"/>
                    <force_cpu_encode>false</force_cpu_encode>
                    <height>240</height>
                    <insert_color_metadata>true</insert_color_metadata>
                    <respond_to_afd>None</respond_to_afd>
                    <sharpness>50</sharpness>
                    <stretch_to_output>false</stretch_to_output>
                    <timecode_passthrough>false</timecode_passthrough>
                    <vbi_passthrough>false</vbi_passthrough>
                    <width>426</width>
                    <h264_settings>
                        <adaptive_quantization>medium</adaptive_quantization>
                        <bitrate>400000</bitrate>
                        <buf_fill_pct nil="true"/>
                        <buf_size nil="true"/>
                        <cabac>true</cabac>
                        <dynamic_sub_gop>false</dynamic_sub_gop>
                        <flicker_aq>true</flicker_aq>
                        <force_field_pictures>false</force_field_pictures>
                        <framerate_denominator nil="true"/>
                        <framerate_follow_source>true</framerate_follow_source>
                        <framerate_numerator nil="true"/>
                        <gop_b_reference>false</gop_b_reference>
                        <gop_closed_cadence>1</gop_closed_cadence>
                        <gop_markers>false</gop_markers>
                        <gop_num_b_frames>2</gop_num_b_frames>
                        <gop_size>90.0</gop_size>
                        <gop_size_units>frames</gop_size_units>
                        <interpolate_frc>false</interpolate_frc>
                        <look_ahead_rate_control>medium</look_ahead_rate_control>
                        <max_bitrate nil="true"/>
                        <max_qp nil="true"/>
                        <min_bitrate nil="true"/>
                        <min_buf_occ nil="true"/>
                        <min_i_interval>0</min_i_interval>
                        <min_qp nil="true"/>
                        <motion_vector_direct_mode nil="true"/>
                        <num_ref_frames>1</num_ref_frames>
                        <par_denominator nil="true"/>
                        <par_follow_source>true</par_follow_source>
                        <par_numerator nil="true"/>
                        <passes>1</passes>
                        <progressive_references>false</progressive_references>
                        <qp nil="true"/>
                        <quality_level nil="true"/>
                        <repeat_pps>false</repeat_pps>
                        <rp2027_syntax>false</rp2027_syntax>
                        <scd>true</scd>
                        <sei_timecode>false</sei_timecode>
                        <slices>1</slices>
                        <slow_pal>false</slow_pal>
                        <softness nil="true"/>
                        <spatial_aq>true</spatial_aq>
                        <svq>0</svq>
                        <telecine>None</telecine>
                        <temporal_aq>true</temporal_aq>
                        <profile>Main</profile>
                        <rate_control_mode>CBR</rate_control_mode>
                        <interlace_mode>progressive</interlace_mode>
                    </h264_settings>
                    <selected_gpu nil="true"/>
                    <codec>h.264</codec>
                </video_description>
                <audio_description>
                    <audio_type>0</audio_type>
                    <follow_input_audio_type>true</follow_input_audio_type>
                    <follow_input_language_code>true</follow_input_language_code>
                    <language_code nil="true"/>
                    <nielsen_rtvod_watermark></nielsen_rtvod_watermark>
                    <order>1</order>
                    <stream_name nil="true"/>
                    <timecode_passthrough>false</timecode_passthrough>
                    <aac_settings>
                        <ad_broadcaster_mix>false</ad_broadcaster_mix>
                        <bitrate>128000</bitrate>
                        <coding_mode>2_0</coding_mode>
                        <latm_loas>false</latm_loas>
                        <mpeg2>false</mpeg2>
                        <sample_rate>48000</sample_rate>
                        <profile>LC</profile>
                        <rate_control_mode>CBR</rate_control_mode>
                    </aac_settings>
                    <codec>aac</codec>
                    <audio_source_name>Audio Selector 1</audio_source_name>
                </audio_description>
            </stream_assembly>
            <output_group>
                <custom_name>adobe_rtmp</custom_name>
                <name nil="true"/>
                <order>1</order>
                <rtmp_group_settings>
                    <ad_markers nil="true"/>
                    <cache_length>30</cache_length>
                    <caption_data>all</caption_data>
                    <cdn></cdn>
                    <disconnect_immediately>true</disconnect_immediately>
                    <enable_oncuepoint_broadcast_time>false</enable_oncuepoint_broadcast_time>
                    <onfi_timecode_frequency>1</onfi_timecode_frequency>
                    <restart_delay>15</restart_delay>
                </rtmp_group_settings>
                <type>rtmp_group_settings</type>
                <output>
                    <arib_captions_passthrough>false</arib_captions_passthrough>
                    <description nil="true"/>
                    <extension>rtmp</extension>
                    <insert_amf_metadata>false</insert_amf_metadata>
                    <log_edit_points>false</log_edit_points>
                    <name_modifier nil="true"/>
                    <order>1</order>
                    <scte35_passthrough>false</scte35_passthrough>
                    <start_paused>false</start_paused>
                    <rtmp_settings>
                        <connection_retry_interval>2</connection_retry_interval>
                        <num_retries>10</num_retries>
                        <stream_name>'.$streaming_prefix_streamname.'_1080p</stream_name>
                        <rtmp_endpoint>
                            <uri>rtmp://203.151.179.165:1935/live</uri>
                        </rtmp_endpoint>
                    </rtmp_settings>
                    <stream_assembly_name>stream_assembly_0</stream_assembly_name>
                    <container>rtmp</container>
                </output>
                <output>
                    <arib_captions_passthrough>false</arib_captions_passthrough>
                    <description nil="true"/>
                    <extension>rtmp</extension>
                    <insert_amf_metadata>false</insert_amf_metadata>
                    <log_edit_points>false</log_edit_points>
                    <name_modifier nil="true"/>
                    <order>2</order>
                    <scte35_passthrough>false</scte35_passthrough>
                    <start_paused>false</start_paused>
                    <rtmp_settings>
                        <connection_retry_interval>2</connection_retry_interval>
                        <num_retries>10</num_retries>
                        <stream_name>'.$streaming_prefix_streamname.'_720p</stream_name>
                        <rtmp_endpoint>
                            <uri>rtmp://203.151.179.165:1935/live</uri>
                        </rtmp_endpoint>
                    </rtmp_settings>
                    <stream_assembly_name>stream_assembly_1</stream_assembly_name>
                    <container>rtmp</container>
                </output>
                <output>
                    <arib_captions_passthrough>false</arib_captions_passthrough>
                    <description nil="true"/>
                    <extension>rtmp</extension>
                    <insert_amf_metadata>false</insert_amf_metadata>
                    <log_edit_points>false</log_edit_points>
                    <name_modifier nil="true"/>
                    <order>3</order>
                    <scte35_passthrough>false</scte35_passthrough>
                    <start_paused>false</start_paused>
                    <rtmp_settings>
                        <connection_retry_interval>2</connection_retry_interval>
                        <num_retries>10</num_retries>
                        <stream_name>'.$streaming_prefix_streamname.'_360p</stream_name>
                        <rtmp_endpoint>
                            <uri>rtmp://203.151.179.165:1935/live</uri>
                        </rtmp_endpoint>
                    </rtmp_settings>
                    <stream_assembly_name>stream_assembly_2</stream_assembly_name>
                    <container>rtmp</container>
                </output>
                <output>
                    <arib_captions_passthrough>false</arib_captions_passthrough>
                    <description nil="true"/>
                    <extension>rtmp</extension>
                    <insert_amf_metadata>false</insert_amf_metadata>
                    <log_edit_points>false</log_edit_points>
                    <name_modifier nil="true"/>
                    <order>4</order>
                    <scte35_passthrough>false</scte35_passthrough>
                    <start_paused>false</start_paused>
                    <rtmp_settings>
                        <connection_retry_interval>2</connection_retry_interval>
                        <num_retries>10</num_retries>
                        <stream_name>'.$streaming_prefix_streamname.'_240p</stream_name>
                        <rtmp_endpoint>
                            <uri>rtmp://203.151.179.165:1935/live</uri>
                        </rtmp_endpoint>
                    </rtmp_settings>
                    <stream_assembly_name>stream_assembly_3</stream_assembly_name>
                    <container>rtmp</container>
                </output>
            </output_group>
        </live_event>';
    }

    public function liveTranscodeAuthentication($url, $hours = 1) {
        $datetime = date('Y-m-d H:i:s');
        $tz_from = date_default_timezone_get();
        $tz_to = 'UTC';
        $format = 'F j, Y T H:i:s';

        $dt = new DateTime($datetime, new DateTimeZone($tz_from));
        $dt->setTimeZone(new DateTimeZone($tz_to));

        $dt->add(new DateInterval('PT' . $hours . 'H'));
        $dateUTC = $dt->format($format);
        $time = $dt->getTimestamp();

        $key = md5(env('LIVE_TRANSCODE_API_KEY') . md5($url . 'admin' . env('LIVE_TRANSCODE_API_KEY') . $time));

        $array = array(
            'key' => $key,
            'expires' => $time
        );

        return $array;
    }

    public function secondsFromTime($time) {
        list($h, $m, $s) = explode(':', $time);
        return ($h * 3600) + ($m * 60) + $s;
    }

    public function timeFromSeconds($seconds) {
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds - ($h * 3600) - ($m * 60);
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }

    public function makeLinks($text) {
        // The Regular Expression filter
        $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

        // Check if there is a url in the text
        if(preg_match($reg_exUrl, $text, $url)) {

               // make the urls hyper links
               return preg_replace($reg_exUrl, "<a href=\"{$url[0]}\" target=\"_blank\">{$url[0]}</a> ", $text);

        } else {

               // if no urls in the text just return the text
               return $text;

        }
    }

    public function _2c2pCheckHash($input, $secret_key) {
        $version = $input['version'];
        $request_timestamp = $input['request_timestamp'];
        $merchant_id = $input['merchant_id'];
        $currency = $input['currency'];
        $order_id = $input['order_id'];
        $amount = $input['amount'];
        $invoice_no = $input['invoice_no'];
        $transaction_ref = $input['transaction_ref'];
        $approval_code = $input['approval_code'];
        $eci = $input['eci'];
        $transaction_datetime = $input['transaction_datetime'];
        $payment_channel = $input['payment_channel'];
        $payment_status = $input['payment_status'];
        $channel_response_code = $input['channel_response_code'];
        $channel_response_desc = $input['channel_response_desc'];
        $masked_pan = $input['masked_pan'];
        $stored_card_unique_id = $input['stored_card_unique_id'];
        $backend_invoice = $input['backend_invoice'];
        $paid_channel = $input['paid_channel'];
        $paid_agent = $input['paid_agent'];
        $recurring_unique_id = $input['recurring_unique_id'];
        $ippPeriod = $input['ippPeriod'];
        $ippInterestType = $input['ippInterestType'];
        $ippInterestRate = $input['ippInterestRate'];
        $ippMerchantAbsorbRate = $input['ippMerchantAbsorbRate'];
        $payment_scheme = $input['payment_scheme'];
        $user_defined_1 = $input['user_defined_1'];
        $user_defined_2 = $input['user_defined_2'];
        $user_defined_3 = $input['user_defined_3'];
        $user_defined_4 = $input['user_defined_4'];
        $user_defined_5 = $input['user_defined_5'];
        $browser_info = $input['browser_info'];
        $hash_value = $input['hash_value'];

        $checkHashStr = $version . $request_timestamp . $merchant_id . $order_id .
        $invoice_no . $currency . $amount . $transaction_ref . $approval_code .
        $eci . $transaction_datetime . $payment_channel . $payment_status .
        $channel_response_code . $channel_response_desc . $masked_pan .
        $stored_card_unique_id . $backend_invoice . $paid_channel . $paid_agent .
        $recurring_unique_id . $user_defined_1 . $user_defined_2 . $user_defined_3 .
        $user_defined_4 . $user_defined_5 . $browser_info . $ippPeriod .
        $ippInterestType . $ippInterestRate . $ippMerchantAbsorbRate . $payment_scheme;

        $checkHash = hash_hmac('sha1', $checkHashStr, $secret_key, false);

        if(strcmp(strtolower($hash_value), strtolower($checkHash)) == 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getCodeCurrency($number) {
        $list["784"] = "AED"; $list["971"] = "AFN"; $list["008"] = "ALL"; $list["051"] = "AMD"; $list["532"] = "ANG";
        $list["973"] = "AOA"; $list["032"] = "ARS"; $list["036"] = "AUD"; $list["533"] = "AWG"; $list["944"] = "AZN";
        $list["977"] = "BAM"; $list["052"] = "BBD"; $list["050"] = "BDT"; $list["975"] = "BGN"; $list["048"] = "BHD";
        $list["108"] = "BIF"; $list["060"] = "BMD"; $list["096"] = "BND"; $list["068"] = "BOB"; $list["984"] = "BOV";
        $list["986"] = "BRL"; $list["044"] = "BSD"; $list["064"] = "BTN"; $list["072"] = "BWP"; $list["933"] = "BYN";
        $list["084"] = "BZD"; $list["124"] = "CAD"; $list["976"] = "CDF"; $list["947"] = "CHE"; $list["756"] = "CHF";
        $list["948"] = "CHW"; $list["990"] = "CLF"; $list["152"] = "CLP"; $list["156"] = "CNY"; $list["170"] = "COP";
        $list["970"] = "COU"; $list["188"] = "CRC"; $list["931"] = "CUC"; $list["192"] = "CUP"; $list["132"] = "CVE";
        $list["203"] = "CZK"; $list["262"] = "DJF"; $list["208"] = "DKK"; $list["214"] = "DOP"; $list["012"] = "DZD";
        $list["818"] = "EGP"; $list["232"] = "ERN"; $list["230"] = "ETB"; $list["978"] = "EUR"; $list["242"] = "FJD";
        $list["238"] = "FKP"; $list["826"] = "GBP"; $list["981"] = "GEL"; $list["936"] = "GHS"; $list["292"] = "GIP";
        $list["270"] = "GMD"; $list["324"] = "GNF"; $list["320"] = "GTQ"; $list["328"] = "GYD"; $list["344"] = "HKD";
        $list["340"] = "HNL"; $list["191"] = "HRK"; $list["332"] = "HTG"; $list["348"] = "HUF"; $list["360"] = "IDR";
        $list["376"] = "ILS"; $list["356"] = "INR"; $list["368"] = "IQD"; $list["364"] = "IRR"; $list["352"] = "ISK";
        $list["388"] = "JMD"; $list["400"] = "JOD"; $list["392"] = "JPY"; $list["404"] = "KES"; $list["417"] = "KGS";
        $list["116"] = "KHR"; $list["174"] = "KMF"; $list["408"] = "KPW"; $list["410"] = "KRW"; $list["414"] = "KWD";
        $list["136"] = "KYD"; $list["398"] = "KZT"; $list["418"] = "LAK"; $list["422"] = "LBP"; $list["144"] = "LKR";
        $list["430"] = "LRD"; $list["426"] = "LSL"; $list["434"] = "LYD"; $list["504"] = "MAD"; $list["498"] = "MDL";
        $list["969"] = "MGA"; $list["807"] = "MKD"; $list["104"] = "MMK"; $list["496"] = "MNT"; $list["446"] = "MOP";
        $list["929"] = "MRU"; $list["480"] = "MUR"; $list["462"] = "MVR"; $list["454"] = "MWK"; $list["484"] = "MXN";
        $list["979"] = "MXV"; $list["458"] = "MYR"; $list["943"] = "MZN"; $list["516"] = "NAD"; $list["566"] = "NGN";
        $list["558"] = "NIO"; $list["578"] = "NOK"; $list["524"] = "NPR"; $list["554"] = "NZD"; $list["512"] = "OMR";
        $list["590"] = "PAB"; $list["604"] = "PEN"; $list["598"] = "PGK"; $list["608"] = "PHP"; $list["586"] = "PKR";
        $list["985"] = "PLN"; $list["600"] = "PYG"; $list["634"] = "QAR"; $list["946"] = "RON"; $list["941"] = "RSD";
        $list["643"] = "RUB"; $list["646"] = "RWF"; $list["682"] = "SAR"; $list["090"] = "SBD"; $list["690"] = "SCR";
        $list["938"] = "SDG"; $list["752"] = "SEK"; $list["702"] = "SGD"; $list["654"] = "SHP"; $list["694"] = "SLL";
        $list["706"] = "SOS"; $list["968"] = "SRD"; $list["728"] = "SSP"; $list["930"] = "STN"; $list["222"] = "SVC";
        $list["760"] = "SYP"; $list["748"] = "SZL"; $list["764"] = "THB"; $list["972"] = "TJS"; $list["934"] = "TMT";
        $list["788"] = "TND"; $list["776"] = "TOP"; $list["949"] = "TRY"; $list["780"] = "TTD"; $list["901"] = "TWD";
        $list["834"] = "TZS"; $list["980"] = "UAH"; $list["800"] = "UGX"; $list["840"] = "USD"; $list["997"] = "USN";
        $list["940"] = "UYI"; $list["858"] = "UYU"; $list["927"] = "UYW"; $list["860"] = "UZS"; $list["928"] = "VES";
        $list["704"] = "VND"; $list["548"] = "VUV"; $list["882"] = "WST"; $list["950"] = "XAF"; $list["961"] = "XAG";
        $list["959"] = "XAU"; $list["955"] = "XBA"; $list["956"] = "XBB"; $list["957"] = "XBC"; $list["958"] = "XBD";
        $list["951"] = "XCD"; $list["960"] = "XDR"; $list["952"] = "XOF"; $list["964"] = "XPD"; $list["953"] = "XPF";
        $list["962"] = "XPT"; $list["994"] = "XSU"; $list["963"] = "XTS"; $list["965"] = "XUA"; $list["999"] = "XXX";
        $list["886"] = "YER"; $list["710"] = "ZAR"; $list["967"] = "ZMW"; $list["932"] = "ZWL";

        return $list[$number];
    }

    public function downloadFileFromSFTP($host ,$port, $authUsing ,$username ,$password ,$remoteDir ,$localDir ,$filename) {
        $results = [
            "filename" => $filename,
            "status" => false
        ];

        if (is_null($port)) {
            $port = 22;
        }

        $isConnected = true;

        if (!function_exists("ssh2_connect")) {
            $isConnected = false;
            $results['message'] = 'Function ssh2_connect does not exist.';
        } else if (!$connection = @ssh2_connect($host, $port)) {
            $isConnected = false;
            $results['message'] = 'Failed to connect.';
        } else if (strtoupper($authUsing) == "PASSWORD" && !@ssh2_auth_password($connection, $username, $password)) {
            $isConnected = false;
            $results['message'] = 'Failed to authenticate using password.';
        } else if (strtoupper($authUsing) == "PUBKEY_FILE" && !@ssh2_auth_pubkey_file($connection, $username, env('PATH_PUBLIC_KEY_TXET'), env('PATH_PRIVATE_KEY_TXET'), '')) {
            $isConnected = false;
            $results['message'] = 'Failed to authenticate using public key.';
        } else if (!$sftp_conn = @ssh2_sftp($connection)) {
            $results['message'] = 'Failed to create a sftp connection.';
        } else if (!$remote = @fopen("ssh2.sftp://".intval($sftp_conn).$remoteDir.$filename, 'r')) {
            $results['message'] = 'Failed to open remote file.';
        } else {
            fclose($remote);

            if (!@ssh2_scp_recv($connection, $remoteDir.$filename, $localDir.$filename)) {
                $results['message'] = 'Failed to download file.';
            } else {
                $results['status'] = true;
                $results['message'] = 'File downloaded successfully.';
            }
        }

        if ($isConnected) {
            if (function_exists("ssh2_disconnect")) {
                @ssh2_disconnect($connection);
            } else {
                @ssh2_exec($connection, 'echo "EXITING" && exit;');
                $connection = null;
            }
        }


        return $results;
    }

    public function downloadFilePatternFromSFTP($host ,$port, $authUsing ,$username ,$password ,$remoteDir ,$localDir ,$filePattern) {
        $results = [
            "files" => [],
            "status" => false
        ];

        if (is_null($port)) {
            $port = 22;
        }

        $isConnected = true;

        if (!function_exists("ssh2_connect")) {
            $isConnected = false;
            $results['message'] = 'Function ssh2_connect does not exist.';
        } else if (!$connection = @ssh2_connect($host, $port)) {
            $isConnected = false;
            $results['message'] = 'Failed to connect.';
        } else if (strtoupper($authUsing) == "PASSWORD" && !@ssh2_auth_password($connection, $username, $password)) {
            $isConnected = false;
            $results['message'] = 'Failed to authenticate using password.';
        } else if (strtoupper($authUsing) == "PUBKEY_FILE" && !@ssh2_auth_pubkey_file($connection, $username, env('PATH_PUBLIC_KEY_TXET'), env('PATH_PRIVATE_KEY_TXET'), '')) {
            $isConnected = false;
            $results['message'] = 'Failed to authenticate using public key.';
        } else if (!$sftp_conn = @ssh2_sftp($connection)) {
            $results['message'] = 'Failed to create a sftp connection.';
        } else {

            $results['status'] = true;
            $results['message'] = 'Login successfully.';
            $files = scandir("ssh2.sftp://".intval($sftp_conn).$remoteDir);

            if (!empty($files)) {
                foreach ($files as $file) {
                    if ($file != '.' && $file != '..') {
                        if (!@ssh2_scp_recv($connection, $remoteDir.$file, $localDir.$file)) {
                            $results['files']['status'] = false;
                            $results['files']['message'] = 'Failed to download file.';
                        } else {
                            $results['files']['status'] = true;
                            $results['files']['message'] = 'File downloaded successfully.';
                        }
                    }
                }
            }
        }

        if ($isConnected) {
            if (function_exists("ssh2_disconnect")) {
                @ssh2_disconnect($connection);
            } else {
                @ssh2_exec($connection, 'echo "EXITING" && exit;');
                $connection = null;
            }
        }


        return $results;
    }

    public function extractZipAllFiles($file, $destination = null) {
        $results = [];
        $zip = new \ZipArchive;
        if ($zip->open($file) === TRUE) {

            if (is_null($destination)) {
                $extractDir = dirname($file);
            } else {
                $extractDir = $destination;
            }

            $zip->extractTo($extractDir);

            $results['status'] = true;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $results['extracted_files'][] = $extractDir.$zip->getNameIndex($i);
            }

            $zip->close();
        } else {
            $results['status'] = false;
            $results['extracted_files'] = [];
        }

        return $results;
    }

    public function str_split_unicode($str, $l = 0) {
        if ($l > 0) {
            $ret = array();
            $len = mb_strlen($str, "UTF-8");
            for ($i = 0; $i < $len; $i += $l) {
                $ret[] = mb_substr($str, $i, $l, "UTF-8");
            }
            return $ret;
        }
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    public function count_digit($number) {
        return strlen((int)$number);
    }

    public function cal_chunk($number) {
        $digit = strlen((int)$number);
        return (int)str_pad("1", $digit - 1, "0", STR_PAD_RIGHT);
    }

}




