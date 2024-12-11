<?php

declare(strict_types=1);

namespace App\Authentication;

use App\Model\AuthModel;
use App\Model\TokenModel;
use App\Listener\LoginListener;
use Laminas\EventManager\EventManagerInterface;
use Olobase\Mezzio\Authentication\JwtEncoderInterface as JwtEncoder;
use Olobase\Mezzio\Exception\BadTokenException;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Authentication\Adapter\AdapterInterface;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function strtoupper, getRealUserIp;

class JwtAuthentication implements AuthenticationInterface
{
    /**
     * Do not change these values
     */
    public const AUTHENTICATION_REQUIRED = 'authenticationRequired';
    public const IP_VALIDATION_FAILED = 'ipValidationFailed';
    public const USER_AGENT_VALIDATION_FAILED = 'userAgentValidationFailed';
    public const USERNAME_OR_PASSWORD_INCORRECT = 'usernameOrPasswordIncorrect';
    public const ACCOUNT_IS_INACTIVE_OR_SUSPENDED = 'accountIsInactiveOrSuspended';
    public const USERNAME_OR_PASSWORD_FIELDS_NOT_GIVEN = 'usernameOrPasswordNotGiven';
    public const NO_ROLE_DEFINED_ON_THE_ACCOUNT = 'noRoleDefinedOnAccount';

    protected static $messageTemplates = [
        Self::AUTHENTICATION_REQUIRED => 'Authentication required. Please sign in to your account',
        Self::USERNAME_OR_PASSWORD_INCORRECT => 'Username or password is incorrect',
        Self::ACCOUNT_IS_INACTIVE_OR_SUSPENDED => 'This account is awaiting approval or suspended',
        Self::USERNAME_OR_PASSWORD_FIELDS_NOT_GIVEN => 'Username and password fields must be given',
        Self::NO_ROLE_DEFINED_ON_THE_ACCOUNT => 'There is no role defined for this user',
        Self::IP_VALIDATION_FAILED => 'Ip validation failed and you are logged out',
        Self::USER_AGENT_VALIDATION_FAILED => 'Browser validation failed and you are logged out',
    ];
    protected $token;
    protected $config;
    protected $authAdapter;
    protected $translator;
    protected $encoder;
    protected $tokenModel;
    protected $authModel;
    protected $events;
    protected $userFactory;
    protected $payload = array();
    protected $ipAddress;
    protected $error;
    protected $code;

    public function __construct(
        array $config,
        AdapterInterface $authAdapter,
        TranslatorInterface $translator,
        JwtEncoder $encoder,
        TokenModel $tokenModel,
        AuthModel $authModel,
        EventManagerInterface $events,
        callable $userFactory
    ) {
        $this->config = $config;
        $this->authAdapter = $authAdapter;
        $this->translator = $translator;
        $this->encoder = $encoder;
        $this->tokenModel = $tokenModel;
        $this->authModel = $authModel;
        $this->events = $events;
        $this->userFactory = function (
            string $id,
            string $identity,
            array $roles = [],
            array $details = []
        ) use ($userFactory) : UserInterface {
            return $userFactory($id, $identity, $roles, $details);
        };
        $this->ipAddress = getRealUserIp();
    }

    public function authenticate(ServerRequestInterface $request) : ?UserInterface
    {
        if (! $this->validate($request)) {
            return null;
        }
        $payload = $this->getPayload()['data'];
        $data = (array)$payload;
        return ($this->userFactory)($data['userId'], $data['identity'], $data['roles'], (array)$data['details']);
    }

