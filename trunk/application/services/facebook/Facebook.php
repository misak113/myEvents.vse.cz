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
		Facebook::$CURL_OPTS[CURLOPT_CAINFO] = getcwd().'/fb_ca_chain_bundle.crt';
		Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false; // @todo neověřuje crt

		if ($accessToken === null) {
			$app_id = $this->getAppId();
			$app_secret = $this->getApiSecret();
			$url = 'https://graph.facebook.com/oauth/access_token';
			$params = array(
				'client_id' => $app_id,
				'client_secret' => $app_secret,
				'grant_type' => 'client_credentials',
			);
			$response = $this->_oauthRequest($url, $params);

			$accessToken = $response['access_token'];
		}

		$this->setAccessToken($accessToken);
	}

}