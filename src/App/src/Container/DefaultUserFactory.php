<?php

declare(strict_types=1);

namespace App\Container;

use App\Authentication\DefaultUser;
use Mezzio\Authentication\UserInterface;
use Psr\Container\ContainerInterface;

/**
 * Produces a callable factory capable of itself producing a UserInterface
 * instance; this approach is used to allow substituting alternative user
 * implementations without requiring extensions to existing repositories.
 */
class DefaultUserFactory
{
    public function __invoke(ContainerInterface $container) : callable
    {
        return function (string $id, string $identity, array $permissions = [], array $details = []) : UserInterface {
            return new DefaultUser($id, $identity, $permissions, $details);
        };
    }
}
