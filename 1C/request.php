<?php

require_once __DIR__ . '/exchange/exchange.class.php';
require_once __DIR__ . '/exchange/exchange.model.php';
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Exception/InvalidJsonException.php';

define('MODULE_URL', HTTPS_SERVER . 'index.php?route=api/exchange/getModuleConfig');
define('LOGIN_URL', HTTPS_SERVER . 'index.php?route=api/login');

if (!function_exists('mb_ucfirst')) {
	function mb_ucfirst($str, $encoding = "UTF-8", $lower_str_end = false) {
		$first_letter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
        if ($lower_str_end) {
			$str_end = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding), $encoding);
		}
		else {
			$str_end = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
		}
		$str = $first_letter . $str_end;
		return $str;
	}
}

/**
 * Class Request
 */
class Request
{
    /**
     * @var int Successful Task counter
     */
    private $successful_tasks = 0;
    /**
     * @var int Skipped tasks counter
     */
    private $skipped_tasks = 0;
    /**
     * @var bool New product flag
     */
    private $flag = true;
    /**
     * @var Exchange 1C connection
     */
    private $connection;
    /**
     * @var ModelExchange
     */
    private $model;
    /**
     * @var false|resource Previous json
     */
    private $file;
    /**
     * @var string Previous json path
     */
    private $filename;
    /**
     * @var false|resource
     */
    private $logFile;

    /**
     * Request constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->connection = new Exchange(MODULE_URL, USERNAME, KEY, LOGIN_URL);
        $this->model = new ModelExchange(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
        $this->filename = 'json/previous.json';
        $this->file = fopen($this->filename, 'r+');
        $this->logFile = fopen("log/1c_log.log", "a+");
    }

    /**
     * Main action method.
     * Gets data and writes it into database, if changes found
     * @throws InvalidJsonException
     * @throws Exception
     */
    public function makeRequest()
    {
    	$this->model->dropOldCategories();
        $this->updateCategories();
		$this->model->loadActiveProductsIds();
		$decoded_json = json_decode($this->connection->request1C(), true);
        if (!isset($decoded_json)) {
            throw new InvalidJsonException("JSON not received or is invalid");
        }
        if (filesize($this->filename) == 0) {
            if (isset($decoded_json)) {
                fwrite($this->file, json_encode($decoded_json));
                self::rewriteJSONData($decoded_json);
                foreach ($decoded_json as $d_json) {
                    if ($d_json['quantity'] >= 0 && strlen($d_json['model']) > 0 && strlen($d_json['price']) > 0 && $d_json['price'] >= 0 && strlen($d_json['sku']) > 0) {
                        $this->model->update($d_json);
                        $this->successful_tasks++;
                    }
                    echo $this->successful_tasks . ' of ' . count($decoded_json) . "\n";
                }
            }
        } else {
            $previous_json = json_decode(file_get_contents($this->filename), true);

            if (isset($decoded_json) && isset($previous_json)) {
                fwrite($this->file, json_encode($decoded_json));
                self::rewriteJSONData($previous_json);
                self::rewriteJSONData($decoded_json);
                foreach ($decoded_json as $d_json) {
                    if ($d_json['quantity'] >= 0 && strlen($d_json['quantity']) > 0 && strlen($d_json['model']) > 0 && strlen($d_json['price']) > 0 && $d_json['price'] > 0 && strlen($d_json['sku']) > 0)
                    {
                        foreach ($previous_json as $p_json) {
                            if ($d_json['sku'] == $p_json['sku']) {
                                $this->flag = false;

                                if ($d_json['quantity'] == $p_json['quantity'] && $d_json['price'] == $p_json['price']) {
                                    $this->skipped_tasks++;
                                    break;
                                } else {
                                    $this->successful_tasks++;

                                    $this->model->update($d_json);
                                    $this->log($d_json);
                                }
                            }
                        }
                        if ($this->flag) {
                            $this->successful_tasks++;
                            $this->model->update($d_json);
                            $this->log($d_json);
                        }
                    }
                    echo $this->successful_tasks . ' of ' . count($decoded_json) . "\n";
                    echo "Skipped " . $this->skipped_tasks . ' of ' . count($decoded_json) . "\n\n";
                    $this->flag = true;
                }
            }
        }
        $this->model->dropHighLevelCategories();
        $this->model->setStatusInactiveForInexistentProducts();
    }

