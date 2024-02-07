<?php

declare(strict_types=1);

namespace App\Handler\Auth;

use App\Model\AuthModel;
use App\Model\TokenModel;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Mezzio\Authentication\AuthenticationInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\I18n\Translator\TranslatorInterface;

class RefreshHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $config = $container->get('config');
        $translator = $container->get(TranslatorInterface::class);
        $auth = $container->get(AuthenticationInterface::class);
        $authModel = $container->get(AuthModel::class);
        $tokenModel = $container->get(TokenModel::class);
        $error = $container->get(Error::class);

        return new RefreshHandler(
            $config, 
            $translator, 
            $auth, 
            $authModel, 
            $tokenModel, 
            $error
        );
    }
}
