<?php

class ControllerCheckoutSuccess extends Controller {
	public function index() {
		$this->load->language('checkout/success');
		$this->load->model('checkout/order');
		$this->load->model('api/exchange');
		$data['order_id'] = isset($this->session->data['order_id']);
		//$order = $this->model_checkout_order->getOrder(58);
//		unset($order['invoice_no']);
//		unset($order['invoice_prefix']);
//		unset($order['store_id']);
//		unset($order['customer_id']);
//		unset($order['store_url']);
//		unset($order['store_name']);
//		unset($order['payment_firstname']);
//		unset($order['payment_lastname']);
//		unset($order['payment_company']);
//		unset($order['payment_address_1']);
//		unset($order['payment_address_2']);
//		unset($order['payment_postcode']);
//		unset($order['payment_zone_id']);
//		unset($order['payment_zone']);
//		unset($order['payment_method']);
//		unset($order['shipping_method']);
//		unset($order['payment_zone_code']);
//		unset($order['payment_country_id']);
//		unset($order['payment_country']);
//		unset($order['payment_iso_code_2']);
//		unset($order['payment_iso_code_3']);
//		unset($order['payment_address_format']);
//		unset($order['payment_custom_field']);
//		unset($order['shipping_zone_id']);
//		unset($order['shipping_zone_code']);
//		unset($order['shipping_country_id']);
//		unset($order['shipping_country']);
//		unset($order['shipping_iso_code_2']);
//		unset($order['shipping_iso_code_3']);
//		unset($order['shipping_address_format']);
//		unset($order['shipping_custom_field']);
//		unset($order['comment']);
//		unset($order['order_status_id']);
//		unset($order['order_status']);
//		unset($order['affiliate_id']);
//		unset($order['commission']);
//		unset($order['language_id']);
//		unset($order['language_code']);
//		unset($order['currency_id']);
//		unset($order['currency_code']);
//		unset($order['currency_value']);
//		unset($order['ip']);
//		unset($order['forwarded_ip']);
//		unset($order['user_agent']);
//		unset($order['accept_language']);

		//$order['products'] = $this->model_checkout_order->getOrderProducts(58);

//		foreach($order['products'] as &$product){
//			unset($product['order_id']);
//			unset($product['order_product_id']);
//		}

		if(isset($this->session->data['order_id'])) {
			$this->cart->clear();

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['guest']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
			unset($this->session->data['totals']);
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_basket'),
			'href' => $this->url->link('checkout/cart')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_checkout'),
			'href' => $this->url->link('checkout/checkout', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_success'),
			'href' => $this->url->link('checkout/success')
		);

		if($this->customer->isLogged()) {
			$data['text_message'] = sprintf($this->language->get('text_customer'), $this->url->link('account/account', '', true), $this->url->link('account/order', '', true), $this->url->link('account/download', '', true), $this->url->link('information/contact'));
		} else {
			$data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('information/contact'));
		}

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		//IT-LAB
		$this->response->setOutput($this->load->view('common/success_order', $data));
	}
}