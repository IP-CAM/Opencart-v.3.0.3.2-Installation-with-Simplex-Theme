<?php
class ControllerExtensionModuleBestSeller extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/bestseller');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		$data['products'] = array();

		$results = $this->model_catalog_product->getBestSellerProducts($setting['limit']);

		if(!$results) {
            $results = $this->model_catalog_product->getLatestProducts($setting['limit']);
        }



		if ($results) {
			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    /* added by it-lab start */
                    $special_percentage = round(100 - (($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax'))*100) / $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'))));
                    $economy= $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')) - $this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax'));
                    /* added by it-lab end */
				} else {
                    $special_percentage = false;
                    $economy = false;
				    $special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = $result['rating'];
				} else {
					$rating = false;
				}

				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
                    /* added by it-lab start */
                    'special_percentage' => $special_percentage,
                    'economy'     => $economy,
                    /* added by it-lab end */
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $result['product_id'])
				);
			}
            /* added by it-lab start */
            $data['currency'] = $this->session->data['currency'];
            /* added by it-lab end */
			return $this->load->view('extension/module/bestseller', $data);
		}
	}
}
