<?php

namespace QuickProductDiscount\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ProductController extends AbstractActionController
{
	protected $shopTable;
	protected $productTable;
	
    public function indexAction()
    {
		$shopifyApi = new \Shopifyapi\Resources();
		$request = $this->getRequest();
		
		if($request->isPost()){
			$request_post = $this->getRequest()->getPost();

			$shop_info = $this->getShopTable()->getShopById($request_post['shop_id']);
			
			if(!empty($request_post['product_variant']['featured_image'])) {
				$image_url = $request_post['product_variant']['featured_image']['src'];
			} else {
				$image_url = "https:".$request_post['product']['images'][0];
			}
			
			$product = array(
				"product" => array(
					"title" => $request_post['product']['title'],
					"body_html" => $request_post['product']['description']."<span id='hidden-qdiscount' style='display: none;'></span><script>window.location.replace('/products/{$request_post['product_url']}');</script>",
					"product_type" => "qdiscount",
					"vendor" => $request_post['product']['vendor'],
					"variants" => array(
						array(
							"title" => $request_post['product_variant']['title'],
							"option1" => $request_post['product_variant']['option1'],
							"option2" => $request_post['product_variant']['option2'],
							"option3" => $request_post['product_variant']['option3'],
							"inventory_management" => $request_post['product_variant']['inventory_management'],
							"inventory_quantity" => $request_post['product_variant']['inventory_quantity'], 
							"price" => $request_post['discount_price'],
							"sku" => $request_post['product_variant']['sku'],
							"barcode" => $request_post['product_variant']['barcode'],
						)
					),
					"options" => $request_post['product']['options'],
					"images" => array(
						"src" => $image_url
					)
				)
			);
			
			$results = $shopifyApi->shopify_call($shop_info['access_token'], $shop_info['shopify_domain'], "/admin/products.json", $product, 'POST');
			$result = json_decode($results['response'], true);
			$created_product = $result['product']['variants'][0];
			
			$this->getProductTable()->saveProductInfo($request_post,$created_product);

			$view = new ViewModel(array('created_variant_id' => $created_product['id']));
		} else {
			return $this->redirect()->toRoute('home');
		}
		$view->setTerminal(true);
		return $view;
    }
	
	public function getShopTable()
    {
        if (!$this->shopTable) {
           $sm = $this->getServiceLocator();
           $this->shopTable = $sm->get('QuickProductDiscount\Model\ShopTable');
        }
        return $this->shopTable;
    }
	
	public function getProductTable()
    {
        if (!$this->productTable) {
           $sm = $this->getServiceLocator();
           $this->productTable = $sm->get('QuickProductDiscount\Model\ProductTable');
        }
        return $this->productTable;
    }
}