    public function initAuthentication(ServerRequestInterface $request) : ?UserInterface
    {
        $post = $request->getParsedBody();
        $usernameField = $this->config['authentication']['form']['username'];
        $passwordField = $this->config['authentication']['form']['password'];
        
        // credentials are given ? 
        //
        if (! isset($post[$usernameField]) || ! isset($post[$passwordField])) {
            $this->error(Self::USERNAME_OR_PASSWORD_FIELDS_NOT_GIVEN);
            return null;
        }
        $this->authAdapter->setIdentity($post[$usernameField]);
        $this->authAdapter->setCredential($post[$passwordField]);

        $eventParams = [
            'request' => $request,
            'translator' => $this->translator,
            'username' => $post[$usernameField],
            'ip' => $this->getIpAddress(),
        ];
        $results = $this->events->trigger(LoginListener::onBeforeLogin, null, $eventParams);
        $loginResponse = $results->last();
        if ($loginResponse['banned']) {
            $this->error($loginResponse['message']);
            return null;
        }
        //
        // credentials are correct ? 
        //
        $result = $this->authAdapter->authenticate();
        if (! $result->isValid()) {
            //
            // failed attempts event
            //
            $this->events->trigger(LoginListener::onFailedLogin, null, $eventParams);
            //
            // default behaviour
            //
            $this->error(Self::USERNAME_OR_PASSWORD_INCORRECT);
            return null;
        }
        $rowObject = $this->authAdapter->getResultRowObject();
        //
        // successful login event
        //
        $eventParams['rowObject'] = $rowObject;
        $this->events->trigger(LoginListener::onSuccessfullLogin, null, $eventParams);
        //
        // user is active ? 
        //
        if (empty($rowObject->active)) {
            $this->error(Self::ACCOUNT_IS_INACTIVE_OR_SUSPENDED);
            return null;
        }
        //
        // is role exists ?
        // 
        $roles = $this->authModel->findRolesById($rowObject->userId);
        if (empty($roles)) {
            $this->error(Self::NO_ROLE_DEFINED_ON_THE_ACCOUNT);
            return null;
        }
        $avatarImage = empty($rowObject->avatar) ? null : "data:".$rowObject->mimeType.";base64,".$rowObject->avatar;
        $details = [
            'email' => $rowObject->email,
            'fullname' => $rowObject->firstname.' '.$rowObject->lastname,
            'avatar' => $avatarImage,
            'ip' => $this->getIpAddress(),
            'deviceKey' => $this->getDeviceKey($request),
        ];
        return ($this->userFactory)(
            $rowObject->userId,
            $result->getIdentity(),
            $roles,
            $details
        );
    }

    private function validate(ServerRequestInterface $request): bool
    {
        $this->token = $this->extractToken($request);
        if (empty($this->token)) {
            $this->error(Self::AUTHENTICATION_REQUIRED);
            return false;
        }
        $token = $this->tokenModel->getTokenEncrypt()->decrypt($this->token);
        $this->payload = $this->encoder->decode($token);

        if ($this->config['token']['validation']['user_ip'] 
            && $this->payload['data']->details->ip != $this->getIpAddress()
        ) {
            $this->tokenModel->kill(
                $this->payload['data']->userId,
                $this->payload['jti'],
            );
            $this->error(Self::IP_VALIDATION_FAILED);
            return false;
        }
        if ($this->config['token']['validation']['user_agent'] 
            && $this->payload['data']->details->deviceKey != $this->getDeviceKey($request)
        ) {
            $this->tokenModel->kill(
                $this->payload['data']->userId,
                $this->payload['jti'],
            );
            $this->error(Self::USER_AGENT_VALIDATION_FAILED);
            return false;
        }
        return $this->payload !== null;
    }

    private function getToken() : string
    {
        return $this->token;
    }

    private function getPayload() : array
    {
        return $this->payload;
    }

    private function extractToken(ServerRequestInterface $request): ?string
    {
        $authHeader = $request->getHeader('Authorization');
        if (empty($authHeader)) {
            return null;
        }
        if (preg_match("/Bearer\s+(.*)$/i", $authHeader[0], $matches)) {
            return $matches[1] == "null" ? null : $matches[1];
        }
        return null;
    }

    public function getTokenModel()
    {
        return $this->tokenModel;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function unauthorizedResponse(ServerRequestInterface $request) : ResponseInterface
    {
        return new JsonResponse(
            [
                'data' => [
                    'code' => $this->getCode(),
                    'error' => $this->getError()
                ]
            ],
            401,
            ['WWW-Authenticate' => 'Bearer realm="Jwt token"']
        );
    }

    protected function error(string $errorKey)
    {
        if (empty(Self::$messageTemplates[$errorKey])) {
            $this->code = $errorKey;
            $this->error = $this->translator->translate($errorKey);
            return;
        }
        $this->code = $errorKey;
        $this->error = $this->translator->translate(Self::$messageTemplates[$errorKey]);
    }

    private function getDeviceKey(ServerRequestInterface $request)
    {
        $server = $request->getServerParams();
        $userAgent = empty($server['HTTP_USER_AGENT']) ? 'unknown' : $server['HTTP_USER_AGENT'];
        return md5($userAgent);
    }

    private function getIpAddress()
    {
        return $this->ipAddress;
    }
}
