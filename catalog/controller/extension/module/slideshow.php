<?php
class ControllerExtensionModuleSlideshow extends Controller {
	public function index($setting) {
		static $module = 0;

		$this->load->model('design/banner');
		$this->load->model('tool/image');

		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
		$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.jquery.js');
		
		$data['banners'] = array();

		$results = $this->model_design_banner->getBanner($setting['banner_id']);

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$data['banners'][] = array(
					'title' => $result['title'],
					'link'  => $result['link'],
					'image' => $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height'])
				);
			}
		}

		$data['module'] = $module++;
        /* added by it-lab* start */

        $this->load->model('catalog/product');
        $this->load->model('catalog/category');
        $this->load->model('catalog/manufacturer');
        $this->load->model('tool/image');
        foreach ($data["banners"] as &$banner){
            if(!empty($banner['description'])){
                if(is_numeric($banner['description'])) {
                    $product_id = (int)$banner["description"];
                    $product = $this->model_catalog_product->getProduct($product_id);
                  //  var_dump($product);
                    if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                        $price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    } else {
                        $price = false;
                    }
                    $banner["price"] = $price;
                    if ((float)$product['special']) {
                        $special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                        $special_percentage = round(100 - (($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')) * 100) / $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'))));
                        $economy = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) - $this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax'));
                        $banner['special'] = $special;
                        $banner['special_percentage'] = $special_percentage;
                        $banner['economy'] = $economy;
                    } else {
                        $banner['special'] = false;                    }




                    $categories = $this->model_catalog_product->getCategories($product_id);
                    if ($categories) {
                        $categories_info = $this->model_catalog_category->getCategory($categories[0]['category_id']);
                        $banner['breadcrumb'] = array(
                            'text' => $categories_info['name'],
                            'href' => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $categories_info["category_id"])
                        );
                    }
                    $manufacturer_image = $this->model_catalog_manufacturer->getManufacturer($product['manufacturer_id']);
                    if($manufacturer_image){
                        $banner['manufacturer_img'] = $manufacturer_image['image'];
                    }else{
                        $banner['manufacturer_img'] = false;
                    }
                    $banner['link_product'] = $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product_id );
                }
            }
        }
        $data['currency'] = $this->session->data['currency'];
        /* added by it-lab* end */
		return $this->load->view('extension/module/slideshow', $data);
	}
}