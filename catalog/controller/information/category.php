<?php

class ControllerInformationCategory extends Controller {
    public function index() {
        $this->load->model('catalog/information');
        $this->load->model('catalog/category');
        
        $this->load->model('tool/image');
        
        $data['informations'] = [];
        /* added by it-lab start */
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ];
        /* added by it-lab end */
        
        
        if(isset($this->request->get['path'])) {
            $url = '';
            
            if(isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }
            
            if(isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }
            
            if(isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }
            
            $path = '';
            
            $parts = explode('_', (string)$this->request->get['path']);
            
            $category_id = (int)array_pop($parts);
            
            foreach($parts as $path_id) {
                if(!$path) {
                    $path = (int)$path_id;
                } else {
                    $path .= '_' . (int)$path_id;
                }
                
                $category_info = $this->model_catalog_category->getCategory($path_id);
                
                if($category_info) {
                    $data['breadcrumbs'][] = [
                        'text' => $category_info['name'],
                        'href' => $this->url->link('product/category', 'path=' . $path . $url)
                    ];
                }
            }
        } else {
            $category_id = 0;
        }
        $limit = $this->getLimit();
        $start = $this->getStart();
        
        $category_info = $this->model_catalog_category->getCategory($category_id);
        if($category_info) {
            $this->document->setTitle($category_info['meta_title']);
            $this->document->setDescription($category_info['meta_description']);
            $this->document->setKeywords($category_info['meta_keyword']);
            
            $data['heading_title'] = $category_info['name'];
            
            $data['breadcrumbs'][] = [
                'text' => $category_info['name'],
                'href' => $this->url->link('information/category', 'path=' . $path . $url)
            ];
        }
        $filter_data = [
            'filter_category_id' => $category_id,
            'sort'               => 'i.date_added',
            'order'              => 'DESC',
            'start'              => 0,
            'limit'              => $limit
        ];
        $results = $this->model_catalog_information->getInformations($filter_data);
        $total = $this->model_catalog_information->getTotalInformations();
        if($category_id == 61) {
            $filter_data['limit'] = $total;
        }
        
