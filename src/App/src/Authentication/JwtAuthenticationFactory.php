<?php

declare(strict_types=1);

namespace App\Authentication;

use App\Model\AuthModel;
use App\Model\TokenModel;
use Psr\Container\ContainerInterface;
use App\Authentication\AuthenticationAdapter;
use Laminas\Db\Adapter\Adapter;
use Laminas\EventManager\EventManagerInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olobase\Mezzio\Authentication\JwtEncoderInterface as JwtEncoder;
use Laminas\Authentication\Adapter\DbTable\CallbackCheckAdapter;
use Mezzio\Authentication\Exception;
use Mezzio\Authentication\UserInterface;
use App\Authentication\JwtAuthentication;

class JwtAuthenticationFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config') ?? [];
        if (! $container->has(UserInterface::class)) {
            throw new Exception\InvalidConfigException(
                'UserInterface factory service is missing for authentication'
            );
        }
        $passwordValidation = function ($hash, $password) {
            return password_verify($password, $hash);
        };
        $authAdapter = new AuthenticationAdapter(  // CallbackCheckAdapter
            $container->get(Adapter::class),
            $config['authentication']['tablename'],
            $config['authentication']['username'],
            $config['authentication']['password'],
            $passwordValidation
        );
        return new JwtAuthentication(
            $config,
            $authAdapter,
            $container->get(TranslatorInterface::class),
            $container->get(JwtEncoder::class),
            $container->get(TokenModel::class),
            $container->get(AuthModel::class),
            $container->get(EventManagerInterface::class),
            $container->has(UserInterface::class) ? $container->get(UserInterface::class) : null
        );
    }
}
