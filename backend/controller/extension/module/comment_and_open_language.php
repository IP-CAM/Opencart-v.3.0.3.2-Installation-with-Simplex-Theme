<?php

class ControllerExtensionModuleCommentAndOpenLanguage extends Controller {

    private $error = array();

    public function index() {
        $this->load->language('extension/module/comment_and_open_language');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_comment_and_open_language', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module'));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data = array();
        

        $data['heading_title'] = $this->language->get('heading_title');


        $data['text_installed'] = $this->language->get('text_installed');

        $data['button_cancel'] = $this->language->get('button_cancel');



        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('module/comment_and_open_language', 'user_token=' . $this->session->data['user_token'], 'SSL')
        );

        
        $data['cancel'] = $this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], 'SSL');

       
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/comment_and_open_language', $data));
    }


    public function install() {
        $this->load->model('setting/setting');
        $this->load->model('setting/event');
        $this->load->model("localisation/language");
        $post['comment_and_open_language_enabled'] = "1";
        $this->model_setting_setting->editSetting('comment_and_open_language', $post);

        $comment = $this->model_setting_setting->getSettingValue('config_comment');
        
        $open = $this->model_setting_setting->getSettingValue('config_open');
        $languages = $this->model_localisation_language->getLanguages();
        $comments = array();
        $opens = array();
        foreach ($languages as $language) {
            $comments[$language['language_id']] = $comment;
            $opens[$language['language_id']] = $open;
        }

         $this->model_setting_setting->editSettingValue('config','config_comment', $comments);
         $this->model_setting_setting->editSettingValue('config','config_open', $opens);
        
        
        $this->model_setting_event->addEvent('comment_and_open_language', 'admin/controller/localisation/language/add/after', 'module/comment_and_open_language/eventUpdatedLanguage');
        $this->model_setting_event->addEvent('comment_and_open_language', 'admin/controller/localisation/language/edit/after', 'module/comment_and_open_language/eventUpdatedLanguage');
        $this->model_setting_event->addEvent('comment_and_open_language', 'admin/controller/localisation/language/delete/after', 'module/comment_and_open_language/eventUpdatedLanguage');
    }

    public function uninstall() {
        $this->load->model('setting/event');
        $this->load->model('setting/setting');
        $this->load->model('localisation/language');
        
        $config = $this->model_setting_setting->getSetting('config');
        $comments = $config['config_comment'];
        $opens = $config['config_open'];
        $languages = $this->model_localisation_language->getLanguages();
       
        $this->model_setting_setting->editSettingValue('config','config_comment', strip_tags($comments[$this->getDefaultLanguageId($languages)]));
        $this->model_setting_setting->editSettingValue('config','config_open', strip_tags($opens[$this->getDefaultLanguageId($languages)]));
       
        $this->model_setting_event->deleteEvent('comment_and_open_language');
    }

    

    public function eventUpdatedLanguage() {
        $this->load->model('setting/setting');
                
        $this->load->model("localisation/language");
        $languages = $this->model_localisation_language->getLanguages();
        $config = $this->model_setting_setting->getSetting('config');
        $comments = $config['config_comment'];
        $opens = $config['config_open'];

        foreach ($comments as $key => $comment) {
            $new_comments = array();
            foreach ($languages as $language) {
                if (isset($comment[$language['language_id']])){
                    $new_comments[$language['language_id']] = $comment[$language['language_id']];
                } else{
                    $new_comments[$language['language_id']] = $comment[$this->getDefaultLanguageId($languages)];
                }
            }
            $new_comments[$key] = $new_comments;
        }
        foreach ($opens as $key => $open) {
            $new_opens = array();
            foreach ($languages as $language) {
                if (isset($open[$language['language_id']])){
                    $new_opens[$language['language_id']] = $open[$language['language_id']];
                } else{
                    $new_opens[$language['language_id']] = $open[$this->getDefaultLanguageId($languages)];
                }
            }
            $new_opens[$key] = $new_opens;
        }
        $this->model_setting_setting->editSetting('config_comment', $new_comments);
        $this->model_setting_setting->editSetting('config_open', $new_opens);
    }
    
    private function getDefaultLanguageId($languages){
        $this->load->model('setting/setting');
        $language_code = $this->model_setting_setting->getSettingValue('config_language');
        return $language_id = 1;
        foreach ($languages as $language) {
            $language_id = $language['language_id'];
            break;            
        }
        foreach ($languages as $language) {
            if ($language['code'] == $language_code){
                $language_id = $language['language_id'];
            }         
        }
        return $language_id;
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/category')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}
