<?php

declare(strict_types=1);

namespace App\Handler\Auth;

use Exception;
use App\Model\AuthModel;
use App\Model\TokenModel;
use Firebase\JWT\ExpiredException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Olobase\Mezzio\Authentication\JwtEncoderInterface as JwtEncoder;
use Mezzio\Authentication\AuthenticationInterface as Auth;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class RefreshHandler implements RequestHandlerInterface
{
    private $config;
    /**
     * This signal is controlled by the frontend, do not change the value.
     */
    protected const LOGOUT_SIGNAL = 'Logout';

    public function __construct(
        array $config,
        private Translator $translator,
        private Auth $auth,
        private AuthModel $authModel,
        private TokenModel $tokenModel,
        private Error $error
    ) {
        $this->config = $config;
        $this->translator = $translator;
        $this->auth = $auth;
        $this->authModel = $authModel;
        $this->tokenModel = $tokenModel;
        $this->error = $error;
    }

    /**
     * @OA\Post(
     *   path="/auth/refresh",
     *   tags={"Auth"},
     *   summary="Refresh the token",
     *   operationId="auth_refresh",
     *
     *   @OA\RequestBody(
     *     description="Token refresh request",
     *     @OA\JsonContent(ref="#/components/schemas/RefreshToken"),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/AuthResult"),
     *   ),
     *   @OA\Response(
     *      response=401,
     *      description="Unauthorized Response: token is expired"
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $post = $request->getParsedBody();
        if (empty($post['token'])) {
            return new JsonResponse(
                [
                    'data' => ['error' => Self::LOGOUT_SIGNAL] // don't change
                ],
                401
            );
        }
        $token = $this->tokenModel->getTokenEncrypt()->decrypt($post['token']);
        if (! $token) {
            return new JsonResponse(
                [
                    'data' => ['error' => Self::LOGOUT_SIGNAL] // don't change
                ],
                401
            );
        }
        try { // Signature check !!
            $this->tokenModel->decode($token);
        } catch (ExpiredException $e) {

            list($header, $payload, $signature) = explode(".", $token);
            $payload = json_decode(base64_decode($payload), true);  

            if (json_last_error() != JSON_ERROR_NONE) {
                return new JsonResponse(
                    [
                        'data' => ['error' => $this->translator->translate("Invalid token")]
                    ], 
                    401
                );
            }
            $data = $this->tokenModel->refresh($request, $payload);
            if (false == $data) {
                return new JsonResponse(
                    [
                        'data' => ['error' => Self::LOGOUT_SIGNAL] // don't change
                    ],
                    401
                );
            }
            $details = $data['data']['details'];
            return new JsonResponse(
                [
                    'data' => [
                        'token' => $data['token'],
                        'user'  => [
                            'id' => $data['data']['userId'],
                            'fullname' => $details['fullname'],
                            'email' => $details['email'],
                            'permissions' => $data['data']['roles'],
                        ],
                        'expiresAt' => $data['expiresAt'],
                    ],
                ]
            );
        } catch (Exception $e) {
            return new JsonResponse(
                [
                    'data' => ['error' => $e->getMessage()]
                ], 
                401
            );
        }
        return new JsonResponse(
            [
                'data' => ['info' => $this->translator->translate("Token not expired to refresh")]
            ], 
            401
        );
    }

}