    /**
     * Rewrites json data as it fits to tables
     * @param array $array
     */
    protected static function rewriteJSONData(&$array)
    {
        $data = [];

        foreach ($array['#value']['row'] as $index => $arr) {
            $data[$index]['sku'] = $arr[0]['#value'];
            $data[$index]['model'] = $arr[1]['#value'];
            $data[$index]['quantity'] = (strlen($arr[2]['#value']) > 0) ? $arr[2]['#value'] : 0;
            $data[$index]['product_description'][1]['name'] = $arr[3]['#value'];
            $data[$index]['product_description'][2]['name'] = $arr[3]['#value'];
            $data[$index]['price'] = $arr[4]['#value'];
            $data[$index]['category_1c'] = $arr[5]['#value'];
        }
        $array = $data;
    }

    protected static function rewriteJSONCategories(&$array)
    {
        $data = [];

        foreach ($array['#value']['row'] as $index => $arr) {
            $data[$arr[0]['#value']]['category_id'] = $arr[0]['#value'];
            $data[$arr[0]['#value']]['name'] = mb_ucfirst(trim($arr[1]['#value']),'UTF-8',true);
            $data[$arr[0]['#value']]['parent_id'] = $arr[2]['#value'];
        }

        $tree = [];

        foreach ($data as $item) {
            if ($item['parent_id'] == '') {
                static::getCategoriesAsTree($data, $item, $tree);
            }
        }

        $array = $tree;
    }

    protected static function getCategoriesAsTree(array $data, $item, array &$tree = [], $depth = 0)
    {
        $item['depth'] = $depth;
        $tree[$item['category_id']] = $item;

        foreach ($data as $child) {
            if ($item['category_id'] == $child['parent_id'] && !in_array($child['category_id'], array_keys($tree))) {
                static::getCategoriesAsTree($data, $child, $tree, $depth + 1);
            }
        }
    }

    /**
     * Logs changes in database
     * @param string $product_json
     */
    public function log($product_json)
    {
        fwrite($this->logFile, "[" . date("Y-m-d H:m:s", time()) . "]" . json_encode($product_json));
    }

    /**
     * Restores previous state of database
     */
    public function restorePrevious()
    {
        if (isset($previous_json)) {
            self::rewriteJSONData($previous_json);
            foreach ($previous_json as $p_json) {
                $this->successful_tasks++;
                $this->model->update($p_json);
                echo $this->successful_tasks . ' of ' . count($previous_json) . "\n";
            }
        }
    }

    public function updateCategories()
    {
        $categories = json_decode($this->connection->requestCategories(), true);
        self::rewriteJSONCategories($categories);

        $depth = 0;
        do {
            $rewrite = false;
            foreach ($categories as $category) {
                if($category['depth'] == $depth) {
					$rewrite = true;
					$this->model->updateCategory($category,$depth);
                }
            }
            $depth++;
        } while($rewrite);
    }

    /**
     * Request destructor
     * Close files, connection. After all optimizations
     */
    public function __destruct()
    {
        fclose($this->file);

        echo "Optimizing tables \n";

        $this->model->optimize(
            [
                'product',
                'product_description'
            ]
        );
        fclose($this->logFile);
    }
}

$request = new Request();
if (strpos(ini_get("disable_functions"), "exec") !== false) {
    exec(
        "mysqldump -u" . DB_USERNAME . " -p" . DB_PASSWORD . " " . DB_DATABASE . " > " . date(
            "Y-m-d"
        ) . DB_DATABASE . ".sql"
    );
}
$request->makeRequest();
