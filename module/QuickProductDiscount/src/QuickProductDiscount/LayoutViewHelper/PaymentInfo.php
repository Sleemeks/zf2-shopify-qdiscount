<?php
namespace QuickProductDiscount\LayoutViewHelper;

use Zend\View\Helper\AbstractHelper;

class PaymentInfo extends AbstractHelper {

    protected $val;

    public function __invoke() 
    {
        return $this->val;
    }
	
    public function getValue($sm, $myCountTable)
    {
        $this->val = $sm->get($myCountTable);
        return $this->val;        
    }
}