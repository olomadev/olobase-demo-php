<?php

namespace App\Entity;

/**
 * @table roles
 */
class RolesEntity
{
    const ENTITY_TYPE = 'null';
    /**
     * @var varchar(36)
     */
    public $roleId;
    /**
     * @var varchar(60)
     */
    public $roleKey;
    /**
     * @var varchar(100)
     */
    public $roleName;
    /**
     * @var tinyint(1)
     */
    public $roleLevel;
}
