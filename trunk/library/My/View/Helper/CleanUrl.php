<?php

/**
 * Helper pro ocisteni textu do URL od problematickych znaku jako znaky s diakritikou, mezery apod.
 *
 */
class My_View_Helper_CleanUrl extends Zend_View_Helper_Abstract {
	
	/**
	 * Ocisteni URL od problematickych znaku
	 *
	 * @param string $url Vstupni text do URL, ktery muze obsahovat problematicke znaky
	 * @return string Ocistena URL od problematickych znaku
	 */
	public function cleanUrl($url) {
		
		$diacritics = array("\xc3\x81"=>'A', "\xc4\x8c"=>'C',
			"\xc4\x8e"=>'D', "\xc3\x89"=>'E', "\xc4\x9a"=>'E', "\xc3\x8d"=>'I',
			"\xc5\x87"=>'N', "\xc3\x93"=>'O', "\xc5\x98"=>'R', "\xc5\xa0"=>'S',
			"\xc5\xa4"=>'T', "\xc3\x9a"=>'U', "\xc5\xae"=>'U', "\xc3\x9d"=>'Y',
			"\xc5\xbd"=>'Z', "\xc3\xa1"=>'a', "\xc4\x8d"=>'c', "\xc4\x8f"=>'d',
			"\xc4\x9b"=>'e', "\xc3\xa9"=>'e', "\xc3\xad"=>'i', "\xc5\x88"=>'n',
			"\xc3\xb3"=>'o', "\xc5\x99"=>'r', "\xc5\xa1"=>'s', "\xc5\xa5"=>'t',
			"\xc3\xba"=>'u', "\xc5\xaf"=>'u', "\xc3\xbd"=>'y', "\xc5\xbe"=>'z',
			"\xc3\xa4"=>'a', "\xc3\xab"=>'e', "\xc3\xb6"=>'o', "\xc3\xbc"=>'u',
			"\xc3\x84"=>'A', "\xc3\x8b"=>'E', "\xc3\x96"=>'O', "\xc3\x9c"=>'U');
		
		$url = str_replace('\'', '', $url);
		$url = strtr(trim($url), $diacritics);
		$url = strtr($url, " ", "-");
		$url = strtr($url, "&()", "a--");
		$url = strtolower($url);
		
		return $url;
		
	}
	
}

?>