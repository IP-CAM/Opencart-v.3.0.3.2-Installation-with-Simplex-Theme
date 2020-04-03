<?php

class ControllerExtensionModuleSlideshow extends Controller
{
    public function index($setting)
    {
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
                    'link' => $result['link'],
                    'product_id' => $result['product_id'],
                    'image' => $this->model_tool_image->resize(
                        $result['image'],
                        $setting['width'],
                        $setting['height']
                    )
                );
            }
        }

        $data['module'] = $module++;
        /* added by it-lab* start */

        $this->load->model('catalog/product');
        $this->load->model('catalog/category');
        $this->load->model('catalog/manufacturer');
        $this->load->model('tool/image');
        $this->load->model('extension/menu/megamenu');
        foreach ($data["banners"] as &$banner) {
            if (!empty($banner['product_id'])) {
                if (is_numeric($banner['product_id'])) {
                    $product_id = (int)$banner["product_id"];
                    $product = $this->model_catalog_product->getProduct($product_id);
                    if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                        $price = $this->currency->format(
                            $this->tax->calculate(
                                $product['price'],
                                $product['tax_class_id'],
                                $this->config->get('config_tax')
                            ),
                            $this->session->data['currency']
                        );
                    } else {
                        $price = false;
                    }
                    $banner["price"] = $price;
                    if ((float)$product['special']) {
                        $special = $this->currency->format(
                            $this->tax->calculate(
                                $product['special'],
                                $product['tax_class_id'],
                                $this->config->get('config_tax')
                            ),
                            $this->session->data['currency']
                        );
                        $special_percentage = round(
                            100 - (($this->tax->calculate(
                                        $product['special'],
                                        $product['tax_class_id'],
                                        $this->config->get('config_tax')
                                    ) * 100) / $this->tax->calculate(
                                    $product['price'],
                                    $product['tax_class_id'],
                                    $this->config->get('config_tax')
                                ))
                        );
                        $economy = $this->tax->calculate(
                                $product['price'],
                                $product['tax_class_id'],
                                $this->config->get('config_tax')
                            ) - $this->tax->calculate(
                                $product['special'],
                                $product['tax_class_id'],
                                $this->config->get('config_tax')
                            );
                        $banner['special'] = $special;
                        $banner['special_percentage'] = $special_percentage;
                        $banner['economy'] = $economy;
                    } else {
                        $banner['special'] = false;
                    }
                    $categories = $this->model_catalog_product->getCategories($product_id);
                    if ($categories) {
                        $categories_info = $this->model_catalog_category->getCategory($categories[0]['category_id']);
                        $banner['breadcrumb'] = array(
                            'text' => $categories_info['name'],
                            'href' => $this->url->link(
                                'product/category',
                                'language=' . $this->config->get(
                                    'config_language'
                                ) . '&path=' . $categories_info["category_id"]
                            )
                        );
                    }

                    $manufacturer = $this->model_catalog_manufacturer->getManufacturer($product['manufacturer_id']);

                    if ($manufacturer) {
                        $banner['manufacturer_img'] = $this->model_tool_image->resize($manufacturer['image'], 0, 0);
                        $banner['manufacturer_link'] = $this->url->link(
                            'product/manufacturer/info',
                            '&manufacturer_id=' . $manufacturer['manufacturer_id']
                        );
                        $banner['link_product'] = $this->url->link('product/product', '&product_id=' . $product_id);
                    } else {
                        $banner['manufacturer_img'] = false;
                    }
                }
            }
        }
        $data['link_specials'] = $this->url->link('product/special');

        $data['currency'] = $this->session->data['currency'];
        $data['interval'] = $setting["interval"];
        $data['catalog'] = $this->model_extension_menu_megamenu->getSubMenu('catalog');
        $data['catalog_tree'] = $this->model_catalog_category->getCatalogTree();

        foreach ($data['catalog_tree'] as &$catalog_item) {
            $catalog_item['href'] = $this->url->link("product/category", ['path' => $catalog_item['path']]);
        }
        $this->load->language('product/special');
        $data['catalog_tree']['oferte'] = [
            'href' => $this->url->link("product/special"),
            'name' => $this->language->get('offers'),
            'image' => '',
            'image_svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g transform="translate(-1304 -1382)"><path d="M1.02-5.58A2.9,2.9,0,0,1,.24-7.725,3.243,3.243,0,0,1,.6-9.3a2.446,2.446,0,0,1,1-1A2.961,2.961,0,0,1,3.03-10.65a2.961,2.961,0,0,1,1.432.345,2.446,2.446,0,0,1,1,1,3.243,3.243,0,0,1,.36,1.575A2.9,2.9,0,0,1,5.04-5.58a2.72,2.72,0,0,1-2.01.78A2.72,2.72,0,0,1,1.02-5.58ZM9.195-10.5H11.73L4.56,0H2.025ZM3.5-6.727a2,2,0,0,0,.188-1,2,2,0,0,0-.187-1A.547.547,0,0,0,3.03-9.03a.547.547,0,0,0-.473.307,2,2,0,0,0-.187,1,2,2,0,0,0,.187,1,.547.547,0,0,0,.473.307A.547.547,0,0,0,3.5-6.727ZM8.715-.63a2.9,2.9,0,0,1-.78-2.145A3.243,3.243,0,0,1,8.3-4.35a2.446,2.446,0,0,1,1-1.005A2.961,2.961,0,0,1,10.725-5.7a2.961,2.961,0,0,1,1.433.345,2.446,2.446,0,0,1,1,1.005,3.243,3.243,0,0,1,.36,1.575,2.9,2.9,0,0,1-.78,2.145,2.72,2.72,0,0,1-2.01.78A2.72,2.72,0,0,1,8.715-.63ZM11.2-1.778a2,2,0,0,0,.188-1,2,2,0,0,0-.187-1,.547.547,0,0,0-.473-.308.547.547,0,0,0-.472.308,2,2,0,0,0-.187,1,2,2,0,0,0,.187,1,.547.547,0,0,0,.472.308A.547.547,0,0,0,11.2-1.778Z" transform="translate(1309.176 1398.636)"/><g transform="translate(1304 1382)" fill="none" stroke="#000" stroke-width="2"><circle cx="12" cy="12" r="12" stroke="none"/><circle cx="12" cy="12" r="11" fill="none"/></g></g></svg>',
            'parent_id' => 0
        ];

        /* added by it-lab* end */

        return $this->load->view('extension/module/slideshow', $data);
    }
}