<?php

namespace App\ViewModel;

use function formatDate;
use function formatMoney;

class ExpenseFindOneByIdVM
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
            'customerId' => (string)$row['customerId'],
            'departmentId' => (string)$row['departmentId'],
            'description' => (string)$row['description'],
            'docNumber' => (string)$row['docNumber'],
            'employeeId' => (string)$row['employeeId'],
            'expenseApplication' => (string)$row['expenseApplication'],
            'expenseDate' => (string)$row['expenseDate'],
            'expenseTypeId' => (string)$row['expenseTypeId'],
            'packageNo' => (string)$row['packageNo'],
            'paymentTypeId' => (string)$row['paymentTypeId'],
            'pernetNumber' => (string)$row['pernetNumber'],
            'sowId' => (string)$row['sowId'],
            'subDepartmentId' => (string)$row['subDepartmentId'],
            'currencyId' => (string)$row['currencyId'],
            'amount' => formatMoney($row['amount']),
            'tax' => formatMoney($row['tax']),
            'totalAmount' => formatMoney($row['totalAmount']),
            'whoPaidId' => (string)$row['whoPaidId'],
            'workplaceId' => (string)$row['workplaceId'],
            'confirmNo' => (string)$row['confirmNo'],
            'confirmStatus' => (int)$row['confirmStatus'],
            'sentForApproval' => (int)$row['sentForApproval'],
            'pernetInvoiceNo' => (string)$row['pernetInvoiceNo'],
            'poNo' => (string)$row['poNo'],
            'isPaidOff' => (int)$row['isPaidOff'],
            'employeePaidDate' => formatDate($row['employeePaidDate']),
        ];
        $data['file'] = array();
        if (! empty($row['files'])) {
            foreach ($row['files'] as $i => $file) {
                $data['file'][$i]["id"] = $file['fileId'];
                $data['file'][$i]["name"] = $file['fileName'];
                $data['file'][$i]["size"] = $file['fileSize'];
                $data['file'][$i]["type"] = $file['fileType'];    
            }
        }
        // $data['file'] = jsonEncode($data['file']);
        return $data;
	}
}