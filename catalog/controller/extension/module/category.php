<?php

class ControllerExtensionModuleCategory extends Controller {
	public function index() {
		$this->load->language('extension/module/category');

		if(isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		} else {
			$parts = array();
		}
		if(isset($parts[0])) {
			$data['category_id'] = $parts[0];
		} else {
			$data['category_id'] = 0;
		}

		if(isset($parts[1])) {
			$data['child_id'] = $parts[1];
		} else {
			$data['child_id'] = 0;
		}

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$data['categories'] = array();

		if(isset($this->request->get['manufacturer_id'])) {
			$manufacturer_id = (int)$this->request->get['manufacturer_id'];
		} else {
			$manufacturer_id = 0;
		}

		if($manufacturer_id) {
			$children_data = array();
			$manufacturer_categories = $this->model_catalog_category->getManufacturerCategories($manufacturer_id);
			foreach($manufacturer_categories as $manufacturer_category) {
				$children_data[] = array(
					'category_id' => $manufacturer_category['category_id'], 'name' => $manufacturer_category['name'], 'href' => $this->url->link('product/category', 'path=' . $manufacturer_category['category_id'] . '&' . 'tf_fm=' . $manufacturer_id)
				);
			}
			$data['categories'][] = array(
				'category_id' => -1, 'name' => '', 'children' => $children_data, 'href' => $this->url->link('product/category', 'path=')
			);
		} else {
			$categories = $this->model_catalog_category->getCategories($data['child_id'] ? $data['category_id'] : 0);
			foreach($categories as $category) {
				$children_data = array();

				if($category['category_id'] == $data['category_id'] || $category['category_id'] == $data['child_id']) {
					$children = $this->model_catalog_category->getCategories($category['category_id']);

					if(!sizeof($children)){
						$children = $this->model_catalog_category->getCategories($data['category_id']);
					}

					foreach($children as $child) {
						$filter_data = array('filter_category_id' => $child['category_id'], 'filter_sub_category' => true);

						$children_data[] = array(
							'category_id' => $child['category_id'], 'name' => $child['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''), 'href' => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])
						);
					}
				}
				$filter_data = array(
					'filter_category_id' => $category['category_id'], 'filter_sub_category' => true
				);

				$data['categories'][] = array(
					'category_id' => $category['category_id'], 'name' => $category['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''), 'children' => $children_data, 'href' => $this->url->link('product/category', 'path=' . $category['category_id'])
				);
			}
		}

		return $this->load->view('extension/module/category', $data);
	}

	public function getCategories() {

	}
}