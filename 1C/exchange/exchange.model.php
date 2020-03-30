<?php

require_once "exchange.class.php";

class ModelExchange
{
    const STATUS_OUT_OF_STOCK = 5;
    const STATUS_SOON_AVAILABLE = 6;
    const STATUS_IN_STOCK = 7;
    const STATUS_PRE_ORDER = 8;

    /**
     * @var mysqli Database connection
     */
    private $connection;

    /**
     * ModelExchange constructor.
     * @param string $hostname
     * @param string $username
     * @param string $password
     * @param string $database
     * @param string $port
     * @throws Exception
     */
    public function __construct($hostname, $username, $password, $database, $port = '3306')
    {
        $this->connection = new \mysqli($hostname, $username, $password, $database, $port);

        if ($this->connection->connect_error) {
            throw new \Exception('Error: ' . $this->connection->error . '<br />Error No: ' . $this->connection->errno);
        }

        $this->connection->set_charset("utf8");
        $this->connection->query("SET SQL_MODE = ''");
    }

    /**
     * Optimize tables
     * @param array $tables
     * @throws Exception
     */
    public function optimize($tables = [])
    {
        count($tables) != 0 ?: $tables = $this->query(
            'SELECT table_name FROM information_schema.tables WHERE table_type = "base table"'
        );
        foreach ($tables as $table) {
            $this->query('OPTIMIZE TABLE ' . $table);
        }
    }

