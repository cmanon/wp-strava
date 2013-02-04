<?php
/*
 * Util is a class with all the utility methods.
 */
class WPStrava_Util {
	public function makePostRequest ($url, $data) {
    	$data = http_build_query($data);
    	$url = parse_url($url);
    	
    	if ($url['scheme'] == "http") {
    		$port = 80;
    		$domain = "tcp://";
    	} elseif ($url['scheme'] == "https") {
    		$port = 443;
    		$domain = "ssl://";
    	} else {
    		$this->feedback .= __('This function only support http and https', 'wp-strava');
    		return false;
    	}
    	
    	$host = $url['host'];
    	$path = $url['path'];
    	$protocol = $url['scheme'] . "://";
    	
    	// Open a socket connection to the specified port - timeout 30 seconds
    	$fp = fsockopen($domain . $host, $port, $error_number, $error_string, 30);
    	
    	if ($fp) {
    		// Build the request headers and data
    		$request = "POST " . $protocol . $host . $path . " HTTP/1.0\r\n";
    		$request .= "Host: " . $host . "\r\n"; 
			$request .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$request .= "Content-Length: " . strlen($data) . "\r\n";
			$request .= "Connection: close\r\n\r\n";
			$request .= $data;

			fputs($fp, $request);
    		
    		$result = "";
    		while (!feof($fp)) {
    			$result .= fgets($fp, 128);
    		}
    	} else {
    		$this->feedback .= __('ERROR - ' . $error_string . '-' . $error_number, 'wp-strava');
    		return false;
    	}
    	
    	fclose($fp);
    	
    	// Split the result header from the content
    	$result = explode("\r\n\r\n", $result, 2);
	    $header = isset($result[0]) ? $result[0] : '';
	    $content = isset($result[1]) ? $result[1] : '';
	    
	    return $content;
    } // makePostRequest
} // class Util