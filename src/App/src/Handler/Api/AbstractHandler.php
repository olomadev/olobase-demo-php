<?php

declare(strict_types=1);

namespace App\Handler\Api;

/**
 * @OA\Info(title="Bütçe API", version="1.0"),
 * @OA\Schemes(format="http"),
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 * ),
 * @OA\SecurityDefinitions(
 *     name="baseUserSecurity",
 *     in="path",
 *     type="basic",
 * ),
 */
class AbstractHandler extends OlomaHandler
{
   
}
