<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class ExchangeRateFindWeeklyChartResultVM
{
    /**
     * @var array
     * @OA\Property(
     * 		type="array",
     *   	@OA\Items(
     *   		type="object",
     *   		ref="#/components/schemas/ExchangeRateWeeklyChart",
     *   	),
     * )
     */
    public $data;
}
