<?php

/**
 * Class Exchange
 */
class Exchange {
	/**
	 * 1C webservice url
	 * @var string
	 */
	protected $url = '';
	/**
	 * @var string
	 */
	protected $login = '';
	/**
	 * @var string
	 */
	protected $password = '';

	/**
	 * Exchange constructor.
	 * @param string $module_url Path to 1C module controller
	 */
	public function __construct($module_url) {
	/*	$module_url="http://simplex.dev.it-lab.md/index.php?route=api/exchange/getModuleConfig";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $module_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$return = json_decode(curl_exec($ch), true);
		curl_close($ch);*/

		$this->url = 'http://195.22.235.118:4480/simplex/hs/ExportArticles/post';//$return['url'];
		$this->login = 'ws';// $return['login'];
		$this->password = 'Simplexws1702';//$return['password'];
	}

	/**
	 * Request data
	 * @return bool|string
	 */
	public function request1C() {
		$ch = curl_init($this->url);
		curl_setopt($ch, CURLOPT_USERPWD, $this->login . ":" . $this->password);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$return = curl_exec($ch);
		curl_close($ch);
		file_put_contents('products.json' , $return);

		return $return;
	}

	/**
	 * Send data to 1C webservice
	 * @param string $module_url Path to 1C module controller
	 * @param string $json
	 * @return bool
	 */
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

    public function requestCategories(){
        $ch = curl_init("http://195.22.235.118:4480/simplex/hs/ExportArticlesGroup/post");
        curl_setopt($ch, CURLOPT_USERPWD, $this->login . ":" . $this->password);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $return = curl_exec($ch);
        curl_close($ch);
		file_put_contents('categories.json',$return);
        return $return;
	}
}
