<?php
class ControllerExtensionModuleBanner extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/banner');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');
        /* added by it-lab start */
        $this->load->model('localisation/language');
        $languages=$data['languages'] = $this->model_localisation_language->getLanguages();
        /* added by it-lab end */
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('banner', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['width'])) {
			$data['error_width'] = $this->error['width'];
		} else {
			$data['error_width'] = '';
		}

		if (isset($this->error['height'])) {
			$data['error_height'] = $this->error['height'];
		} else {
			$data['error_height'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/banner', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/banner', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/banner', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/banner', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['banner_id'])) {
			$data['banner_id'] = $this->request->post['banner_id'];
		} elseif (!empty($module_info)) {
			$data['banner_id'] = $module_info['banner_id'];
		} else {
			$data['banner_id'] = '';
		}

		$this->load->model('design/banner');

		$data['banners'] = $this->model_design_banner->getBanners();

		if (isset($this->request->post['width'])) {
			$data['width'] = $this->request->post['width'];
		} elseif (!empty($module_info)) {
			$data['width'] = $module_info['width'];
		} else {
			$data['width'] = '';
		}

		if (isset($this->request->post['height'])) {
			$data['height'] = $this->request->post['height'];
		} elseif (!empty($module_info)) {
			$data['height'] = $module_info['height'];
		} else {
			$data['height'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}
        /* added by it-lab start */
        if (isset($this->request->post['link'])) {
            $data['link'] = $this->request->post['link'];
        } elseif (!empty($module_info)) {
            $data['link'] = $module_info['link'];
        } else {
            $data['link'] = '';
        }

        if (isset($this->request->post['phone'])) {
            $data['phone'] = $this->request->post['phone'];
        } elseif (!empty($module_info)) {
            $data['phone'] = !empty($module_info['phone'])?$module_info['phone']:'';
        } else {
            $data['phone'] = '';
        }
        if (isset($this->request->post['banner_type'])) {
            $data['banner_type'] = $this->request->post['banner_type'];
        } elseif (!empty($module_info)) {
            $data['banner_type'] = !empty($module_info['banner_type'])?$module_info['banner_type']:'';
        } else {
            $data['banner_type'] = 'none';
        }
        $banner_types=[];
        $banner_types[]=['type'=>'none','name'=>"Not specified"];
        $banner_types[]=['type'=>'about','name'=>"About"];
        $banner_types[]=['type'=>'steps','name'=>"Steps"];
        $banner_types[]=['type'=>'despre_noi','name'=>"Despre noi"];
        $banner_types[]=['type'=>'showroom','name'=>"Showroom"];
        $banner_types[]=['type'=>'greu','name'=>"Greu Sa Alegi"];

        $data["banner_types"] = $banner_types;

        foreach ($languages as $language){
            $title_language="title{$language['language_id']}";
            $description_language="description{$language['language_id']}";
            $link_language="link{$language['language_id']}";

            if (isset($this->request->post[$title_language])) {
                $data[$title_language] = $this->request->post[$title_language];
            } elseif (!empty($module_info)) {
               if(!empty( $module_info[$title_language])){
                    $data[$title_language] = $module_info[$title_language];
               }else{
                   $data[$title_language] = '';
               }
            } else {
                $data[$title_language] = '';
            }
            if (isset($this->request->post[$description_language])) {
                $data[$description_language] = $this->request->post[$description_language];
            } elseif (!empty($module_info)) {
                if(!empty( $module_info[$description_language])){
                    $data[$description_language] = $module_info[$description_language];
                }else{
                    $data[$description_language] = '';
                }
            } else {
                $data[$description_language] = '';
            }

            if (isset($this->request->post[$link_language])) {
                $data[$link_language] = $this->request->post[$link_language];
            } elseif (!empty($module_info)) {
                if(!empty( $module_info[$link_language])){
                    $data[$link_language] = $module_info[$link_language];
                }else{
                    $data[$link_language] = '';
                }
            } else {
                $data[$link_language] = '';
            }
        }

        /* added by it-lab end */
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/banner', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/banner')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if (!$this->request->post['width']) {
			$this->error['width'] = $this->language->get('error_width');
		}

		if (!$this->request->post['height']) {
			$this->error['height'] = $this->language->get('error_height');
		}

		return !$this->error;
	}
}