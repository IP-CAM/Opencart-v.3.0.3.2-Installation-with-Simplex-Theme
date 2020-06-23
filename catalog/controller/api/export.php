<?php

class ControllerApiExport extends Controller
{
    public function product()
    {
        if (!isset($this->session->data['api_key'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
        } else if (!isset($this->request->get['sku'])){
            $json['error']['warning'] = "SKU field is obligatory";
        } else {
            $this->load->model('catalog/product');

            if(!($json = $this->model_catalog_product->getProductBySku($this->request->get['sku']))){
                $json['error']['warning'] = "No product found";
            }
        }
        $this->response->addHeader("Content-type: application/json");
        $this->response->setOutput(json_encode($json));
    }
}
