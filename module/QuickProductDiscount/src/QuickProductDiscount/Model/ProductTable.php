<?php

namespace QuickProductDiscount\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;

class ProductTable
{
    protected $sql;
    
    public function __construct(Sql $sql)
	{
		$this->sql = $sql;
    }
	
	public function fetchall()
	{
		$select = $this->sql->select()->from("product");
		$statement = $this->sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        $resultSet = new ResultSet();
        $resultSet->initialize($results);
        $array = $resultSet->toArray();
		return $array;
	}
	
	public function saveProductInfo($request = array(),$created_product = array())
	{
		$insert = $this->sql->insert();
        $insert->into('product');
        $insert->values(array(
            'shop_id' 				=> $request['shop_id'],
			'product_id'			=> $request['product']['id'],
			'variant_id'			=> $request['product_variant']['id'],
			'created_product_id'	=> $created_product['product_id'],
			'created_variant_id'	=> $created_product['id'],
            'life_time'				=> time() + 60*60*24*30
        ));
        $statement = $this->sql->prepareStatementForSqlObject($insert);
        $statement->execute();
	}
	
	public function getProductByShopId($shop_id)
	{
		$select = $this->sql->select()->from("product");
		$select->where(array("shop_id" => $shop_id));
		$statement = $this->sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        $resultSet = new ResultSet();
        $resultSet->initialize($results);
        $array = $resultSet->toArray();
		
		return $array;
	}
	
	public function getProduct($shop_id,$product_id)
	{
		$select = $this->sql->select()->from("product");
		$select->where(array("shop_id" => $shop_id, "product_id" => $product_id));
		$statement = $this->sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        $resultSet = new ResultSet();
        $resultSet->initialize($results);
        $array = $resultSet->toArray();
		
		return $array;
	}
	
	public function getCreatedProduct($shop_id,$created_product_id)
	{
		$select = $this->sql->select()->from("product");
		$select->where(array("shop_id" => $shop_id, "created_product_id" => $created_product_id));
		$statement = $this->sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        $resultSet = new ResultSet();
        $resultSet->initialize($results);
        $array = $resultSet->toArray();
		
		foreach($array as $data) {
			if(!empty($data))
				return $data;
		}
	}
	
	public function deleteProduct($shop_id,$product_id)
	{
		$delete = $this->sql->delete()->from("product");
		$delete->where(array("shop_id" => $shop_id, "product_id" => $product_id));
		$statement = $this->sql->prepareStatementForSqlObject($delete);
        $statement->execute();
	}
	public function deleteCreatedProduct($shop_id,$created_product_id)
	{
		$delete = $this->sql->delete()->from("product");
		$delete->where(array("shop_id" => $shop_id, "created_product_id" => $created_product_id));
		$statement = $this->sql->prepareStatementForSqlObject($delete);
        $statement->execute();
	}
}