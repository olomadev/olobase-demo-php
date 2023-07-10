<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class ExchangeRateFindAllByPageResultVM
{
    /**
     * @var array
     * @OA\Property(
     * 		type="array",
     *   	@OA\Items(
     *   		type="object",
     *   		ref="#/components/schemas/ExchangeRateListItem",
     *   	),
     * )
     */
    public $data;
    /**
     * @var integer
     * @OA\Property()
     */
    public $page;
    /**
     * @var integer
     * @OA\Property()
     */
    public $perPage;
    /**
     * @var integer
     * @OA\Property()
     */
    public $totalPages;
    /**
     * @var integer
     * @OA\Property()
     */
    public $totalItems;
}
