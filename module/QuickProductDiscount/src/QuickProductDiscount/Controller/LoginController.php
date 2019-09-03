<?php

namespace QuickProductDiscount\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class LoginController extends AbstractActionController
{
    public function indexAction()
    {   
        return new ViewModel();
    }
}

