<?php

class ControllerExtensionModuleBanner extends Controller
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
        /* added by it-lab start */
        if (isset($this->request->get['route'])) {
            if ($this->request->get['route'] == 'common/home') {
                $data['is_home'] = true;
            } else {
                $data['is_home'] = false;
            }
            if ($this->request->get['route'] == 'product/category' || $this->request->get['route'] == 'product/manufacturer/info' || $this->request->get['route'] == 'product/latest' || $this->request->get['route'] == 'product/special' || $this->request->get['route'] == 'product/category') {
                $data['is_catalog'] = true;
            }
        } else {
            $data['is_home'] = true;
        }
        $language_id = $this->config->get('config_language_id');
        $data['title'] = array_key_exists('title' . $language_id, $setting) ? $setting['title' . $language_id] : '';
        $data['description'] = array_key_exists('description' . $language_id, $setting) ? html_entity_decode(
            $setting['description' . $language_id]
        ) : '';
        $data['link_text'] = array_key_exists('link' . $language_id, $setting) ? $setting['link' . $language_id] : '';
        $data['link'] = $setting["link"];
        if ($setting["banner_type"] == "greu") {
            $data['contact'] = $this->url->link('information/contact');
            $this->load->model('localisation/location');
            $data['telephone'] = $this->config->get('config_telephone');
            $data['telephone_link'] = str_replace(' ', '', $data['telephone']);
            //$data['locations']=$this->model_localisation_location->getLocationDescriptions();
            $locations = $this->model_localisation_location->getLocationDescriptions();
            foreach ($locations as &$location) {
                if (!$location['is_online']) {
                    $location['contacts_link'] = $data['contact'] . "#location-{$location['location_id']}";
                    $data['locations'][] = $location;
                }
            }
        }
        /* added by it-lab end */

        foreach ($results as $result) {
            if (is_file(DIR_IMAGE . $result['image'])) {
                $data['banners'][] = array(
                    'title' => $result['title'],
                    'link' => $result['link'],
                    'image_original' => "image/" . $result['image'],
                    'description' => $result['description'],
                    'image' => $this->model_tool_image->resize(
                        $result['image'],
                        $setting['width'],
                        $setting['height']
                    )
                );
            }
        }

        $data['module'] = $module++;

        $this->load->model("catalog/category");

        if (!isset($this->request->get['route']) || $this->request->get['route'] == 'common/home') {
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
        }

        return $this->load->view('extension/module/banner_' . $setting['banner_type'], $data);
    }
}