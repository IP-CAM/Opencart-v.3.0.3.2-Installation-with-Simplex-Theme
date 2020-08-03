<?php

class ControllerInformationInformation extends Controller {
	public function index() {
		$this->load->language('information/information');
		$this->load->language('information/contact');

		$this->load->model('catalog/information');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		if($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}
		$data['base'] = $server;

		if(isset($this->request->get['information_id'])) {
			$information_id = (int)$this->request->get['information_id'];
		} else {
			$information_id = 0;
		}

		if(($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setReplyTo($this->request->post['email']);
			$mail->setSender(html_entity_decode($this->request->post['name'], ENT_QUOTES, 'UTF-8'));
			/* added by it-lab start */
			$mail->setSubject(html_entity_decode(sprintf($this->language->get('email_subject'), $this->request->post['subject']), ENT_QUOTES, 'UTF-8'));
			if(!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
				// Sanitize the filename
				$filename = $this->request->files['file']['name'];
				// Validate the filename length
				if((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 64)) {
					$json['error'] = $this->language->get('error_filename');
				}

				// Allowed file extension types
				$allowed = array();

				$extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));

				$filetypes = explode("\n", $extension_allowed);

				foreach($filetypes as $filetype) {
					$allowed[] = trim($filetype);
				}

				if(!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
					$json['error'] = $this->language->get('error_filetype');
				}

				// Allowed file mime types
				$allowed = array();

				$mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));

				$filetypes = explode("\n", $mime_allowed);

				foreach($filetypes as $filetype) {
					$allowed[] = trim($filetype);
				}

				if(!in_array($this->request->files['file']['type'], $allowed)) {
					$json['error'] = $this->language->get('error_filetype');
				}

				// Check to see if any PHP files are trying to be uploaded
				$content = file_get_contents($this->request->files['file']['tmp_name']);

				if(preg_match('/<\?php/i', $content)) {
					$json['error'] = $this->language->get('error_filetype');
				}

