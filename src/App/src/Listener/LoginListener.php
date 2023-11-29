<?php

declare(strict_types=1);

namespace App\Listener;

use function createGuid;

use App\Model\FailedLoginModel;
use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Http\PhpEnvironment\RemoteAddress;

class LoginListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    const onFailedLogin = 'onFailedLogin';
    const onSuccessfullLogin = 'onSuccessfullLogin';

    public function __construct(FailedLoginModel $failedLoginModel)
    {
        $this->failedLoginModel = $failedLoginModel;
    }

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(Self::onFailedLogin, [$this, Self::onFailedLogin]);
        $this->listeners[] = $events->attach(Self::onSuccessfullLogin, [$this, Self::onSuccessfullLogin]);
    }

    public function onFailedLogin(EventInterface $e)
    {
        $params = $e->getParams();
        $request = $params['request'];
        $username = trim($params['username']);

        $remoteAddress = new RemoteAddress;
        $realIpAddress = $remoteAddress->getIpAddress();
        $server = $request->getServerParams();
        $userAgent = empty($server['HTTP_USER_AGENT']) ? 'unknown' : $server['HTTP_USER_AGENT'];
        //
        // check if the username coming with the IP address is banned ?
        // 
        if ($this->failedLoginModel->checkUsername($username)) {
            return [
                'banned' => true,
                'message' => $this->failedLoginModel->getMessage(),
            ];
        }
        $this->failedLoginModel->createAttempt(
            [
                'loginId' => createGuid(),
                'username' => $username,
                'userAgent' => $userAgent,
                'attemptedAt' => date("Y-m-d"),
                'ip' => $realIpAddress,
            ]
        );
        return ['banned' => false];
    }

    public function onSuccessfullLogin(EventInterface $e)
    {
        $params = $e->getParams();
        // $request = $params['request'];
        $username = trim($params['username']);
        /**
         * We delete attempts:
         *
         * 1- When user do the successful login
         * 2- When the user clicks on the reset link in the email we send
         */
        $this->failedLoginModel->deleteAttempts($username);
    }

}