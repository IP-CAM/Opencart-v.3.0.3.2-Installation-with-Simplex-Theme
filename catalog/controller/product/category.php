<?php

class ControllerProductCategory extends Controller {
	/**
	 * @throws Exception
	 */
	public function index() {
		$this->load->language('product/category');

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		$filter = $this->request->get['filter'] ?? '';
		$sort = $this->request->get['sort'] ?? 'p.sort_order';
		$order = $this->request->get['order'] ?? 'ASC';
		$page = $this->request->get['page'] ?? 1;
		$limit = $this->request->get['limit'] ?? $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		$pager_limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_pager_limit') ?? $limit;

		//$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		if(isset($this->request->get['path'])) {
			$url = '';
			$url .= $this->request->get['sort'] ?? '';
			$url .= $this->request->get['order'] ?? '';
			$url .= $this->request->get['limit'] ?? '';

			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);

			foreach($parts as $path_id) {
				$path = $path ? '_' . (int)$path_id : (int)$path_id;

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'path=' . $path . $url)
					);
				}
			}
		} else {
			$category_id = 0;
		}

		$category_info = $this->model_catalog_category->getCategory($category_id);

		if($category_info) {
			$this->document->setTitle($category_info['meta_title']);
			$this->document->setDescription($category_info['meta_description']);
			$this->document->setKeywords($category_info['meta_keyword']);

			$data['heading_title'] = $category_info['name'];

			$data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));

			// Set the last category breadcrumb
			$data['breadcrumbs'][] = array(
				'text' => $category_info['name'],
				'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'])
			);

			if($category_info['image']) {
				$data['thumb'] = $this->model_tool_image->resize($category_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
			} else {
				$data['thumb'] = '';
			}

			$data['description'] = html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8');
			$data['compare'] = $this->url->link('product/compare');

			$url = '';
			$url .= $this->request->get['filter'] ?? '';
			$url .= $this->request->get['sort'] ?? '';
			$url .= $this->request->get['order'] ?? '';
			$url .= $this->request->get['limit'] ?? '';

			$data['categories'] = array();

			$results = $this->model_catalog_category->getCategories($category_id);
			foreach($results as $result) {
				$filter_data = array(
					'filter_category_id'  => $result['category_id'],
					'filter_sub_category' => true
				);

				$data['categories'][] = array(
					'name' => $result['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
					'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '_' . $result['category_id'] . $url)
				);
			}

			$data['products'] = array();
			/* added by it-lab start */
			if(isset($this->request->get['method']) && $this->request->get['method'] == "load_all") {
				$num_pages = $this->request->get['num_pages'];
				$limit_to_end = ($num_pages - $page + 1) * $limit;
			} else {
				$limit_to_end = $pager_limit;
			}
			/* added by it-lab end */

			$filter_data = array(
				'filter_category_id' => $category_id,
				'filter_filter'      => $filter,
				'sort'               => $sort,
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit_to_end
			);

			$product_total = $this->model_catalog_product->getTotalProducts($filter_data);

			$results = $this->model_catalog_product->getProducts($filter_data);
			/* added by it-lab start */
			$latest_products = $this->model_catalog_product->getLatestProducts($this->config->get('theme_' . $this->config->get('config_theme') . '_latest_count'));
			/* added by it-lab end */
			foreach($results as $result) {
				if($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				}

				if($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					/* added by it-lab start */
					$special_percentage = round(100 - (($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')) * 100) / $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'))));
					$economy = $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')) - $this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax'));
					/* added by it-lab end */
				} else {
					$special = false;
					/* added by it-lab start */
					$special_percentage = false;
					$economy = false;
					/* added by it-lab end */
				}

				if($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

//				if($this->config->get('config_review_status')) {
//					$rating = (int)$result['rating'];
//				} else {
//					$rating = false;
//				}
				$is_new = array_key_exists($result['product_id'], $latest_products);
				$data['products'][] = array(
					'product_id'         => $result['product_id'],
					'thumb'              => $image,
					'name'               => $result['name'],
					'description'        => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'              => $price,
					'special'            => $special,
					/* added by it-lab start */
					'special_percentage' => $special_percentage,
					'economy'            => $economy,
					'hide_price'         => $result['hide_price'] ? false : true,
					'is_new'             => $is_new,
					/* added by it-lab end */
					'tax'                => $tax,
					'minimum'            => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'             => $result['rating'],
					'href'               => $this->url->link('product/product', 'path=' . $this->request->get['path'] . '&product_id=' . $result['product_id'] . $url)
				);
			}
			/* added by it-lab start */
			$data['currency'] = $this->session->data['currency'];
			/* added by it-lab end */
			$url = '';
			$url .= $this->request->get['filter'] ?? '';
			$url .= $this->request->get['limit'] ?? '';

			$data['sorts'] = array();

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				'value' => 'p.sort_order-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.sort_order&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=pd.name&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=pd.name&order=DESC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=DESC' . $url)
			);

			if($this->config->get('config_review_status')) {
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_desc'),
					'value' => 'rating-DESC',
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=rating&order=DESC' . $url)
				);

				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_asc'),
					'value' => 'rating-ASC',
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=rating&order=ASC' . $url)
				);
			}

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.model&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_desc'),
				'value' => 'p.model-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.model&order=DESC' . $url)
			);

			/* added by it-lab start */

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_date_added_desc'),
				'value' => 'p.date_added-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.date_added&order=DESC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get("text_popular_desc"),
				'value' => 'order_quantity-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=order_quantity&order=DESC' . $url)
			);
			/* added by it-lab end */

			$url = '';
			$url .= $this->request->get['filter'] ?? '';
			$url .= $this->request->get['sort'] ?? '';
			$url .= $this->request->get['order'] ?? '';

			$data['limits'] = array();

			$limits = array_unique(array(
				$this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'),
				16,
				24,
				32
			));

			sort($limits);

			foreach($limits as $value) {
				$data['limits'][] = array(
					'text'  => $value,
					'value' => $value,
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url . '&limit=' . $value)
				);
			}

			//$url = '';
