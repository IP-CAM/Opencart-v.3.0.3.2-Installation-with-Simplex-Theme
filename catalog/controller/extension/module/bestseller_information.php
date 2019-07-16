<?php
/**
 * @package    OptimBlog
 * @version    3.0.0.8
 * @author     Dmitriy Khokhlov <admin@optimlab.com>
 * @copyright  Copyright (c) 2018, Dmitriy Khokhlov. (http://optimlab.com/)
 * @license    https://opensource.org/licenses/GPL-3.0
 * @link       http://optimlab.com
 */
class ControllerExtensionModuleBestSellerInformation extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/bestseller_information');

		$this->load->model('catalog/information');
		$this->load->model('catalog/category');

		$this->load->model('tool/image');

		$data['informations'] = array();
        /* added by it-lab start */
        if (isset($this->request->get['information_id'])) {
            $information_id = (int)$this->request->get['information_id'];
        } else {
            $information_id = 0;
        }
        $data["archive_link"] = $this->url->link('product/gallery_album');
        $categories = $this->model_catalog_information->getCategories($information_id);
        if(is_array($categories) && count($categories)>0) {
            $category_id = $categories[0]["category_id"];
            $category = $this->model_catalog_category->getCategory($category_id);
            $template = $category["template"];

            /* added by it-lab end */


            $filter_data = array(
                'filter_category_id' => $category_id ? $category_id : $setting['category_id'],
                'sort' => $setting['sort'],
                'order' => 'DESC',
                'start' => 0,
                'limit' => $setting['limit']
            );

            $results = $this->model_catalog_information->getInformations($filter_data);

            if ($results) {
                foreach ($results as $result) {
                    if ($result['image']) {
                        $image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
                    } else {
                        $image = false;
                    }
                    $result_full=$this->model_catalog_information->getInformationFull($result['information_id']);

                    $data['informations'][] = array(
                        'information_id' => $result['information_id'],
                        'thumb' => $image,
                        'original_image' => "image/" . $result['image'],
                        'title' => $result['title'],
                        'description' => !empty($result['short_description']) ? trim(html_entity_decode($result['short_description'], ENT_QUOTES, 'UTF-8')) : utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('information_description_length')) . '..',
                        'city' => $result_full['city'],
                        'user_id' => $result['user_id'],
                        'author' => $result['author'],
                        'date_added' => $this->model_catalog_information->getDateWithMonth(($result['date_added']), $this->language->get('code')),
                        'reviews' => sprintf($this->language->get('text_review'), $result['reviews']),
                        'rating' => $result['rating'],
                        'href' => $this->url->link('information/information', '&information_id=' . $result['information_id'])
                    );
                }

                if ($setting['title'][$this->config->get('config_language_id')]) {
                    $data['heading_title'] = html_entity_decode($setting['title'][$this->config->get('config_language_id')]);
                }

                $data['author'] = $setting['author'];
                $data['date'] = $setting['date'];
                $data['review'] = $setting['review'];
                $data['rating'] = $setting['rating'];
                $data['href_category'] = $this->url->link('information/category', 'path=' . $setting['category_id']);

                if ($template == "project" || $template == "news") {
                    return $this->load->view('extension/module/bestseller_information_' . $template, $data);
                } else {
                    return $this->load->view('extension/module/bestseller_information_news', $data);
                }
            }

        }
	}
}
