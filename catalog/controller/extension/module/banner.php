<?php
class ControllerExtensionModuleBanner extends Controller {
	public function index($setting) {
		static $module = 0;

		$this->load->model('design/banner');
		$this->load->model('tool/image');

		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
		$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.jquery.js');

		$data['banners'] = array();

		$results = $this->model_design_banner->getBanner($setting['banner_id']);
        /* added by it-lab start */
        if(isset($this->request->get['route'])) {
            if ($this->request->get['route'] == 'common/home') {
                $data['is_home'] = true;
            } else {
                $data['is_home'] = false;
            }
            if ($this->request->get['route'] == 'product/category') {
                $data['is_catalog'] = true;
            }
        }else{
            $data['is_home']=true;
        }
        $language_id = $this->config->get('config_language_id');
        $data['title']=array_key_exists('title'.$language_id,$setting)?$setting['title'.$language_id]:'';
        $data['description']=array_key_exists('description'.$language_id,$setting)?html_entity_decode($setting['description'.$language_id]):'';
        $data['link_text']=array_key_exists('link'.$language_id,$setting)?$setting['link'.$language_id]:'';
        $data['link']=$setting["link"];
        if($setting["banner_type"]=="greu"){
            $data['contact'] = $this->url->link('information/contact');
            $this->load->model('localisation/location');
            $data['telephone'] = $this->config->get('config_telephone');
            $data['telephone_link']=str_replace(' ','',$data['telephone']);
            //$data['locations']=$this->model_localisation_location->getLocationDescriptions();
            $locations = $this->model_localisation_location->getLocationDescriptions();
            foreach ($locations as &$location){
                if(!$location['is_online']){
                    $location['contacts_link']=$data['contact']."#location-{$location['location_id']}";
                    $data['locations'][]=$location;
                }
            }
        }
        /* added by it-lab end */

        foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$data['banners'][] = array(
					'title' => $result['title'],
					'link'  => $result['link'],
					'image_original' => "image/".$result['image'],
					'description' => $result['description'],
					'image' => $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height'])
				);
			}
		}

		$data['module'] = $module++;

		return $this->load->view('extension/module/banner_'.$setting['banner_type'], $data);
	}
}