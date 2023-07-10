<?php

declare(strict_types=1);

namespace App\Handler\Api;

use function json_decode;
use function json_last_error;

use Exception;
use App\Utils\Error;
use App\Utils\Mailer;
use App\Model\AuthModel;
use App\Model\TokenModel;
use App\Filter\AuthFilter;
use App\Filter\SendResetPasswordFilter;
use App\Filter\ResetPasswordFilter;
use App\Authentication\JwtEncoder;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\AuthenticationInterface;
use Laminas\I18n\Translator\TranslatorInterface as Translator;
use Laminas\InputFilter\InputFilterPluginManager;
use Firebase\JWT\ExpiredException;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Diactoros\Response\JsonResponse;

class AuthHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        AuthenticationInterface $auth,
        InputFilterPluginManager $filter,
        AuthModel $authModel,
        TokenModel $tokenModel,
        JwtEncoder $encoder,
        Mailer $mailer,
        Error $error
    ) {
        $this->auth = $auth;
        $this->filter = $filter;
        $this->tokenModel = $tokenModel;
        $this->encoder = $encoder;
        $this->translator = $translator;
        $this->authModel = $authModel;
        $this->error = $error;
        $this->mailer = $mailer;
    }

    /**
     * @OA\Get(
     *   path="/auth/getFindAllPermissions",
     *   tags={"Auth"},
     *   summary="Get all permissions before the login",
     *   operationId="auth_getAllPermissions",
     *
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *   )
     *)
     **/
    public function onGetFindAllPermissions()
    {
        $data = $this->authModel->findAllPermissions();
        return new JsonResponse(['data' => $data]);
    }

    /**
     * @OA\Get(
     *   path="/auth/getFindResources",
     *   tags={"Auth"},
     *   summary="Get resource data with role permissions",
     *   operationId="auth_getFindResources",
     *
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *   )
     *)
     **/
    public function onGetFindResources()
    {
        $data = $this->authModel->findResources();
        return new JsonResponse(['data' => $data]);
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
     *     @OA\JsonContent(ref="#/components/schemas/Auth"),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/AuthResultVM"),
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad request, returns to validation errors"
     *   )
     *)
     **/
    public function onPostToken(array $post)
    {
        $inputFilter = $this->filter->get(AuthFilter::class);
        $inputFilter->setInputData($post);
        if ($inputFilter->isValid()) {
            try {
                $user = $this->auth->initAuthentication($this->request);       

                if (null !== $user) {
                    $request = $this->request->withAttribute(UserInterface::class, $user);
                    $encoded = $this->auth->getTokenModel()->create($request);                    
                    $details = $user->getDetails();

                    return new JsonResponse([
                        'data' => [
                            'token' => $encoded['token'],
                            'user'  => [
                                'id' => trim($user->getId()),
                                'firstname' => trim($details['firstname']),
                                'lastname' => trim($details['lastname']),
                                'email' => trim($details['email']),
                                'roles' => $user->getRoles(),
                                'avatar' => $details['avatar'],
                            ],
                            'expiresAt' => $encoded['expiresAt']
                        ]
                    ]);
                }
            } catch (ExpiredException $e) {
                return new JsonResponse(
                    [
                        'data' => ['error' => "Token Expired"]
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
            return $this->auth->unauthorizedResponse($this->request);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
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
     *     @OA\JsonContent(ref="#/components/schemas/AuthResultVM"),
     *   ),
     *   @OA\Response(
     *      response=401,
     *      description="Unauthorized Response: token is expired"
     *   )
     *)
     **/
    public function onPostRefresh(array $post)
    {
        if (empty($post['token'])) {
            $message = $this->translator->translate('Token input cannot be empty');
            return new JsonResponse(['error' => $message], 401);
        }
        try {
            // Signature check !!
            //
            $this->encoder->decode($post['token']);
        } catch (ExpiredException $e) {
            list($header, $payload, $signature) = explode(".", $post['token']);
            $payload = json_decode(base64_decode($payload), true);
            if (json_last_error() != JSON_ERROR_NONE) {
                $message = $this->translator->translate('Invalid token');
                return new JsonResponse(['error' => $message], 401);
            }
            $tokenId = $payload['jti'];
            $data = $this->tokenModel->refresh($this->request, $tokenId, $payload);
            if (false == $data) {
                return new JsonResponse(['data' => ['error' => 'Logout']], 401);
            }
            $details = $data['data']['details'];
            return new JsonResponse(
                [
                    'data' => [
                        'token' => $data['token'],
                        'user'  => [
                            'id' => $data['data']['userId'],
                            'firstname' => trim($details['firstname']),
                            'lastname' => trim($details['lastname']),
                            'roles' => $data['data']['roles'],
                            'email'=> $details['email']
                        ],
                        'expiresAt' => $data['expiresAt'],
                    ]
                ]
            );
        } catch (Exception $e) {
            return new JsonResponse(['data' => ['error' => $e->getMessage()]], 401);
        }
        $message = $this->translator->translate('Token not expired to refresh');
        return new JsonResponse(['data' => ['info' => $message]], 401);
    }

    /**
     * @OA\Post(
     *   path="/auth/sendResetPassword",
     *   tags={"Auth"},
     *   summary="Send reset password code to user",
     *   operationId="auth_sendResetPassword",
     *
     *   @OA\RequestBody(
     *     description="Send reset password request",
     *     @OA\JsonContent(ref="#/components/schemas/SendResetPassword"),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *   )
     *)
     **/
    public function onPostSendResetPassword(array $post)
    {
        $inputFilter = $this->filter->get(SendResetPasswordFilter::class);
        $inputFilter->setInputData($post);
        if ($inputFilter->isValid()) {
            $username = $inputFilter->getValue('username');
            $code = $this->authModel->generateResetPassword($username);
            $userRow = $this->authModel->findOneByUsername($username);
            
            // send reset password e-mail to user
            //
            $link = 'http://'.PROJECT_DOMAIN.'/resetPassword?resetCode='.$code;
            $this->mailer->isHtml(true);
            $this->mailer->setLocale($this->translator->getLocale());
            $data = [
                'email' => $username,
                'resetPasswordLink' => urlencode($link),
                'themeColor' => $userRow['themeColor']
            ];
            $body = $this->mailer->getTemplate('forgotPassword', $data);
            $this->mailer->to($username);
            $this->mailer->subject($this->translator->translate('Forgotten Password', 'templates'));
            $this->mailer->body($body);
            $this->mailer->send();

        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse([]);
    }

    /**
     * @OA\Post(
     *   path="/auth/resetPassword",
     *   tags={"Auth"},
     *   summary="Reset password",
     *   operationId="auth_resetPassword",
     *
     *   @OA\RequestBody(
     *     description="Reset password of user",
     *     @OA\JsonContent(ref="#/components/schemas/ResetPassword"),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *   )
     *)
     **/
    public function onPostResetPassword(array $post)
    {
        $inputFilter = $this->filter->get(ResetPasswordFilter::class);
        $inputFilter->setInputData($post);
        if ($inputFilter->isValid()) {
            $this->authModel->resetPassword($inputFilter->getValue('username'));
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse([]);
    }

    /**
     * @OA\Get(
     *   path="/auth/logout",
     *   tags={"Auth"},
     *   summary="Logout the user",
     *   operationId="auth_logout",
     *
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *   )
     *)
     **/
    public function onGetLogout()
    {
        $server = $this->request->getServerParams();
        $userAgent = empty($server['HTTP_USER_AGENT']) ? 'unknown' : $server['HTTP_USER_AGENT'];
        $deviceKey = md5($userAgent);

        // user token may expired thats why it's 
        // important manually extract the token from header 
        //
        $token = null;
        $authHeader = $this->request->getHeader('Authorization');
        if (empty($authHeader)) {
            $token = null;
        } else if (preg_match("/Bearer\s+(.*)$/i", $authHeader[0], $matches)) {
            $token = $matches[1];
        }
        if (! empty($token)) {
            try {
                $data = $this->encoder->decode($token);
                if (! empty($data['data']->userId)) {
                    $this->tokenModel->kill($data['data']->userId, $deviceKey); // delete the user from session db
                }
            } catch (ExpiredException $e) {
                list($header, $payload, $signature) = explode(".", $token);
                $payload = json_decode(base64_decode($payload), true);
                if (json_last_error() != JSON_ERROR_NONE) {
                    $message = $this->translator->translate('Invalid token');
                    return new JsonResponse(['error' => $message], 401);
                }
                if (! empty($payload['data']['userId'])) {
                    $this->tokenModel->kill($payload['data']['userId'], $deviceKey); // delete the user from session db
                }
            } catch (Exception $e) {
                return new JsonResponse(['data' => ['error' => $e->getMessage()]], 401);
            }
        }
        return new JsonResponse([]);
    }
}
