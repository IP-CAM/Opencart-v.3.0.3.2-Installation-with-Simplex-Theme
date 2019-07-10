<?php
class ControllerAccountNewsletter extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/newsletter', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/newsletter');

		$this->document->setTitle($this->language->get('heading_title'));
        /* added by it-lab start */
        $this->language->load('extension/module/pavnewsletter');
        $this->load->model('account/customer');
        $this->load->model('extension/pavnewsletter/subscribe');

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->load->model('account/customer');

			$this->model_account_customer->editNewsletter($this->request->post['newsletter']);

			$this->session->data['success'] = $this->language->get('text_success');

            /* added by it-lab start */
            $customer=$this->model_account_customer->getCustomer($this->customer->getId());
            $data                = array();
            $data['store_id']    = $this->config->get('config_store_id');
            $data['customer_id'] = $customer['customer_id'];
            $data['email']       = $customer['email'];
            var_dump($data);
            if($this->request->post['newsletter']){
                if (!$this->model_extension_pavnewsletter_subscribe->checkExists($customer['email'])) {
                    if ($customer = $this->model_account_customer->getCustomerByEmail($customer['email'])) {
                        $data['customer_id'] = $customer['customer_id'];
                    }
                    var_dump($data);

                    $this->model_extension_pavnewsletter_subscribe->storeSubscribe($data);
                }
            }else{
                $this->load->model('extension/pavnewsletter/subscribe');
                $subscribe_id=$this->model_extension_pavnewsletter_subscribe->getSubscribeId($customer['email']);
                if($subscribe_id){
                    $this->model_extension_pavnewsletter_subscribe->updateAction($subscribe_id, 0);
                }
            }





			$this->response->redirect($this->url->link('account/account', '', true));
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_newsletter'),
			'href' => $this->url->link('account/newsletter', '', true)
		);

		$data['action'] = $this->url->link('account/newsletter', '', true);

		$data['newsletter'] = $this->customer->getNewsletter();

		$data['back'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/newsletter', $data));
	}
}