<?php

use Fruitware\MaibApi\MaibClient;
use Fruitware\MaibApi\MaibDescription;
use GuzzleHttp\Client;

class ControllerEventTransaction extends Controller
{
    //
    public function redirect()
    {
        $this->response->redirect("http://simplex-vasea.dev.it-lab.md/");
    }

    public function callback()
    {
    	//var_dump(3123);exit;
    	$this->load->model('checkout/order');
    	$this->load->model('extension/payment/maib_transaction');
    	$TRANSACTION_ID=$this->request->post['trans_id'];
    	$transaction = $this->model_extension_payment_maib_transaction->getTransaction($TRANSACTION_ID);
    	if($transaction){
    		if($this->verifyTransaction($TRANSACTION_ID)) {
				$order_id = $transaction['order_id'];
				$this->config->get('payment_maib_order_callback_status');
				$this->model_extension_payment_maib_transaction->setTransactionCallbacked($TRANSACTION_ID);
				$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_maib_order_callback_status'));
				$this->response->redirect($this->url->link('checkout/success'));
			}
    	}
		$this->response->setOutput('error');
    }

    public function verifyTransaction($TRANSACTION_ID){
		$certificate_folder = str_replace('catalog/', '', DIR_APPLICATION) . $this->config->get('payment_maib_certificate_folder');

		$verify = $certificate_folder . '/cacert.pem';
		$cert =  $certificate_folder . '/pcert.pem';
		$ssl_key =  $certificate_folder . '/key.pem';
		$password = $this->config->get('payment_maib_certificate_password');
		$merchant_url = $this->config->get('payment_maib_merchant_url');
		$options = [
			'base_url' => $merchant_url,
			'debug' => true,
			'verify' => false,
			'defaults' => [
				'verify' => $verify,
				'cert' => [$cert, $password],
				'ssl_key' => $ssl_key,
				'config' => [
					'curl' => [
						CURLOPT_SSL_VERIFYHOST => false,
						CURLOPT_SSL_VERIFYPEER => false,
						CURLOPT_VERBOSE => 1
					]
				]
			],
		];
		$guzzleClient = new Client($options);
		$client = new MaibClient($guzzleClient);
		$transactionResult = $client->getTransactionResult($TRANSACTION_ID,'127.0.0.1');
		if(isset($transactionResult['RESULT']) && $transactionResult['RESULT'] == 'OK') {
			$this->model_extension_payment_maib_transaction->setTransactionVerifiedOK($TRANSACTION_ID, 0, $transactionResult);
			return true;
		}
		return false;
	}

}