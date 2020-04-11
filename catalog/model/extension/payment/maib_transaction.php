<?php
class ModelExtensionPaymentMaibTransaction extends Model {
	const MAIB_TRANSACTION_GENERATED=1;
	CONST MAIB_TRANSACTION_SUCCES=10;

	public function addTransaction($data) {
		$query = $this->db->query("INSERT INTO `" . DB_PREFIX . "maib_transaction` SET order_id='" . (int)$data['order_id'] . "',status ='" . self::MAIB_TRANSACTION_GENERATED . "', transaction_id='" . $this->db->escape($data['TRANSACTION_ID']) . "',  date_added='" .$data['date_added']."'");
		return $query;
	}

	public function getTransaction($TRANSACTION_ID){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "maib_transaction WHERE transaction_id = '" . $TRANSACTION_ID . "'");
		return $query->row;
	}

	public function setTransactionCallbacked($TRANSACTION_ID){
		$this->db->query("UPDATE `" . DB_PREFIX . "maib_transaction` SET status=" .self::MAIB_TRANSACTION_SUCCES.", callbacked = 1 WHERE transaction_id='" . $TRANSACTION_ID . "'");
	}
}
