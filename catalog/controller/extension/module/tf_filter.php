<?php
class ControllerExtensionModuleTfFilter extends Controller {
        private $info = array(); // Module setting
        private $category_id = 0; // Active category id
        private $sub_category = 0; // Active category id
        private $route = 'common/home'; // Current route
        private $common_product_table = 'tf_temp_common_product'; // Temporary product table by common filter
        private $filter_product_table = ''; // Temporary product table by common filter and scaler filter
        private $common_param = array(); // Common get parameter of page
        private $filter_param = array(); // Filter parameter
        
        
        public function index($setting) {
                $this->load->model('extension/maza/tf_product');
                $this->load->model('extension/module/tf_filter');

                // Setting
                $this->info = $setting;
                $this->info['search_on_limit'] = 10;
                
                // Language
                $this->load->language('extension/module/tf_filter');
                $this->translate(); // Translate language
                
                $this->document->addStyle('catalog/view/javascript/maza/jquery-ui-1.12.1/jquery-ui.min.css');
                $this->document->addScript('catalog/view/javascript/maza/jquery-ui-1.12.1/jquery-ui.min.js');
                $this->document->addScript('catalog/view/javascript/maza/tf_filter.js');

                
                $this->loadData(); // Generate Data
                

                $data['filters']  =  array();

                // Price
                if($this->info['filter']['price']['status']){
                    $data['filters'][] = $this->getPriceFilter();
                }

                // Manufacturer
                if($this->info['filter']['manufacturer']['status']){
                    $data['filters'][] = $this->getManufacturerFilter();
                }

                // Availability
                if($this->info['filter']['availability']['status']){
                    $data['filters'][] = $this->getAvailabilityFilter();
                }

                // Filter
                if($this->info['filter']['filter']['status']){
                    $data['filters'] = array_merge($data['filters'], $this->getFilterFilter());
                }

                
                // Setting
                $data['count_product'] = $this->info['count_product'];
//                $data['ajax']          = $this->info['ajax'];
                $data['delay']         = $this->info['delay'];
                $data['reset_all']     = $this->info['reset_all'];
                $data['reset_group']   = $this->info['reset_group'];
                
                if(isset($this->request->get['description'])) {
                        $data['search_in_description'] = $this->request->get['description'];
                } else {
                        $data['search_in_description'] = 1;
                }
                
                $url = '';

                if (isset($this->request->get['filter'])) {
                        $url .= '&filter=' . $this->request->get['filter'];
                }
                
                if (isset($this->request->get['path'])) {
                        $url .= '&path=' . $this->request->get['path'];
                }
                
                if (isset($this->request->get['search'])) {
                        $url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
                }

                if (isset($this->request->get['tag'])) {
                        $url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
                }

                if (isset($this->request->get['description'])) {
                        $url .= '&description=' . $this->request->get['description'];
                }

                if (isset($this->request->get['category_id'])) {
                        $url .= '&category_id=' . $this->request->get['category_id'];
                }

                if (isset($this->request->get['sub_category'])) {
                        $url .= '&sub_category=' . $this->request->get['sub_category'];
                }
                
                if (isset($this->request->get['limit'])) {
                        $url .= '&limit=' . $this->request->get['limit'];
                }
                
                // Module setting
                $url .= '&sub_category=' . $this->sub_category;
                if ($this->info['count_product']) {
                        $url .= '&_count_product';
                }
                if($this->info['filter']['manufacturer']['status']){
                        $url .= '&_manufacturer';
                }
                if($this->info['filter']['availability']['status']){
                        $url .= '&_availability';
                }
                if($this->info['filter']['filter']['status']){
                        $url .= '&_filter';
                }
                
                $url .= '&target_route=' . $this->route;
                
                $data['requestURL'] = 'index.php?route=extension/module/tf_filter/product' . $url;

                $this->dropData(); // Delete data

                array_multisort(array_column($data['filters'], 'sort_order'), SORT_ASC, SORT_NUMERIC, $data['filters']);
                if($data['filters']){
                    return $this->load->view('extension/module/tf_filter', $data);
                }
        }

