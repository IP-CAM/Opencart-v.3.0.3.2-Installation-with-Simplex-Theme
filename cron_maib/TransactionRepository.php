<?php
/**
 * simplex_vasea - TransactionModel.php
 *
 * Created by Admin
 * Created on: 12.04.2020 0:22
 */

class TransactionRepository {
	private $db;
	private $registry;
	private $config;

	public function __construct(Registry $registry) {
		$this->registry = $registry;
		$this->registry->get('load')->model('extension/payment/maib_transaction');
		$this->registry->get('load')->model('checkout/order');
		$this->config = $this->getConfig();
	}

	public function getTransactions(){
		return $this->registry->get('model_extension_payment_maib_transaction')->getTransactions();
	}

	public function getTransactionsForVerification($minutes, $verification_number) {
		return $this->registry->get('model_extension_payment_maib_transaction')->getTransactionsForVerification($minutes, $verification_number);
	}

	public function getConfig() {
		$config = [];
		$query = $this->registry->get('db')->query('SELECT * FROM ' . DB_PREFIX . "setting WHERE code='payment_maib'");
		foreach ($query->rows as $row) {
			$config[$row['key']] = $row['value'];
		}
		return $config;
	}

	public function setOrderPaymentOK($order_id) {
		$this->registry->get('model_checkout_order')->addOrderHistory($order_id, $this->config['payment_maib_order_callback_status']);
	}
	public function setTransactionVerifiedOK($TRANSACTION_ID,$verification_number, $transactionResult) {
		return $this->registry->get('model_extension_payment_maib_transaction')->setTransactionVerifiedOK($TRANSACTION_ID, $verification_number, $transactionResult);
	}

	public function setTransactionVerified($TRANSACTION_ID,$verification_number, $transactionResult){
		return $this->registry->get('model_extension_payment_maib_transaction')->setTransactionVerified($TRANSACTION_ID,$verification_number, $transactionResult);
	}

	public function addTransactionError($data){
		return $this->registry->get('model_extension_payment_maib_transaction_error')->addTransactionError($data);
	}

}