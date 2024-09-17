<?php

declare(strict_types=1);

namespace App\Handler\FailedLogins;

use App\Model\FailedLoginModel;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllIpAdressesHandler implements RequestHandlerInterface
{
    public function __construct(private FailedLoginModel $failedLoginModel)
    {
        $this->failedLoginModel = $failedLoginModel;
    }

    /**
     * @OA\Get(
     *   path="/failedloginips/findAll",
     *   tags={"Failed Logins"},
     *   summary="Find all ips for failed logins",
     *   operationId="failedloginips_findAll",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/CommonFindAll"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $allIpData = $this->failedLoginModel->findAllIpAdresses();
        return new JsonResponse([
            'data' => $allIpData,
        ]);
    }

}
