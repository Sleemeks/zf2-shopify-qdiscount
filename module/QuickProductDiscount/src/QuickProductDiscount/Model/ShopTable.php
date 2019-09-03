<?php

namespace QuickProductDiscount\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;

class ShopTable
{
    protected $tableGateway;
    
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }
    
    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select(); 
        return $resultSet;
    }
	
    public function saveShop($token, $info, $shop = array())
    {
        if(empty($shop['customer_email'])){
            $shop['customer_email'] = $shop['email'];
        }
        $data = array(
            'shop_id' 			=> $shop['id'],
            'email'   			=> $shop['customer_email'],
            'name'              => $shop['name'],
            'shopify_domain'    => $shop['myshopify_domain'],
            'access_token'      => $token,
            'additional_info'   => $info
        );
        $this->tableGateway->insert($data);
    }
    
    public function updateShopById($info, $shop = array(), $token = false)
    {
        if(empty($shop['customer_email'])){
            $shop['customer_email'] = $shop['email'];
        }
        $data = array(
            'email'   			=> $shop['customer_email'],
            'name'              => $shop['name'],
            'shopify_domain'	=> $shop['myshopify_domain'],
            'additional_info'	=> $info,
            'uninstall'			=> 0
        );
		
		if($token) {
			$data['access_token'] = $token;
		}
		
        $where = array('shop_id' => $shop['id']);
        $this->tableGateway->update($data, $where);
    }
	
    public function getShopById($shop_id)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->columns( 
            array(
                'shop_id','email','name','shopify_domain',
                'access_token','additional_info','uninstall'
            )
        );
        $select->where(array('shop_id' => $shop_id));
        $rowset = $this->tableGateway->selectWith($select);
        $resultSet = new ResultSet();
        $resultSet->initialize($rowset);
        $array = $resultSet->toArray();
		
        foreach($array as $info) {
            return $info;
        }
    }
    
    public function getOAuth($shop_id)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->columns( array('access_token','shop_id'));
        $select->where(array('shop_id' => $shop_id));
        $rowset = $this->tableGateway->selectWith($select);
        $resultSet = new ResultSet();
        $resultSet->initialize($rowset);
        $array = $resultSet->toArray();
		
        foreach($array as $info) {
            return $info;
        }
    }
	
	public function updateUninstall($shop_id)
	{
		$data = array(
			'uninstall'	=> 1,
			'access_token' => ''
        );
		$where = array('shop_id' => $shop_id);
        $this->tableGateway->update($data, $where);
	}
}