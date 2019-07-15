<?php
	require('config.php');

	class Common {
		protected $url;

		protected $fields = array();

		protected function do_curl_request($url, $params = array()) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
			curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
			$params_string = '';
			if(is_array($params) && count($params)) {
				foreach($params as $key => $value) {
					$params_string .= $key . '=' . $value . '&';
				}
				rtrim($params_string, '&');
				curl_setopt($ch, CURLOPT_POST, count($params));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
			}
			//execute post
			$return = curl_exec($ch);
			//close connection
			curl_close($ch);
			return $return;
		}

		public function login() {
			$this->fields = array(
				'username' => USERNAME,
				'key'      => KEY,
			);
			$this->url = SRV_ADRESS . '/index.php?route=api/login';
			$result = json_decode($this->do_curl_request($this->url, $this->fields), true);
			if(isset($result)) {
				setcookie('token', $result['api_token'], time() + 3600);
				return 'Logged In';
			} else return 'Error on log in';
		}

		public function action($action, $json) {
			$this->fields = array(
				'action' => $action,
				'json'   => $json,
			);
			$this->url = SRV_ADRESS . "/index.php?route=api/exchange&api_token=" . $_COOKIE["token"];
			$result = $this->do_curl_request($this->url, $this->fields);

			if(isset($result)) {
				return $result;
			} else return 'Error';
		}

		public function receive($json) {
			return $json;
		}
	}