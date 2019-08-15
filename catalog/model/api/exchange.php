<?php

class ModelApiExchange extends Model {
	public function getOrder($order_id) {
		$sql = "SELECT firstname, lastname, telephone, email, CONCAT( shipping_address_1, ',', shipping_address_2, ' ', shipping_city ) AS delivery_address, payment_method, total FROM " . DB_PREFIX . "order WHERE order_id = " . $order_id;

		$query = $this->db->query($sql);
		$sql2 = "select * from " . DB_PREFIX . "order_product where order_id = " . $order_id;

		$query2 = $this->db->query($sql2);

		$query->row['products'] = $query2->rows;

		return $query->rows;
	}

	public function sendOrder($url, $json) {
		$params = array(
			'json' => $json,
		);

		$server = $url . "?action=send";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $server);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
		curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
		$params_string = '';
		if(is_array($params) && count($params)) {
			foreach($params as $key => $value) {
				$params_string .= $key . '=' . $value . '&';
			}
			rtrim($params_string, '&');
			curl_setopt($ch, CURLOPT_POST, count($params));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
		}
		//execute post
		$return = curl_exec($ch);
		//close connection
		curl_close($ch);

		return $return;
	}
}
