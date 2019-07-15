<?php
	require 'common.php';
	$connection = new Common();
	if(isset($_GET['action'])) {
		if($_GET['action'] == 'login') {
			$data = $connection->login();
		}
		elseif($_GET['action'] == 'send'){
			$data = $connection->receive($_POST);
		}
		else {
			$data = $connection->action($_GET['action'], $_GET['json']);
		}
	} else return 'Action error';
//[{"sku":"67858","model":"Product 666","quantity":"999","name":"Xiaomi Mi Mix"},{"sku":"67857","model":"Product 666","quantity":"999","name":"Xiaomi Mi Mix 2"}]
	print_r($data);