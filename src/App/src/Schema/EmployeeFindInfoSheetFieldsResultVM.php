<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class EmployeeFindInfoSheetFieldsResultVM
{
    /**
    *  @var object
    *  @OA\Property(
    *      @OA\Property(
    *          property="fieldId",
    *          type="string",
    *      ),
    *      @OA\Property(
    *          property="fieldLabel",
    *          type="string",
    *      ),
    *  );
    */
    public $data;
}
