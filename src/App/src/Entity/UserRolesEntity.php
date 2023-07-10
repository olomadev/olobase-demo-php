<?php

namespace App\Entity;

/**
 * @table userRoles
 */
class UserRolesEntity
{
    const ENTITY_TYPE = 'array';
    /**
     * @var int(5) unsigned zerofill
     */
    public $clientId;
    /**
     * @var varchar(36)
     */
    public $userId;
    /**
     * @var varchar(36)
     */
    public $roleId;
}
