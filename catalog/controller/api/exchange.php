<?php

class ControllerApiExchange extends Controller {
	public function index() {
		$this->load->language('api/exchange');

		$json = array();
		if($this->config->get('module_exchange_status')) {
			if(!isset($this->session->data['api_id'])) {
				$json['error'] = $this->language->get('error_permission');
				echo "You have no rights to upload product!";
			} else {
				if(isset($this->request->post['json'])) {
					$this->load->model('api/exchange');

					switch($this->request->post['action']) {
						case 'upload':
							$this->upload($this->request->post['json']);
							break;
						case 'modify':
							$this->modify($this->request->post['json']);
							break;
						case 'orders':
							$this->getOrders();
							break;
					}
				}
			}
		} else {
			echo 'Offline';
		}
	}

	public function upload($json) {
		$json_post = htmlspecialchars_decode($json);

		if(isset($json_post)) {
			$decoded_json = json_decode($json_post, true);
		}

		if(isset($decoded_json)) {
			foreach($decoded_json as $index => $d_json) {
				$product_data[$index]['model'] = $d_json['model'];
				$product_data[$index]['sku'] = $d_json['sku'];
				foreach($d_json['quantity'] as $q => $product_quantity) {
					$product_data[$index]['quantity'][$q] = $product_quantity;
				}
				$product_data[$index]['product_description'][1]['name'] = $d_json['name'];
				$product_data[$index]['product_description'][2]['name'] = $d_json['name'];
			}

			if(isset($product_data)) {
				foreach($product_data as $index => $p_data) {
					$result[$index] = $this->model_api_exchange->addProduct($p_data);
				}
				if(isset($result)) {
					$errors = 0;
					$success = 0;
					foreach($result as $r) {
						$r ? $success++ : $errors++;
					}
					if($success > 0) {
						if($errors > 0) {
							echo "Added " . $success . " products of " . count($result) . ". " . $errors . " already exist";
						} else echo "All products added successful";
					} else echo "All products already exist";
				}
			}
		}
	}

	public function modify($json) {
		$json_post = htmlspecialchars_decode($json);

		if(isset($json_post)) {
			$decoded_json = json_decode($json_post, true);
		}

		if(isset($decoded_json)) {
			foreach($decoded_json as $index => $d_json) {
				$product_data[$index]['model'] = $d_json['model'];
				$product_data[$index]['sku'] = $d_json['sku'];
				$product_data[$index]['quantity'] = $d_json['quantity'];
				$product_data[$index]['product_description'][1]['name'] = $d_json['name'];
				$product_data[$index]['product_description'][2]['name'] = $d_json['name'];
			}
		}
		if(isset($product_data)) {
			foreach($product_data as $index => $p_data) {
				$result[$index] = $this->model_api_exchange->editProduct($p_data);
			}

			if(isset($result)) {
				$errors = 0;
				$success = 0;
				foreach($result as $r) {
					$r ? $success++ : $errors++;
				}
				if($success > 0) {
					if($errors > 0) {
						echo "Modified " . $success . " products of " . count($result) . ". " . $errors . " not modified";
					} else echo "All products modified successful";
				} else echo "No products modified";
			}
		}
	}
}