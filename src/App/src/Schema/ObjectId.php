<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class ObjectId
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $id;
    /**
     * @var string
     * @OA\Property()
     */
    public $name;
}
