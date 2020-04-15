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

	private $log;

	private $errorSender;

	public function __construct(TransactionRepository $repository, MaibClient $maibClient, Logger $log, ErrorSender $errorSender) {
		$this->transactionRepository = $repository;
		$this->maibClient = $maibClient;
		$this->log = $log;
		$this->errorSender = $errorSender;
	}

	public function verifyTransactions($minutes, $verification_number = 1) {
		$transaction_for_verification = $this->transactionRepository->getTransactionsForVerification($minutes, $verification_number);
		foreach ($transaction_for_verification as $transaction) {
			$this->verifyTransaction($transaction, $verification_number);
		}
	}

	public function verifyTransaction($transaction, $verification_number) {
		try {
			echo "<br>verify transaction ($verification_number) : " . $transaction['transaction_id'] . "<br>";
			$transactionResult = $this->maibClient->getTransactionResult($transaction['transaction_id'], '127.0.0.1');
			$this->log->addInfo(print_r($transactionResult, true));
			$transactionResult = ['error' => 'test error response'];
			if (isset($transactionResult['RESULT'])) {
				if ($transactionResult['RESULT'] == 'OK') {
					echo "<br> setOrderPaymentOK setTransactionVerifiedOK<br>";
					$this->transactionRepository->setOrderPaymentOK($transaction['order_id']);
					$this->transactionRepository->setTransactionVerifiedOK($transaction['transaction_id'], $verification_number, $transactionResult);
				} else {
					echo "<br> setTransactionVerified";
					$this->transactionRepository->setTransactionVerified($transaction['transaction_id'], $verification_number, $transactionResult);
				}
			} else {
				$data = [];
				$data['TRANSACTION_ID'] = $transaction['transaction_id'];
				$data['error_info'] = $transactionResult;
				$transaction_error_id = $this->transactionRepository->addTransactionError($data);
				$this->errorSender->sendErrorMail($transaction_error_id);
			}
			echo "<br><br>";
		}catch (\Exception $ex){
			$data = [];
			$data['TRANSACTION_ID'] = $transaction['transaction_id'];
			$data['error_info'] = ['exception_message'=>$ex->getMessage()];
			$transaction_error_id = $this->transactionRepository->addTransactionError($data);
			$this->errorSender->sendErrorMail($transaction_error_id);
		}
	}

}