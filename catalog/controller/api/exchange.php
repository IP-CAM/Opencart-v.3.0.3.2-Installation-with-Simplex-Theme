<?php

class ControllerApiExchange extends Controller {
    public function getModuleConfig() {
        if (!isset($this->session->data['api_id'])) {
            $json['error'] = $this->language->get('error_permission');
            $this->response->setOutput(json_encode($json));
        } else {
            $module_config['url'] = $this->config->get('module_exchange_receive_url');
            $module_config['login'] = $this->config->get('module_exchange_login');
            $module_config['password'] = $this->config->get('module_exchange_password');

            $this->response->setOutput(json_encode($module_config));
        }
    }
}