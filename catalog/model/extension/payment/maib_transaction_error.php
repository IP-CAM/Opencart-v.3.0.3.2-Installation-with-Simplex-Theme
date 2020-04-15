<?php

class ModelExtensionPaymentMaibTransactionError extends Model {
	public function addTransactionError($data) {
		$query = $this->db->query("INSERT INTO `" . DB_PREFIX . "maib_transaction_error` SET error_info = '" . json_encode($data['error_info']) . "', transaction_id='" . $this->db->escape($data['TRANSACTION_ID']) . "',  date_added=NOW()");
		return $this->db->getLastId();
	}

	public function getTransactionError($transaction_error_id){
		return $this->db->query('SELECT * FROM '.DB_PREFIX.'maib_transaction_error WHERE transaction_error_id='.$transaction_error_id)->row;
	}

}