        /**
         * Price filter
         * @return Array
         */
        private function getPriceFilter(){
                $min_price = $selected_min = floor($this->currency->format($this->model_extension_module_tf_filter->getMinimumPrice($this->common_product_table), $this->session->data['currency'], null, false));
                $max_price = $selected_max = ceil($this->currency->format($this->model_extension_module_tf_filter->getMaximumPrice($this->common_product_table), $this->session->data['currency'], null, false));
                
                // Filter Price
                if(!empty($this->request->get['tf_fp'])){
                    $price = explode('p', $this->request->get['tf_fp']);
                    
                    if(!empty($price[0])){ // Minimum price
                        $selected_min = $price[0];
                    }
                    
                    if(!empty($price[1])){ // Maximum price
                        $selected_max = $price[1];
                    }
                }
                
                return array(
                        'type'       => 'price',
                        'sort_order' => $this->info['filter']['price']['sort_order'],
                        'collapse'   => $this->info['filter']['price']['collapse'],
                        'selected'   => array('min' => $selected_min, 'max' => $selected_max),
                        'min_price'  => $min_price,
                        'max_price'  => $max_price
                );
        }

        /**
         * Manufacturer filter
         * @return Array
         */
        private function getManufacturerFilter(){
                // user selected
                if(!empty($this->request->get['tf_fm'])){
                    $selected = explode('.', $this->request->get['tf_fm']);
                } else {
                    $selected = array();
                }
                
                $data = array(
                        'type'       => 'manufacturer',
                        'sort_order' => $this->info['filter']['manufacturer']['sort_order'],
                        'collapse'   => $this->info['filter']['manufacturer']['collapse'],
                        'values'     => array()
                );
                
                // Get post filtered manufacturer
                $post_filter_manufacturers = array(); // available manufacturers after applying filter
                
                if($this->filter_param){
                    $filter_data = $this->filter_param;
                    
                    unset($filter_data['filter_manufacturer_id']);
                    
                    if($filter_data){
                        $filter_data['field_total'] = $this->info['count_product'];
                        
                        $manufacturers = $this->model_extension_module_tf_filter->getManufacturers($this->filter_product_table?:$this->common_product_table, $filter_data);
                        
                        foreach($manufacturers as $manufacturer){
                            $post_filter_manufacturers[$manufacturer['manufacturer_id']] = $manufacturer;
                        }
                    }
                }
                
                // Get pre filtered manufacturer
                $manufacturers = $this->model_extension_module_tf_filter->getManufacturers($this->common_product_table, ['field_total' => $this->info['count_product']]);
                    
                
                
                foreach($manufacturers as $manufacturer){
                    $image = null;

                    $status = true;
                    $total = $this->info['count_product']?$manufacturer['total']:null;
                    
                    if($post_filter_manufacturers){
                        if(isset($post_filter_manufacturers[$manufacturer['manufacturer_id']])){
                            $status = true;
                            $total = $post_filter_manufacturers[$manufacturer['manufacturer_id']]['total'];
                        } else {
                            $status = false;
                            $total = $this->info['count_product']?0:null;
                        }
                    }

                    $data['values'][] = array(
                        'manufacturer_id' => $manufacturer['manufacturer_id'],
                        'name' => $manufacturer['name'],
                        'total' => $total,
                        'selected' => in_array($manufacturer['manufacturer_id'], $selected),
                        'status'   => $status
                    );
                }
                
                return $data;
        }

        /**
         * Availability filter
         * @return Array
         */
        private function getAvailabilityFilter(){
                // User selected
                if(isset($this->request->get['tf_fs'])){
                    $selected = $this->request->get['tf_fs'];
                } else {
                    $selected = null;
                }
                
                $total = array(); // Total product by stock status
                
                if($this->filter_param){ // Get post filted availability statua
                    $filter_data = $this->filter_param;
                    
                    unset($filter_data['filter_in_stock']);
                    
                    if($filter_data){
                        $total['in_stock'] = $this->model_extension_module_tf_filter->getTotalProductsByStock($this->filter_product_table?:$this->common_product_table, true, $filter_data);
                        $total['out_of_stock'] = $this->model_extension_module_tf_filter->getTotalProductsByStock($this->filter_product_table?:$this->common_product_table, false, $filter_data);
                    }
                }
                
                if(!$total) {
                    $total['in_stock'] = $this->model_extension_module_tf_filter->getTotalProductsByStock($this->common_product_table, true);
                    $total['out_of_stock'] = $this->model_extension_module_tf_filter->getTotalProductsByStock($this->common_product_table, false);
                }
                
                $values = array();
                $values['in_stock'] = array(
                    'total' => $this->info['count_product']?$total['in_stock']:null,
                    'selected' => (!is_null($selected) && $selected)?true:false,
                    'status' => (bool)$total['in_stock']
                );
                $values['out_of_stock'] = array(
                    'total' => $this->info['count_product']?$total['out_of_stock']:null,
                    'selected' => (!is_null($selected) && !$selected)?true:false,
                    'status' => (bool)$total['out_of_stock']
                );

                return array(
                        'type'       => 'availability',
                        'sort_order' => $this->info['filter']['availability']['sort_order'],
                        'collapse'   => $this->info['filter']['availability']['collapse'],
                        'values'     => $values
                );
        }


