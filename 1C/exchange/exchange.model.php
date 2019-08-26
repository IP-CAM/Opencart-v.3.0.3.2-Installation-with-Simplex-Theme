<?php

class ModelExchange {
	private $connection;

	public function __construct($hostname, $username, $password, $database, $port = '3306') {
		$this->connection = new \mysqli($hostname, $username, $password, $database, $port);

		if($this->connection->connect_error) {
			throw new \Exception('Error: ' . $this->connection->error . '<br />Error No: ' . $this->connection->errno);
		}

		$this->connection->set_charset("utf8");
		$this->connection->query("SET SQL_MODE = ''");
	}

	public function query($sql) {
		$query = $this->connection->query($sql);

		if(!$this->connection->errno) {
			if($query instanceof \mysqli_result) {
				$data = array();

				while($row = $query->fetch_assoc()) {
					$data[] = $row;
				}
				$result['num_rows'] = $query->num_rows;
				$result['row'] = isset($data[0]) ? $data[0] : array();
				$result['rows'] = $data;

				$query->close();

				return $result;
			} else {
				return true;
			}
		}
	}

	public function optimize($tables = array()) {
		sizeof($tables) != 0 ?: $tables = $this->query('SELECT table_name FROM information_schema.tables WHERE table_type = "base table"');
		foreach($tables as $table) {
			$this->query('OPTIMIZE TABLE ' . $table);
		}
	}

	public function escape($value) {
		return $this->connection->real_escape_string($value);
	}

	public function countAffected() {
		return $this->connection->affected_rows;
	}

	public function getLastId() {
		return $this->connection->insert_id;
	}

	public function connected() {
		return $this->connection->ping();
	}

	public function __destruct() {
		$this->connection->close();
	}

	public function update($data) {
		$sku_check = $this->query("SELECT sku from " . DB_PREFIX . "product where sku='" . $this->escape($data['sku']) . "'");
		if(empty($sku_check->row)) {
			$this->addProduct($data);
		} else $this->editProduct($data);
	}

	public function addProduct($data) {
		$q = $data['quantity'];

		$this->query("INSERT INTO " . DB_PREFIX . "product SET model = '" . $this->escape($data['model']) . "', sku = '" . $this->escape($data['sku']) . "', quantity = '" . $q . "', date_added = NOW(), date_modified = NOW() on duplicate key update quantity = '" . $q . "',  date_modified = NOW()");

		$product_id = $this->getLastId();

		foreach($data['product_description'] as $language_id => $value) {
			$this->query("INSERT IGNORE INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->escape($value['name']) . "'");
		}
	}

	public function editProduct($data) {
		$q = $data['quantity'];

		$this->query("UPDATE " . DB_PREFIX . "product SET model = '" . $this->escape($data['model']) . "', quantity = " . $q . " WHERE sku = '" . $this->escape($data['sku']) . "'");
	}
}
