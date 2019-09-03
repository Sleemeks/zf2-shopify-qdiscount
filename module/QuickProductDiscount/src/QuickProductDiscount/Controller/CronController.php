<?php

namespace QuickProductDiscount\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class CronController extends AbstractActionController
{
	protected $productTable;
	protected $shopTable;
	protected $appPaymentTable;
	protected $discountOptionTable;
	
	public function indexAction()
	{
		// Delete products every 30 days
		
		$shopifyapi = new \Shopifyapi\Resources();
		$shop_info = $this->getShopTable()->fetchAll();
		foreach($shop_info as $shop) {
			$product_info = $this->getProductTable()->getProductByShopId($shop['shop_id']);
			foreach($product_info as $product) {
				if($product['life_time'] <= time()) {
					$shopifyapi->shopify_call($shop['access_token'],$shop['shopify_domain'], "/admin/products/".$product['created_product_id'].".json", array(), 'DELETE');
					$this->getProductTable()->deleteCreatedProduct($shop['shop_id'],$product['created_product_id']);
				}
			}
		}
		return $this->response;
	}
	
	public function accessAction()
	{
		// Check every 12 hours for access to the admin panel
		
		$shopifyapi = new \Shopifyapi\Resources();
		$shop_info = $this->getShopTable()->fetchAll();
		foreach($shop_info as $shop) {
			$paymentinfo = $this->getAppPaymentTable()->getPayment($shop['shop_id']);
			
			if($paymentinfo['trial_time']) {
				$left_seconds_trial = $paymentinfo['trial_time'] - 1447567518; //time();
				$left_days_trial =  $left_seconds_trial/60/60/24;
				if(ceil($left_days_trial) <= 0) {
					$discount_option = $this->getDiscountOptionTable()->getOptionByShopId($shop['shop_id']);
					$this->getAppPaymentTable()->updateTrialTime($shop['shop_id'], 'no_trial');
					$this->getAppPaymentTable()->updateAllowed($shop['shop_id'], false);
					$shopifyapi->shopify_call($shop['access_token'], $shop['shopify_domain'], "/admin/script_tags/".$discount_option['shopify_script_id'].".json", array(), 'DELETE');
				}
				
			} elseif($paymentinfo['trial_time'] == false) {
				
				$left_seconds_subscription = $paymentinfo['payment_day'] - time();
				$left_days_subscription =  $left_seconds_subscription/60/60/24;
				
				if(ceil($left_days_subscription) <= 5 && ceil($left_days_subscription) > 0) {
					$this->getAppPaymentTable()->updatePaymentStatus($shop['shop_id'], false);
				} elseif (ceil($left_days_subscription) <= 0) {
					$discount_option = $this->getDiscountOptionTable()->getOptionByShopId($shop['shop_id']);
					$this->getAppPaymentTable()->updateAllowed($shop['shop_id'], false);
					$this->getAppPaymentTable()->updateStartPaymentDay($shop['shop_id'], "");
					$shopifyapi->shopify_call($shop['access_token'], $shop['shopify_domain'], "/admin/script_tags/".$discount_option['shopify_script_id'].".json", array(), 'DELETE');
				}
			}
		}
		return $this->response;
	}
	
	public function getProductTable()
    {
        if (!$this->productTable) {
           $sm = $this->getServiceLocator();
           $this->productTable = $sm->get('QuickProductDiscount\Model\ProductTable');
        }
        return $this->productTable;
    }
	public function getShopTable()
    {
        if (!$this->shopTable) {
           $sm = $this->getServiceLocator();
           $this->shopTable = $sm->get('QuickProductDiscount\Model\ShopTable');
        }
        return $this->shopTable;
    }
	public function getAppPaymentTable()
    {
        if (!$this->appPaymentTable) {
           $sm = $this->getServiceLocator();
           $this->appPaymentTable = $sm->get('QuickProductDiscount\Model\AppPaymentTable');
        }
        return $this->appPaymentTable;
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