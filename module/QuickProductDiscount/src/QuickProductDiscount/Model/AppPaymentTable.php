<?php

namespace QuickProductDiscount\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;

class AppPaymentTable
{
    protected $sql;
    
    public function __construct(Sql $sql) 
	{
        $this->sql = $sql;
    }
	
	public function setTrialTime($shop_id)
	{
		$insert = $this->sql->insert();
        $insert->into('app_payment');
        $insert->values(array(
            'shop_id' 		=> $shop_id,
            'trial_time'	=> time() + 60*60*24*15
        ));
        $statement = $this->sql->prepareStatementForSqlObject($insert);
        $statement->execute();  
	}
	
	public function getPaymentByShopId($shop_id)
	{
		$select = $this->sql->select();
		$select->from("app_payment");
		$select->join("shop", "app_payment.shop_id = shop.shop_id");
		$select->where(array("shop.shop_id" => $shop_id));
		$statement = $this->sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        $resultSet = new ResultSet();
        $resultSet->initialize($results);
        $array = $resultSet->toArray();
		
		foreach($array as $arr){
			return $arr;
		}
	}
	
	public function getPayment($shop_id)
	{
		$select = $this->sql->select()->from("app_payment");
		$select->where(array("shop_id" => $shop_id));
		$statement = $this->sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        $resultSet = new ResultSet();
        $resultSet->initialize($results);
        $array = $resultSet->toArray();
		
		foreach($array as $arr){
			return $arr;
		}
	}
	
	public function updatePaymentInfo($shop_id,$info = array())
	{
		$update = $this->sql->update();
        $update->table('app_payment');
        $update->set(array(
			'charge_id' 		=> $info['id'],
			'confirmation_url'	=> $info['confirmation_url']
		));
        $update->where(array("shop_id" => $shop_id));
        $statement = $this->sql->prepareStatementForSqlObject($update);
        $statement->execute();
	}
	public function updateStartPaymentDay($shop_id,$day)
	{
		$update = $this->sql->update()->table('app_payment');
        $update->set(array('start_payment_day' => $day));
        $update->where(array("shop_id" => $shop_id));
        $statement = $this->sql->prepareStatementForSqlObject($update);
        $statement->execute();
	}
	public function updatePaymentDay($shop_id,$day)
	{
		$update = $this->sql->update()->table('app_payment');
        $update->set(array('payment_day' => $day));
        $update->where(array("shop_id" => $shop_id));
        $statement = $this->sql->prepareStatementForSqlObject($update);
        $statement->execute();
	}
	public function updatePaymentStatus($shop_id,$val)
	{
		$update = $this->sql->update()->table('app_payment');
        $update->set(array('payment_status' => $val));
        $update->where(array("shop_id" => $shop_id));
        $statement = $this->sql->prepareStatementForSqlObject($update);
        $statement->execute();
	}
	public function updateAllowed($shop_id,$val)
	{
		$update = $this->sql->update()->table('app_payment');
        $update->set(array('allowed' => $val));
        $update->where(array("shop_id" => $shop_id));
        $statement = $this->sql->prepareStatementForSqlObject($update);
        $statement->execute();
	}
	public function updateTrialTime($shop_id,$val)
	{
		$update = $this->sql->update()->table('app_payment');
        $update->set(array('trial_time' => $val));
        $update->where(array("shop_id" => $shop_id));
        $statement = $this->sql->prepareStatementForSqlObject($update);
        $statement->execute();
	}
}