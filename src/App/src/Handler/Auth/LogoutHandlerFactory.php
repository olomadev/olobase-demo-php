<?php

declare(strict_types=1);

namespace App\Handler\Auth;

use App\Model\TokenModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\I18n\Translator\TranslatorInterface;

class LogoutHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $translator = $container->get(TranslatorInterface::class);
        $tokenModel = $container->get(TokenModel::class);

        return new LogoutHandler(
            $translator, 
            $tokenModel
        );
    }
}
