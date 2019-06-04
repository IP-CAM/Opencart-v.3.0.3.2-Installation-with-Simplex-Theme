<?php

/**
 * @package		MazaTheme
 * @author		Jay padaliya
 * @copyright           Copyright (c) 2017, TemplateMaza
 * @license		One domain license
 * @link		http://www.templatemaza.com
 */
class ModelExtensionModuleTfFilter extends model {
        /**
         * Get minimum price
         * @param String $product_table product table 
         * @return Int
         */
        public function getMinimumPrice($product_table){
                $query = $this->db->query("SELECT MIN(CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE price END) total FROM $product_table");
                
                return $query->row['total'];
        }
        
        /**
         * Get maximum price
         * @param String $product_table product table 
         * @return Int
         */
        public function getMaximumPrice($product_table){
                $query = $this->db->query("SELECT MAX(CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE price END) total FROM $product_table");
                
                return $query->row['total'];
        }
        
        /**
         * Get manufacturers for filter
         * @param String $product_table product table 
         * @param Array $data
         * @return Array
         */
        public function getManufacturers($product_table, $data = array()){
                $sql = 'SELECT m.manufacturer_id, m.name, m.image';
                
                if(!empty($data['field_total'])){
                    $sql .= ', COUNT(p.product_id) total';
                }
                
                $sql .= " FROM $product_table p LEFT JOIN " . DB_PREFIX . "manufacturer m ON (m.manufacturer_id = p.manufacturer_id)";
                
                if (!empty($data['filter_filter'])) {
                        $sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p.product_id = pf.product_id)";
                }
                
                $sql .= " WHERE m.manufacturer_id IS NOT NULL";
                
                if (isset($data['filter_in_stock']) && $data['filter_in_stock'] !== '') {
                    if($data['filter_in_stock']){
                        $sql .= " AND p.quantity != 0";
                    } else {
                        $sql .= " AND p.quantity = 0";
                    }
		}
                
                if (!empty($data['filter_filter'])) {
                        $filters = is_array($data['filter_filter'])?$data['filter_filter']:explode(',', $data['filter_filter']);

                        $sql .= " AND pf.filter_id IN (" . implode(',', array_map('intval', $filters)) . ")";
                }
                
                $sql .= " GROUP BY m.manufacturer_id";
                
                if(!empty($data['field_total'])){
                    $sql .= " ORDER BY m.sort_order ASC, total DESC";
                } else {
                    $sql .= " ORDER BY m.sort_order ASC";
                }
                
                $query = $this->db->query($sql);
                
                return $query->rows;
        }
        
        /**
         * Get total products by stock
         * @param String $product_table product table 
         * @param Bool $stock_status
         * @return Int
         */
        public function getTotalProductsByStock($product_table, $stock_status, $data = array()){
                $sql = "SELECT COUNT(*) total FROM $product_table p";
                
                if (!empty($data['filter_filter'])) {
                        $sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p.product_id = pf.product_id)";
                }
                
                if($stock_status){
                    $sql .= " WHERE p.quantity > 0";
                } else {
                    $sql .= " WHERE p.quantity <= 0";
                }
                
                if (!empty($data['filter_manufacturer_id'])) {
                    if(is_array($data['filter_manufacturer_id'])){
                        $sql .= " AND p.manufacturer_id IN (" . implode(',', array_map('intval', $data['filter_manufacturer_id'])) . ")";
                    } else {
                        $sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
                    }
		}
                
                if (!empty($data['filter_filter'])) {
                        $filters = is_array($data['filter_filter'])?$data['filter_filter']:explode(',', $data['filter_filter']);

                        $sql .= " AND pf.filter_id IN (" . implode(',', array_map('intval', $filters)) . ")";
                }
                
                $query = $this->db->query($sql);
                
                return $query->row['total'];
        }
        
        /**
         * Get opencart filters for filter
         * @param String $product_table product table 
         * @param Array $data 
         * @return Array
         */
        public function getFilters($product_table, $data = array()){
                $sql = "SELECT f.filter_id, f.filter_group_id, fd.name";
                
                if(!empty($data['field_total'])){
                    $sql .= ', COUNT(p.product_id) total';
                }
                
                $sql .= " FROM $product_table p";
                
                $sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "filter f ON (f.filter_id = pf.filter_id) LEFT JOIN " . DB_PREFIX . "filter_description fd ON (fd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND f.filter_id = fd.filter_id)";
                
                if(!empty($data['filter_category_id'])){
                    if(!empty($data['filter_sub_category'])){
                        $sql .= " LEFT JOIN " . DB_PREFIX . "category_filter cf ON (cf.filter_id = f.filter_id) LEFT JOIN " . DB_PREFIX . "category_path cp ON (cp.category_id = cf.category_id)";
                    } else {
                        $sql .= " LEFT JOIN " . DB_PREFIX . "category_filter cf ON (cf.filter_id = f.filter_id)";
                    }
                }
                
                $sql .= " WHERE f.filter_id IS NOT NULL";
                
                if(!empty($data['filter_category_id'])){
                    if(!empty($data['filter_sub_category'])){
                        $sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
                    } else {
                        $sql .= " AND cf.category_id = '" . (int)$data['filter_category_id'] . "'";
                    }
                }
                
                if (!empty($data['filter_manufacturer_id'])) {
                    if(is_array($data['filter_manufacturer_id'])){
                        $sql .= " AND p.manufacturer_id IN (" . implode(',', array_map('intval', $data['filter_manufacturer_id'])) . ")";
                    } else {
                        $sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
                    }
		}
                
                if (isset($data['filter_in_stock']) && $data['filter_in_stock'] !== '') {
                    if($data['filter_in_stock']){
                        $sql .= " AND p.quantity != 0";
                    } else {
                        $sql .= " AND p.quantity = 0";
                    }
		}
                
                $sql .= " GROUP BY f.filter_id";
                
                if(!empty($data['field_total'])){
                    $sql .= " ORDER BY f.sort_order ASC, total DESC";
                } else {
                    $sql .= " ORDER BY f.sort_order ASC";
                }
                
                $query = $this->db->query($sql);
                
                return $query->rows;
        }
        
        /**
         * Get list of filter group detail by ids
         * @param String $product_table product table 
         * @param Array $filter_group_ids
         * @return Array
         */
        public function getFilterGroups($filter_group_ids){
                if($filter_group_ids){
                    $query = $this->db->query("SELECT fg.filter_group_id, fg.sort_order, fgd.name FROM " . DB_PREFIX . "filter_group fg LEFT JOIN " . DB_PREFIX . "filter_group_description fgd ON (fgd.language_id = '" . $this->config->get('config_language_id') . "' AND fg.filter_group_id = fgd.filter_group_id) WHERE fg.filter_group_id IN (" . implode(",", array_map('intval', $filter_group_ids)) . ")");
                
                    return $query->rows;
                } else {
                    return array();
                }
        }
        
}
