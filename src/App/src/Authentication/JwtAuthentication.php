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
            $this->error = 'Authentication required. Please sign in to your account';
            return null;
        }
        $payload = $this->getPayload()['data'];
        $data = (array)$payload;
        return ($this->userFactory)($data['userId'], $data['identity'], $data['roles'], (array)$data['details']);
    }

    public function unauthorizedResponse(ServerRequestInterface $request) : ResponseInterface
    {
        $errorMessage = $this->getError();
        return new JsonResponse(
            ['data' => ['error' => $this->translator->translate($errorMessage)]],
            401,
            ['WWW-Authenticate' => 'Bearer realm="Jwt token"']
        );
    }

    public function initAuthentication(ServerRequestInterface $request) : ?UserInterface
    {
        $params = $request->getParsedBody();
        if (isset($params['username']) && isset($params['email'])) { // signup sayfasında her ikisi birden var
            $username = 'username';
        } else if (isset($params['email'])) { // sadece email geliyorsa request de
            $username = 'email';
        } else if (isset($params['username'])) { // sadece username geliyorsa request de
            $username = 'username';
        }
        $password = $this->config['password'] ?? 'password';

        if (! isset($params[$username]) || ! isset($params[$password])) {
            $this->error = 'Username and password fields must be given';
            return null;
        }
        $this->authAdapter->setIdentity($params[$username]);
        $this->authAdapter->setCredential($params[$password]);

        $result = $this->authAdapter->authenticate();
        if (! $result->isValid()) {
            $this->error = 'Username or password is incorrect';
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
            $this->error = 'This account is inactive or awaiting approval';
            return null;
        }
        // is the role exists ?
        // 
        $roles = $this->authModel->findRolesById($rowObject->userId);

        if (empty($roles)) {
            $this->error = 'There is no role defined for this user';
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

    public function getError()
    {
        return $this->error;
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
        if (preg_match(self::HEADER_VALUE_PATTERN, $authHeader[0], $matches)) {
            return $matches[1];
        }
        return null;
    }

    public function getTokenModel()
    {
        return $this->tokenModel;
    }
    
}
