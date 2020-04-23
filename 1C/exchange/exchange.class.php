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
     * @var string
     */
    protected $apiId;
    /**
     * @var string
     */
    protected $categoriesUrl;

    /**
     * Exchange constructor.
     *
     * @param string $module_url Path to 1C module controller
     *
     * @throws Exception
     */
    public function __construct($module_url, $login, $password, $loginUrl)
    {
        $this->login($login, $password, $loginUrl);
        $ch = curl_init();
        if (!isset($this->apiId)) {
            throw new Exception("Auth failed");
        }
        curl_setopt($ch, CURLOPT_URL, $module_url . "&api_token=$this->apiId");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $return = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $this->url = $return['url'];
        $this->login = $return['login'];
        $this->password = $return['password'];
        $this->categoriesUrl = $return['categories_import_url'];
    }

    /**
     * Request data
     * @return bool|string
     */
    public function request1C()
    {
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_USERPWD, $this->login . ":" . $this->password);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $return = curl_exec($ch);
        curl_close($ch);
		file_put_contents('products.json', $return);
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

    public function requestCategories()
    {
        $ch = curl_init("http://195.22.235.118:4480/simplex/hs/ExportArticlesGroup/post");
        curl_setopt($ch, CURLOPT_USERPWD, $this->login . ":" . $this->password);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $return = curl_exec($ch);
        curl_close($ch);
        file_put_contents('categories.json', $return);

        return $return;
    }

    protected function login($login, $password, $loginUrl)
    {
        $ch = curl_init($loginUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POST, 2);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "username=$login&key=$password");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->apiId = json_decode(curl_exec($ch))->api_token;
    }
}
