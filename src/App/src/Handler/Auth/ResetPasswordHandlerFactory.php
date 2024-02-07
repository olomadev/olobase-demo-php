<?php

declare(strict_types=1);

namespace App\Handler\Auth;

use App\Model\UserModel;
use App\Utils\SmtpMailer;
use App\Filter\Auth\ResetPasswordFilter;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class ResetPasswordHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $translator = $container->get(Translator::class);
        $userModel = $container->get(UserModel::class);
        $smtpMailer = $container->get(SmtpMailer::class);
        $error = $container->get(Error::class);

        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(ResetPasswordFilter::class);

        return new ResetPasswordHandler($translator, $userModel, $inputFilter, $smtpMailer, $error);
    }
}
