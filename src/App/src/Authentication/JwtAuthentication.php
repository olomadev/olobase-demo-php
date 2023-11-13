<?php

declare(strict_types=1);

namespace App\Authentication;

use App\Model\AuthModel;
use App\Model\TokenModel;
use Oloma\Php\Authentication\JwtEncoderInterface as JwtEncoder;
use Oloma\Php\Exception\BadTokenException;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Authentication\Adapter\AdapterInterface;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Http\PhpEnvironment\RemoteAddress;

use function strtoupper;

class JwtAuthentication implements AuthenticationInterface
{
    private const HEADER_VALUE_PATTERN = "/Bearer\s+(.*)$/i";
    private const AUTHENTICATIN_REQUIRED = 'authenticationRequired';
    private const USERNAME_OR_PASSWORD_INCORRECT = 'usernameOrPasswordIncorrect';
    private const ACCOUNT_IS_INACTIVE_OR_SUSPENDED = 'accountIsInactiveOrSuspended';
    private const USERNAME_OR_PASSWORD_FIELDS_NOT_GIVEN = 'usernameOrPasswordNotGiven';
    private const NO_ROLE_DEFINED_ON_THE_ACCOUNT = 'noRoleDefinedOnAccount';

    /**
     * @var array
     */
    protected static $messageTemplates = [
        Self::AUTHENTICATIN_REQUIRED => 'Authentication required. Please sign in to your account',
        Self::USERNAME_OR_PASSWORD_INCORRECT => 'Username or password is incorrect',
        Self::ACCOUNT_IS_INACTIVE_OR_SUSPENDED => 'This account is awaiting approval or suspended',
        Self::USERNAME_OR_PASSWORD_FIELDS_NOT_GIVEN => 'Username and password fields must be given',
        Self::NO_ROLE_DEFINED_ON_THE_ACCOUNT => 'There is no role defined for this user',
    ];

    /**
     * @var Laminas auth adapter
     */
    protected $authAdapter;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var TokenModel
     */
    protected $tokenModel;

    /**
     * @var callable
     */
    protected $userFactory;

    /**
     * @var Jwt encoder
     */
    protected $encoder;

    /**
     * @var AuthModel
     */
    protected $authModel;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var Jwt payload
     */
    protected $payload = array();

    /**
     * @var string
     */
    protected $error;

    public function __construct(
        array $config,
        AdapterInterface $authAdapter,
        TranslatorInterface $translator,
        JwtEncoder $encoder,
        TokenModel $tokenModel,
        AuthModel $authModel,
        callable $userFactory
    ) {
        $this->config = $config;
        $this->authAdapter = $authAdapter;
        $this->translator = $translator;
        $this->encoder = $encoder;
        $this->tokenModel = $tokenModel;
        $this->authModel = $authModel;
        $this->userFactory = function (
            string $id,
            string $identity,
            array $roles = [],
            array $details = []
        ) use ($userFactory) : UserInterface {
            return $userFactory($id, $identity, $roles, $details);
        };
    }
    /**
     * Authenticate
     */
    public function authenticate(ServerRequestInterface $request) : ?UserInterface
    {
        if (! $this->validate($request)) {
            $this->error(Self::AUTHENTICATIN_REQUIRED);
            return null;
        }
        $payload = $this->getPayload()['data'];
        $data = (array)$payload;
        return ($this->userFactory)($data['userId'], $data['identity'], $data['roles'], (array)$data['details']);
    }

    public function initAuthentication(ServerRequestInterface $request) : ?UserInterface
    {
        $post = $request->getParsedBody();
        $usernameField = $this->config['form']['username'];
        $passwordField = $this->config['form']['password'];
        
        // credentials are given ? 
        //
        if (! isset($post[$usernameField]) || ! isset($post[$passwordField])) {
            $this->error(Self::USERNAME_OR_PASSWORD_FIELDS_NOT_GIVEN);
            return null;
        }
        $this->authAdapter->setIdentity($post[$usernameField]);
        $this->authAdapter->setCredential($post[$passwordField]);

        // credentials are correct ? 
        //
        $result = $this->authAdapter->authenticate();
        if (! $result->isValid()) {
            $this->error(Self::USERNAME_OR_PASSWORD_INCORRECT); 
            return null;
        }
        $remoteAddress = new RemoteAddress;
        $ip = $remoteAddress->getIpAddress();
        $server = $request->getServerParams();
        $userAgent = empty($server['HTTP_USER_AGENT']) ? 'unknown' : $server['HTTP_USER_AGENT'];
        $deviceKey = md5($userAgent);
        $rowObject = $this->authAdapter->getResultRowObject();

        // user is active ? 
        //
        if (empty($rowObject->active)) {
            $this->error(Self::ACCOUNT_IS_INACTIVE_OR_SUSPENDED);
            return null;
        }
        // is the role exists ?
        // 
        $roles = $this->authModel->findRolesById($rowObject->userId);
        if (empty($roles)) {
            $this->error(Self::NO_ROLE_DEFINED_ON_THE_ACCOUNT);
            return null;
        }
        $details = [
            'email' => $rowObject->email,
            'firstname' => $rowObject->firstname,
            'lastname' => $rowObject->lastname,
            'avatar' => $rowObject->avatar,
            'ip' => $ip,
            'deviceKey' => $deviceKey,
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
            return false;
        }
        $this->payload = $this->encoder->decode($this->token);
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
        if (preg_match(Self::HEADER_VALUE_PATTERN, $authHeader[0], $matches)) {
            return $matches[1];
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

    public function unauthorizedResponse(ServerRequestInterface $request) : ResponseInterface
    {
        return new JsonResponse(
            ['data' => ['error' => $this->getError()]],
            401,
            ['WWW-Authenticate' => 'Bearer realm="Jwt token"']
        );
    }

    protected function error(string $errorKey)
    {
        if (empty(Self::$messageTemplates[$errorKey])) {
            $this->error = $errorKey;
        }
        $this->error = $this->translator->translate(Self::$messageTemplates[$errorKey]);
    }
    
}