        /**
         * Filter filter
         * @return Array
         */
        private function getFilterFilter(){
                if($this->info['filter']['filter']['require_category'] && !$this->category_id){
                    return array(); // Can not get filter without category
                }
                
                // User selected
                if(!empty($this->request->get['tf_ff'])){
                    $selected = explode('.', $this->request->get['tf_ff']);
                } else {
                    $selected = array();
                }
                
                // Get Post filtered filters
                $post_filter_filter = array();
                
                if($this->filter_param){
                    $filter_data = $this->filter_param;
                    
                    unset($filter_data['filter_filter']);
                    
                    if($filter_data){
                        $filter_data['field_total'] = $this->info['count_product'];
                        $filters = $this->model_extension_module_tf_filter->getFilters($this->filter_product_table?:$this->common_product_table, $filter_data);
                        
                        foreach($filters as $filter){
                            $post_filter_filter[$filter['filter_id']] = $filter;
                        }
                    }
                }
                
                // Get filters list before apply filter
                $filter_data = array();
                $filter_data['field_total'] = $this->info['count_product'];
                $filter_data['filter_category_id'] = $this->category_id;
                $filter_data['filter_sub_category'] = $this->sub_category;

                $filters = $this->model_extension_module_tf_filter->getFilters($this->common_product_table, $filter_data);
                   
                // Organise filter values by filter group
                $filter_group_values = array();

                foreach($filters as $filter){
                    $total = $this->info['count_product']?$filter['total']:null;
                    $status = true;
                    
                    if($post_filter_filter){
                        if(isset($post_filter_filter[$filter['filter_id']])){
                            $status = true;
                            $total = $this->info['count_product']?$post_filter_filter[$filter['filter_id']]['total']:null;
                        } else {
                            $status = false;
                            $total = $this->info['count_product']?0:null;
                        }
                    }
                    
                    $filter_group_values[$filter['filter_group_id']]['values'][] = array(
                        'filter_id' => $filter['filter_id'],
                        'name' => $filter['name'],
                        'selected' => in_array($filter['filter_id'], $selected),
                        'total' => $total,
                        'status' => $status
                    );
                }

                unset($filters); // free memorary

                $filter_group_ids = array();

                foreach($filter_group_values as $filter_group_id =>  $filter_group){
                    $filter_group_ids[] = $filter_group_id;
                }

                $filter_groups = $this->model_extension_module_tf_filter->getFilterGroups($filter_group_ids);

                foreach($filter_groups as $key => &$filter_group){
                    if(isset($filter_group_values[$filter_group['filter_group_id']])){
                        
                        $filter_group['type'] = 'filter';
                        $filter_group['collapse'] = $this->info['filter']['filter']['collapse'];
                        $filter_group['values'] = $filter_group_values[$filter_group['filter_group_id']]['values'];
                        
                        unset($filter_group_values[$filter_group['filter_group_id']]); // Free memorary
                    } else {
                        unset($filter_groups[$key]);
                    }
                }

                return array_values($filter_groups);
        }

