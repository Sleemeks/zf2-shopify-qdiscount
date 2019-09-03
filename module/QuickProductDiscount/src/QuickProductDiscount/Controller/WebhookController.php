<?php

namespace QuickProductDiscount\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\PhpEnvironment\Request;

class WebhookController extends AbstractActionController
{
    protected $shopTable;
	protected $productTable;
	protected $discountOptionTable;
    
    public function indexAction()
    {
		$this->redirectHome();
		/*
		$info = file_get_contents('php://input');
		$a = __DIR__ . '/text.txt';		
		$file=fopen($a, "a");
		fwrite($file, $info);
		*/
        //$shopifyapi = new \Shopifyapi\Resources();
        //$shopifyapi->shopify_call();
		//return $this->response;
    }
    
    public function uninstallAction()
    {
        $this->redirectHome();	
        $data = file_get_contents('php://input');
        $shop = json_decode($data, true);
		$this->getDiscountOptionTable()->deleteDiscount($shop['id']);
		unlink($this->getRequest()->getServer('DOCUMENT_ROOT').'/js-qdiscount/'.$shop['id'].'.js');
        $this->getShopTable()->updateUninstall($shop['id']);
		return $this->response;
    }
	
	public function updateAction()
	{
		$this->redirectHome();
		$info = file_get_contents('php://input');
		$shop = json_decode($info, true);
		$this->getShopTable()->updateShopById($info, $shop);	
		return $this->response;
	}

	
	public function productupdateAction()
	{		
		$this->redirectHome();
		$shopifyapi = new \Shopifyapi\Resources();
		$info = file_get_contents('php://input');
			
		$updated_product = json_decode($info, true);
				
		$shop_id = $this->params()->fromRoute('id');		
		$shop = $this->getShopTable()->getShopById($shop_id);
		
		$product_info = $this->getProductTable()->getProduct($shop_id,$updated_product['id']);
		
		if($product_info) {
			foreach($updated_product['variants'] as $prod_upd) {
				foreach($product_info as $prod_inf) {
					if($prod_upd['id'] == $prod_inf['variant_id']) {
						$modify_product_info = array(
							'product' => array(
								'body_html' => $updated_product['body_html']."<span id='hidden-qdiscount' style='display: none;'></span>",
								'title' => $updated_product['title'],
							)
						);
						$shopifyapi->shopify_call($shop['access_token'],$shop['shopify_domain'],"/admin/products/".$prod_inf['created_product_id'].".json", $modify_product_info, 'PUT');
						
						$modify_product_variant_info = array(
							'variant' => array(
								'title' => $prod_upd['title'],
								'option1' => $prod_upd['option1'],
								'option2' => $prod_upd['option2'],
								'option3' => $prod_upd['option3'],
								'barcode' => $prod_upd['barcode'],
								'inventory_management' => $prod_upd['inventory_management'],
								'sku' => $prod_upd['sku'],
								'price' => $prod_upd['price'],
								'inventory_quantity' => $prod_upd['inventory_quantity']
							)
						);
						$shopifyapi->shopify_call($shop['access_token'],$shop['shopify_domain'],"/admin/variants/".$prod_inf['created_variant_id'].".json", $modify_product_variant_info, 'PUT');
					}
				}
			}
		}
		return $this->response;
	}
	
	public function productdeleteAction()
	{
		$this->redirectHome();
		$shopifyapi = new \Shopifyapi\Resources();
		$info = file_get_contents('php://input');
		
		$product_deleted = json_decode($info, true);
		
		$shop_id = $this->params()->fromRoute('id');
		$shop = $this->getShopTable()->getShopById($shop_id);
		$product_info = $this->getProductTable()->getProduct($shop_id,$product_deleted['id']);
		
		if($product_info){
			foreach($product_info as $product){
				$delete = $shopifyapi->shopify_call($shop['access_token'],$shop['shopify_domain'], "/admin/products/{$product['created_product_id']}.json", array(), 'DELETE');
			}
			$this->getProductTable()->deleteProduct($shop_id,$product_deleted['id']);
		}
		return $this->response;
	}
	
	public function ordercreateAction()
	{
		$this->redirectHome();
		$shopifyapi = new \Shopifyapi\Resources();
		$info = file_get_contents('php://input');
		
		$order_create = json_decode($info, true);
		
		$shop_id = $this->params()->fromRoute('id');
		$shop = $this->getShopTable()->getShopById($shop_id);
		
		foreach($order_create['line_items'] as $order) {
			$product = $this->getProductTable()->getCreatedProduct($shop_id,$order['product_id']);
			if($product['created_product_id'] == $order['product_id']) {
				$single_product = $shopifyapi->shopify_call($shop['access_token'],$shop['shopify_domain'],"/admin/products/".$product['product_id'].".json", array(), 'GET');
				$single_product = json_decode($single_product['response'], true);
				foreach($single_product['product']['variants'] as $origin_product) {
					if($origin_product['id'] == $product['variant_id']) {
						$quantity = $origin_product['inventory_quantity'] - $order['quantity'];
						$modify_product = array(
							'variant' => array(
								'inventory_quantity' => $quantity
							)
						);
						$shopifyapi->shopify_call($shop['access_token'],$shop['shopify_domain'],"/admin/variants/".$product['variant_id'].".json", $modify_product, 'PUT');
						break;
					}
				}
			}
		}
		return $this->response;
	}
	
	public function refundAction()
	{		
		$this->redirectHome();
		$shopifyapi = new \Shopifyapi\Resources();
		$info = file_get_contents('php://input');
			
		$refund_create = json_decode($info, true);
		
		$shop_id = $this->params()->fromRoute('id');
		$shop = $this->getShopTable()->getShopById($shop_id);
		
		foreach($refund_create['refund_line_items'] as $refund) {
			$product = $this->getProductTable()->getCreatedProduct($shop_id,$refund['line_item']['product_id']);
			if($product) {
				$single_product = $shopifyapi->shopify_call($shop['access_token'],$shop['shopify_domain'],"/admin/products/".$product['product_id'].".json", array(), 'GET');
				$single_product = json_decode($single_product['response'], true);
				
				foreach($single_product['product']['variants'] as $origin_product) {
					if($origin_product['id'] == $product['variant_id']) {
						$quantity = $origin_product['inventory_quantity'] + $refund['quantity'];
						$modify_product = array(
							'variant' => array(
								'inventory_quantity' => $quantity
							)
						);
						$shopifyapi->shopify_call($shop['access_token'],$shop['shopify_domain'],"/admin/variants/".$product['variant_id'].".json", $modify_product, 'PUT');
						break;
					}
				}
			}
		}
		return $this->response;
	}
	
	public function redirectHome()
	{
		$request = new Request();
		if($request->getServer('REQUEST_METHOD') == 'GET'){
			return $this->redirect()->toRoute('home');
		}
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
	public function getDiscountOptionTable()
    {
        if (!$this->discountOptionTable) {
           $sm = $this->getServiceLocator();
           $this->discountOptionTable = $sm->get('QuickProductDiscount\Model\DiscountOptionTable');
        }
        return $this->discountOptionTable;
    }
}
