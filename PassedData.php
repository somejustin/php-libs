<?php

/**
 * Created by PhpStorm.
 * User: jworsley
 * Date: 5/26/2016
 * Time: 9:38 AM
 */
class PassedData
{
	/**
	 * @param $key
	 * @return null
	 */
	public function allPassedData($key) {
		if (isset($_POST[$key])) {
			return $_POST[$key];
		} else if (isset($_GET[$key])) {
			return $_GET[$key];
		} else {
			return NULL;
		}
	}

	/**
	 * @param $key
	 * @return null
	 */
	public function getPassedData($key) {
		if (isset($_GET[$key])) {
			return $_GET[$key];
		} else {
			return NULL;
		}
	}

	/**
	 * @param $key
	 * @return null
	 */
	public function postPassedData($key) {
		if (isset($_POST[$key])) {
			return $_POST[$key];
		} else {
			return NULL;
		}
	}
}