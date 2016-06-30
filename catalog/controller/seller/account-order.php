<?php

class ControllerSellerAccountOrder extends ControllerSellerAccount {
	public function getTableData() {
		$colMap = array(
			'customer_name' => 'firstname',
			'date_created' => 'o.date_added',
		);
		
		$sorts = array('order_id', 'customer_name', 'date_created', 'total_amount');
		$filters = array_merge($sorts, array('products'));
		
		list($sortCol, $sortDir) = $this->MsLoader->MsHelper->getSortParams($sorts, $colMap);
		$filterParams = $this->MsLoader->MsHelper->getFilterParams($filters, $colMap);
		
		$seller_id = $this->customer->getId();
		$this->load->model('account/order');

		$orders = $this->MsLoader->MsOrderData->getOrders(
			array(
				'seller_id' => $seller_id,
				'order_status' => $this->config->get('msconf_display_order_statuses')
			),
			array(
				'order_by'  => $sortCol,
				'order_way' => $sortDir,
				'offset' => $this->request->get['iDisplayStart'],
				'limit' => $this->request->get['iDisplayLength'],
				'filters' => $filterParams
			),
			array(
				'total_amount' => 1,
				'products' => 1,
			)
		);

		$total_orders = isset($orders[0]) ? $orders[0]['total_rows'] : 0;

		$columns = array();
		foreach ($orders as $order) {
			$order_products = $this->MsLoader->MsOrderData->getOrderProducts(array('order_id' => $order['order_id'], 'seller_id' => $seller_id));
			
			if ($this->config->get('msconf_hide_customer_email')) {
				$customer_name = "{$order['firstname']} {$order['lastname']}";
			} else {
				$customer_name = "{$order['firstname']} {$order['lastname']} ({$order['email']})";
			}
			
			$products = "";
			foreach ($order_products as $p) {
                $products .= "<p style='text-align:left'>";
				$products .= "<span class='name'>" . ($p['quantity'] > 1 ? "{$p['quantity']} x " : "") . "<a href='" . $this->url->link('product/product', 'product_id=' . $p['product_id'], 'SSL') . "'>{$p['name']}</a></span>";

                $options   = $this->model_account_order->getOrderOptions($order['order_id'], $p['order_product_id']);

                foreach ($options as $option)
                {
                    if ($option['type'] != 'file') {
                        $value = $option['value'];
                    } else {
                        $value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
                    }

                    $option['value']	=  utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value;

                    $products .= "<br />";
                    $products .= "<small> - {$option['name']} : {$option['value']} </small>";
                }

                $products .= "<span class='total'>" . $this->currency->format($p['seller_net_amt'], $order['currency_code'], $order['currency_value']) . "</span>";
				$products .= "</p>";
			}

			$suborder = $this->MsLoader->MsOrderData->getSuborders(array(
				'order_id' => $order['order_id'],
				'seller_id' => $this->customer->getId(),
				'single' => 1
			));

			$status_name = $this->MsLoader->MsHelper->getStatusName(array('order_status_id' => $order['order_status_id']));

			if (isset($suborder['order_status_id']) && $suborder['order_status_id'] && $order['order_status_id'] != $suborder['order_status_id']) {
				$status_name .= ' (' . $this->MsLoader->MsHelper->getStatusName(array('order_status_id' => $suborder['order_status_id'])) . ')';
			}

			$actions = '<a class="btn btn-primary" href="' . $this->url->link('seller/account-order/viewOrder', 'order_id=' . $order['order_id'], 'SSL') . '" title="' . $this->language->get('ms_view_modify') . '"><i class="fa fa-search"></i></a>';
			$actions .= '<a class="btn btn-default" target="_blank" href="' . $this->url->link('seller/account-order/invoice', 'order_id=' . $order['order_id'], 'SSL') . '" title="' . $this->language->get('ms_view_invoice') . '"><i class="fa fa-file-text-o"></i></a>';


			$columns[] = array_merge(
				$order,
				array(
					'order_id' => $order['order_id'],
					'customer_name' => $customer_name,
					'products' => $products,
					'suborder_status' => $status_name,
					'date_created' => date($this->language->get('date_format_short'), strtotime($order['date_added'])),
					'total_amount' => $this->currency->format($order['total_amount'], $order['currency_code'], $order['currency_value']),
					'view_order' => $actions
				)
			);
		}

		$this->response->setOutput(json_encode(array(
			'iTotalRecords' => $total_orders,
			'iTotalDisplayRecords' => $total_orders,
			'aaData' => $columns
		)));
	}

