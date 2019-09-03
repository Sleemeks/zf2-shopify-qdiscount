<?php

namespace QuickProductDiscount\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

use QuickProductDiscount\Permission\General;

class InstallController extends AbstractActionController
{
    protected $shopTable;
	protected $appPaymentTable;
    protected $discountOptionTable;
	
    public function indexAction()
    {   
        $view = new ViewModel();
        $request = $this->getRequest()->getQuery();
        $shop = $request['shop'];
        if(isset($shop)){
            $redirect_uri = "http://".$this->getRequest()->getServer('HTTP_HOST').$this->url()->fromRoute('install', array('action' => 'generatetoken'));
            $install_url = 'http://' . $shop . "/admin/oauth/authorize?client_id=" . General::API_KEY . "&scope=" . General::SCOPES . "&redirect_uri=" . urlencode($redirect_uri);
            return $this->redirect()->toUrl($install_url);
        } else{
            $request = $this->getRequest();
            if($request->isPost()){
                $login = $request->getPost('login');
                if(!empty($login)) {
                    $toInstall = "http://".$this->getRequest()->getServer('HTTP_HOST').$this->url()->fromRoute('install'); 
                    $url = "http://".htmlspecialchars($request->getPost('login')).".myshopify.com/admin/oauth/authorize?client_id=" . General::API_KEY . "&scope=" . General::SCOPES . "&redirect_uri=" . urlencode($toInstall);
                    return $this->redirect()->toUrl($url);
                }
            } 
            return $this->redirect()->toRoute('login');
        }
        return $view;
    }
    
    public function generatetokenAction()
    {
        $shopifyApi = new \Shopifyapi\Resources();
        
        $request = $this->getRequest()->getQuery();
        $shop = $request['shop'];

        $query = array(
            "Content-type" => "application/json", 
            "client_id" => General::API_KEY, 
            "client_secret" => General::SECRET, 
            "code" => $request["code"]
        );
		
        $shopify_response = $shopifyApi->shopify_install(NULL, $shop, "/admin/oauth/access_token", $query, 'POST');
        $shopify_response = json_decode($shopify_response['response']);
       		
		$access_token = get_object_vars($shopify_response);
        $token =  $access_token['access_token'];
        
        $getShop = $this->getShopifyShopInfo($shop,$token,$shopifyApi);
        $additional_info = serialize($getShop);	
        
        $oauth = $this->getShopTable()->getOAuth($getShop['shop']['id']);
        
        if(!empty($oauth['shop_id']) && empty($oauth['access_token'])) {
            $this->getShopTable()->updateShopById($additional_info,$getShop['shop'],$token);
        } elseif(empty($oauth['shop_id'])) {
            $this->getShopTable()->saveShop($token,$additional_info,$getShop['shop']);
			$this->getAppPaymentTable()->setTrialTime($getShop['shop']['id']);
        }
        
        
        $hook = $shopifyApi->shopify_call($token, $shop, "/admin/webhooks.json", array(), 'GET');
        $webhooks = json_decode($hook['response'], true);
        
		
		if(!isset($webhooks['webhooks'][0])){
			$welcome_message = new Container('message');
            $welcome_message->offsetSet('show', true);
            $redirect_to_get_started = new Container('start');
            $redirect_to_get_started->offsetSet('redirect', true);
			
			$this->insertScriptToShop($shop,$token,$shopifyApi,$getShop['shop']['id']);
			$this->insertWebhookAppUninstall($shop,$token,$shopifyApi);
			$this->insertWebhookShopUpdate($shop,$token,$shopifyApi);
			$this->insertWebhookProductDelete($shop,$token,$shopifyApi,$getShop['shop']['id']);
			$this->insertWebhookProductUpdate($shop,$token,$shopifyApi,$getShop['shop']['id']);
			$this->insertWebhookOrderCreate($shop,$token,$shopifyApi,$getShop['shop']['id']);
			$this->insertWebhookRefund($shop,$token,$shopifyApi,$getShop['shop']['id']);
        }
        
        $session = new Container('shop');
        $session->offsetSet('id', $getShop['shop']['id']);
        $session->offsetSet('name', $getShop['shop']['name']);
        return $this->redirect()->toRoute("home");
    }
    
