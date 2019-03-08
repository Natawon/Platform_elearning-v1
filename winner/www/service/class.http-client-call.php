<?php
/**
* Name : Helper Functions
* Created By : Nawee (nawee.ku.dootvmedia@gmail.com)
* Created Date : 25/03/2016
* Updated Date : 25/03/2016
* Updated By : Nawee (nawee.ku.dootvmedia@gmail.com)
*/

@session_start();

class HttpClientCall extends HelperFunctions {

	var $_ch;

	public function __construct(){
		# do something
		$this->_ch = curl_init();
	}

	private function parseHeaders($headers) {
	    $newHeaders = array();
	    foreach( $headers as $key => $value ) {
	        $t = explode( ':', $value, 2 );
	        if( isset( $t[1] ) ) {
	            $newHeaders[ trim($t[0]) ] = trim( $t[1] );
	        } else {
	            $newHeaders[] = $value;
	            if( preg_match( '#HTTP/[0-9\.]+\s+([0-9]+)#', $value, $out ) ) {
	            	$status_text = explode(' ', $value, 2);
	                $newHeaders['StatusCode'] = intval($out[1]);
	                $newHeaders['StatusText'] = $status_text[1];
	            }
	        }
	    }
	    return $newHeaders;
	} // FN : parseHeaders()

	public function fgc($url, $method = 'GET', $is_token = 0/*default: 1*/, $data = array(), $is_json = 0) {
		$result = array();
		$opts = array('http' =>
			array(
				'method'  => $method,
				// 'header'  => array('Content-type: application/x-www-form-urlencoded'),
				'ignore_errors' => true
			)
		);

		if($is_token == 1 && isset($_SESSION['token']) && $_SESSION['token'] != "") {
			$opts['http']['header'][] = 'Authorization: Bearer '.$_SESSION['token'];
		}

		if (is_array($data)) {
			$opts['http']['content'] = http_build_query($data);
		}

		$context  = stream_context_create($opts);

		// $json = file_get_contents($url, false, $context);
		$json = file_get_contents($url, false);

		echo "ssss :::: ";
		var_dump($json);
		exit();

		if ($json === false) {
			$result['headers'] = $this->parseHeaders($http_response_header);
			$result['body'] = $json;
			return $result;
		} else {
			$result['headers'] = $this->parseHeaders($http_response_header);
			$result['headers']['is_done'] = ($result['headers']['StatusCode'] < 400) ? true : false ;
			$result['body'] = ($is_json == 1) ? $json : json_decode($json,true);
			return $result;
		}

	} // FN : fgc()

	public function curl($url, $method = 'GET', $cookie = null, $is_token = 0/*default: 1*/, $data = array(), $is_json = 0) {
		$result = array();

		// Setup the curl resource
		// $ch = curl_init();
		$ch = $this->_ch;

		$requestHeaders = array();

		// Virtual Host
		// $requestHeaders[] = 'Host: xxxx.elearning.set.or.th';
		// $requestHeaders[] = 'Connection: Keep-Alive';
		// $requestHeaders[] = 'Keep-Alive: 1000000000000';

		if($is_token == 1 && isset($_SESSION['token']) && $_SESSION['token'] != "") {
			$requestHeaders[] = 'Authorization: Bearer '.$_SESSION['token'];
		}

		curl_setopt($ch, CURLOPT_URL, $url);

		if (isset($cookie)) {
			// curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		}

		switch (strtoupper($method)) {
			case 'GET':
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				// curl_setopt($ch, CURLOPT_VERBOSE, 1);
				// curl_setopt($ch, CURLOPT_FORBID_REUSE, false);
				// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_HEADER, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
				break;

			case 'POST':
				$data_string = "";
				if (is_array($data)) {
					$data_string = json_encode($data);
				}

				$requestHeaders[] = 'Content-Type: application/json';
				$requestHeaders[] = 'Content-Length: ' . strlen($data_string);

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HEADER, 1);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
				break;

			case 'PUT':
				$data_string = "";
				if (is_array($data)) {
					$data_string = json_encode($data);
				}

				$requestHeaders[] = 'Content-Type: application/json';
				$requestHeaders[] = 'Content-Length: ' . strlen($data_string);

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HEADER, 1);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
				break;

			case 'DELETE':
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HEADER, 1);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
				break;

			default:
				$result['error'] = "Request error : Method Not Found";
				return $result;
				break;
		}

		// Execute the request
		$output = curl_exec($ch);

		if (!curl_errno($ch)) {
			list($header, $body) = explode("\r\n\r\n", $output, 2);
			$headers = explode("\r\n", $header);
			$result['headers'] = $this->parseHeaders($headers);
			$result['headers']['is_done'] = ($result['headers']['StatusCode'] < 400) ? true : false ;
			$result['body'] = ($is_json == 1) ? $body : json_decode($body, true);

			if ($is_json == 0 && $result['body'] == null) {
				$taint = ")]}',\n";
				// var_dump(substr($body, strlen($taint), strlen($body)));
				if (strrpos($body, $taint) === 0) {
					$result['body'] = json_decode(substr($body, strlen($taint), strlen($body)), true);
					// var_dump($result['body']);
				}
			}
		} else {
			$result['error'] = 'Curl error : ' . curl_error($ch);
		}

		// $result = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		// Close curl resource to free up system resources
		// curl_close($ch);

		return $result;
	} // FN : curl()

	public function curl_close() {
		curl_close($this->_ch);
	}

	public function curl_init() {
		$this->_ch = curl_init();
	}

	public function curl_get() {
		return $this->_ch;
	}
}
?>