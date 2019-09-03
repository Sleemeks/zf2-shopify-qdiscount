<?php

namespace QuickProductDiscount\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class LogoutController extends AbstractActionController
{
    public function indexAction()
    {   
		$session = new Container('shop');
		$session->offsetUnset('id');
		$session->offsetUnset('name');
		return $this->redirect()->toRoute('login');
    }
}

