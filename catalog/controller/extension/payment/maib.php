<?php
use Fruitware\MaibApi\MaibClient;
use Fruitware\MaibApi\MaibDescription;
use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Log\Formatter;
use GuzzleHttp\Subscriber\Log\LogSubscriber;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Handler\PHPConsoleHandler;
use Symfony\Component\Validator\Validator\RecursiveContextualValidator;

class ControllerExtensionPaymentMaib extends \Controller {
	public function index() {
		$data['button_confirm'] = $this->language->get('button_confirm');

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$data['ap_amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
		$data['ap_currency'] = $order_info['currency_code'];
		$data['ap_purchasetype'] = 'Item';
		$data['ap_itemname'] = $this->config->get('config_name') . ' - #' . $this->session->data['order_id'];
		$data['ap_itemcode'] = $this->session->data['order_id'];
		$data['ap_returnurl'] = $this->url->link('checkout/success');
		$data['ap_cancelurl'] = $this->url->link('checkout/checkout', '', true);

		return $this->load->view('extension/payment/maib', $data);
	}

	public function callback() {
		if (isset($this->request->post['ap_securitycode']) && ($this->request->post['ap_securitycode'] == $this->config->get('payment_payza_security'))) {
			$this->load->model('checkout/order');

			$this->model_checkout_order->addOrderHistory($this->request->post['ap_itemcode'], $this->config->get('payment_payza_order_status_id'));
		}
	}

	public function confirmmaib(){

		$this->load->model('checkout/order');

		if($this->session->data['payment_method']['code'] == 'maib') {
			$this->load->model('checkout/order');
			$this->load->model('extension/payment/maib_transaction');
			$order_info=$this->model_checkout_order->getOrder($this->session->data['order_id']);

			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_maib_order_status_id'));



			$log = new Logger('maib_guzzle_request');

			$verify = str_replace('catalog/', '', DIR_APPLICATION) . '_ccc_/cacert.pem';
			$cert = str_replace('catalog/', '', DIR_APPLICATION) . '_ccc_/pcert.pem';
			$ssl_key = str_replace('catalog/', '', DIR_APPLICATION) . '_ccc_/key.pem';

			$options = [
				'base_url' => 'https://ecomm.maib.md:4499/ecomm2/MerchantHandler',
				'debug' => true,
				'verify' => false,
				'defaults' => [
					'verify' => $verify,
					'cert' => [$cert, 'Za86DuC$'],
					'ssl_key' => $ssl_key,
					'config' => [
						'curl' => [
							CURLOPT_SSL_VERIFYHOST => false,
							CURLOPT_SSL_VERIFYPEER => false,
							CURLOPT_TIMEOUT => 50,
							CURLOPT_VERBOSE => 1
						]
					]
				],
			];

// init Client
			$guzzleClient = new Client($options);
/*			$log->pushHandler(new StreamHandler(__DIR__ . '/logs/maib_guzzle_request.log', Logger::DEBUG));
			$subscriber = new LogSubscriber($log, Formatter::SHORT);*/

			$client = new MaibClient($guzzleClient);
//			$client->getHttpClient()->getEmitter()->attach($subscriber);
// examples

			$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
			$amout = $order_info['total'];

			$smsTransactionResult = $client->registerSmsTransaction($amout, 498, '127.0.0.1', '', 'ru');
			if (isset($smsTransactionResult['TRANSACTION_ID'])) {
				$data=[];
				$data['TRANSACTION_ID'] = $smsTransactionResult['TRANSACTION_ID'];
				$data['order_id'] = $this->session->data['order_id'];
				$data['date_added'] = date('Y-m-d H:i:s');
				$result = $this->model_extension_payment_maib_transaction->addTransaction($data);
				$log->addInfo($result);
				return $this->response->setOutput(json_encode($smsTransactionResult));
			} else {
				return $this->response->setOutput(json_encode(['result' => 'error']));
			}
		}
		return $this->response->setOutput(json_encode(['result' => 'error']));
	}



	public function get_currency() {
		/* added by it-lab start */
		$data['currency'] = $this->session->data['currency'];
		/* added by it-lab end */

		$this->response->setOutput(json_encode($data));
	}
}