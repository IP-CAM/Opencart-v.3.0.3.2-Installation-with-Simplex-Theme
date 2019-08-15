<?php
class ControllerApiExchange extends Controller {
	protected $url = '';
	protected $login = '';
	protected $password = '';

	public function getModuleConfig() {
		$module_config['url'] = $this->config->get('module_exchange_url');
		$module_config['login'] = $this->config->get('module_exchange_login');
		$module_config['password'] = $this->config->get('module_exchange_password');

		$this->response->setOutput(json_encode($module_config));
	}
}