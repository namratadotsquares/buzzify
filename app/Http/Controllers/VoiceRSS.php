<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
class VoiceRSS extends Controller 
{ 
 
	public function getSpeech($text , $lang = 'en', $Speechvoice = 'Linda', $accentCode = null){ 
		$defaultCode = $this->resolveVoiceRssHl($lang, $accentCode); 
		$voice = $this->speech([ 
		    'key' => '7317929352c94b72b51d47b4ef139946', 
		    'hl' => $defaultCode, 
		    'v' => $Speechvoice, 
		    'src' => $text,
		    'r' => '0',
		    'c' => 'mp3',
		    'f' => '44khz_16bit_stereo',
		    'ssml' => 'false',
		    'b64' => 'true'
		]); 
		return $voice['response']; 
	} 
 
	private function resolveVoiceRssHl($lang = 'en', $accentCode = null) { 
		$defaultCode = 'en-us'; 
 
		$langMap = config('constant.speech_lang_code') ?: []; 
		if (is_string($lang) && $lang !== '' && isset($langMap[$lang])) { 
			$defaultCode = $langMap[$lang]; 
		} 
 
		if (!is_string($accentCode)) { 
			return $defaultCode; 
		} 
 
		$accentCode = trim($accentCode); 
		if ($accentCode === '') { 
			return $defaultCode; 
		} 
 
		// If accent is a short language key like "en" or "ar", prefer configured mapping 
		$accentLower = strtolower($accentCode); 
		if (isset($langMap[$accentLower])) { 
			return $langMap[$accentLower]; 
		} 
		if (isset($langMap[$accentCode])) { 
			return $langMap[$accentCode]; 
		} 
 
		// Otherwise treat as VoiceRSS "hl" (normalize "en-US" / "en_US" => "en-us") 
		return str_replace('_', '-', $accentLower); 
	} 
 
 
	public function speech($settings) { 
	    $this->_validate($settings); 
	    return $this->_request($settings);
	}

	private function _validate($settings) {
	    if (!isset($settings) || count($settings) == 0) throw new Exception('The settings are undefined');
        if (!isset($settings['key']) || empty($settings['key'])) throw new Exception('The API key is undefined');
        if (!isset($settings['src']) || empty($settings['src'])) throw new Exception('The text is undefined');
        if (!isset($settings['hl']) || empty($settings['hl'])) throw new Exception('The language is undefined');
	}

	private function _request($settings) {
    	$url = ((isset($settings['ssl']) && $settings['ssl']) ? 'https' : 'http') . '://api.voicerss.org/';
    	$ch = curl_init($url);
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, (isset($settings['b64']) && $settings['b64']) ? 0 : 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded; charset=UTF-8'));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_buildRequest($settings));
		$resp = curl_exec($ch);
		curl_close($ch);
		$is_error = strpos($resp, 'ERROR') === 0;
    	return array(
    		'error' => ($is_error) ? $resp : null,
    		'response' => (!$is_error) ? $resp: null);
	}

	private function _buildRequest($settings) {
	    return http_build_query(array(
	        'key' => isset($settings['key']) ? $settings['key'] : '',
	        'src' => isset($settings['src']) ? $settings['src'] : '',
	        'hl' => isset($settings['hl']) ? $settings['hl'] : '',
	        'v' => isset($settings['v']) ? $settings['v'] : '',
	        'r' => isset($settings['r']) ? $settings['r'] : '',
	        'c' => isset($settings['c']) ? $settings['c'] : '',
	        'f' => isset($settings['f']) ? $settings['f'] : '',
	        'ssml' => isset($settings['ssml']) ? $settings['ssml'] : '',
	        'b64' => isset($settings['b64']) ? $settings['b64'] : ''
	    ));
	}
}
?>
