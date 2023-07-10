<?php

namespace App\Entity;

/**
 * @table rolePermissions
 */
class RolePermissionsEntity
{
    const ENTITY_TYPE = 'array';
    /**
     * @var varchar(36)
     */
    public $roleId;
    /**
     * @var varchar(36)
     */
    public $permId;
}
