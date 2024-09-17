<?php

declare(strict_types=1);

namespace App\Listener;

use App\Model\FailedLoginModel;
use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;

use function createGuid;
use function getRealUserIp;

class LoginListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    protected $failedLoginModel;

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
                'ip' => getRealUserIp(),
            ]
        );
        return ['banned' => false];
    }

    public function onSuccessfullLogin(EventInterface $e)
    {
        $params = $e->getParams();
        $username = trim($params['username']);
        $rowObject = $params['rowObject'];
        $translator = $params['translator'];
        $updateData = [
            'locale' => $translator->getLocale(), // last locale
            'lastLogin' => date("Y-m-d H:i:s", time()),
        ];
        /**
         * We delete attempts:
         *
         * 1- When user do the successful login
         * 2- When the user clicks on the reset link in the email we send
         */
        $this->failedLoginModel->deleteAttemptsAndUpdateUser(
            $updateData,
            ['username' => $username, 'userId' => $rowObject->userId]
        );
    }

}