<?php

declare(strict_types=1);

namespace App\Container;

use Laminas\Validator\NotEmpty;
use App\Filter\ObjectInputFilter;
use Psr\Container\ContainerInterface;
use Laminas\Validator\ValidatorPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ObjectInputFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $optionalInputFilter = new ObjectInputFilter;
        $validator = $container->get(ValidatorPluginManager::class);
        $notEmptyValidator = $validator->get(NotEmpty::class);
        $optionalInputFilter->setNotEmptyValidator($notEmptyValidator);
        return $optionalInputFilter;
    }
}
