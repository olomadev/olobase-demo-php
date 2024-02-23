<?php

declare(strict_types=1);

namespace App\Handler\Auth;

use Exception;
use App\Filter\Auth\AuthFilter;
use Firebase\JWT\ExpiredException;
use Mezzio\Authentication\UserInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Mezzio\Authentication\AuthenticationInterface as Auth;

/**
 * @OA\Info(title="Demo API", version="1.0"),
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
class TokenHandler implements RequestHandlerInterface
{
    /**
     * This signal is controlled by the frontend, do not change the value.
     */
    protected const EXPIRE_SIGNAL = 'Token Expired';

    public function __construct(
        array $config, 
        private Auth $auth,
        private AuthFilter $filter,
        private Error $error
    ) {
        $this->config = $config;
        $this->auth = $auth;
        $this->filter = $filter;
        $this->error = $error;
    }
    
    /**
     * @OA\Post(
     *   path="/auth/token",
     *   tags={"Auth"},
     *   summary="Authenticate the user",
     *   operationId="auth_token",
     *
     *   @OA\RequestBody(
     *     description="Authenticate",
     *     @OA\JsonContent(ref="#/components/schemas/AuthRequest"),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/AuthResult"),
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad request, returns to validation errors"
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->filter->setInputData($request->getParsedBody());
        if ($this->filter->isValid()) {
            try {
                $user = $this->auth->initAuthentication($request);
                
                if (null !== $user) {
                    $request = $request->withAttribute(UserInterface::class, $user);
                    $encoded = $this->auth->getTokenModel()->create($request);                   
                    $details = $user->getDetails();
                    return new JsonResponse(
                        [
                            'data' => [
                                'token' => $encoded['token'],
                                'user'  => [
                                    'id' => $user->getId(),
                                    'fullname' => $details['fullname'],
                                    'email' => trim($details['email']),
                                    'permissions' => $user->getRoles(),
                                ],
                                'avatar' => $details['avatar'],
                                'expiresAt' => $encoded['expiresAt']
                            ]
                        ]
                    );
                }
            } catch (ExpiredException $e) {
                return new JsonResponse(
                    [
                        'data' => ['error' => Self::EXPIRE_SIGNAL]
                    ], 
                    401,
                    ['Token-Expired' => 1]
                );
            } catch (Exception $e) {
                return new JsonResponse(
                    [
                        'data' => ['error' => $e->getMessage()]
                    ], 
                    401
                );
            }
            return $this->auth->unauthorizedResponse($request);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
    }
}
