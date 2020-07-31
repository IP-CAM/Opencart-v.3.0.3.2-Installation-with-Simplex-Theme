<?php
error_reporting(E_ERROR | E_PARSE);
class ControllerAccountWishList extends Controller {
	public function index() {
		$this->load->language('account/wishlist');

		$this->load->model('account/wishlist');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		
		if (!isset($this->session->data['wishlist'])) {
			$this->session->data['wishlist'] = array();
		}
		
		if (isset($this->request->get['remove'])) {
			// Remove Wishlist
			if ($this->customer->isLogged()) {
				$this->model_account_wishlist->deleteWishlist($this->request->get['remove']);
				$this->session->data['success'] = $this->language->get('text_remove');
			} else {
				$key = array_search($this->request->get['remove'], $this->session->data['wishlist']);
				if ($key !== false) {
					unset($this->session->data['wishlist'][$key]);
					$this->session->data['success'] = $this->language->get('text_remove');
				}
			}
			$this->response->redirect($this->url->link('account/wishlist'));
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/wishlist')
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_empty'] = $this->language->get('text_empty');
		
		
		$data['products'] = array();

		
		$results = array();
		if($this->customer->isLogged()) {
			$results = $this->model_account_wishlist->getWishlist();
		} else {
			foreach($this->session->data['wishlist'] as $k => $pid) {
				$results[$k]['product_id'] = (int)$pid;
			}
		}
		
		foreach ($results as $key => $result) {
			$product_info = $this->model_catalog_product->getProduct($result['product_id']);

			if ($product_info) {
				if ($product_info['image']) {
					$image = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_wishlist_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_wishlist_height'));
				} else {
					$image = false;
				}

				if ($product_info['quantity'] <= 0) {
					$stock = $product_info['stock_status'];
				} elseif ($this->config->get('config_stock_display')) {
					$stock = $product_info['quantity'];
				} else {
					$stock = $this->language->get('text_instock');
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$product_info['special']) {
					$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    /* added by it-lab start */
                    $special_percentage = round(100 - (($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'))*100) / $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'))));
                    $economy= $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')) - $this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'));
                    /* added by it-lab end */
				} else {
					$special = false;
                    /* added by it-lab start */
                    $special_percentage = false;
                    $economy = false;
                    /* added by it-lab end */
				}

				$data['products'][] = array(
					'product_id' => $product_info['product_id'],
					'thumb'      => $image,
					'name'       => $product_info['name'],
					'model'      => $product_info['model'],
					'stock'      => $stock,
					'price'      => $price,
					'special'    => $special,
                    /* added by it-lab start */
                    'special_percentage' => $special_percentage,
                    'economy'     => $economy,
                    'hide_price'  => $product_info['hide_price']?false:true,
                    /* added by it-lab end */
					'href'       => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
					'remove'     => $this->url->link('account/wishlist', 'remove=' . $product_info['product_id'])
				);

			} else {
				if($this->customer->isLogged()) {
					$this->model_account_wishlist->deleteWishlist($result['product_id']);
				} else {
					unset($this->session->data['wishlist'][$key]);
				}
			}
		}
        /* added by it-lab start */
        $data['currency'] = $this->currency->getSymbolRight($this->session->data['currency']);
        /* added by it-lab end */
		$data['continue'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/wishlist', $data));
	}

	public function add() {
		$this->load->language('account/wishlist');

		$json = array();

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			if ($this->customer->isLogged()) {
				// Edit customers cart
				$this->load->model('account/wishlist');

				$this->model_account_wishlist->addWishlist($this->request->post['product_id']);

				$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . (int)$this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));
                /* added by it-lab start */
                $json['success_message'] = $this->language->get('text_success_message');
                /* added by it-lab end */
				$json['total_msd'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
				$json['total'] = $this->model_account_wishlist->getTotalWishlist();


			} else {
				if (!isset($this->session->data['wishlist'])) {
					$this->session->data['wishlist'] = array();
				}

				$this->session->data['wishlist'][] = $this->request->post['product_id'];

				$this->session->data['wishlist'] = array_unique($this->session->data['wishlist']);

				$json['success'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true), $this->url->link('product/product', 'product_id=' . (int)$this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));
                /* added by it-lab start */
                $json['success_message'] = $this->language->get('text_success_message');
                /* added by it-lab end */
				$json['total_msg'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));

                $json['total'] =  (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0);

			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

    public function remove() {

        $this->load->language('account/wishlist');

        $json = array();

        if (isset($this->request->post['product_id'])) {
            $product_id = $this->request->post['product_id'];
        } else {
            $product_id = 0;
        }

        $this->load->model('catalog/product');
        if($product_id!="all") {
            $product_info = $this->model_catalog_product->getProduct($product_id);

            if ($product_info) {
                if ($this->customer->isLogged()) {
                    // Edit customers cart
                    $this->load->model('account/wishlist');

                    $this->model_account_wishlist->deleteWishlist($this->request->post['product_id']);

                    $json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . (int)$this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));

                    $json['total'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());

                    $json['total'] = $this->model_account_wishlist->getTotalWishlist();


                } else {
                    if (!isset($this->session->data['wishlist'])) {
                        $this->session->data['wishlist'] = array();
                    }

                    $key = array_search($product_id, $this->session->data['wishlist']);
                    if ($key !== false) {
                        unset($this->session->data['wishlist'][$key]);
                        $this->session->data['success'] = $this->language->get('text_remove');
                    }

                    $json['total'] = (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0);

                }
            }
        }else{
            if ($this->customer->isLogged()) {
                $this->load->model('account/wishlist');

                $this->model_account_wishlist->deleteWishlistAll();

                //$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . (int)$this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));

                $json['total'] = $this->model_account_wishlist->getTotalWishlist();
            }else{

                unset($this->session->data['wishlist']);

                $json['total'] = 0;
            }
        }
        if($json['total'] == 0){
            $data['breadcrumbs'] = array();

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home')
            );

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_account'),
                'href' => $this->url->link('account/account', '', true)
            );

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('account/wishlist')
            );

            if (isset($this->session->data['success'])) {
                $data['success'] = $this->session->data['success'];

                unset($this->session->data['success']);
            } else {
                $data['success'] = '';
            }


            $data['heading_title'] = $this->language->get('heading_title');
            $data['text_empty'] = $this->language->get('text_empty');

            $json['wishlist_empty']= $this->load->view('account/wishlist_empty',$data);
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
