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

    private $active_products_ids;
    private $pructs_from_1C_ids=[];

    public function loadActiveProductsIds(){
    	$this->active_products_ids = array_column($this->query("select product_id from oc_product where status=1")['rows'],'product_id');
	}

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
            "SELECT count(sku) AS count from " . DB_PREFIX . "product where sku='" . $this->escape($data['sku']) . "'"
        );

/*		if (!isset($sku_check['row']['count']) || $sku_check['row']['count']==0)
		{
			$sku_check = $this->query(
				"SELECT count(sku) AS count from " . DB_PREFIX . "product where model='" . $this->escape($data['model']) . "'"
			);
		}*/

        if (isset($sku_check['row']['count']) && $sku_check['row']['count']) {
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
		$category = $this->query(
			"SELECT category_id, parent_id FROM oc_category WHERE category_1c = '{$data['category_1c']}'"
		)['row'];
		$this->query(
			"INSERT INTO " . DB_PREFIX . "product SET  model = '" . $this->escape(
				$data['model']
			) . "', sku = '" . $this->escape(
				$data['sku']
			) . "', quantity = '" . $data['quantity'] . "', price = " . $data['price'] . ", date_added = NOW(), date_modified = NOW() on duplicate key update quantity = '" . $data['quantity'] . "', price =" . $data['price'] . ",  date_modified = NOW()"
		);

		$product_id = $this->getLastId();
		$this->pructs_from_1C_ids[] = $product_id;
		foreach ($data['product_description'] as $language_id => $value) {
			$this->query(
				"INSERT IGNORE INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->escape(
					$value['name']
				) . "'"
			);
		}

		if ($data['price'] == 0 || $data['quantity']==0) {
			$this->setStockStatus($product_id, self::STATUS_OUT_OF_STOCK);
		}else{
			$this->setStockStatus($product_id, self::STATUS_IN_STOCK);
		}

		if(isset($category['category_id']) && strlen($category['category_id'])) {

			$category_id = $category['category_id'];
			$parent_id = $category['parent_id'];
			$this->query(
				"UPDATE " . DB_PREFIX . "product SET model = '" . $this->escape(
					$data['model']
				) . "', quantity = " . $data['quantity'] . ", price = " . $data['price'] . " WHERE sku = '" . $this->escape(
					$data['sku']
				) . "'"
			);
			$main_category = 1;
			$this->query(
				"INSERT IGNORE INTO oc_product_to_category SET product_id = $product_id, category_id = $category_id, main_category = $main_category"
			);
			$main_category = 1;
			while($parent_id!=0) {
				$category = $this->query(
					"SELECT category_id, parent_id FROM oc_category WHERE category_id = '$parent_id'"
				)['row'];

				$category_id = $category['category_id'];
				$parent_id = $category['parent_id'];
				$this->query(
					"INSERT IGNORE INTO oc_product_to_category SET product_id = $product_id, category_id = $category_id, main_category = $main_category"
				);
			}

		}else{
			var_dump("UNDEFINED CATEGORY on  add CATEGORY | {$data['category_1c']} | {$data['sku']}");
		}
    }

    /**
     * Get last inserted id
     * @return int
     */
    public function getLastId()
    {
        return $this->connection->insert_id;
    }

    private $edited_by_model_count=0;
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

		$result = $this->query(
			"SELECT product_id from " . DB_PREFIX . "product where sku='" . $this->escape($data['sku']) . "'"
		);
		$product_id = $result['row']['product_id'];
		$this->pructs_from_1C_ids[] = $product_id;
		$status = in_array($product_id,$this->active_products_ids)?1:0;
		$this->query(
			"UPDATE " . DB_PREFIX . "product SET model = '" . $this->escape(
				$data['model']
			) . "', quantity = " . $data['quantity'] . ", price = " . $data['price'] . ", status = " . $status . " WHERE sku = '" . $this->escape(
				$data['sku']
			) . "'"
		);
        if(isset($category['category_id']) && strlen($category['category_id'])) {
			$category_id = $category['category_id'];
			$parent_id = $category['parent_id'];
			$main_category = 1;
			$this->query(
			"INSERT IGNORE INTO oc_product_to_category SET product_id = $product_id, category_id = $category_id, main_category = $main_category"
			);
			$main_category = 0;
			while($parent_id!=0) {
				$category = $this->query(
					"SELECT category_id, parent_id FROM oc_category WHERE category_id = '$parent_id'"
				)['row'];

				$category_id = $category['category_id'];
				$parent_id = $category['parent_id'];
				$this->query(
				"INSERT IGNORE INTO oc_product_to_category SET product_id = $product_id, category_id = $category_id, main_category = $main_category"
				);

			}
		}else{
			var_dump("UNDEFINED CATEGORY on edit | {$data['category_1c']} | {$data['sku']}");
		}
    }

    public function categoryExists($category_1c)
    {
        return $this->query(
            "SELECT count(category_1c) as count_categories from oc_category WHERE category_1c = '$category_1c'"
        )['row']['count_categories'];
    }

    public function addCategory($data,$depth)
    {
        $parent_id = strlen($data['parent_id']) ? $data['parent_id'] : 0;

        $parent_category = $this->query(
                "SELECT category_id FROM oc_category WHERE category_1c = '$parent_id' and information = 0"
            )['row']['category_id'] ?? 0;

        $category_status=$depth>=2?0:1;
        $this->query(
            "INSERT INTO oc_category SET status=$category_status,top=0,`column`=1,information=0, category_1c = '{$data['category_id']}', parent_id = $parent_category"
        );
        var_dump(
        	"INSERT INTO oc_category SET status=$category_status,top=0,`column`=1,information=0, category_1c = '{$data['category_id']}', parent_id = $parent_category"
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

		$this->addCategoryPath($data,$category_id);
		return $category_id;
    }

    public function updateCategory($data,$depth)
    {
    	$oc_category=$this->query(
			"SELECT category_id,parent_id,status FROM oc_category WHERE category_1c = '{$data['category_id']}'")['row'];

		if ( isset($oc_category['category_id']) && isset($data['category_id']) and $data['category_id']) {
			$category_status=$depth>=2?0:1;

			$parent_id = strlen($data['parent_id']) ? $data['parent_id'] : 0;
			$parent_category = $this->query(
					"SELECT category_id FROM oc_category WHERE category_1c = '$parent_id' and information = 0"
				)['row']['category_id'] ?? 0;
			$this->query(
				"UPDATE oc_category SET status = $category_status, parent_id=$parent_category WHERE category_id = {$oc_category['category_id']}"
			);

			var_dump(
				"UPDATE oc_category SET status = $category_status, parent_id=$parent_category WHERE category_id = {$oc_category['category_id']}"
			);
			//var_dump("UPDATE oc_category SET status = $category_status WHERE category_id = {$oc_category['category_id']}");

			$this->query(
                "UPDATE oc_category_description SET name = '{$data['name']}' WHERE category_id = {$oc_category['category_id']} and language_id = 2"
            );
			$category_id = $oc_category['category_id'];
			$this->query("DELETE FROM oc_category_path WHERE category_id = $category_id");
			var_dump("DELETE FROM oc_category_path WHERE category_id = $category_id");
			$this->addCategoryPath($data,$category_id);

        } else {
            $this->addCategory($data,$depth);
        }
    }

    private function addCategoryPath($data,$category_id){
		$depth = $data['depth'];
		$current_parent = $category_id;
		$current_category = $category_id;
		do {
			var_dump("INSERT INTO oc_category_path SET category_id = $category_id, path_id = $current_parent, level = $depth");
			$this->query(
				"INSERT INTO oc_category_path SET category_id = $category_id, path_id = $current_parent, level = $depth"
			);

			$current_parent = $this->query(
				"SELECT parent_id, category_id FROM oc_category WHERE category_id = $current_category"
			)['row']['parent_id'];
			$current_category = $current_parent;
		} while (--$depth >= 0);
	}

    public function dropOldCategories()
    {
        $categories_low_level_to_delete = $this->query(
            "SELECT category_id FROM oc_category WHERE category_id IN((select category_id FROM oc_category_path GROUP BY category_id Having max(level)<2)) AND (category_1c is null and information = 0)"
        )['rows'];
        $categories_high_level_to_delete = $this->query(
			"SELECT category_id FROM oc_category WHERE (NOT (category_1c is null)  and information = 0) AND category_id IN ( SELECT category_id FROM oc_category_path WHERE level>=2)"
		)['rows'];

        var_dump('categories_low_level_to_delete : ',$categories_low_level_to_delete);
        var_dump('categories_high_level_to_delete : ',$categories_high_level_to_delete);
        $implode_low=array_column($categories_low_level_to_delete,'category_id');
		$implode_high=array_column($categories_high_level_to_delete,'category_id');
		$implode=array_merge($implode_low,$implode_high);

        if (count($implode)) {
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

    public function dropHighLevelCategories() {
		$categories = $this->query(
				 "SELECT category_id FROM oc_category WHERE (NOT (category_1c is null)  and information = 0) AND category_id IN ( SELECT category_id FROM oc_category_path WHERE level>=2)"
		)['rows'];

		if ($categories) {
			$high_level_categories_to_delete = array_column($categories,'category_id');
		}

		if (isset($high_level_categories_to_delete) && count($high_level_categories_to_delete)) {
			var_dump('high level categories to delete at end :'.count($high_level_categories_to_delete));

			$this->query("DELETE FROM oc_category_description where category_id in (" . implode(",", $high_level_categories_to_delete) . ")");
            $this->query("DELETE FROM oc_category where category_id in (" . implode(",", $high_level_categories_to_delete) . ")");
            $this->query(
				"DELETE FROM oc_product_to_category WHERE category_id in (" . implode(",", $high_level_categories_to_delete) . ")"
			);
            $this->query("DELETE FROM oc_category_to_layout WHERE category_id in (" . implode(",", $high_level_categories_to_delete) . ")");
            $this->query("DELETE FROM oc_category_to_store WHERE category_id in (" . implode(",", $high_level_categories_to_delete) . ")");
            $this->query("DELETE FROM oc_category_path WHERE category_id in (" . implode(",", $high_level_categories_to_delete) . ")");
        }
	}

	public function dropInexistentIn1CCategories($categories){
		$categories_1c = array_column($categories,'category_id');
		$categories=[];
		foreach ($categories_1c as $category_1c) {
			if(strlen($category_1c)) {
				$categories[] = "'" . $category_1c . "'";
			}
		}
		if(count($categories)) {
			$sql = "SELECT category_id FROM oc_category WHERE (NOT (category_1c is null)  and information = 0)  AND category_id IN ( SELECT category_id FROM oc_category_path WHERE level<2)  AND category_1c NOT IN (" . implode(',', $categories) . ")";
			$categories_to_delete = $this->query($sql)['rows'];
			if(count($categories_to_delete)) {
				$implode = array_column($categories_to_delete, 'category_id');
				if (count($implode)) {
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
		}

	}

	public function setProductsStatusInactive() {
    	$this->query("UPDATE oc_product SET status=0");
	}

	public function setStatusInactiveForInexistentProducts(){
    	$inexistent_ids=array_diff($this->active_products_ids,$this->pructs_from_1C_ids);
    	var_dump($inexistent_ids);
    	if(count($inexistent_ids)){
    		$sql="UPDATE oc_product SET status=0 WHERE product_id IN (". implode(',',$inexistent_ids ).")";
			$this->query($sql);
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
