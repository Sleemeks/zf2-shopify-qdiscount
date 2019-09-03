<?php

namespace QuickProductDiscount\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Math\Rand;

class IndexController extends AbstractActionController
{
	protected $discountOptionTable;
	protected $appPaymentTable;
	
    public function indexAction()
	{
		$shop = new Container('shop');
		$welcome_message = new Container('message');
		$redirect_to_get_started = new Container('start');
		$shopifyapi = new \Shopifyapi\Resources();
		
		if($redirect_to_get_started->redirect){
			return $this->redirect()->toRoute('get-started');
		}
		
		$payment_and_shopinfo = $this->getAppPaymentTable()->getPaymentByShopId($shop->id);
		$discount_option = $this->getDiscountOptionTable()->getOptionByShopId($shop->id);
		
		// Checking access for admin
		
		if($payment_and_shopinfo['trial_time']) {
			$left_seconds_trial = $payment_and_shopinfo['trial_time'] - time();
			$left_days_trial =  $left_seconds_trial/60/60/24;
			if(ceil($left_days_trial) <= 0) {
				$this->getAppPaymentTable()->updateTrialTime($shop->id, 'no_trial');
				$this->getAppPaymentTable()->updateAllowed($shop->id, false);
				$this->deleteScript($shopifyapi,$payment_and_shopinfo,$discount_option);
			}
				
		} elseif($payment_and_shopinfo['trial_time'] == false) {
			
			$left_seconds_subscription = $payment_and_shopinfo['payment_day'] - time();
			$left_days_subscription =  $left_seconds_subscription/60/60/24;
			
			if(ceil($left_days_subscription) <= 5 && ceil($left_days_subscription) > 0) {
				$this->getAppPaymentTable()->updatePaymentStatus($shop['shop_id'], false);
			} elseif (ceil($left_days_subscription) <= 0) {
				
				$this->getAppPaymentTable()->updateAllowed($shop['shop_id'], false);
				$this->getAppPaymentTable()->updateStartPaymentDay($shop['shop_id'], "");
				$this->deleteScript($shopifyapi,$payment_and_shopinfo,$discount_option);
			}
		}
		
		$payment_and_shopinfo = $this->getAppPaymentTable()->getPaymentByShopId($shop->id);
		$discount_option = $this->getDiscountOptionTable()->getOptionByShopId($shop->id);
		
		if($payment_and_shopinfo['allowed'] == false) {
			$view = new ViewModel(array(
				'allowed' => $payment_and_shopinfo['allowed']
			));	
		} else {
			$request = $this->getRequest();
			if($request->isPost()){
				$option = $request->getPost();
				$this->getDiscountOptionTable()->setOptionByShopId($shop->id,$option['from'],$option['to']);
				$script_url = $this->getRequest()->getServer('DOCUMENT_ROOT').'/js-qdiscount/'.$shop->id.'.js';
				$script = $this->getServiceLocator()->get('ScriptDiscount')->code($option['from'],$option['to']);
				file_put_contents($script_url, $script);
			}
			$view = new ViewModel(array(
				'from' 		=> $discount_option['discount_from'],
				'to'		=> $discount_option['discount_to'],
				'message'	=> $welcome_message->show
			));			
			$welcome_message->offsetSet('show', false);
		}
		return $view;
    }
	
	public function deleteScript($shopifyapi,$payment_and_shopinfo,$discount_option)
	{
		$shopifyapi->shopify_call($payment_and_shopinfo['access_token'], $payment_and_shopinfo['shopify_domain'], "/admin/script_tags/".$discount_option['shopify_script_id'].".json", array(), 'DELETE');
	}
	
	public function getDiscountOptionTable()
    {
        if (!$this->discountOptionTable) {
           $sm = $this->getServiceLocator();
           $this->discountOptionTable = $sm->get('QuickProductDiscount\Model\DiscountOptionTable');
        }
        return $this->discountOptionTable;
    }
	public function getAppPaymentTable()
    {
        if (!$this->appPaymentTable) {
           $sm = $this->getServiceLocator();
           $this->appPaymentTable = $sm->get('QuickProductDiscount\Model\AppPaymentTable');
        }
        return $this->appPaymentTable;
    }
}