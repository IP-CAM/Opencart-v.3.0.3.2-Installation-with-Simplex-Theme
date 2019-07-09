<?php
/**
 * @package    OptimBlog
 * @version    3.0.0.8
 * @author     Dmitriy Khokhlov <admin@optimlab.com>
 * @copyright  Copyright (c) 2018, Dmitriy Khokhlov. (http://optimlab.com/)
 * @license    https://opensource.org/licenses/GPL-3.0
 * @link       http://optimlab.com
 */
class ControllerExtensionModuleLatestInformation extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/latest_information');

		$this->load->model('catalog/information');

		$this->load->model('tool/image');

		$data['informations'] = array();

		$filter_data = array(
			'filter_category_id' => $setting['category_id'],
			'sort'               => 'i.date_added',
			'order'              => 'DESC',
			'start'              => 0,
			'limit'              => $setting['limit']
		);

		$results = $this->model_catalog_information->getInformations($filter_data);

		if ($results) {
			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
				} else {
					$image = false;
				}

				$data['informations'][] = array(
					'information_id' => $result['information_id'],
					'thumb'          => $image,
					'title'          => $result['title'],
					'description'    => !empty($result['short_description']) ? trim(html_entity_decode($result['short_description'], ENT_QUOTES, 'UTF-8')) : utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('information_description_length')) . '..',
					'user_id'        => $result['user_id'],
					'author'         => $result['author'],
					'date_added'     => self::getDateWithMonth($result['date_added'],$this->language->get('code')),
					'reviews'        => sprintf($this->language->get('text_review'), $result['reviews']),
					'rating'         => $result['rating'],
					'href'           => $this->url->link('information/information', '&information_id=' . $result['information_id'])
				);
				self::getDateWithMonth($result['date_added'],$this->language->get('code'));
			}

			if ($setting['title'][$this->config->get('config_language_id')]) {
				$data['heading_title'] = html_entity_decode($setting['title'][$this->config->get('config_language_id')]);
			}

			$data['author'] = $setting['author'];
			$data['date'] = $setting['date'];
			$data['review'] = $setting['review'];
			$data['rating'] = $setting['rating'];
            $data['href_category'] = $this->url->link('information/category', 'path=' . $setting['category_id']);
			return $this->load->view('extension/module/latest_information', $data);
		}
	}

	public static function getDateWithMonth($date,$lang){
	    $date=strtotime($date);
        $mon = date("n", $date);
        $day = date("d", $date);
        $year = date("Y", $date);


        $months = array(
            "ro" => ["ianuarie", "febuarie", "martie", "aprilie", "mai", "iunie", "iulie", "august", "septembrie", "octombrie", "noembrie", "decembrie"],
            "ru" => ["января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря"],
            "en" => ["january", "february", "march", "april ", "may", "june ", "july ", "august ", "september ", "october ", "november", "december"]
        );

        $mon_=$months[$lang][$mon-1];
        $date_str = "$day $mon_ $year";
        return $date_str;

    }
}