				// Return any upload error
				if($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
					$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
				}
			} else {
				$json['error'] = $this->language->get('error_upload');
			}

			if(!isset($json) || !$json) {
				$json['file'] = $file = $filename;

				move_uploaded_file($this->request->files['file']['tmp_name'], DIR_UPLOAD . $file);

				// Hide the uploaded file name so people can not link to it directly.
				$this->load->model('tool/upload');

				$json['code'] = $this->model_tool_upload->addUpload($filename, $file);

				$json['success'] = $this->language->get('text_upload');
			}

			if(isset($this->request->files['file'])) {
				$mail->addAttachment(DIR_UPLOAD . $file);
			}
			/* added by it-lab end */
			$mail->setText($this->request->post['enquiry']);

			$mail->send();
			$this->response->redirect($this->url->link('information/contact/success'));
		}

		if(isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if(isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}

		if(isset($this->error['enquiry'])) {
			$data['error_enquiry'] = $this->error['enquiry'];
		} else {
			$data['error_enquiry'] = '';
		}

		$data['button_submit'] = $this->language->get('button_submit');

		$data['action'] = $this->url->link('information/information', '&information_id=' . $information_id, true);

		if(isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} else {
			$data['name'] = $this->customer->getFirstName();
		}

		if(isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = $this->customer->getEmail();
		}

		if(isset($this->request->post['enquiry'])) {
			$data['enquiry'] = $this->request->post['enquiry'];
		} else {
			$data['enquiry'] = '';
		}
		/* added by it-lab end */

		$information_info = $this->model_catalog_information->getInformation($information_id);
		if($information_info) {
			$this->document->setTitle($information_info['meta_title']);
			$this->document->setDescription($information_info['meta_description']);
			$this->document->setKeywords($information_info['meta_keyword']);


			$data['heading_title'] = $information_info['title'];
			$information_info_full = $this->model_catalog_information->getInformationFull($information_id);
			$data['description'] = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');
			$data['description_additional'] = html_entity_decode($information_info_full['description_additional'], ENT_QUOTES, 'UTF-8');
			$data['city'] = html_entity_decode($information_info_full['city'], ENT_QUOTES, 'UTF-8');
			$data['continue'] = $this->url->link('common/home');
			$data['date_added'] = $this->model_catalog_information->getDateWithMonth(($information_info_full['date_added']), $this->language->get('code'));

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');


			/* added by it-lab start */
            $data['fax'] = $this->config->get("config_fax");
			$this->load->model('catalog/category');
			$data['thumb'] = (strlen($information_info['image']) > 0) ? $this->model_tool_image->resize
			($information_info['image'], 1920, 866, "w") : false;

			$categories = $this->model_catalog_information->getCategories($information_id);
			if(is_array($categories) && count($categories) > 0) {
				$category = $categories[0]["category_id"];
				$previousInfo = $this->model_catalog_information->getPreviousInformation($information_id);

                $data["back_to_the_list"] = false;
				if(isset($previousInfo)) {
                    $data["archive_link"] = $this->url->link(
                        'information/information',
                        ['information_id' => $previousInfo]
                    );
                } else {
                    $data["back_to_the_list"] = true;
                    $data["archive_link"] = $this->url->link('information/category', ['path' => $category]);
                }
				$data['date_added'] = $this->model_catalog_information->getDateWithMonth(($information_info_full['date_added']), $this->language->get('code'));
				$category = $this->model_catalog_category->getCategory($category);
				$data['breadcrumbs'][] = array(
					'text' => $category['name'],
					'href' => $this->url->link('information/category', 'path=' . $category['category_id'])
				);
				$data['breadcrumbs'][] = array(
					'text' => $information_info['title'],
					'href' => $this->url->link('information/information', 'information_id=' . $information_id)
				);

				if(!empty($category['template'])) {

					$results = $this->model_catalog_information->getInformationImages($information_id);
					foreach($results as $result) {
						$data['original_images'][] = array(
							'popup' => "image/" . $result['image'],
							'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('information_image_additional_width'), $this->config->get('information_image_additional_height'))
						);
					}

					$template = 'information/information_' . $category['template'];
					if($category["template"] == "vacancy") {

					} else if($category["template"] == "project") {
						$this->load->model('catalog/manufacturer');
						$this->load->model('catalog/product');
						$manufacturer_ids = [];
						foreach($data["products"] as &$product) {
							$manufacturer_id = $this->model_catalog_product->getProduct($product["product_id"])["manufacturer_id"];
							$manufacturer_ids[$manufacturer_id] = $manufacturer_id;
						}
						if(count($manufacturer_ids) > 0) {
							$manufacturers = $this->model_catalog_manufacturer->getManufacturersByIds(array_keys($manufacturer_ids));
							foreach($manufacturers as &$manufacturer) {
								$manufacturer['image'] = "image/" . $manufacturer['image'];
							}
							$data["manufacturers"] = $manufacturers;
						}
					}


					$this->response->setOutput($this->load->view($template, $data));

					return;
				} else {
					$this->response->setOutput($this->load->view('information/information_default', $data));

					return;
				}
			} else {
				$data['breadcrumbs'][] = array(
					'text' => $information_info['title'],
					'href' => $this->url->link('information/information', 'information_id=' . $information_id)
				);

				$this->response->setOutput($this->load->view('information/information_default', $data));

				return;
			}

			/* added by it-lab end */
			//$this->response->setOutput($this->load->view('information/information_vacancy', $data));
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('information/information', 'information_id=' . $information_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function agree() {
		$this->load->model('catalog/information');

		if(isset($this->request->get['information_id'])) {
			$information_id = (int)$this->request->get['information_id'];
		} else {
			$information_id = 0;
		}

		$output = '';

		$information_info = $this->model_catalog_information->getInformation($information_id);

		if($information_info) {
			$output .= html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8') . "\n";
		}

		$this->response->setOutput($output);
	}

	private $error = [];

	protected function validate() {
		if((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 32)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if(!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if((utf8_strlen($this->request->post['enquiry']) < 1) || (utf8_strlen($this->request->post['enquiry']) > 3000)) {
			$this->error['enquiry'] = $this->language->get('error_enquiry');
		}

		// Captcha
		if($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
			$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

			if($captcha) {
				$this->error['captcha'] = $captcha;
			}
		}

		return !$this->error;
	}
}