//			$url .= $this->request->get['filter'] ?? '';
//			$url .= $this->request->get['sort'] ?? '';
//			$url .= $this->request->get['order'] ?? '';
			$url .= $this->request->get['limit'] ?? '';
			$pager_limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_pager_limit') ?? $limit;

			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $pager_limit;
			$pagination->url = $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url . '&page={page}');

			$data['pagination'] = $pagination->render();

			//$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

			// http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
			$this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . ($page == 1 ? '&page=' . $page : '')), 'canonical');

			if($page > 1) {
				$this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . (($page - 2) ? '&page=' . ($page - 1) : '')), 'prev');
			}

			if($pager_limit && ceil($product_total / $limit) > $page) {
				$this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&page=' . ($page + 1)), 'next');
			}

			$data['sort'] = $sort;
			$data['order'] = $order;
			$data['limit'] = $limit;
			$data['pager_limit'] = $pager_limit;
			/* added by it-lab start */
			$data['product_total'] = $product_total;
			if(!isset($this->request->get['method']) || $this->request->get['method'] != "load_all") {
				$data['num_pages'] = ceil(($product_total - $limit) / $pager_limit) + 1;
			}
			$data['page'] = $page;
			$data['show_more_limit'] = ($product_total - $page * $pager_limit) < $pager_limit ? $product_total - $page * $pager_limit : $pager_limit;
			/* added by it-lab end */
			$data['continue'] = $this->url->link('common/home');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			/* added by it-lab start */
			if(isset($this->request->get['method'])) {
				$this->response->setOutput($this->load->view('product/load_more', $data));
			} else {
				/* added by it-lab end */
				$this->response->setOutput($this->load->view('product/category', $data));
			}
		} else {
			$url = '';

			$url .= $this->request->get['path'] ?? '';
			$url .= $this->request->get['filter'] ?? '';
			$url .= $this->request->get['sort'] ?? '';
			$url .= $this->request->get['order'] ?? '';
			$url .= $this->request->get['page'] ?? '';
			$url .= $this->request->get['limit'] ?? '';

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('product/category', $url)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
}