    /**
     * Execute SQL query
     * @param string $sql
     * @return bool|array
     * @throws Exception
     */
    public function query($sql)
    {
        $query = $this->connection->query($sql);

        if (!$this->connection->errno) {
            if ($query === true) {
                return true;
            }
            if ($query instanceof mysqli_result) {
                $data = [];

                while ($row = $query->fetch_assoc()) {
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
     * @return int
     */
    public function countAffected()
    {
        return $this->connection->affected_rows;
    }

    /**
     * Connection status
     * @return bool
     */
    public function connected()
    {
        return $this->connection->ping();
    }

    /**
     * ExchangeModel destructor
     * Closes connection
     */
    public function __destruct()
    {
        $this->connection->close();
    }

    /**
     * Update product
     * @param array $data
     * @throws Exception
     */
    public function update($data)
    {
        $sku_check = $this->query(
            "SELECT count(sku) from " . DB_PREFIX . "product where sku='" . $this->escape($data['sku']) . "'"
        );

        if ($sku_check['row']) {
            $this->editProduct($data);
        } else {
            $this->addProduct($data);
        }
    }

    /**
     * Escape html chars
     * @param string $value
     * @return string
     */
    public function escape($value)
    {
        return $this->connection->real_escape_string($value);
    }

    /**
     * Add new product
     * @param array $data
     * @throws Exception
     */
    public function addProduct($data)
    {
        $this->query(
            "INSERT INTO " . DB_PREFIX . "product SET category_1c = '" . $data['category_1c'] . "' model = '" . $this->escape(
                $data['model']
            ) . "', sku = '" . $this->escape(
                $data['sku']
            ) . "', quantity = '" . $data['quantity'] . "', price = " . $data['price'] . ", date_added = NOW(), date_modified = NOW() on duplicate key update quantity = '" . $data['quantity'] . "', price =" . $data['price'] . ",  date_modified = NOW()"
        );

        $product_id = $this->getLastId();

        foreach ($data['product_description'] as $language_id => $value) {
            $this->query(
                "INSERT IGNORE INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->escape(
                    $value['name']
                ) . "'"
            );
        }

        if ($data['price'] == 0) {
            $this->setStockStatus($product_id, self::STATUS_OUT_OF_STOCK);
        }
        $category = $this->query(
            "SELECT category_id, parent_id FROM oc_category WHERE category_1c = '{$data['category_1c']}'"
        )['row'];
        $category_id = $category['category_id'];
        $parent_id = $category['parent_id'] == 0 ? 0 : 1;
        $this->query(
            "UPDATE " . DB_PREFIX . "product SET model = '" . $this->escape(
                $data['model']
            ) . "', quantity = " . $data['quantity'] . ", price = " . $data['price'] . " WHERE sku = '" . $this->escape(
                $data['sku']
            ) . "'"
        );
        $this->query(
            "INSERT IGNORE INTO oc_product_to_category SET product_id = $product_id, category_id = $category_id, main_category = $parent_id"
        );
        $this->query(
            "INSERT IGNORE INTO oc_product_to_category SET product_id = $product_id, category_id = $category_id, main_category = $parent_id"
        );
    }

    /**
     * Get last inserted id
     * @return int
     */
    public function getLastId()
    {
        return $this->connection->insert_id;
    }

    /**
     * Edit existing product by known data
     * @param array $data
     * @throws Exception
     */
    public function editProduct($data)
    {
        $category = $this->query(
            "SELECT category_id, parent_id FROM oc_category WHERE category_1c = '{$data['category_1c']}'"
        )['row'];
        $category_id = $category['category_id'];
        $parent_id = $category['parent_id'] == 0 ? 0 : 1;
        $this->query(
            "UPDATE " . DB_PREFIX . "product SET model = '" . $this->escape(
                $data['model']
            ) . "', quantity = " . $data['quantity'] . ", price = " . $data['price'] . " WHERE sku = '" . $this->escape(
                $data['sku']
            ) . "'"
        );
        $product_id = $this->getLastId();
        $this->query(
            "INSERT IGNORE INTO oc_product_to_category SET product_id = $product_id, category_id = $category_id, main_category = $parent_id"
        );
    }

    public function categoryExists($category_1c)
    {
        return $this->query(
            "SELECT count(category_1c) as count_categories from oc_category WHERE category_1c = '$category_1c'"
        )['row']['count_categories'];
    }

    public function addCategory($data)
    {
        $parent_id = strlen($data['parent_id']) ? $data['parent_id'] : 0;

        $parent_category = $this->query(
                "SELECT category_id FROM oc_category WHERE category_1c = '$parent_id' and information = 0"
            )['row']['category_id'] ?? 0;
        $this->query(
            "INSERT INTO oc_category SET status= 1, category_1c = '{$data['category_id']}', parent_id = $parent_category"
        );
        $category_id = $this->getLastId();
        $this->query(
            "INSERT INTO oc_category_description SET category_id = $category_id, language_id = 1, name = '{$data['name']}', meta_title = '{$data['name']}'"
        );
        $this->query(
            "INSERT INTO oc_category_description SET category_id = $category_id, language_id = 2, name = '{$data['name']}', meta_title = '{$data['name']}'"
        );
        $this->query(
            "INSERT INTO oc_category_description SET category_id = $category_id, language_id = 3, name = '{$data['name']}', meta_title = '{$data['name']}'"
        );
        $this->query(
            "INSERT INTO oc_category_to_layout SET category_id = $category_id, store_id = 0, layout_id = 0"
        );
        $this->query(
            "INSERT INTO oc_category_to_store SET category_id = $category_id, store_id = 0"
        );

        $depth = $data['depth'];
        $current_parent = $category_id;
        var_dump($data['depth']);
        do {
            var_dump("INSERT INTO oc_category_path SET category_id = $category_id, path_id = $current_parent, level = $depth");
            $this->query(
                "INSERT INTO oc_category_path SET category_id = $category_id, path_id = $current_parent, level = $depth"
            );

            $current_parent = $this->query(
                "SELECT parent_id FROM oc_category WHERE category_id = $category_id"
            )['row']['parent_id'];
            var_dump($current_parent);
        } while (--$depth >= 0);

        return $category_id;
    }

    public function updateCategory($data)
    {
        $category = $this->query(
            "SELECT c.category_id FROM oc_category_description cd LEFT JOIN oc_category c on cd.category_id = c.category_id WHERE c.information = 0 and name = '{$data['name']}'"
        )['row'];

        if (isset($category['category_id']) && isset($data['category_id']) and $data['category_id']) {
            $this->query(
                "UPDATE oc_category SET category_1c = '{$data['category_id']}' WHERE category_id = {$category['category_id']}"
            );
            $this->query(
                "UPDATE oc_category_description SET name = '{$data['name']}' WHERE category_id = {$category['category_id']} and language_id = 1"
            );
        } else {
            $this->addCategory($data);
        }
    }

    public function dropOldCategories()
    {
        $categories = $this->query(
            "SELECT category_id FROM oc_category WHERE category_1c is null and information = 0"
        )['rows'];
        if ($categories) {
            foreach ($categories as $category) {
                $implode[] = $category['category_id'];
            }
        }
        if (isset($implode)) {
            $this->query("DELETE FROM oc_category_description where category_id in (" . implode(",", $implode) . ")");
            $this->query("DELETE FROM oc_category where category_id in (" . implode(",", $implode) . ")");
            $this->query(
                "DELETE FROM oc_product_to_category WHERE category_id in (" . implode(",", $implode) . ")"
            );
            $this->query("DELETE FROM oc_category_to_layout WHERE category_id in (" . implode(",", $implode) . ")");
            $this->query("DELETE FROM oc_category_to_store WHERE category_id in (" . implode(",", $implode) . ")");
            $this->query("DELETE FROM oc_category_path WHERE category_id in (" . implode(",", $implode) . ")");
        }
    }

    /**
     * @param $product_id
     * @param $stock_status_id
     * @throws Exception
     */
    public function setStockStatus(int $product_id, int $stock_status_id)
    {
        $this->query(
            "UPDATE " . DB_PREFIX . "product set stock_status_id = $stock_status_id WHERE product_id = $product_id"
        );
    }
}
