<?php

class ControllerCommonHeader extends Controller
{
    public function index()
    {
        // Analytics
        $this->load->model('setting/extension');

        $data['analytics'] = array();

        $analytics = $this->model_setting_extension->getExtensions('analytics');

        foreach ($analytics as $analytic) {
            if ($this->config->get('analytics_' . $analytic['code'] . '_status')) {
                $data['analytics'][] = $this->load->controller(
                    'extension/analytics/' . $analytic['code'],
                    $this->config->get(
                        'analytics_' . $analytic['code'] . '_status'
                    )
                );
            }
        }

        if ($this->request->server['HTTPS']) {
            $server = $this->config->get('config_ssl');
        } else {
            $server = $this->config->get('config_url');
        }

        if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
            $this->document->addLink($server . 'image/' . $this->config->get('config_icon'), 'icon');
        }

        if (isset($this->request->get['information_id'])) {
            $this->load->model('catalog/information');

            $information_id = (int)$this->request->get['information_id'];

            $information_info = $this->model_catalog_information->getInformation($information_id);

            $data['thumb'] = (strlen($information_info['image']) > 0) ? 'image/' . $information_info['image'] : false;
        }

        $data['title'] = $this->document->getTitle();

        $data['base'] = $server;
        $data['description'] = $this->document->getDescription();
        $data['keywords'] = $this->document->getKeywords();
        $data['links'] = $this->document->getLinks();
        $data['styles'] = $this->document->getStyles();
        $data['scripts'] = $this->document->getScripts('header');
        $data['lang'] = $this->language->get('code');
        $data['direction'] = $this->language->get('direction');

        $data['name'] = $this->config->get('config_name');

        $data['url'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";


        if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
            $data['logo'] = $server . 'image/' . $this->config->get('config_logo');
        } else {
            $data['logo'] = '';
        }

        $this->load->language('common/header');

        // Wishlist
        if ($this->customer->isLogged()) {
            $this->load->model('account/wishlist');
            $data['text_wishlist'] = sprintf(
                $this->language->get('text_wishlist'),
                $this->model_account_wishlist->getTotalWishlist()
            );
            $data['wishlist_count'] = $this->model_account_wishlist->getTotalWishlist();
        } else {
            $data['text_wishlist'] = sprintf(
                $this->language->get('text_wishlist'),
                (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0)
            );
            $data['wishlist_count'] = (isset($this->session->data['wishlist']) ? count(
                $this->session->data['wishlist']
            ) : 0);
        }

        if (!isset($this->session->data['compare'])) {
            $data['compare_count'] = 0;
        } else {
            $data['compare_count'] = count($this->session->data['compare']);
        }
        $data['text_logged'] = sprintf(
            $this->language->get('text_logged'),
            $this->url->link('account/account', '', true),
            $this->customer->getFirstName(),
            $this->url->link('account/logout', '', true)
        );

        $data['home'] = $this->url->link('common/home');

        /*IT-LAB*/
        $data['compare'] = $this->url->link('product/compare');
        /*END*/

        $data['wishlist'] = $this->url->link('account/wishlist', '', true);
        $data['logged'] = $this->customer->isLogged();
        $data['account'] = $this->url->link('account/account', '', true);
        $data['register'] = $this->url->link('account/register', '', true);
        $data['login'] = $this->url->link('account/login', '', true);
        $data['order'] = $this->url->link('account/order', '', true);
        $data['transaction'] = $this->url->link('account/transaction', '', true);
        $data['download'] = $this->url->link('account/download', '', true);
        $data['logout'] = $this->url->link('account/logout', '', true);
        $data['shopping_cart'] = $this->url->link('checkout/cart');
        $data['checkout'] = $this->url->link('checkout/checkout', '', true);
        $data['contact'] = $this->url->link('information/contact');
        $data['telephone'] = $this->config->get('config_telephone');

        $data['language'] = $this->load->controller('common/language');
        $data['currency'] = $this->load->controller('common/currency');
        $data['search'] = $this->load->controller('common/search');
        $data['cart'] = $this->load->controller('common/cart');
        $data['menu'] = $this->load->controller('common/menu');

