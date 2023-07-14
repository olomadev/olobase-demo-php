<?php

declare(strict_types=1);

namespace App\Handler\Account;

use App\Model\UserModel;
use Mezzio\Authentication\UserInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindMeHandler implements RequestHandlerInterface
{
    public function __construct(private UserModel $userModel) 
    {
        $this->userModel = $userModel;
    }
    
    /**
     * @OA\Get(
     *   path="/account/findMe",
     *   tags={"Account"},
     *   summary="Find my account data",
     *   operationId="account_findOneById",
     *  
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/AccountFindMeResultVM"),
     *   ),
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(UserInterface::class); // get id from current token
        $userId = $user->getId();
        $row = $this->userModel->findOneById($userId);
        if ($row) {
            $viewModel = new FindMeViewModel($row);
            return new JsonResponse(['data' => $viewModel->getData()]);            
        }
        return new JsonResponse([], 404);
    }
}
