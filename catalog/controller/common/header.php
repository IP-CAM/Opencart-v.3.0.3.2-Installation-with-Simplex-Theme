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
        }
        /* added by it-lab* start end */

        return $this->load->view('common/header', $data);
    }
}
