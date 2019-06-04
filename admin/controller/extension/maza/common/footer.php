<?php
class ControllerExtensionMazaCommonFooter extends Controller {
	public function index() {
                $this->load->language('extension/maza/common/footer');
                $this->config->set('template_engine', 'template');
		return $this->load->view('extension/maza/common/footer');
	}
}
