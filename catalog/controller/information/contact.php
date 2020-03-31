<?php

class ControllerInformationContact extends Controller
{
    private $error = [];

    /* added by it-lab* start */
    public function upload()
    {
        $this->load->language('tool/upload');

        $json = [];

        if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
            // Sanitize the filename
            $filename = $this->request->files['file']['name'];

            // Validate the filename length
            if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 64)) {
                $json['error'] = $this->language->get('error_filename');
            }

            // Allowed file extension types
            $allowed = [];

            $extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));

            $filetypes = explode("\n", $extension_allowed);

            foreach ($filetypes as $filetype) {
                $allowed[] = trim($filetype);
            }

            if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
                $json['error'] = $this->language->get('error_filetype');
            }

            // Allowed file mime types
            $allowed = [];

            $mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));

            $filetypes = explode("\n", $mime_allowed);

            foreach ($filetypes as $filetype) {
                $allowed[] = trim($filetype);
            }

            if (!in_array($this->request->files['file']['type'], $allowed)) {
                $json['error'] = $this->language->get('error_filetype');
            }

            // Check to see if any PHP files are trying to be uploaded
            $content = file_get_contents($this->request->files['file']['tmp_name']);

            if (preg_match('/<\?php/i', $content)) {
                $json['error'] = $this->language->get('error_filetype');
            }

            // Return any upload error
            if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
                $json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
            }
        } else {
            $json['error'] = $this->language->get('error_upload');
        }

        if (!$json) {
            $json['file'] = $file = $filename;

            move_uploaded_file($this->request->files['file']['tmp_name'], DIR_UPLOAD . $file);

            // Hide the uploaded file name so people can not link to it directly.
            $this->load->model('tool/upload');

            $json['code'] = $this->model_tool_upload->addUpload($filename, $file);

            $json['success'] = $this->language->get('text_upload');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /* added by it-lab* end */

    public function index()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $this->load->language('information/contact');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['errors'] = $this->request->server['REQUEST_METHOD'] == 'POST';
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
            $data['errors'] = false;
            $mail = new Mail($this->config->get('config_mail_engine'));
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
            $mail->smtp_password = html_entity_decode(
                $this->config->get('config_mail_smtp_password'),
                ENT_QUOTES,
                'UTF-8'
            );
            $mail->smtp_port = $this->config->get('config_mail_smtp_port');
            $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

            $mail->setTo($this->config->get('config_mail_smtp_username'));
            $mail->setTo('vasile.costiuc@it-lab.md');
            $mail->setFrom($this->config->get('config_mail_smtp_username'));
            if (!isset($this->request->post['customer_phone'])) {
                $mail->setReplyTo($this->request->post['email']);
                $mail->setSender(html_entity_decode($this->request->post['name'], ENT_QUOTES, 'UTF-8'));
                $mail->setSubject(
                    html_entity_decode(
                        sprintf($this->language->get('email_subject'), $this->request->post['subject']),
                        ENT_QUOTES,
                        'UTF-8'
                    )
                );
            } else {
                if ($this->validatePhoneNumber()) {
                    $mail->setSender(html_entity_decode($this->request->post['customer_phone'], ENT_QUOTES, 'UTF-8'));
                    $mail->setSubject(
                        html_entity_decode($this->language->get('text_telephone') . " : ", ENT_QUOTES, 'UTF-8')
                    );
                    $mail->setText(
                        $this->language->get('text_customer_telephone') . " : " . html_entity_decode(
                            $this->request->post['customer_phone'],
                            ENT_QUOTES,
                            'UTF-8'
                        )
                    );
                    $mail->send();
                    $json["result"] = $this->language->get('text_phone_succes');
                    $json['config_mail_smtp_usernamel'] = $this->config->get('config_mail_smtp_username');
                } else {
                    $json['phone_error'] = true;
                    $json["result"] = $this->language->get('text_phone_error');
                }
                $this->response->setOutput(json_encode($json));

                return;
            }
            /* added by it-lab start */
            if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
                // Sanitize the filename
                $filename = $this->request->files['file']['name'];
                // Validate the filename length
                if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 64)) {
                    $json['error'] = $this->language->get('error_filename');
                }
                // Allowed file extension types
                $allowed = [];

                $extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));

                $filetypes = explode("\n", $extension_allowed);

                foreach ($filetypes as $filetype) {
                    $allowed[] = trim($filetype);
                }

                if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
                    $json['error'] = $this->language->get('error_filetype');
                }

                // Allowed file mime types
                $allowed = [];

                $mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));

                $filetypes = explode("\n", $mime_allowed);

                foreach ($filetypes as $filetype) {
                    $allowed[] = trim($filetype);
                }

                if (!in_array($this->request->files['file']['type'], $allowed)) {
                    $json['error'] = $this->language->get('error_filetype');
                }

                // Check to see if any PHP files are trying to be uploaded
                $content = file_get_contents($this->request->files['file']['tmp_name']);

                if (preg_match('/<\?php/i', $content)) {
                    $json['error'] = $this->language->get('error_filetype');
                }

                // Return any upload error
                if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
                    $json['error'] = $this->language->get('error_upload' . $this->request->files['file']['error']);
                }
            } else {
                $json['error'] = $this->language->get('error_upload');
            }

            if (!$json) {
                $json['file'] = $file = $filename;

                move_uploaded_file($this->request->files['file']['tmp_name'], DIR_UPLOAD . $file);

                // Hide the uploaded file name so people can not link to it directly.
                $this->load->model('tool/upload');

                $json['code'] = $this->model_tool_upload->addUpload($filename, $file);

                $json['success'] = $this->language->get('text_upload');
            }

            if (isset($file)) {
                $mail->addAttachment(DIR_UPLOAD . $file);
            }

            $mail->setText($this->request->post['enquiry']);
            $mail->send();
            $this->response->redirect($this->url->link('information/contact/success'));
        }

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('information/contact')
        ];

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = '';
        }

        if (isset($this->error['email'])) {
            $data['error_email'] = $this->error['email'];
        } else {
            $data['error_email'] = '';
        }

        if (isset($this->error['enquiry'])) {
            $data['error_enquiry'] = $this->error['enquiry'];
        } else {
            $data['error_enquiry'] = '';
        }

        $data['button_submit'] = $this->language->get('button_submit');

        $data['action'] = $this->url->link('information/contact', '', true);

        $this->load->model('tool/image');

        if ($this->config->get('config_image')) {
            $data['image'] = $this->model_tool_image->resize(
                $this->config->get('config_image'),
                $this->config->get(
                    'theme_' . $this->config->get(
                        'config_theme'
                    ) . '_image_location_width'
                ),
                $this->config->get(
                    'theme_' . $this->config->get(
                        'config_theme'
                    ) . '_image_location_height'
                )
            );
        } else {
            $data['image'] = false;
        }

        $data['store'] = $this->config->get('config_name');
        $data['address'] = nl2br($this->config->get('config_address'));
        $data['geocode'] = $this->config->get('config_geocode');
        $data['geocode_hl'] = $this->config->get('config_language');
        $data['telephone'] = $this->config->get('config_telephone');
        $data['fax'] = $this->config->get('config_fax');
        $data['open'] = nl2br($this->config->get('config_open'));
        $data['comment'] = $this->config->get('config_comment');

        $data['locations'] = [];

        $this->load->model('localisation/location');
        $locations = $this->model_localisation_location->getLocations();
        $location_descriptions = $this->model_localisation_location->getLocationDescriptions();

        foreach ($locations as $location_info) {
            $location_id = $location_info["location_id"];

            if ($location_info & !$location_info['is_online']) {
                if ($location_info['image']) {
                    $image = $this->model_tool_image->resize(
                        $location_info['image'],
                        $this->config->get(
                            'theme_' . $this->config->get(
                                'config_theme'
                            ) . '_image_location_width'
                        ),
                        $this->config->get(
                            'theme_' . $this->config->get(
                                'config_theme'
                            ) . '_image_location_height'
                        )
                    );
                    $image = "image/" . $location_info['image'];
                } else {
                    $image = false;
                }

                $data['locations'][] = [
                    'location_id' => $location_info['location_id'],
                    'name'        => $location_info['name'],
                    'address'     => nl2br($location_descriptions[$location_id]['address']),
                    'country'     => nl2br($location_descriptions[$location_id]['country']),
                    'city'        => nl2br($location_descriptions[$location_id]['city']),
                    'district'    => nl2br($location_descriptions[$location_id]['district']),
                    'geocode'     => $location_info['geocode'],
                    'telephone'   => $location_info['telephone'],
                    'telephone1'  => $location_info['telephone1'],
                    'telephone2'  => $location_info['telephone2'],
                    'email'       => $location_info['email'],
                    'fax'         => $location_info['fax'],
                    'image'       => $image,
                    'open'        => html_entity_decode(
                        $location_descriptions[$location_id]['open'],
                        ENT_QUOTES,
                        'UTF-8'
                    ),
                    'comment'     => $location_info['comment']
                ];
            }
        }
        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } else {
            $data['name'] = $this->customer->getFirstName();
        }

        if (isset($this->request->post['email'])) {
            $data['email'] = $this->request->post['email'];
        } else {
            $data['email'] = $this->customer->getEmail();
        }

        if (isset($this->request->post['enquiry'])) {
            $data['enquiry'] = $this->request->post['enquiry'];
        } else {
            $data['enquiry'] = '';
        }

        // Captcha
        if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array(
                'contact',
                (array)$this->config->get(
                    'config_captcha_page'
                )
            )) {
            $data['captcha'] = $this->load->controller(
                'extension/captcha/' . $this->config->get('config_captcha'),
                $this->error
            );
        } else {
            $data['captcha'] = '';
        }

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        /* added by it-lab start */
        $this->load->model('catalog/information');
        $data['footer_titles'] = $this->model_catalog_information->getFooterTitle();
        $this->response->setOutput($this->load->view('information/contact', $data));
        /* added by it-lab end */
    }

    protected function validate()
    {
        if (!isset($this->request->post['customer_phone'])) {
            if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 32)) {
                $this->error['name'] = $this->language->get('error_name');
            }

            if (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
                $this->error['email'] = $this->language->get('error_email');
            }

            if ((utf8_strlen($this->request->post['enquiry']) < 10) || (utf8_strlen(
                        $this->request->post['enquiry']
                    ) > 3000)) {
                $this->error['enquiry'] = $this->language->get('error_enquiry');
            }
        }
        // Captcha
        if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array(
                'contact',
                (array)$this->config->get(
                    'config_captcha_page'
                )
            )) {
            $captcha = $this->load->controller(
                'extension/captcha/' . $this->config->get('config_captcha') . '/validate'
            );

            if ($captcha) {
                $this->error['captcha'] = $captcha;
            }
        }

        return !$this->error;
    }

    protected function validatePhoneNumber()
    {
        if ((utf8_strlen($this->request->post['customer_phone']) < 6) || (utf8_strlen(
                    $this->request->post['customer_phone']
                ) > 15)) {
            $this->error['customer_phone'] = $this->language->get('error_customer_phone');

            return false;
        }

        return true;
    }

    public function success()
    {
        $this->load->language('information/contact');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('information/contact')
        ];

        $data['continue'] = $this->url->link('common/home');
        $data['heading_title'] = $this->language->get('success_text');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('common/success', $data));
    }
}
