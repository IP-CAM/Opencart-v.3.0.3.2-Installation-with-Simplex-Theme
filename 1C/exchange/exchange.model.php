<?php

class ModelExchange {
    /**
     * @var mysqli Database connection
     */
    private $connection;
    
    /**
     * ModelExchange constructor.
     *
     * @param string $hostname
     * @param string $username
     * @param string $password
     * @param string $database
     * @param string $port
     *
     * @throws Exception
     */
    public function __construct($hostname, $username, $password, $database, $port = '3306') {
        $this->connection = new \mysqli($hostname, $username, $password, $database, $port);
        
        if($this->connection->connect_error) {
            throw new \Exception('Error: ' . $this->connection->error . '<br />Error No: ' . $this->connection->errno);
        }
        
        $this->connection->set_charset("utf8");
        $this->connection->query("SET SQL_MODE = ''");
    }
    
    /**
     * Optimize tables
     *
     * @param array $tables
     *
     * @throws Exception
     */
    public function optimize($tables = []) {
        sizeof($tables) != 0 ?: $tables = $this->query('SELECT table_name FROM information_schema.tables WHERE table_type = "base table"');
        foreach($tables as $table) {
            $this->query('OPTIMIZE TABLE ' . $table);
        }
    }
    
    /**
     * Execute SQL query
     *
     * @param string $sql
     *
     * @return bool|array
     * @throws Exception
     */
    public function query($sql) {
        $query = $this->connection->query($sql);
        
        if(!$this->connection->errno) {
            if($query === true) {
                return true;
            }
            if($query instanceof mysqli_result) {
                $data = [];
                
                while($row = $query->fetch_assoc()) {
                    $data[] = $row;
                }
                $result['num_rows'] = $query->num_rows;
                $result['row'] = isset($data[0]) ? $data[0] : [];
                $result['rows'] = $data;
                
                $query->close();
                
                return $result;
            }
        }
        
        throw new Exception("Database error! SQL: $sql; " . $this->connection->error);
    }
    
    /**
     * Affected rows count
     *
     * @return int
     */
    public function countAffected() {
        return $this->connection->affected_rows;
    }
    
    /**
     * Connection status
     *
     * @return bool
     */
    public function connected() {
        return $this->connection->ping();
    }
    
    /**
     * ExchangeModel destructor
     * Closes connection
     */
    public function __destruct() {
        $this->connection->close();
    }
    
    /**
     * Update product
     *
     * @param array $data
     *
     * @throws Exception
     */
    public function update($data) {
        $sku_check = $this->query("SELECT count(sku) from " . DB_PREFIX . "product where sku='" . $this->escape($data['sku']) . "'");
        
        if($sku_check['row']) {
            $this->editProduct($data);
        } else {
            $this->addProduct($data);
        }
    }
    
    /**
     * Escape html chars
     *
     * @param string $value
     *
     * @return string
     */
    public function escape($value) {
        return $this->connection->real_escape_string($value);
    }
    
    /**
     * Add new product
     *
     * @param array $data
     *
     * @throws Exception
     */
    public function addProduct($data) {
        $this->query("INSERT INTO " . DB_PREFIX . "product SET model = '" . $this->escape($data['model']) . "', sku = '" . $this->escape($data['sku']) . "', quantity = '" . $data['quantity'] . "', price = " . $data['price'] . ", date_added = NOW(), date_modified = NOW() on duplicate key update quantity = '" . $data['quantity'] . "', price =" . $data['price'] . ",  date_modified = NOW()");
        
        $product_id = $this->getLastId();
        
        foreach($data['product_description'] as $language_id => $value) {
            $this->query("INSERT IGNORE INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->escape($value['name']) . "'");
        }
    }
    
    /**
     * Get last inserted id
     *
     * @return int
     */
    public function getLastId() {
        return $this->connection->insert_id;
    }
    
    /**
     * Edit existing product by known data
     *
     * @param array $data
     *
     * @throws Exception
     */
    public function editProduct($data) {
        $this->query("UPDATE " . DB_PREFIX . "product SET model = '" . $this->escape($data['model']) . "', quantity = " . $data['quantity'] . ", price = " . $data['price'] . " WHERE sku = '" . $this->escape($data['sku']) . "'");
    }
}