        /**
         * Create temporary table for products
         * and generate filter values
         */
        private function loadData(){
                // Category
                if(isset($this->request->get['category_id'])){
                    $this->category_id = $this->request->get['category_id'];
                } elseif (isset($this->request->get['path'])) {
                    $path = explode('_', (string)$this->request->get['path']);
                    $this->category_id = end($path);
		}
                
                if(isset($this->request->get['sub_category'])){
                    $this->sub_category = $this->request->get['sub_category'];
                } else {
                    $this->sub_category = 0;
		}

                // Route
                if(isset($this->request->get['route'])){
                    $this->route = $this->request->get['route'];
                }
                
                $this->common_param = $this->commonParam();
                $this->filter_param = $this->param();
                
                // Create temporary table for common filter
                $filter_param = array_merge($this->common_param, $this->filter_param);
                $additional_field = array();
                
                if(($this->info && $this->info['filter']['price']['status']) || isset($filter_param['filter_min_price']) || isset($filter_param['filter_max_price']) || isset($filter_param['filter_special'])){
                    $additional_field[] = 'price';
                }
                if(($this->info && $this->info['filter']['manufacturer']['status']) || isset($this->request->get['_manufacturer']) || isset($filter_param['filter_manufacturer_id'])){
                    $additional_field[] = 'p.manufacturer_id';
                }
                if(($this->info && $this->info['filter']['availability']['status']) || isset($this->request->get['_availability']) || isset($filter_param['filter_in_stock'])){
                    $additional_field[] = 'p.quantity';
                }
                
                $this->model_extension_maza_tf_product->createTempTable($this->common_product_table, $additional_field, $this->common_param);

                // Create temporary table for filter scaler parameter
                if(!empty($this->filter_param['filter_min_price']) || !empty($this->filter_param['filter_max_price']) || !empty($this->filter_param['filter_name'])){
                    $filter_data = $this->common_param;
                    
                    if(!empty($this->filter_param['filter_min_price'])){
                        $filter_data['filter_min_price'] = $this->filter_param['filter_min_price'];
                    }
                    
                    if(!empty($this->filter_param['filter_max_price'])){
                        $filter_data['filter_max_price'] = $this->filter_param['filter_max_price'];
                    }
                    
                    if(!empty($this->filter_param['filter_name'])){
                        $filter_data['filter_name'] = $this->filter_param['filter_name'];
                    }
                    
                    if (($key = array_search('p.price', $additional_field)) !== false) { // price field not require
                        unset($additional_field[$key]);
                    }
                    
                    $this->model_extension_maza_tf_product->createTempTable('tf_temp_filter_product', $additional_field, $filter_data);
                    
                    $this->filter_product_table = 'tf_temp_filter_product';
                }
                
        }

        private function dropData(){
                if($this->filter_product_table){
                    $this->model_extension_maza_tf_product->dropTempTable($this->filter_product_table);
                }
                
                $this->model_extension_maza_tf_product->dropTempTable($this->common_product_table);
        }
        
        private function getPostFilterValues(){
                $data = array();
                
                if(isset($this->request->get['_count_product'])){
                    $count_product = true;
                } else {
                    $count_product = false;
                }
                
                // Manufacturer
                $data['manufacturer'] = array();
                
                if(isset($this->request->get['_manufacturer'])){
                    $filter_data = $this->filter_param;
                    $filter_data['field_total'] = $count_product;
                    unset($filter_data['filter_manufacturer_id']);
                    
                    $manufacturers = $this->model_extension_module_tf_filter->getManufacturers($this->filter_product_table?:$this->common_product_table, $filter_data);

                    foreach($manufacturers as $manufacturer){
                        $data['manufacturer'][] = array(
                            'manufacturer_id' => $manufacturer['manufacturer_id'],
                            'total' => $count_product?$manufacturer['total']:null,
                        );
                    }
                }
                
                
                // Avaibility
                $data['availability'] = array();
                
                if(isset($this->request->get['_availability'])){
                    $filter_data = $this->filter_param;
                    unset($filter_data['filter_in_stock']);
                    
                    $data['availability']['in_stock'] = $this->model_extension_module_tf_filter->getTotalProductsByStock($this->filter_product_table?:$this->common_product_table, true, $filter_data);
                    $data['availability']['out_of_stock'] = $this->model_extension_module_tf_filter->getTotalProductsByStock($this->filter_product_table?:$this->common_product_table, false, $filter_data);
                }
                
                // Filter
                $data['filter'] = array();
                
                if(isset($this->request->get['_filter'])){
                    $filter_data = $this->filter_param;
                    $filter_data['field_total'] = $count_product;
                    $filter_data['filter_category_id'] = $this->category_id;
                    $filter_data['filter_sub_category'] = $this->sub_category;
                    
                    unset($filter_data['filter_filter']);

                    $filters = $this->model_extension_module_tf_filter->getFilters($this->filter_product_table?:$this->common_product_table, $filter_data);

                    foreach($filters as $filter){
                        $data['filter'][] = array(
                            'filter_id' => $filter['filter_id'],
                            'total' => $count_product?$filter['total']:null,
                        );
                    }
                }
                
                
                return $data;
        }
        
