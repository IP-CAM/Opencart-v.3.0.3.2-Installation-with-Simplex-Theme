<?php

class ControllerProductSpecial extends Controller
{
    public function index()
    {
        $this->load->language('product/special');

        $this->load->model('catalog/product');

        $this->load->model('tool/image');

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'p.sort_order';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        if (isset($this->request->get['limit'])) {
            $limit = (int)$this->request->get['limit'];
        } else {
            $limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
        }

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        if (isset($this->request->get['limit'])) {
            $url .= '&limit=' . $this->request->get['limit'];
        }

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('product/special', $url)
        );

        $data['text_compare'] = sprintf(
            $this->language->get('text_compare'),
            (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0)
        );

        $data['compare'] = $this->url->link('product/compare');

        $data['products'] = array();
        /* added by it-lab start */
        if (isset($this->request->get['method']) && $this->request->get['method'] == "load_all") {
            $num_pages = $this->request->get['num_pages'];
            $limit_to_end = ($num_pages - $page + 1) * $limit;
        } else {
            $limit_to_end = $limit;
        }
        /* added by it-lab end */
        $filter_data = array(
            'sort'  => $sort,
            'order' => $order,
            'start' => ($page - 1) * $limit,
            /* added by it-lab start */
            'limit' => $limit_to_end
            /* added by it-lab end */
        );

        $product_total = $this->model_catalog_product->getTotalProductSpecials();

