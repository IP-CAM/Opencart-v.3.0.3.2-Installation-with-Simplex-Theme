<?php

class ErrorSender {
	private $registry;

	public function __construct($registry) {
		$this->registry = $registry;
		$this->registry->get('load')->model('extension/payment/maib_transaction_error');
		$this->registry->get('load')->model('extension/payment/maib_transaction');
	}

	public function sendErrorMail($transaction_error_id) {
		$transaction_error = $this->registry->get('model_extension_payment_maib_transaction_error')->getTransactionError($transaction_error_id);
		$transaction = $this->registry->get('model_extension_payment_maib_transaction')->getTransaction($transaction_error['transaction_id']);
		$to = $this->registry->get('config')->get('payment_maib_error_mail');
		var_dump($transaction_error);
		var_dump($transaction);
		var_dump($to);

		$mail = new Mail($this->registry->get('config')->get('config_mail_engine'));
		$mail->parameter = $this->registry->get('config')->get('config_mail_parameter');
		$mail->smtp_hostname = $this->registry->get('config')->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->registry->get('config')->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode(
			$this->registry->get('config')->get('config_mail_smtp_password'),
			ENT_QUOTES,
			'UTF-8'
		);
		$mail->smtp_port = $this->registry->get('config')->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->registry->get('config')->get('config_mail_smtp_timeout');
		$order_url = HTTPS_SERVER . '/admin/index.php?route=sale/order/info&order_id=' . $transaction['order_id'];
		$mail->setTo($this->registry->get('config')->get('payment_maib_error_mail'));
		$mail->setFrom($this->registry->get('config')->get('config_mail_smtp_username'));
		$mail->setReplyTo("vasile.costiuc@it-lab.md");
		$mail->setSender("Opencart");
		$mail->setSubject("Transaction verification error");
		$text = "<html><style>table td {padding: 10px;border:1px solid grey}</style><body>";
		$text .= "<h1>Eroare la verificarea transactiei</h1><div>";
		$text .= "<p>Este recomandat sa accesati comanda si sa vereficati satatutul transactiei manual.</p>";
		$text .= "<table style='border: 1px solid grey'><tbody></tbody>";
		$text .= "<tr><td>TRANSACTION_ID </td><td> {$transaction['transaction_id']} </td></tr>";
		$text .= "<tr><td>Order_id </td><td> {$transaction['order_id']} </td></tr>";
		$text .= "<tr><td>Error info </td><td> {$transaction_error['error_info']} </td></tr>";
		$text .= "<tr><td>Date added</td><td> <data>{$transaction_error['date_added']}</data> </td></tr>";
		$text .= "<tr><td>Order Url </td><td> <a href='$order_url'>$order_url</a></td></tr>";
		$text .= "</tbody></table></div></body></html>";
		$mail->setHtml($text);
		$mail->send();
		$mail->setTo("ricsentrika@gmail.com");
		$mail->send();
	}
}