        if($results) {
            foreach($results as $result) {
                
                if($result['image']) {
                    $image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'), "w");
                } else {
                    $image = false;
                }
                $result_full = $this->model_catalog_information->getInformationFull($result['information_id']);
                $data['informations'][] = [
                    'information_id' => $result['information_id'],
                    'thumb'          => $image,
                    'title'          => $result['title'],
                    'description'    => !empty($result['short_description']) ? trim(html_entity_decode($result['short_description'], ENT_QUOTES, 'UTF-8')) : utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('information_description_length')) . '..',
                    'city'           => $result_full['city'],
                    'user_id'        => $result['user_id'],
                    'author'         => $result['author'],
                    'date_added'     => $this->model_catalog_information->getDateWithMonth(($result['date_added']), $this->language->get('code')),
                    'reviews'        => sprintf($this->language->get('text_review'), $result['reviews']),
                    'rating'         => $result['rating'],
                    'href'           => $this->url->link('information/information', '&information_id=' . $result['information_id'])
                ];
            }
        }
        $data["limit"] = $limit;
        $data["start"] = $start + count($results);
        $data["show_load_more_button"] = !(count($results) < $start + count($results));
        $data['href_category'] = $this->url->link('product/category', 'path=' . $category_id);
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        /* added by it-lab start */
        if($category_info['template']) {
            $template = 'information/category_' . $category_info['template'];
            $category_info["description"] = trim(htmlspecialchars_decode($category_info["description"]), '"');
            $data["category"] = $category_info;
            $data['pozitions_numeral'] = $this->getNumeral(count($results));
            
            $this->response->setOutput($this->load->view($template, $data));
            
            return;
        }
        
        $this->response->setOutput($this->load->view('information/category', $data));
    }
    
    public function load_more() {
        $this->load->model('catalog/information');
        $this->load->model('catalog/category');
        
        $this->load->model('tool/image');
        
        $data['informations'] = [];
        
        
        if(isset($this->request->get['path'])) {
            $url = '';
            
            if(isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }
            
            if(isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }
            
            if(isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }
            
            $path = '';
            
            $parts = explode('_', (string)$this->request->get['path']);
            
            $category_id = (int)array_pop($parts);
            
            foreach($parts as $path_id) {
                if(!$path) {
                    $path = (int)$path_id;
                } else {
                    $path .= '_' . (int)$path_id;
                }
                
                $category_info = $this->model_catalog_category->getCategory($path_id);
                
                if($category_info) {
                    $data['breadcrumbs'][] = [
                        'text' => $category_info['name'],
                        'href' => $this->url->link('product/category', 'path=' . $path . $url)
                    ];
                }
            }
        } else {
            $category_id = 0;
        }
        $limit = $this->getLimit();
        $start = $this->getStart();
        
        $filter_data = [
            'filter_category_id' => $category_id,
            'sort'               => 'i.date_added',
            'order'              => 'DESC',
            'start'              => $start,
            'limit'              => $limit
        ];
        $category_info = $this->model_catalog_category->getCategory($category_id);
        
        if($category_info) {
            $this->document->setTitle($category_info['meta_title']);
            $this->document->setDescription($category_info['meta_description']);
            $this->document->setKeywords($category_info['meta_keyword']);
            
            $data['heading_title'] = $category_info['name'];
            
            $data['breadcrumbs'][] = [
                'text' => $category_info['name'],
                'href' => $this->url->link('information/category', 'path=' . $path . $url)
            ];
        }
        $results = $this->model_catalog_information->getInformations($filter_data);
        $total = $this->model_catalog_information->getTotalInformations($filter_data);
        if($results) {
            foreach($results as $result) {
                if($result['image']) {
                    $image = "image/" . $result['image'];
                } else {
                    $image = false;
                }
                $result_full = $this->model_catalog_information->getInformationFull($result['information_id']);
                $data['informations'][] = [
                    'information_id' => $result['information_id'],
                    'thumb'          => $image,
                    'title'          => $result['title'],
                    'description'    => !empty($result['short_description']) ? trim(html_entity_decode($result['short_description'], ENT_QUOTES, 'UTF-8')) : utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('information_description_length')) . '..',
                    'city'           => $result_full['city'],
                    'user_id'        => $result['user_id'],
                    'author'         => $result['author'],
                    'date_added'     => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                    'reviews'        => sprintf($this->language->get('text_review'), $result['reviews']),
                    'rating'         => $result['rating'],
                    'href'           => $this->url->link('information/information', '&information_id=' . $result['information_id'])
                ];
            }
        }
        
        $response = [];
        $response["start"] = $start + count($results);
        if($total <= $start + count($results)) {
            $response['displayed_all'] = true;
        } else {
            $response['displayed_all'] = false;
        }
        $template = 'information/category_' . $category_info['template'] . '_load_more';
        
        $response['items'] = $this->load->view($template, $data);
        
        //return $this->response->setOutput($this->load->view('information/category', $data));
        $this->response->setOutput(json_encode($response));
    }
    
    private function getLimit() {
        if(isset($this->request->get['limit'])) {
            $limit = $this->request->get['limit'];
        } else {
            $limit = 6;
        }
        
        return $limit;
    }
    
    private function getStart() {
        if(isset($this->request->get['start'])) {
            $start = $this->request->get['start'];
        } else {
            $start = 0;
        }
        
        return $start;
    }
    
    /* added by it-lab start */
    
    public function getNumeral($count) {
        $lang = $this->language->get('code');
        if($lang == "ru") {
            $last_digit = substr($count, -1);
            if($last_digit == 1) {
                $photos_msg = "позиция";
            } else if($last_digit >= 2 & $last_digit <= 4) {
                $photos_msg = "позиций";
            } else {
                $photos_msg = "позиций";
            }
        } else if($lang == "ro") {
            if($count == 1) {
                $photos_msg = "poziție";
            } else {
                $photos_msg = "poziții";
            }
        } else {
            if($count == 1) {
                $photos_msg = "pozition";
            } else {
                $photos_msg = "pozitions ";
            }
        }
        
        return $photos_msg;
    }
    /* added by it-lab end */
    
}
