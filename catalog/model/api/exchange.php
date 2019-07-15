<?php

class ModelApiExchange extends Model {
	public function addProduct($data) {
		$sku_check = $this->db->query("SELECT sku from " . DB_PREFIX . "product where sku=" . $this->db->escape($data['sku']));

		if(empty($sku_check->row)) {
			$q = 0;
			foreach($data['quantity'] as $quantity) {
				$q += $quantity;
			}

			$this->db->query("INSERT INTO " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', quantity = '" . $q . "', date_added = NOW(), date_modified = NOW()");

			$product_id = $this->db->getLastId();

			foreach($data['product_description'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
			}

			$locations = $this->db->query("SELECT location_id FROM " . DB_PREFIX . "location");

			foreach($data['quantity'] as $index => $quantity) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_location SET quantity = " . $quantity . ", product_id = " . (int)$product_id . ", location_id = " . $locations->rows[$index - 1]['location_id']);
			}

			$this->cache->delete('product');

			return $product_id;
		} else return false;
	}

	public function editProduct($data) {
		$sku_check = $this->db->query("SELECT product_id,sku from " . DB_PREFIX . "product where sku=" . $this->db->escape($data['sku']));

		if(!empty($sku_check->row)) {
			$q = 0;
			foreach($data['quantity'] as $quantity) {
				$q += $quantity;
			}

			$this->db->query("UPDATE " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', quantity = " . $q . " WHERE sku = " . $this->db->escape($data['sku']));

			foreach($data['quantity'] as $index => $quantity) {
				$this->db->query("UPDATE " . DB_PREFIX . "product_to_location SET quantity = " . $quantity . " WHERE product_id = " . $sku_check->row['product_id']);
			}

			return true;
		} else return false;
	}

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
