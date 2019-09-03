<?php

namespace QuickProductDiscount\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;


class PaymentController extends AbstractActionController
{
	protected $shopTable;
	protected $appPaymentTable;
	protected $discountOptionTable;
	
    public function indexAction()
    {
		$shopifyapi = new \Shopifyapi\Resources();
		$shop = new Container('shop');		
		$shopinfo = $this->getAppPaymentTable()->getPaymentByShopId($shop->id);
		
		if($shopinfo['payment_status'] == true) {
			return $this->redirect()->toRoute('home');
		} else {
		
			$charge_id = $this->params()->fromQuery('charge_id');
			
			if(isset($charge_id) && ($shopinfo['charge_id'] == $charge_id)) {
				
				$charge_answer = $shopifyapi->shopify_call($shopinfo['access_token'], $shopinfo['shopify_domain'], "/admin/recurring_application_charges/".$charge_id.".json", array(), 'GET');		
				$charge_answer = json_decode($charge_answer['response'], true);
				
				if($charge_answer['recurring_application_charge']['status'] == 'accepted'){
							
					$shopifyapi->shopify_call($shopinfo['access_token'], $shopinfo['shopify_domain'], "/admin/recurring_application_charges/".$charge_id."/activate.json", "", 'POST');
					
					$this->getAppPaymentTable()->updatePaymentStatus($shop['id'], true);
					$this->getAppPaymentTable()->updateTrialTime($shop['id'], false);
					
					if(empty($shopinfo['start_payment_day'])) {
						$start_day = time();
						$this->getAppPaymentTable()->updateStartPaymentDay($shop['id'], time());
					} else {
						$start_day = $shopinfo['start_payment_day'];
					}
					
					$start_date = strtotime(date("Y-m-d", $start_day));
					$last_date = strtotime(date("Y-m-d", $start_date) . "last day of +1 month");
					$last_day = date("d-m-Y", $last_date);
					$start_day = date("d", $start_date);
					$payment_day = date("d", $last_date);
					
					if($start_day > $payment_day) {
						$payment_date = strtotime($last_day);
					} elseif ($start_day <= $payment_day) {
						$payment_date = strtotime(date("Y-m-d", $start_date) . "+1 month");
					}
					
					$this->getAppPaymentTable()->updatePaymentDay($shop['id'], $payment_date);
					$this->getAppPaymentTable()->updateAllowed($shop['id'], true);
					
					/*----------Insert script to shop if if paid subscription-----------*/
					
					$modified_script = $shopifyapi->shopify_call($shopinfo['access_token'], $shopinfo['shopify_domain'], "/admin/script_tags.json", array(), 'GET');
					$script = json_decode($modified_script['response'], true);
					if(empty($script['script_tags'])) {
						$modify_script = array(
							"script_tag" => array(
								"event" => "onload",
								"src"	=> "http://".$this->getRequest()->getServer('HTTP_HOST').$this->url()->fromRoute('home').'js-qdiscount/'.$shop['id'].'.js'
							)
						);
						$modified_script = $shopifyapi->shopify_call($shopinfo['access_token'], $shopinfo['shopify_domain'], "/admin/script_tags.json", $modify_script, 'POST');
						$script = json_decode($modified_script['response'], true);
						
						$this->getDiscountOptionTable()->updateScriptId($shop['id'],$script['script_tag']['id']);
					}
					
					/*---------------------*/
					
				} elseif($charge_answer['recurring_application_charge']['status'] == 'declined'){
					$this->getAppPaymentTable()->updatePaymentStatus($shop['id'], false);
				}
				
				$this->getAppPaymentTable()->updatePaymentInfo($shop['id']);
			
				return $this->redirect()->toRoute('home');
			}
				
			/* Create payment */ //-------------------------------//
			//
			if(!empty($shopinfo['confirmation_url'])) {
				$url = $shopinfo['confirmation_url'];
			} else {
				$charge = array(
					"recurring_application_charge" => array(
						"name"          => "Main",
						"price"         => 15.0,
						"return_url"    => "http://".$this->getRequest()->getServer('HTTP_HOST').$this->url()->fromRoute('payment')
						//"test"			=> true
					)
				);
					
				$modified = $shopifyapi->shopify_call($shopinfo['access_token'], $shopinfo['shopify_domain'], "/admin/recurring_application_charges.json", $charge, 'POST');	
				$modified = json_decode($modified['response'], true);
				
				$this->getAppPaymentTable()->updatePaymentInfo($shop['id'], $modified['recurring_application_charge']);
				$url = $modified['recurring_application_charge']['confirmation_url'];
			}
			return $this->redirect()->toUrl($url);
			//
			/* End of create payment *///-------------------------------//
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