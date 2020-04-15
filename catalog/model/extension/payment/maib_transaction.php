<?php
DEFINE ( 'MAIB_TRANSACTION_GENERATED', "GENERATED");
DEFINE( "MAIB_TRANSACTION_OK", "OK");
DEFINE( "MAIB_TRANSACTION_FAILED" , "FAILED");
DEFINE( "MAIB_TRANSACTION_CREATED", "CREATED");
DEFINE( "MAIB_TRANSACTION_PENDING" , "OK");
DEFINE( "MAIB_TRANSACTION_DECLINED", "DECLINED");
DEFINE( "MAIB_TRANSACTION_REVERSED" , "REVERSED");
DEFINE( "MAIB_TRANSACTION_AUTOREVERSED", "AUTOREVERSED");
DEFINE( "MAIB_TRANSACTION_TIMEOUT" , "TIMEOUT");
class ModelExtensionPaymentMaibTransaction extends Model {
	public function addTransaction($data) {
		$query = $this->db->query("INSERT INTO `" . DB_PREFIX . "maib_transaction` SET order_id='" . (int)$data['order_id'] . "',status ='" . MAIB_TRANSACTION_GENERATED . "', transaction_id='" . $this->db->escape($data['TRANSACTION_ID']) . "',  date_added=NOW()");
		return $query;
	}

	public function getTransaction($TRANSACTION_ID){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "maib_transaction WHERE transaction_id = '" . $TRANSACTION_ID . "'");
		return $query->row;
	}

	public function setTransactionCallbacked($TRANSACTION_ID){
		$this->db->query("UPDATE `" . DB_PREFIX . "maib_transaction` SET status='". MAIB_TRANSACTION_OK."', callbacked = 1 WHERE transaction_id='" . $TRANSACTION_ID . "'");
	}

	public function getTransactions (){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "maib_transaction WHERE 1");
		return $query->rows;
	}
	public function getOrderTransactions($order_id){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "maib_transaction WHERE order_id=".$order_id);
		return $query->rows;
	}
	public function getTransactionsForVerification($minutes, $verification_number) {
		$now = date('Y-m-d H:i:s');
		$now_20 = date('Y-m-d H:i:s', strtotime("-$minutes minutes", strtotime($now)));
		$query = $this->db->query("SELECT * from " . DB_PREFIX . "maib_transaction WHERE date_added<'" . $now_20 . "' AND verifications_count<$verification_number AND callbacked=0 AND status<>'".MAIB_TRANSACTION_OK."'");
/*		var_dump("SELECT * from " . DB_PREFIX . "maib_transaction WHERE date_added<'" . $now_20 . "' AND verifications_count<$verification_number AND callbacked=0 AND status<>".self::MAIB_TRANSACTION_OK);*/
		return $query->rows;
	}

	public function setTransactionVerifiedOK($TRANSACTION_ID,$verification_number, $transactionResult){
		$this->db->query("UPDATE `" . DB_PREFIX . "maib_transaction` SET status='" .MAIB_TRANSACTION_OK."', verifications_count = $verification_number , verification_result ='".json_encode($transactionResult)."' WHERE transaction_id='" . $TRANSACTION_ID . "'");
	}

	public function setTransactionVerified($TRANSACTION_ID,$verification_number, $transactionResult){
		$this->db->query("UPDATE `" . DB_PREFIX . "maib_transaction` SET status='".$transactionResult['RESULT']."', verifications_count = $verification_number , verification_result ='".json_encode($transactionResult)."' WHERE transaction_id='" . $TRANSACTION_ID . "'");
	}

}
