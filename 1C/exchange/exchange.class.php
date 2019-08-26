<?php

class Exchange {
	protected $url = '';
	protected $login = '';
	protected $password = '';

	public function __construct($module_url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $module_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$return = json_decode(curl_exec($ch), true);
		curl_close($ch);

		$this->url = $return['url'];
		$this->login = $return['login'];
		$this->password = $return['password'];
	}

	public function request1C() {
		$ch = curl_init($this->url);
		curl_setopt($ch, CURLOPT_USERPWD, $this->login . ":" . $this->password);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$return = curl_exec($ch);
		curl_close($ch);

		return $return;
	}

	public function import($module_url, $json) {
		$fields = array(
			'json' => $json,
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $module_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$params_string = '';
		if(is_array($fields) && count($fields)) {
			foreach($fields as $key => $value) {
				$params_string .= $key . '=' . $value . '&';
			}
			rtrim($params_string, '&');
			curl_setopt($ch, CURLOPT_POST, count($fields));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
		}
		//execute post
		$return = (bool)curl_exec($ch);
		//close connection
		curl_close($ch);

		return $return;
	}
}