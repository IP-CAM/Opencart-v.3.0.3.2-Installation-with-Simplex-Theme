<?php
require ('../index_cron.php');
require_once "TransactionRepository.php";
require_once "TransactionVerifier.php";
use Fruitware\MaibApi\MaibClient;
use Fruitware\MaibApi\MaibDescription;
use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Log\Formatter;
use GuzzleHttp\Subscriber\Log\LogSubscriber;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Handler\PHPConsoleHandler;
use Symfony\Component\Validator\Validator\RecursiveContextualValidator;
$repository=new TransactionRepository($registry);
$config=$repository->getConfig();
if(isset($config['payment_maib_status']) && $config['payment_maib_status']){
	$certificate_folder = str_replace('catalog/', '', DIR_APPLICATION) . $config['payment_maib_certificate_folder'];
	$verify = $certificate_folder . '/cacert.pem';
	$cert =  $certificate_folder . '/pcert.pem';
	$ssl_key =  $certificate_folder . '/key.pem';
	$password = $config['payment_maib_certificate_password'];
	$merchant_url = $config['payment_maib_merchant_url'];
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
	$maibClient = new MaibClient($guzzleClient);
	//var_dump($maibClient->closeDay());exit;
	$transactionVerifier=new TransactionVerifier($repository,$maibClient);
	echo '------------------VERIFICATION 20----------------------<br>';
	$transactionVerifier->verifyTransactions(20,1);
	echo '------------------VERIFICATION 40----------------------<br>';
	$transactionVerifier->verifyTransactions(40,2);
	//$transactionVerifier->verifyTransactions(40,2);

}