        private function commonParam(){
                $filter_data = array();
                
                if($this->category_id){
                        $filter_data['filter_category_id'] = $this->category_id;
                }

                if($this->sub_category){
                        $filter_data['filter_sub_category'] = $this->sub_category;
                }

                if (isset($this->request->get['search'])) {
			$filter_data['filter_name'] = $this->request->get['search'];
		}

                if (isset($this->request->get['tag'])) {
			$filter_data['filter_tag'] = $this->request->get['tag'];
		}

                if (isset($this->request->get['description'])) {
			$filter_data['filter_description'] = $this->request->get['description'];
		}

                if (isset($this->request->get['filter'])) {
			$filter_data['filter_filter'] = $this->request->get['filter'];
		}

                if (isset($this->request->get['manufacturer_id'])) {
			$filter_data['filter_manufacturer_id'] = (int)$this->request->get['manufacturer_id'];
		}

                if($this->route == 'product/special'){ // Special page
                    $filter_data['filter_special'] = 1;
                }
                
                return $filter_data;
        }
        
        /**
         * Get active filter parameter from URL
         */
        public function param($filter_data = array()){
                
                // Filter Price
                if(!empty($this->request->get['tf_fp'])){
                    $price = explode('p', $this->request->get['tf_fp']);
                    
                    if(isset($price[0])){ // Minimum price
                        $filter_data['filter_min_price'] = (int)$price[0] / $this->currency->getValue($this->session->data['currency']);
                    }
                    
                    if(isset($price[1])){ // Maximum price
                        $filter_data['filter_max_price'] = (int)$price[1] / $this->currency->getValue($this->session->data['currency']);
                    }
                }
                
                // Filter Manufacturer
                if(!empty($this->request->get['tf_fm'])){
                    $filter_data['filter_manufacturer_id'] = explode('.', $this->request->get['tf_fm']);
                }
                
                // Filter availability
                if(isset($this->request->get['tf_fs'])){
                    $filter_data['filter_in_stock'] = $this->request->get['tf_fs'];
                    
                    if (isset($this->request->get['description'])) {
                        $filter_data['filter_description'] = $this->request->get['description'];
                    }
                }
                
                // Filter Filter
                if(!empty($this->request->get['tf_ff'])){
                    $filter_data['filter_filter'] = explode('.', $this->request->get['tf_ff']);
                }
                
                // For special product page
                if(isset($this->request->get['route']) && $this->request->get['route'] == 'product/special'){
                    $filter_data['filter_special'] =  1;
                }
                
                return $filter_data;
        }
        
        /**
         * Add filter parameter in URL
         */
        public function url($url){
                
                // Filter Price
                if(!empty($this->request->get['tf_fp'])){
                    $url .= '&tf_fp=' . $this->request->get['tf_fp'];
                }
                
                // Filter Manufacturer
                if(!empty($this->request->get['tf_fm'])){
                    $url .= '&tf_fm=' . $this->request->get['tf_fm'];
                }
                
                // Filter availability
                if(isset($this->request->get['tf_fs']) && $this->request->get['tf_fs'] !== ''){
                    $url .= '&tf_fs=' . $this->request->get['tf_fs'];
                }
                
                // Filter filter
                if(!empty($this->request->get['tf_ff'])){
                    $url .= '&tf_ff=' . $this->request->get['tf_ff'];
                }
                
                return $url;
        }

        private function translate(){
                // Heading title
                if($this->info['title'] && !empty($this->info['title'][$this->config->get('config_language_id')])){
                    $this->language->set('heading_title', $this->info['title'][$this->config->get('config_language_id')]);
                }

                // Price
                if($this->info['filter']['price']['title'] && !empty($this->info['filter']['price']['title'][$this->config->get('config_language_id')])){
                    $this->language->set('text_price', $this->info['filter']['price']['title'][$this->config->get('config_language_id')]);
                }

                // Manufacturer
                if($this->info['filter']['manufacturer']['title'] && !empty($this->info['filter']['manufacturer']['title'][$this->config->get('config_language_id')])){
                    $this->language->set('text_manufacturer', $this->info['filter']['manufacturer']['title'][$this->config->get('config_language_id')]);
                }

                // Availability
                if($this->info['filter']['availability']['title'] && !empty($this->info['filter']['availability']['title'][$this->config->get('config_language_id')])){
                    $this->language->set('text_availability', $this->info['filter']['availability']['title'][$this->config->get('config_language_id')]);
                }

        }

}