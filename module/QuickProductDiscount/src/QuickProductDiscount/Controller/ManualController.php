<?php

namespace QuickProductDiscount\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class ManualController extends AbstractActionController
{	
	protected $shopTable;
    
	public function indexAction()
    {	
		$shop = new Container('shop');
		$shop_info = $this->getShopTable()->getShopById($shop->id);
		
		$redirect_to_get_started = new Container('start');
		$redirect_to_get_started->offsetSet('redirect', false);
		
		return new ViewModel(array('domain' => $shop_info['shopify_domain']));
	}
	
	public function getShopTable()
    {
        if (!$this->shopTable) {
           $sm = $this->getServiceLocator();
           $this->shopTable = $sm->get('QuickProductDiscount\Model\ShopTable');
        }
        return $this->shopTable;
    }
}