    public function getShopifyShopInfo($shop,$token,$shopifyApi)
    {
        $getShop = $shopifyApi->shopify_call($token, $shop, "/admin/shop.json", array(), 'GET');
        $shopinfo = json_decode($getShop['response'], true);
        return $shopinfo;	
    }
	public function insertScriptToShop($shop,$token,$shopifyApi,$shop_id)
	{
		$script_url = $this->getRequest()->getServer('DOCUMENT_ROOT').'/js-qdiscount/'.$shop_id.'.js';	
		$script = "$(document).ready(function(){var qdiscount = 'qdiscount';});";
		file_put_contents($script_url, $script);
		
		$modify_script = array(
			"script_tag" => array(
				"event" => "onload",
				"src"	=> "http://".$this->getRequest()->getServer('HTTP_HOST').$this->url()->fromRoute('home').'js-qdiscount/'.$shop_id.'.js'
			)
		);
		$modified_script = $shopifyApi->shopify_call($token, $shop, "/admin/script_tags.json", $modify_script, 'POST');
		$script = json_decode($modified_script['response'], true);
			
		$this->getDiscountOptionTable()->saveScriptId($shop_id,$script['script_tag']['id']);
	}
    public function insertWebhookAppUninstall($shop,$token,$shopifyApi)
    {
        $modify_webhook = array(
            "webhook" => array(
                "topic" 	=> "app/uninstalled",
                "address" 	=> "http://".$this->getRequest()->getServer('HTTP_HOST').$this->url()->fromRoute('webhook', array("action" => "uninstall")),
                "format" 	=> "json"	
            )
        );
        $modified_webhook = $shopifyApi->shopify_call_webhook($token, $shop, "/admin/webhooks.json", json_encode($modify_webhook), 'POST');        
    }
	public function insertWebhookShopUpdate($shop,$token,$shopifyApi)
	{
		$modify_webhook = array(
			"webhook" => array(
				"topic" 		=> "shop/update",
				"address" 		=> "http://".$this->getRequest()->getServer('HTTP_HOST').$this->url()->fromRoute('webhook', array("action" => "update")),
				"format" 		=> "json"	
			)
		);
		$modified_webhook = $shopifyApi->shopify_call_webhook($token, $shop, "/admin/webhooks.json", json_encode($modify_webhook), 'POST');
	}	
	public function insertWebhookProductUpdate($shop,$token,$shopifyApi,$shop_id)
	{
		$modify_webhook = array(
			"webhook" => array(
				"topic" => "products/update",
				"address" => "http://".$this->getRequest()->getServer('HTTP_HOST').$this->url()->fromRoute('webhook', array("action" => "productupdate"))."/{$shop_id}",
				"format" => "json"
			)
		);
		$modified_webhook = $shopifyApi->shopify_call_webhook($token, $shop, "/admin/webhooks.json", json_encode($modify_webhook), 'POST');
	}
	public function insertWebhookProductDelete($shop,$token,$shopifyApi,$shop_id)
	{
		$modify_webhook = array(
			"webhook" => array(
				"topic" => "products/delete",
				"address" => "http://".$this->getRequest()->getServer('HTTP_HOST').$this->url()->fromRoute('webhook', array("action" => "productdelete"))."/{$shop_id}",
				"format" => "json"
			)
		);
		$modified_webhook = $shopifyApi->shopify_call_webhook($token, $shop, "/admin/webhooks.json", json_encode($modify_webhook), 'POST');
	}
	public function insertWebhookOrderCreate($shop,$token,$shopifyApi,$shop_id)
	{
		$modify_webhook = array(
			"webhook" => array(
				"topic" => "orders/create",
				"address" => "http://".$this->getRequest()->getServer('HTTP_HOST').$this->url()->fromRoute('webhook', array("action" => "ordercreate"))."/{$shop_id}",
				"format" => "json"
			)
		);
		$modified_webhook = $shopifyApi->shopify_call_webhook($token, $shop, "/admin/webhooks.json", json_encode($modify_webhook), 'POST');
	}
	public function insertWebhookRefund($shop,$token,$shopifyApi,$shop_id)
	{
		$modify_webhook = array(
			"webhook" => array(
				"topic" => "refunds/create",
				"address" => "http://".$this->getRequest()->getServer('HTTP_HOST').$this->url()->fromRoute('webhook', array("action" => "refund"))."/{$shop_id}",
				"format" => "json"
			)
		);
		$modified_webhook = $shopifyApi->shopify_call_webhook($token, $shop, "/admin/webhooks.json", json_encode($modify_webhook), 'POST');
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