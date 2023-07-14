<?php

declare(strict_types=1);

namespace App\Handler\Auth;

use App\Model\AuthModel;
use App\Model\TokenModel;
use Oloma\Php\Error\ErrorWrapperInterface as Error;
use Mezzio\Authentication\AuthenticationInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Oloma\Php\Authentication\JwtEncoderInterface;

class RefreshHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $translator = $container->get(TranslatorInterface::class);
        $auth = $container->get(AuthenticationInterface::class);
        $authModel = $container->get(AuthModel::class);
        $tokenModel = $container->get(TokenModel::class);
        $encoder = $container->get(JwtEncoderInterface::class);
        $error = $container->get(Error::class);

        return new RefreshHandler(
            $translator, 
            $auth, 
            $authModel, 
            $tokenModel, 
            $encoder, 
            $error
        );
    }
}
