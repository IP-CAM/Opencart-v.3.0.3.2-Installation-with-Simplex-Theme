<?php
/**
 * simplex_vasea - TransactionVerifier.php
 *
 * Created by Admin
 * Created on: 12.04.2020 0:03
 */
require_once "TransactionRepository.php";

use Fruitware\MaibApi\MaibClient;
use Fruitware\MaibApi\MaibDescription;
use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Log\Formatter;
use GuzzleHttp\Subscriber\Log\LogSubscriber;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Handler\PHPConsoleHandler;
use Symfony\Component\Validator\Validator\RecursiveContextualValidator;

class TransactionVerifier {

	private $transactionRepository;

	private $maibClient;

	public function __construct(TransactionRepository $repository, $maibClient) {
		$this->transactionRepository = $repository;
		$this->maibClient = $maibClient;
	}

	public function verifyTransactions($minutes, $verification_number = 1) {
		$transaction_for_verification = $this->transactionRepository->getTransactionsForVerification($minutes, $verification_number);
		foreach ($transaction_for_verification as $transaction) {
			$this->verifyTransaction($transaction,$verification_number);
		}
	}

	public function verifyTransaction($transaction, $verification_number) {
		echo "<br>verify transaction ($verification_number) : ".$transaction['transaction_id']."<br>";
		$transactionResult = $this->maibClient->getTransactionResult($transaction['transaction_id'], '127.0.0.1');
		print_r($transactionResult);
		if (isset($transactionResult['RESULT'])) {
			if ($transactionResult['RESULT'] == 'OK') {
				echo "<br> setOrderPaymentOK setTransactionVerifiedOK<br>";
				$this->transactionRepository->setOrderPaymentOK($transaction['order_id']);
				$this->transactionRepository->setTransactionVerifiedOK($transaction['transaction_id'], $verification_number);
			} else {
			    echo "<br> setTransactionVerified";
				$this->transactionRepository->setTransactionVerified($transaction['transaction_id'], $verification_number);
			}
		}
		echo "<br><br>";
	}

}