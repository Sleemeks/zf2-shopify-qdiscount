<?php

namespace QuickProductDiscount\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;

class DiscountOptionTable
{
    protected $sql;
    
    public function __construct(Sql $sql) 
	{
        $this->sql = $sql;
    }
	
	public function saveScriptId($shop_id,$script_id) 
	{
		$insert = $this->sql->insert();
        $insert->into('discount_option');
        $insert->values(array(
            'shop_id' 		=> $shop_id,
            'shopify_script_id'		=> $script_id
        ));
        $statement = $this->sql->prepareStatementForSqlObject($insert);
        $statement->execute(); 
	}
	
	public function updateScriptId($shop_id,$script_id)
	{
		$update = $this->sql->update()->table('discount_option');
        $update->set(array('shopify_script_id'	=> $script_id));
        $update->where(array("shop_id" => $shop_id));
        $statement = $this->sql->prepareStatementForSqlObject($update);
        $statement->execute();
	}
	
	public function getOptionByShopId($shop_id)
	{
		$select = $this->sql->select()->from("discount_option");
		$select->where(array("shop_id" => $shop_id));
		$statement = $this->sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        $resultSet = new ResultSet();
        $resultSet->initialize($results);
        $array = $resultSet->toArray();
		
		foreach($array as $info) {
			return $info;
		}
	}
	
	public function setOptionByShopId($shop_id,$from,$to)
	{
		$update = $this->sql->update()->table('discount_option');
        $update->set(array(
			'discount_from'	=> $from,
			'discount_to'	=> $to
		));
        $update->where(array("shop_id" => $shop_id));
        $statement = $this->sql->prepareStatementForSqlObject($update);
        $statement->execute();
	}
	
	public function deleteDiscount($shop_id)
	{
		$delete = $this->sql->delete()->from("discount_option");
		$delete->where(array("shop_id" => $shop_id));
		$statement = $this->sql->prepareStatementForSqlObject($delete);
        $statement->execute();
	}
}