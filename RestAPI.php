<?php
/**
 * Created by PhpStorm.
 * User: Justin Worsley
 * Date: 5/26/2016
 * Time: 7:39 AM
 */

class RestAPI
{
	private $session_id = NULL;
	public $error = NULL;
	const REST_URLS = array();
	const COOKIE_NAME = '';

	/**
	 * RestAPI constructor.
	 * @param $session_id
	 */
	public function __construct($session_id = NULL)
	{
		$this->session_id = $session_id;
	}

	/**
	 * @param $method
	 * @param $url
	 * @param bool $data
	 * @return mixed
	 */
	private function call($method, $url, $data = FALSE)
	{
		$this->error = NULL;
		$curl = curl_init();
		if ($this->session_id) {
			curl_setopt($curl, CURLOPT_HTTPHEADER, array("Cookie: " . COOKIE_NAME . "=$this->session_id"));
		}
		curl_setopt($curl, CURLOPT_FAILONERROR, TRUE);
		switch ($method) {
			case 'GET':
				curl_setopt($curl, CURLOPT_HTTPGET, TRUE);
				break;
			case "POST":
				curl_setopt($curl, CURLOPT_POST, TRUE);
				if ($data)
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				break;
			case "PUT":
				curl_setopt($curl, CURLOPT_PUT, TRUE);
				break;
			default:
				if ($data)
					$url = sprintf("%s?%s", $url, http_build_query($data));
		}
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		$response = curl_exec($curl);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		if ($http_code > 200) {
			$this->error = $http_code;
			return $http_code;
		}
		else {
			return json_decode($response);
		}
	}

	/**
	 * @param $url_call
	 * @param $conditionals
	 * @return mixed
	 */
	public function getCall($url_call, $conditionals = NULL) {
		return $this->call('GET', self::REST_URLS[$url_call] . $conditionals);
	}

	/**
	 * @param $url_call
	 * @param $data
	 * @param null $conditionals
	 * @return mixed
	 */
	public function postCall($url_call, $data, $conditionals = NULL) {
		return $this->call('POST', self::REST_URLS[$url_call] . $conditionals, $data);
	}

	/**
	 * @param $url_call
	 * @param $data
	 * @param null $conditionals
	 * @return mixed
	 */
	public function putCall($url_call, $data, $conditionals = NULL) {
		return $this->call('PUT', self::REST_URLS[$url_call] . $conditionals, $data);
	}


}