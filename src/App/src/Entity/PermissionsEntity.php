<?php

namespace App\Entity;

/**
 * @table permissions
 */
class PermissionsEntity
{
    const ENTITY_TYPE = 'null';
    /**
     * @var varchar(36)
     */
    public $permId;
    /**
     * @var varchar(60)
     */
    public $moduleName;
    /**
     * @var varchar(100)
     */
    public $resource;
    /**
     * @var varchar(15)
     */
    public $action;
    /**
     * @var varchar(160)
     */
    public $route;
    /**
     * @var varchar(10)
     */
    public $method;
}