        $results = $this->model_catalog_product->getProductSpecials($filter_data);
        /* added by it-lab start */
        $latest_products = $this->model_catalog_product->getLatestProducts(
            $this->config->get('theme_' . $this->config->get('config_theme') . '_latest_count')
        );
        /* added by it-lab end */
        foreach ($results as $result) {
            if ($result['product_id']) {
                if ($result['image']) {
                    $image = $this->model_tool_image->resize(
                        $result['image'],
                        $this->config->get(
                            'theme_' . $this->config->get(
                                'config_theme'
                            ) . '_image_product_width'
                        ),
                        $this->config->get(
                            'theme_' . $this->config->get(
                                'config_theme'
                            ) . '_image_product_height'
                        )
                    );
                } else {
                    $image = $this->model_tool_image->resize(
                        'placeholder.png',
                        $this->config->get(
                            'theme_' . $this->config->get(
                                'config_theme'
                            ) . '_image_product_width'
                        ),
                        $this->config->get(
                            'theme_' . $this->config->get(
                                'config_theme'
                            ) . '_image_product_height'
                        )
                    );
                }

                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $price = $this->currency->format(
                        $this->tax->calculate(
                            $result['price'],
                            $result['tax_class_id'],
                            $this->config->get('config_tax')
                        ),
                        $this->session->data['currency']
                    );
                } else {
                    $price = false;
                }

                if ((float)$result['special']) {
                    $special = $this->currency->format(
                        $this->tax->calculate(
                            $result['special'],
                            $result['tax_class_id'],
                            $this->config->get('config_tax')
                        ),
                        $this->session->data['currency']
                    );
                    /* added by it-lab start */
                    $special_percentage = round(
                        100 - (($this->tax->calculate(
                                    $result['special'],
                                    $result['tax_class_id'],
                                    $this->config->get('config_tax')
                                ) * 100) / $this->tax->calculate(
                                $result['price'],
                                $result['tax_class_id'],
                                $this->config->get('config_tax')
                            ))
                    );
                    $economy = $this->tax->calculate(
                            $result['price'],
                            $result['tax_class_id'],
                            $this->config->get('config_tax')
                        ) - $this->tax->calculate(
                            $result['special'],
                            $result['tax_class_id'],
                            $this->config->get('config_tax')
                        );
                    /* added by it-lab end */
                } else {
                    $special = false;
                    /* added by it-lab start */
                    $special_percentage = false;
                    $economy = false;
                    /* added by it-lab end */
                }

                if ($this->config->get('config_tax')) {
                    $tax = $this->currency->format(
                        (float)$result['special'] ? $result['special'] : $result['price'],
                        $this->session->data['currency']
                    );
                } else {
                    $tax = false;
                }

                if ($this->config->get('config_review_status')) {
                    $rating = (int)$result['rating'];
                } else {
                    $rating = false;
                }
                $is_new = array_key_exists($result['product_id'], $latest_products);

                $data['products'][] = array(
                    'product_id'         => $result['product_id'],
                    'thumb'              => $image,
                    'name'               => $result['name'],
                    'description'        => utf8_substr(
                            trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))),
                            0,
                            $this->config->get(
                                'theme_' . $this->config->get('config_theme') . '_product_description_length'
                            )
                        ) . '..',
                    'price'              => $price,
                    'special'            => $special,
                    /* added by it-lab start */
                    'special_percentage' => $special_percentage,
                    'economy'            => $economy,
                    'hide_price'         => $result['hide_price'] ? false : true,
                    'is_new'             => $is_new,
					'stock_status'       => $result['stock_status'],
					'stock_status_id'    => $result['stock_status_id'],
                    /* added by it-lab end */
                    'tax'                => $tax,
                    'minimum'            => $result['minimum'] > 0 ? $result['minimum'] : 1,
                    'rating'             => $result['rating'],
                    'href'               => $this->url->link(
                        'product/product',
                        'product_id=' . $result['product_id'] . $url
                    )
                );
            }
        }
        /* added by it-lab start */
        $data['currency'] = $this->currency->getSymbolRight($this->session->data['currency']);
        /* added by it-lab end */
        $url = '';

        if (isset($this->request->get['limit'])) {
            $url .= '&limit=' . $this->request->get['limit'];
        }

        $data['sorts'] = array();

        $data['sorts'][] = array(
            'text'  => $this->language->get('text_default'),
            'value' => 'p.sort_order-ASC',
            'href'  => $this->url->link('product/special', 'sort=p.sort_order&order=ASC' . $url)
        );

        $data['sorts'][] = array(
            'text'  => $this->language->get('text_name_asc'),
            'value' => 'pd.name-ASC',
            'href'  => $this->url->link('product/special', 'sort=pd.name&order=ASC' . $url)
        );

        $data['sorts'][] = array(
            'text'  => $this->language->get('text_name_desc'),
            'value' => 'pd.name-DESC',
            'href'  => $this->url->link('product/special', 'sort=pd.name&order=DESC' . $url)
        );

        $data['sorts'][] = array(
            'text'  => $this->language->get('text_price_asc'),
            'value' => 'p.price-ASC',
            'href'  => $this->url->link('product/special', 'sort=p.price&order=ASC' . $url)
        );

        $data['sorts'][] = array(
            'text'  => $this->language->get('text_price_desc'),
            'value' => 'p.price-DESC',
            'href'  => $this->url->link('product/special', 'sort=p.price&order=DESC' . $url)
        );

        if ($this->config->get('config_review_status')) {
            $data['sorts'][] = array(
                'text'  => $this->language->get('text_rating_desc'),
                'value' => 'rating-DESC',
                'href'  => $this->url->link('product/special', 'sort=rating&order=DESC' . $url)
            );

            $data['sorts'][] = array(
                'text'  => $this->language->get('text_rating_asc'),
                'value' => 'rating-ASC',
                'href'  => $this->url->link('product/special', 'sort=rating&order=ASC' . $url)
            );
        }

        $data['sorts'][] = array(
            'text'  => $this->language->get('text_model_asc'),
            'value' => 'p.model-ASC',
            'href'  => $this->url->link('product/special', 'sort=p.model&order=ASC' . $url)
        );

        $data['sorts'][] = array(
            'text'  => $this->language->get('text_model_desc'),
            'value' => 'p.model-DESC',
            'href'  => $this->url->link('product/special', 'sort=p.model&order=DESC' . $url)
        );
        /* added by it-lab start */

        $data['sorts'][] = array(
            'text'  => $this->language->get('text_date_added_desc'),
            'value' => 'p.date_added-DESC',
            'href'  => $this->url->link('product/special', 'sort=p.date_added&order=DESC' . $url)
        );
        $data['sorts'][] = array(
            'text'  => $this->language->get("text_popular_desc"),
            'value' => 'order_quantity-DESC',
            'href'  => $this->url->link('product/special', 'sort=order_quantity&order=DESC' . $url)
        );
        /* added by it-lab end */

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $data['limits'] = array();

        $limits = array_unique(
            array($this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'), 16, 24, 32, 48)
        );

        sort($limits);

        foreach ($limits as $value) {
            $data['limits'][] = array(
                'text'  => $value,
                'value' => $value,
                'href'  => $this->url->link('product/special', $url . '&limit=' . $value)
            );
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['limit'])) {
            $url .= '&limit=' . $this->request->get['limit'];
        }

        $pagination = new Pagination();
        $pagination->total = $product_total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('product/special', $url . '&page={page}');

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf(
            $this->language->get('text_pagination'),
            ($product_total) ? (($page - 1) * $limit) + 1 : 0,
            ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit),
            $product_total,
            ceil($product_total / $limit)
        );

        // http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
        if ($page == 1) {
            $this->document->addLink($this->url->link('product/special', '', true), 'canonical');
        } else {
            $this->document->addLink($this->url->link('product/special', 'page=' . $page, true), 'canonical');
        }

        if ($page > 1) {
            $this->document->addLink(
                $this->url->link('product/special', (($page - 2) ? '&page=' . ($page - 1) : ''), true),
                'prev'
            );
        }

        if ($limit && ceil($product_total / $limit) > $page) {
            $this->document->addLink($this->url->link('product/special', 'page=' . ($page + 1), true), 'next');
        }

        $data['sort'] = $sort;
        $data['order'] = $order;
        $data['limit'] = $limit;
        /* added by it-lab start */
        $data['product_total'] = $product_total;
        if (!isset($this->request->get['method']) || $this->request->get['method'] != "load_all") {
            $num_pages = ceil($product_total / $limit);
            $data['num_pages'] = $num_pages;
        }
        $data['page'] = $page;
        if (($product_total - $page * $limit) < $limit) {
            $show_more_limit = $product_total - $page * $limit;
        } else {
            $show_more_limit = $limit;
        }
        $data["show_more_limit"] = $show_more_limit;
        /* added by it-lab end */
        $data['continue'] = $this->url->link('common/home');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        /* added by it-lab start */
        if (isset($this->request->get['method'])) {
            $this->response->setOutput($this->load->view('product/load_more', $data));
        } else {
            $this->response->setOutput($this->load->view('product/special', $data));
        }
        /* added by it-lab end */
    }
}
