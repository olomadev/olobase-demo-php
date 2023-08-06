<?php

namespace App\Entity;

/**
 * @table users
 */
class UsersEntity
{
    const ENTITY_TYPE = 'null';
    const TABLE_NAME = 'users';
    /**
     * @var varchar(36)
     */
    public $userId;
    /**
     * @var varchar(160)
     */
    public $email;
    /**
     * @var varchar(255)
     */
    public $password;
    /**
     * @var varchar(120)
     */
    public $firstname;
    /**
     * @var varchar(120)
     */
    public $lastname;
    /**
     * @var datetime
     */
    public $createdAt;
    /**
     * @var datetime
     */
    public $updatedAt;
    /**
     * @var datetime
     */
    public $lastLogin;
    /**
     * @var tinyint(1)
     */
    public $active;
    /**
     * @var tinyint(1)
     */
    public $emailActivation;
    /**
     * @var char(7)
     */
    public $themeColor;
}
