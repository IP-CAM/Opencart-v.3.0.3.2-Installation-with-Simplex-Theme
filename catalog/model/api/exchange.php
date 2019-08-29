<?php

class ModelApiExchange extends Model {
	public function getOrder($order_id) {
		$order_query = $this->db->query("SELECT *, (SELECT os.name FROM `" . DB_PREFIX . "order_status` os WHERE os.order_status_id = o.order_status_id AND os.language_id = o.language_id) AS order_status FROM `" . DB_PREFIX . "order` o WHERE o.order_id = '" . (int)$order_id . "'");

		if($order_query->num_rows) {
			return array(
				'order_id'           => $order_query->row['order_id'],
				'firstname'          => $order_query->row['firstname'],
				'lastname'           => $order_query->row['lastname'],
				'email'              => $order_query->row['email'],
				'telephone'          => $order_query->row['telephone'],
				'custom_field'       => json_decode($order_query->row['custom_field'], true),
				'payment_city'       => $order_query->row['payment_city'],
				'payment_code'       => $order_query->row['payment_code'],
				'shipping_firstname' => $order_query->row['shipping_firstname'],
				'shipping_lastname'  => $order_query->row['shipping_lastname'],
				'shipping_company'   => $order_query->row['shipping_company'],
				'shipping_address_1' => $order_query->row['shipping_address_1'],
				'shipping_address_2' => $order_query->row['shipping_address_2'],
				'shipping_postcode'  => $order_query->row['shipping_postcode'],
				'shipping_city'      => $order_query->row['shipping_city'],
				'shipping_zone'      => $order_query->row['shipping_zone'],
				'shipping_code'      => $order_query->row['shipping_code'],
				'total'              => $order_query->row['total'],
				'date_added'         => $order_query->row['date_added'],
				'date_modified'      => $order_query->row['date_modified']
			);
		} else {
			return false;
		}
	}

	public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT product_id, name, model, quantity, price, total FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}

	public function sendOrder($url, $json, $login = "", $password = "") {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERPWD, $login . ":" . $password);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		//execute post
		$return = curl_exec($ch);
		//close connection
		curl_close($ch);

		return $return;
	}
}
