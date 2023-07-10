<?php

namespace App\ViewModel;

class ExchangeRateFindOneVM
{
	public function __construct(array $row)
	{
		$this->row = $row;
	}
    
	public function getData() : array
	{
        $row = $this->row;
        $data = [
            'usdExchangeRate' => (string)$row['usdExchangeRate'],
            'euroExchangeRate' => (string)$row['euroExchangeRate'],
            'poundExchangeRate' => (string)$row['poundExchangeRate'],
            'exchangeRateDate' => (string)$row['exchangeRateDate'],
        ];
		return $data;
	}
}