	public function viewOrder() {
		$order_id = isset($this->request->get['order_id']) ? (int)$this->request->get['order_id'] : 0;
		$this->load->model('account/order');

		$order_info = $this->model_account_order->getOrder($order_id, 'seller');
		$products = $this->MsLoader->MsOrderData->getOrderProducts(array(
			'order_id' => $order_id,
			'seller_id' => $this->customer->getId()
		));

		// stop if no order or no products belonging to seller
		if (!$order_info || empty($products)) {
			$this->response->redirect($this->url->link('seller/account-order', '', 'SSL'));
		}

		// load default OC language file for orders
		$this->data = array_merge($this->data, $this->load->language('account/order'));

		// order statuses
		$this->load->model('localisation/order_status');
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$suborder = $this->MsLoader->MsOrderData->getSuborders(array(
			'order_id' => $order_id,
			'seller_id' => $this->customer->getId(),
			'single' => 1
		));

		$this->data['suborder_status_id'] = $suborder ? $suborder['order_status_id'] : 0;
		$this->data['suborder_id'] = isset($suborder['suborder_id']) ? $suborder['suborder_id'] : '';

		// OC way of displaying addresses and invoices
		$this->data['invoice_no'] = isset($order_info['invoice_no']) ? $order_info['invoice_prefix'] . $order_info['invoice_no'] : '';

		$this->data['order_status_id'] = $order_info['order_status_id'];
		$this->data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));
		$this->data['order_id'] = $this->request->get['order_id'];

		$this->data['order_info'] = $order_info;

		$types = array("payment", "shipping");

		$this->_loadAddressData($types, $order_info);

		// products
		$this->data['products'] = array();
		foreach ($products as $product) {
			$this->data['products'][] = array(
				'product_id' => $product['product_id'],
				'name'     => $product['name'],
				'href' => $this->url->link('product/product', 'product_id=' . $product['product_id'], 'SSL'),
				'model'    => $product['model'],
				'option'     => $this->model_account_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']),
				'quantity' => $product['quantity'],
				'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
				'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
				'return'   => $this->url->link('account/return/insert', 'order_id=' . $order_info['order_id'] . '&product_id=' . $product['product_id'], 'SSL')
			);
		}

		// sub-order history entries
		$this->data['order_history'] = $this->MsLoader->MsOrderData->getSuborderHistory(array(
			'suborder_id' => $this->data['suborder_id']
		));

		// totals @todo
		$subordertotal = $this->currency->format(
			$this->MsLoader->MsOrderData->getOrderTotal($order_id, array('seller_id' => $this->customer->getId())),
			$order_info['currency_code'], $order_info['currency_value']
		);

		//$this->data['totals'] = $this->model_account_order->getOrderTotals($this->request->get['order_id']);
		$this->data['totals'][0] = array('text' => $subordertotal, 'title' => 'Total');

		// render
		$this->data['link_back'] = $this->url->link('seller/account-order', '', 'SSL');
		$this->data['continue'] = $this->url->link('account/order', '', 'SSL');

		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs(array(
			array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', 'SSL'),
			),
			array(
				'text' => $this->language->get('ms_account_dashboard_breadcrumbs'),
				'href' => $this->url->link('seller/account-dashboard', '', 'SSL'),
			),
			array(
				'text' => $this->language->get('ms_account_orders_breadcrumbs'),
				'href' => $this->url->link('seller/account-order', '', 'SSL'),
			)
		));

		$this->document->setTitle($this->language->get('text_order'));

		list($template, $children) = $this->MsLoader->MsHelper->loadTemplate('account-order-info');
		$this->response->setOutput($this->load->view($template, array_merge($this->data, $children)));
	}
	
	public function invoice() {
		// check order details
		$customer_id = $this->customer->getId();
		$order_id = isset($this->request->get['order_id']) ? (int)$this->request->get['order_id'] : 0;
		$this->load->model('account/order');

		$order_info = $this->model_account_order->getOrder($order_id, 'seller');
		$products = $this->MsLoader->MsOrderData->getOrderProducts(array(
			'order_id' => $order_id,
			'seller_id' => $customer_id
		));

		// stop if no order or no products belonging to seller
		if (!$order_info || empty($products)) $this->response->redirect($this->url->link('seller/account-order', '', 'SSL'));

        //get seller settings
		$seller_settings = $this->MsLoader->MsSetting->getSettings(array('seller_id' => $customer_id));
		$defaults = $this->MsLoader->MsSetting->getDefaults();
		$this->data['settings'] = array_merge($defaults, $seller_settings);

		$server = $this->request->server['HTTPS'] ? $this->config->get('config_ssl') : $this->config->get('config_url');

        $this->load->model('localisation/country');
        $this->data['settings']['slr_country'] = $this->model_localisation_country->getCountry($this->data['settings']['slr_country']);

		$this->load->model('tool/image');
		if (is_file(DIR_IMAGE . $this->data['settings']['slr_logo'])) {
			$this->data['logo'] = $this->MsLoader->MsFile->resizeImage($this->data['settings']['slr_logo'], 80, 80);
		} else {
			$this->data['logo'] = '';
		}

		// load default OC language file for orders
		$this->data = array_merge($this->data, $this->load->language('account/order'));

		// order statuses
		$this->load->model('localisation/order_status');

		// OC way of displaying addresses and invoices
		$this->data['invoice_no'] = isset($order_info['invoice_no']) ? $order_info['invoice_prefix'] . $order_info['invoice_no'] : '';
		$this->data['order_status_id'] = $order_info['order_status_id'];
		$this->data['order_id'] = $this->request->get['order_id'];

		$types = array("payment");
		$this->_loadAddressData($types, $order_info);

		// order info
		$this->data['order_info'] = $order_info;

		// products
		$this->data['products'] = array();
		foreach ($products as $product) {
			$this->data['products'][] = array(
				'product_id'=> $product['product_id'],
				'name'		=> $product['name'],
				'model'		=> $product['model'],
				'option'     => $this->model_account_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']),
				'quantity'	=> $product['quantity'],
				'price'		=> $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
				'total'		=> $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
				'return'	=> $this->url->link('account/return/insert', 'order_id=' . $order_info['order_id'] . '&product_id=' . $product['product_id'], 'SSL')
			);
		}

		// totals @todo
		$subordertotal = $this->currency->format($this->MsLoader->MsOrderData->getOrderTotal($order_id, array('seller_id' => $this->customer->getId() )));
		$this->data['totals'][0] = array('text' => $subordertotal, 'title' => 'Total');

		// custom styles
		$this->MsLoader->MsHelper->addStyle('multimerch/invoice/default', 'stylesheet', 'all');
		$this->MsLoader->MsHelper->addStyle('stylesheet', 'stylesheet', 'all');

		// OC's default header things
		$this->data['base'] = $server;
		$this->data['styles'] = $this->document->getStyles();
		$this->data['scripts'] = $this->document->getScripts();
		$this->data['lang'] = $this->language->get('code');
		$this->data['direction'] = $this->language->get('direction');
		$this->data['title'] = $this->language->get('heading_invoice_title');

		// load template parts
		list($template, $children) = $this->MsLoader->MsHelper->loadTemplate('multiseller/invoice/header');
		$head = $this->load->view($template, array_merge($this->data, $children));

		list($template, $children) = $this->MsLoader->MsHelper->loadTemplate('multiseller/invoice/body-default');
		$body = $this->load->view($template, array_merge($this->data, $children));

		list($template, $children) = $this->MsLoader->MsHelper->loadTemplate('multiseller/invoice/footer');
		$foot = $this->load->view($template, array_merge($this->data, $children));

		// render
		$this->response->setOutput($head . $body . $foot);
	}
		
	public function index() {
		$this->data['link_back'] = $this->url->link('account/account', '', 'SSL');
		
		$this->document->setTitle($this->language->get('ms_account_order_information'));
		
		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs(array(
			array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', 'SSL'),
			),
			array(
				'text' => $this->language->get('ms_account_dashboard_breadcrumbs'),
				'href' => $this->url->link('seller/account-dashboard', '', 'SSL'),
			),			
			array(
				'text' => $this->language->get('ms_account_orders_breadcrumbs'),
				'href' => $this->url->link('seller/account-order', '', 'SSL'),
			)
		));
		
		list($template, $children) = $this->MsLoader->MsHelper->loadTemplate('account-order');
		$this->response->setOutput($this->load->view($template, array_merge($this->data, $children)));
	}

	public function jxAddHistory() {
		if(!isset($this->request->post['order_comment']) || !isset($this->request->post['order_status']) || !isset($this->request->post['suborder_id'])) return false;
		if(empty($this->request->post['order_comment']) && !$this->request->post['order_status']) return false;

		// keep current status if not changing explicitly
		$suborderData = $this->MsLoader->MsOrderData->getSuborders(array(
			'suborder_id' => (int)$this->request->post['suborder_id'],
			'single' => 1
		));

		$suborder_status_id = $this->request->post['order_status'] ? (int)$this->request->post['order_status'] : $suborderData['order_status_id'];

		$this->MsLoader->MsOrderData->updateSuborderStatus(array(
			'suborder_id' => (int)$this->request->post['suborder_id'],
			'order_status_id' => $suborder_status_id
		));

		$this->MsLoader->MsOrderData->addSuborderHistory(array(
			'suborder_id' => (int)$this->request->post['suborder_id'],
			'comment' => $this->request->post['order_comment'],
			'order_status_id' => $suborder_status_id
		));

		// get customer information
		$this->load->model('checkout/order');
		$this->load->model('account/order');
		$order_info = $this->model_checkout_order->getOrder($suborderData['order_id']);

		$mails[] = array(
			'type' => MsMail::CMT_ORDER_UPDATED,
			'data' => array(
				'status' => $this->MsLoader->MsHelper->getStatusName(array('order_status_id' => $suborder_status_id)),
				'comment' => $this->request->post['order_comment'],
				'seller_id' => $this->customer->getId(),
				'order_id' => $suborderData['order_id'],

				// send email to customer
				'recipients' => $order_info['email'],
				'addressee' => $order_info['firstname']
			)
		);

		$this->MsLoader->MsMail->sendMails($mails);
	}

	private function _loadAddressData($types, $order_info) {
		foreach ($types as $key => $type) {

			$address_data_keys = array(
				'_firstname',
				'_lastname',
				'_company',
				'_address_1',
				'_address_2',
				'_city',
				'_postcode',
				'_zone',
				'_zone_code',
				'_country',
			);

			foreach ($address_data_keys as $address_data_key) {
				$this->data[$type . $address_data_key] = $order_info[$type . $address_data_key];
			}


			$this->data[$type . '_method'] = $order_info[$type . '_method'];
		}

		$this->data['telephone'] = $order_info['telephone'];
	}
}

?>
