<?php

declare(strict_types=1);

namespace App;

use Predis\ClientInterface;
use Oloma\Php\ColumnFiltersInterface;
use Oloma\Php\Authentication\JwtEncoderInterface;
use Oloma\Php\Authorization\PermissionModelInterface;

use Laminas\Cache\Storage\StorageInterface;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'input_filters' => [
                'factories' => [
                    // Core Input Filters
                    Filter\ObjectInputFilter::class => Container\ObjectInputFilterFactory::class,
                    Filter\CollectionInputFilter::class => Container\CollectionInputFilterFactory::class,
                    // Auth
                    Filter\Auth\AuthFilter::class => InvokableFactory::class,
                    // Account
                    Filter\Account\SaveFilter::class => Filter\Account\SaveFilterFactory::class,
                    Filter\Account\PasswordChangeFilter::class => Filter\Account\PasswordChangeFilterFactory::class,
                    // Users
                    Filter\Users\SaveFilter::class => Filter\Users\SaveFilterFactory::class,
                    Filter\Users\DeleteFilter::class => Filter\Users\DeleteFilterFactory::class,
                    Filter\Users\PasswordSaveFilter::class => Filter\Users\PasswordSaveFilterFactory::class,
                    // Roles
                    Filter\Roles\SaveFilter::class => Filter\Roles\SaveFilterFactory::class,
                    Filter\Roles\DeleteFilter::class => Filter\Roles\DeleteFilterFactory::class,
                    // Permissions
                    Filter\Permissions\SaveFilter::class => Filter\Permissions\SaveFilterFactory::class,
                    Filter\Permissions\DeleteFilter::class => Filter\Permissions\DeleteFilterFactory::class,
                    // Employee Grades
                    Filter\EmployeeGrades\SaveFilter::class => Filter\EmployeeGrades\SaveFilterFactory::class,
                    Filter\EmployeeGrades\DeleteFilter::class => Filter\EmployeeGrades\DeleteFilterFactory::class,
                    // Companies
                    Filter\Companies\SaveFilter::class => Filter\Companies\SaveFilterFactory::class,
                    Filter\Companies\DeleteFilter::class => Filter\Companies\DeleteFilterFactory::class,
                    // Job Title Lists
                    Filter\JobTitleLists\SaveFilter::class => Filter\JobTitleLists\SaveFilterFactory::class,
                    Filter\JobTitleLists\DeleteFilter::class => Filter\JobTitleLists\DeleteFilterFactory::class,
                    Filter\JobTitleLists\FileUploadFilter::class => InvokableFactory::class,
                    Filter\JobTitleLists\ImportFilter::class => Filter\JobTitleLists\ImportFilterFactory::class,
                    // Files
                    Filter\Files\DownloadFilter::class => Filter\Files\DownloadFilterFactory::class,
                ],
            ],
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies() : array
    {
        return [
            'invokables' => [
                // Handler\PingHandler::class => Handler\PingHandler::class,
            ],
            'delegators' => [
                TranslatorInterface::class => [
                    'App\Container\TranslatorDelegatorFactory',
                ],
            ],
            'aliases' => [
                \Mezzio\Authentication\AuthenticationInterface::class => Authentication\JwtAuthentication::class,
                \App\Model\PermissionModel::class => PermissionModelInterface::class,
            ],
            'factories' => [

                // Classes
                //
                Authentication\JwtAuthentication::class => Container\JwtAuthenticationFactory::class,
                Middleware\ClientMiddleware::class => Middleware\ClientMiddlewareFactory::class,
                Middleware\JwtAuthenticationMiddleware::class => Middleware\JwtAuthenticationMiddlewareFactory::class,
                StorageInterface::class => Container\CacheFactory::class,
                SimpleCacheInterface::class => Container\SimpleCacheFactory::class,   
                ClientInterface::class => Container\PredisFactory::class,
                Mailer::class => Container\MailerFactory::class,
                ErrorMailer::class => Container\ErrorMailerFactory::class,

                // Handlers
                //
                // common
                Handler\Common\Years\FindAllHandler::class => Handler\Common\Years\FindAllHandlerFactory::class,
                Handler\Common\Months\FindAllHandler::class => Handler\Common\Months\FindAllHandlerFactory::class,
                Handler\Common\Cities\FindAllHandler::class => Handler\Common\Cities\FindAllHandlerFactory::class,
                Handler\Common\Countries\FindAllHandler::class => Handler\Common\Countries\FindAllHandlerFactory::class,
                Handler\Common\AreaCodes\FindAllHandler::class => Handler\Common\AreaCodes\FindAllHandlerFactory::class,
                Handler\Common\Files\FindOneByIdHandler::class => Handler\Common\Files\FindOneByIdHandlerFactory::class,
                
                // auth
                Handler\Auth\TokenHandler::class => Handler\Auth\TokenHandlerFactory::class,
                Handler\Auth\RefreshHandler::class => Handler\Auth\RefreshHandlerFactory::class,
                Handler\Auth\LogoutHandler::class => Handler\Auth\LogoutHandlerFactory::class,
                Handler\Auth\FindAllPermissionsHandler::class => Handler\Auth\FindAllPermissionsHandlerFactory::class,
                // account
                Handler\Account\FindMeHandler::class => Handler\Account\FindMeHandlerFactory::class,
                Handler\Account\UpdateHandler::class => Handler\Account\UpdateHandlerFactory::class,
                Handler\Account\UpdatePasswordHandler::class => Handler\Account\UpdatePasswordHandlerFactory::class,
                // users
                Handler\Users\CreateHandler::class => Handler\Users\CreateHandlerFactory::class,
                Handler\Users\UpdateHandler::class => Handler\Users\UpdateHandlerFactory::class,
                Handler\Users\DeleteHandler::class => Handler\Users\DeleteHandlerFactory::class,
                Handler\Users\FindOneByIdHandler::class => Handler\Users\FindOneByIdHandlerFactory::class,
                Handler\Users\FindAllByPagingHandler::class => Handler\Users\FindAllByPagingHandlerFactory::class,
                Handler\Users\FindAllHandler::class => Handler\Users\FindAllHandlerFactory::class,
                // roles
                Handler\Roles\CreateHandler::class => Handler\Roles\CreateHandlerFactory::class,
                Handler\Roles\UpdateHandler::class => Handler\Roles\UpdateHandlerFactory::class,
                Handler\Roles\DeleteHandler::class => Handler\Roles\DeleteHandlerFactory::class,
                Handler\Roles\FindOneByIdHandler::class => Handler\Roles\FindOneByIdHandlerFactory::class,
                Handler\Roles\FindAllByPagingHandler::class => Handler\Roles\FindAllByPagingHandlerFactory::class,
                Handler\Roles\FindAllHandler::class => Handler\Roles\FindAllHandlerFactory::class,
                // permissions
                Handler\Permissions\CopyHandler::class => Handler\Permissions\CopyHandlerFactory::class,
                Handler\Permissions\CreateHandler::class => Handler\Permissions\CreateHandlerFactory::class,
                Handler\Permissions\UpdateHandler::class => Handler\Permissions\UpdateHandlerFactory::class,
                Handler\Permissions\DeleteHandler::class => Handler\Permissions\DeleteHandlerFactory::class,
                Handler\Permissions\FindAllByPagingHandler::class => Handler\Permissions\FindAllByPagingHandlerFactory::class,
                Handler\Permissions\FindAllHandler::class => Handler\Permissions\FindAllHandlerFactory::class,
                // employee grades
                Handler\EmployeeGrades\CreateHandler::class => Handler\EmployeeGrades\CreateHandlerFactory::class,
                Handler\EmployeeGrades\UpdateHandler::class => Handler\EmployeeGrades\UpdateHandlerFactory::class,
                Handler\EmployeeGrades\DeleteHandler::class => Handler\EmployeeGrades\DeleteHandlerFactory::class,
                Handler\EmployeeGrades\FindAllByPagingHandler::class => Handler\EmployeeGrades\FindAllByPagingHandlerFactory::class,
                Handler\EmployeeGrades\FindAllHandler::class => Handler\EmployeeGrades\FindAllHandlerFactory::class,
                // companies
                Handler\Companies\CreateHandler::class => Handler\Companies\CreateHandlerFactory::class,
                Handler\Companies\UpdateHandler::class => Handler\Companies\UpdateHandlerFactory::class,
                Handler\Companies\DeleteHandler::class => Handler\Companies\DeleteHandlerFactory::class,
                Handler\Companies\FindOneByIdHandler::class => Handler\Companies\FindOneByIdHandlerFactory::class,
                Handler\Companies\FindAllByPagingHandler::class => Handler\Companies\FindAllByPagingHandlerFactory::class,
                Handler\Companies\FindAllHandler::class => Handler\Companies\FindAllHandlerFactory::class,
                // job titles
                Handler\JobTitles\CreateHandler::class => Handler\JobTitles\CreateHandlerFactory::class,
                Handler\JobTitles\UpdateHandler::class => Handler\JobTitles\UpdateHandlerFactory::class,
                Handler\JobTitles\DeleteHandler::class => Handler\JobTitles\DeleteHandlerFactory::class,
                Handler\JobTitles\FindAllByPagingHandler::class => Handler\JobTitles\FindAllByPagingHandlerFactory::class,
                Handler\JobTitles\FindAllHandler::class => Handler\JobTitles\FindAllHandlerFactory::class,
                // job title lists
                Handler\JobTitleLists\UploadHandler::class => Handler\JobTitleLists\UploadHandlerFactory::class,
                Handler\JobTitleLists\PreviewHandler::class => Handler\JobTitleLists\PreviewHandlerFactory::class,
                Handler\JobTitleLists\ImportHandler::class => Handler\JobTitleLists\ImportHandlerFactory::class,
                Handler\JobTitleLists\StatusHandler::class => Handler\JobTitleLists\StatusHandlerFactory::class,
                Handler\JobTitleLists\ResetHandler::class => Handler\JobTitleLists\ResetHandlerFactory::class,
                Handler\JobTitleLists\RemoveHandler::class => Handler\JobTitleLists\RemoveHandlerFactory::class,
                Handler\JobTitleLists\UpdateHandler::class => Handler\JobTitleLists\UpdateHandlerFactory::class,
                Handler\JobTitleLists\DeleteHandler::class => Handler\JobTitleLists\DeleteHandlerFactory::class,
                Handler\JobTitleLists\FindAllHandler::class => Handler\JobTitleLists\FindAllHandlerFactory::class,
                Handler\JobTitleLists\FindAllByPagingHandler::class => Handler\JobTitleLists\FindAllByPagingHandlerFactory::class,

                // Models
                //
                Model\AuthModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $simpleCache = $container->get(SimpleCacheInterface::class);
                    $users = new TableGateway('users', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\AuthModel(
                        $dbAdapter,
                        $simpleCache,
                        $users
                    );
                },
                Model\CompanyModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $companies = new TableGateway('companies', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Model\CompanyModel($companies, $columnFilters);
                },
                Model\CommonModel::class => function ($container) {
                    $config = $container->get('config');
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $cacheStorage = $container->get(StorageInterface::class);
                    return new Model\CommonModel($dbAdapter, $cacheStorage, $config);
                },
                Model\EmployeeModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $employees = new TableGateway('employees', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Model\EmployeeModel(
                        $employees,
                        $columnFilters
                    );
                },
                Model\EmployeeGradeModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $employeeGrades = new TableGateway('employeeGrades', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Model\EmployeeGradeModel($employeeGrades, $columnFilters);
                },
                Model\JobTitleModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $jobtitles = new TableGateway('jobTitles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Model\JobTitleModel(
                        $jobtitles,
                        $columnFilters
                    );
                },
                Model\JobTitleListModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $jobTitleList = new TableGateway('jobTitleList', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $cacheStorage = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Model\JobTitleListModel(
                        $jobTitleList,
                        $cacheStorage,
                        $columnFilters
                    );
                },
                PermissionModelInterface::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $cacheStorage = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    $permissions = new TableGateway('permissions', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\PermissionModel(
                        $permissions, 
                        $cacheStorage,
                        $columnFilters
                    );
                },
                Model\RoleModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $cacheStorage = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    $roles = new TableGateway('roles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $rolePermissions = new TableGateway('rolePermissions', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\RoleModel(
                        $roles,
                        $rolePermissions,
                        $cacheStorage,
                        $columnFilters
                    );
                },
                Model\TokenModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $jwtEncoder = $container->get(JwtEncoderInterface::class);
                    $users = new TableGateway('users', $dbAdapter, null);
                    $refreshToken = new TableGateway('refreshTokens', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\TokenModel($container->get('config'), $jwtEncoder, $users, $refreshToken);
                },
                Model\UserModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $users = new TableGateway('users', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $userRoles = new TableGateway('userRoles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $userAvatars = new TableGateway('userAvatars', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    $cacheStorage = $container->get(StorageInterface::class);
                    return new Model\UserModel(
                        $users,
                        $userRoles,
                        $userAvatars,
                        $columnFilters,
                        $cacheStorage
                    );
                },

            ]
        ];
    }
}