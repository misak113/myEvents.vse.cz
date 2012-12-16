<?php
namespace app\services\facebook;

use Facebook as FacebookAbstract;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 9.12.12
 * Time: 19:42
 * To change this template use File | Settings | File Templates.
 */
class Facebook extends FacebookAbstract
{

	/**
	 * Příhlásí FB SDK pro aktuální příkazy importu
	 */
	public function login($accessToken = null) {
		//Facebook::$CURL_OPTS[CURLOPT_CAINFO] = getcwd().'/fb_ca_chain_bundle.crt';
		Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false; // @todo neověřuje crt

		if ($accessToken === null) {
			$app_id = $this->getAppId();
			$app_secret = $this->getApiSecret();
			$url = 'https://graph.facebook.com/oauth/access_token';
			$params = array(
				'client_id' => $app_id,
				'client_secret' => $app_secret,
				'grant_type' => 'client_credentials',
				'scope' => 'offline_access',
			);
			$user = $this->getUser();
			$response = $this->_oauthRequest($url, $params);

			$data = array();
			parse_str($response, $data);
			$accessToken = $data['access_token'];
			_dBar($accessToken);
			$loginUrl = $this->getLoginUrl(array('scope' => 'user_events,user_about_me,email'));
//			echo $loginUrl;
			$params = array();
			$ex = explode('?', $loginUrl);
			$loginUri = $ex[0];
			$query = $ex[1];
			parse_str($query, $params);
//			dump($params);
//			//echo file_get_contents($loginUrl);
//			$loggedResponse = $this->_oauthRequest($loginUrl, array());
//			$ch = curl_init();
//			$result = curl_exec($ch);
//			dump(get_headers($loginUrl));
//			dump($result);
//			dump(curl_getinfo($ch));
//			dump(curl_multi_info_read($ch));
//			dump($loggedResponse);
//			die();
		}

		$this->setAccessToken($accessToken);
	}

}