        /* added by it-lab* start */

        $data['cart_count'] = $this->cart->countProducts();
        $this->load->model('extension/menu/megamenu');
        $data['pavmegamenu'] = $this->load->controller('extension/module/pavmegamenu');
        $data['catalog'] = $this->model_extension_menu_megamenu->getSubMenu('catalog');
        $this->load->model('catalog/category');
        $data['catalog_tree'] = $this->model_catalog_category->getCatalogTree();


        foreach ($data['catalog_tree'] as &$catalog_item) {
			$catalog_item['href'] = $this->url->link("product/category", ['path' => $catalog_item['path']]);
/*			if ($catalog_item['depth'] == 0) {
				$category_images = $this->model_catalog_category->getCategoryImages($catalog_item['category_id']);
				if(count($category_images)){

					$url=$category_images[0]['link'];
					$catalog_item['banners']['big']['link']=$url;
					$catalog_item['banners']['big']['image']='image/' . $category_images[0]['image'];
				}
			}*/
		}
        $this->load->language('product/special');
        $data['catalog_tree']['oferte'] = [
            'href' => $this->url->link("product/special"),
            'name' => $this->language->get('offers'),
            'image' => '',
            'image_svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g transform="translate(-1304 -1382)"><path d="M1.02-5.58A2.9,2.9,0,0,1,.24-7.725,3.243,3.243,0,0,1,.6-9.3a2.446,2.446,0,0,1,1-1A2.961,2.961,0,0,1,3.03-10.65a2.961,2.961,0,0,1,1.432.345,2.446,2.446,0,0,1,1,1,3.243,3.243,0,0,1,.36,1.575A2.9,2.9,0,0,1,5.04-5.58a2.72,2.72,0,0,1-2.01.78A2.72,2.72,0,0,1,1.02-5.58ZM9.195-10.5H11.73L4.56,0H2.025ZM3.5-6.727a2,2,0,0,0,.188-1,2,2,0,0,0-.187-1A.547.547,0,0,0,3.03-9.03a.547.547,0,0,0-.473.307,2,2,0,0,0-.187,1,2,2,0,0,0,.187,1,.547.547,0,0,0,.473.307A.547.547,0,0,0,3.5-6.727ZM8.715-.63a2.9,2.9,0,0,1-.78-2.145A3.243,3.243,0,0,1,8.3-4.35a2.446,2.446,0,0,1,1-1.005A2.961,2.961,0,0,1,10.725-5.7a2.961,2.961,0,0,1,1.433.345,2.446,2.446,0,0,1,1,1.005,3.243,3.243,0,0,1,.36,1.575,2.9,2.9,0,0,1-.78,2.145,2.72,2.72,0,0,1-2.01.78A2.72,2.72,0,0,1,8.715-.63ZM11.2-1.778a2,2,0,0,0,.188-1,2,2,0,0,0-.187-1,.547.547,0,0,0-.473-.308.547.547,0,0,0-.472.308,2,2,0,0,0-.187,1,2,2,0,0,0,.187,1,.547.547,0,0,0,.472.308A.547.547,0,0,0,11.2-1.778Z" transform="translate(1309.176 1398.636)"/><g transform="translate(1304 1382)" fill="none" stroke="#000" stroke-width="2"><circle cx="12" cy="12" r="12" stroke="none"/><circle cx="12" cy="12" r="11" fill="none"/></g></g></svg>',
            'parent_id' => 0
        ];
        /* added by it-lab* start end */
		/* added by it-lab start */
		$this->load->model('localisation/location');
		$data['telephone'] = $this->config->get('config_telephone');

		//$data['locations']=$this->model_localisation_location->getLocationDescriptions();
		$locations = $this->model_localisation_location->getLocationDescriptions();
		foreach ($locations as &$location){
			if(!$location['is_online']){
				$location['contacts_link']=$data['contact']."#location-{$location['location_id']}";
				$data['locations'][]=$location;
			}else{
				$data['open'] = html_entity_decode($location['open'], ENT_QUOTES, 'UTF-8');
			}

		}
		/* added by it-lab end */

        return $this->load->view('common/header', $data);
    }
}
