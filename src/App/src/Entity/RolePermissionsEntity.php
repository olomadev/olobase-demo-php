<?php

namespace App\Entity;

/**
 * @table rolePermissions
 */
class RolePermissionsEntity
{
    const ENTITY_TYPE = 'array';
    const TABLE_NAME = 'rolePermissions';
    /**
     * @var varchar(36)
     */
    public $roleId;
    /**
     * @var varchar(36)
     */
    public $permId;
}
