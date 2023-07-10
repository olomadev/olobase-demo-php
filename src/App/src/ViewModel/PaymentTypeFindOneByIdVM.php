<?php

namespace App\ViewModel;

class PaymentTypeFindOneByIdVM
{
	public function __construct(array $row)
	{
		$this->row = $row;
	}
    
	public function getData() : array
	{
        $row = $this->row;
        $data = [
            'id' => (string)$row['id'],
            'paymentTypeName' => (string)$row['paymentTypeName'],
        ];
        return $data;
	}
}