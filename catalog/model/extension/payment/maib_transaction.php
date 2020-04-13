<?php
class ModelExtensionPaymentMaibTransaction extends Model {
	const MAIB_TRANSACTION_GENERATED=1;
	CONST MAIB_TRANSACTION_OK=10;

	public function addTransaction($data) {
		$query = $this->db->query("INSERT INTO `" . DB_PREFIX . "maib_transaction` SET order_id='" . (int)$data['order_id'] . "',status ='" . self::MAIB_TRANSACTION_GENERATED . "', transaction_id='" . $this->db->escape($data['TRANSACTION_ID']) . "',  date_added=NOW()");
		return $query;
	}

	public function getTransaction($TRANSACTION_ID){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "maib_transaction WHERE transaction_id = '" . $TRANSACTION_ID . "'");
		return $query->row;
	}

	public function setTransactionCallbacked($TRANSACTION_ID){
		$this->db->query("UPDATE `" . DB_PREFIX . "maib_transaction` SET status=" .self::MAIB_TRANSACTION_OK.", callbacked = 1 WHERE transaction_id='" . $TRANSACTION_ID . "'");
	}

	public function getTransactions (){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "maib_transaction WHERE 1");
		return $query->rows;
	}

	public function getTransactionsForVerification($minutes, $verification_number) {
		$now = date('Y-m-d H:i:s');
		$now_20 = date('Y-m-d H:i:s', strtotime("-$minutes minutes", strtotime($now)));
		$query = $this->db->query("SELECT * from " . DB_PREFIX . "maib_transaction WHERE date_added<'" . $now_20 . "' AND verifications_count<$verification_number AND callbacked=0 AND status<>".self::MAIB_TRANSACTION_OK);
/*		var_dump("SELECT * from " . DB_PREFIX . "maib_transaction WHERE date_added<'" . $now_20 . "' AND verifications_count<$verification_number AND callbacked=0 AND status<>".self::MAIB_TRANSACTION_OK);*/
		return $query->rows;
	}

	public function setTransactionVerifiedOK($TRANSACTION_ID,$verification_number){
		$this->db->query("UPDATE `" . DB_PREFIX . "maib_transaction` SET status=" .self::MAIB_TRANSACTION_OK.", verifications_count = $verification_number WHERE transaction_id='" . $TRANSACTION_ID . "'");
	}

	public function setTransactionVerified($TRANSACTION_ID,$verification_number){
		$this->db->query("UPDATE `" . DB_PREFIX . "maib_transaction` SET verifications_count = $verification_number WHERE transaction_id='" . $TRANSACTION_ID . "'");
	}

}
