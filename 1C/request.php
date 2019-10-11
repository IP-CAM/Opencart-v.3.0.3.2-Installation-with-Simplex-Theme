<?php
require 'exchange/exchange.class.php';
require 'exchange/exchange.model.php';
require '../config.php';

define('MODULE_URL', HTTP_SERVER . 'index.php?route=api/exchange/');

class Request1C {
	private $current_task = 0;
	private $skipped_task = 0;
	private $bool = true;
	private $connection;
	private $model;
	private $file;
	private $filename;

	public function __construct() {
		$this->connection = new Exchange(MODULE_URL . 'getModuleConfig');
		$this->model = new ModelExchange(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
		$this->filename = 'json/previous.json';
		$this->file = fopen($this->filename, 'r+');
	}

	protected static function rewriteJSONData(&$array) {
		$data = [];
		foreach($array['#value']['row'] as $index => $arr) {
			$data[$index]['sku'] = $arr[0]['#value'];
			$data[$index]['model'] = $arr[1]['#value'];
			$data[$index]['quantity'] = $arr[2]['#value'];
			$data[$index]['product_description'][1]['name'] = $arr[3]['#value'];
			$data[$index]['product_description'][2]['name'] = $arr[3]['#value'];
		}
		$array = $data;
	}

	public function makeRequest() {
		$decoded_json = json_decode($this->connection->request1C(), true);

		if(filesize($this->filename) == 0) {
			fwrite($this->file, json_encode($decoded_json));
			if(isset($decoded_json)) {
				self::rewriteJSONData($decoded_json);
				foreach($decoded_json as $d_json) {
					$this->current_task++;
					$this->model->update($d_json);
					echo $this->current_task . ' of ' . sizeof($decoded_json) . "\n";
				}
			}
		} else {
			$previous_json = json_decode(fread($this->file, filesize($this->filename)), true);
			if(isset($decoded_json) && isset($previous_json)) {
				self::rewriteJSONData($previous_json);
				self::rewriteJSONData($decoded_json);

				foreach($decoded_json as $d_json) {
					foreach($previous_json as $p_json) {
						if($d_json['sku'] == $p_json['sku']) {
							$this->bool = false;
							if($d_json['quantity'] == $p_json['quantity']) {
								$this->skipped_task++;
								break;
							} else {
								$this->current_task++;
								$this->model->update($d_json);
							}
						}
					}
					if($this->bool) {
						$this->current_task++;
						$this->model->update($d_json);
					}
					echo $this->current_task . ' of ' . count($decoded_json) . "\n";
					echo "Skipped " . $this->skipped_task . ' of ' . count($decoded_json) . "\n\n";
					$this->bool = true;
				}
			}
		}
	}

	public function restorePrevious(){
		if(isset($previous_json)) {
			self::rewriteJSONData($previous_json);
			foreach($previous_json as $p_json) {
				$this->current_task++;
				$this->model->update($p_json);
				echo $this->current_task . ' of ' . count($previous_json) . "\n";
			}
		}
	}

	public function __destruct() {
		fclose($this->file);

		echo "Optimizing tables \n";

		$tables = array(
			'product',
			'product_description'
		);
		$this->model->optimize($tables);
	}
}

$request = new Request1C();

$request->makeRequest();
