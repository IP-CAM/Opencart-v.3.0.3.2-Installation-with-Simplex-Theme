<?php

class ControllerEventTransaction extends Controller
{
    //
    public function redirect()
    {
        $this->response->redirect("http://simplex-vasea.dev.it-lab.md/");
    }

    public function callback()
    {
    	$this->load->model('checkout/order');
    	$this->load->model('extension/payment/maib_transaction');
    	$TRANSACTION_ID=$this->request->post['trans_id'];
    	$transaction = $this->model_extension_payment_maib_transaction->getTransaction($TRANSACTION_ID);
    	if($transaction){
    		$order_id=$transaction['order_id'];
			$this->config->get('payment_maib_order_callback_status');
    		$this->model_extension_payment_maib_transaction->setTransactionCallbacked($TRANSACTION_ID);
    		$this->model_checkout_order->addOrderHistory($order_id,$this->config->get('payment_maib_order_callback_status'));
			$this->response->redirect( $this->url->link('checkout/success'));
    	}
    }
}