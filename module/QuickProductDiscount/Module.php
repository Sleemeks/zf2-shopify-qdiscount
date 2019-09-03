<?php

namespace QuickProductDiscount;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use Zend\Session\Config\SessionConfig;
use Zend\Session\SessionManager;
use Zend\Session\Container;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;

use QuickProductDiscount\Model\ShopTable;
use QuickProductDiscount\Model\AppPaymentTable;
use QuickProductDiscount\Model\DiscountOptionTable;
use QuickProductDiscount\Model\ProductTable;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
		
		$eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this,'boforeDispatch'),100);
		
		$headers = $e->getResponse()->getHeaders();
        $headers->addHeaderLine('Access-Control-Allow-Origin: *');
		
		$config = $e->getApplication()->getServiceManager()->get('Configuration');
        $sessionConfig = new SessionConfig();
        $sessionConfig->setOptions($config['session']);
        $sessionManager = new SessionManager($sessionConfig);
        $sessionManager->start();
        Container::setDefaultManager($sessionManager);
		
		date_default_timezone_set('Europe/Berlin');
    }
	
	public function boforeDispatch(MvcEvent $event)
	{
		$request = $event->getRequest();
		$response = $event->getResponse();
		$whiteList = array(
			'QuickProductDiscount\Controller\Webhook-uninstall',
			'QuickProductDiscount\Controller\Webhook-update',
			'QuickProductDiscount\Controller\Webhook-productupdate',
			'QuickProductDiscount\Controller\Webhook-productdelete',
			'QuickProductDiscount\Controller\Webhook-ordercreate',
			'QuickProductDiscount\Controller\Webhook-refund',
			'QuickProductDiscount\Controller\Product-index',
			'QuickProductDiscount\Controller\Manual-index',
			'QuickProductDiscount\Controller\Logout-index',
			'QuickProductDiscount\Controller\Login-index',
		);
    
		$controller = $event->getRouteMatch()->getParam('controller');
		$action = $event->getRouteMatch()->getParam('action');
		$requestedResourse = $controller . "-" . $action;
		
		$session = new Container('shop');
    	
		if (!$session->offsetExists('id')) {
			if($requestedResourse == 'QuickProductDiscount\Controller\Webhook-index' || in_array($requestedResourse, $whiteList)){
				return true;
			}else {
				$url = '/logout';
				$response->setHeaders($response->getHeaders()->addHeaderLine('Location', $url));
				$response->setStatusCode(302);
			}
			$response->sendHeaders();
		}
	}
	
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                    'Shopifyapi' => __DIR__ . '/../../vendor/Shopifyapi'
                ),
            ),
        );
    }
    
    public function getServiceConfig()
    {
        return array(
			'invokables' => array(
                'ScriptDiscount' => 'QuickProductDiscount\ScriptDiscount\Script'
            ),
            'factories' => array(
                'QuickProductDiscount\Model\ShopTable' => function($sm) {
                    $tableGateway = $sm->get('ShopTableGateway');
                    return new ShopTable($tableGateway);
                },
				'QuickProductDiscount\Model\AppPaymentTable' => function($sm) {
                    $sqlGateway = $sm->get('SqlAdapter');
                    $sql = new AppPaymentTable($sqlGateway);
                    return $sql;
                },
				'QuickProductDiscount\Model\DiscountOptionTable' => function($sm) {
                    $sqlGateway = $sm->get('SqlAdapter');
                    $sql = new DiscountOptionTable($sqlGateway);
                    return $sql;
                },
				'QuickProductDiscount\Model\ProductTable' => function($sm) {
                    $sqlGateway = $sm->get('SqlAdapter');
                    $sql = new ProductTable($sqlGateway);
                    return $sql;
                },
				
				
				
                'ShopTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    return new TableGateway('shop', $dbAdapter);
                },
				'SqlAdapter' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    return new Sql($dbAdapter);
                },
            ),
        );
    }
    
    public function getViewHelperConfig() 
    {
        return array(
            'factories' => array(
                'PaymentInfo' => function($sm) {
                    $helper = new \QuickProductDiscount\LayoutViewHelper\PaymentInfo();
                    $helper->getValue($sm->getServiceLocator(), 'QuickProductDiscount\Model\AppPaymentTable');
                    return $helper;
                },
            ),
        );
    }
}