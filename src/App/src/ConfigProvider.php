<?php

declare(strict_types=1);

namespace App;

use Olobase\Mezzio\ColumnFiltersInterface;
use Olobase\Mezzio\Authentication\JwtEncoderInterface;
use Olobase\Mezzio\Authorization\PermissionModelInterface;

use Predis\ClientInterface as PredisInterface;
use Laminas\Cache\Storage\StorageInterface;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\EventManager\EventManagerInterface;
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
                    Filter\Auth\ResetPasswordFilter::class => Filter\Auth\ResetPasswordFilterFactory::class,
                    Filter\Auth\ChangePasswordFilter::class => Filter\Auth\ChangePasswordFilterFactory::class,
                    // Account
                    Filter\Account\SaveFilter::class => Filter\Account\SaveFilterFactory::class,
                    Filter\Account\PasswordChangeFilter::class => Filter\Account\PasswordChangeFilterFactory::class,
                    // Companies
                    Filter\Companies\SaveFilter::class => Filter\Companies\SaveFilterFactory::class,
                    Filter\Companies\DeleteFilter::class => Filter\Companies\DeleteFilterFactory::class,
                    // Employees
                    Filter\Employees\SaveFilter::class => Filter\Employees\SaveFilterFactory::class,
                    Filter\Employees\DeleteFilter::class => Filter\Employees\DeleteFilterFactory::class,
                    // Employee Grades
                    Filter\EmployeeGrades\SaveFilter::class => Filter\EmployeeGrades\SaveFilterFactory::class,
                    Filter\EmployeeGrades\DeleteFilter::class => Filter\EmployeeGrades\DeleteFilterFactory::class,
                    // Departments
                    Filter\Departments\SaveFilter::class => Filter\Departments\SaveFilterFactory::class,
                    Filter\Departments\DeleteFilter::class => Filter\Departments\DeleteFilterFactory::class,
                    // Files
                    Filter\Files\ReadFileFilter::class => Filter\Files\ReadFileFilterFactory::class,
                    // Job Titles
                    Filter\JobTitles\SaveFilter::class => Filter\JobTitles\SaveFilterFactory::class,
                    Filter\JobTitles\DeleteFilter::class => Filter\JobTitles\DeleteFilterFactory::class,
                    // Job Title Lists
                    Filter\JobTitleLists\SaveFilter::class => Filter\JobTitleLists\SaveFilterFactory::class,
                    Filter\JobTitleLists\DeleteFilter::class => Filter\JobTitleLists\DeleteFilterFactory::class,
                    Filter\JobTitleLists\FileUploadFilter::class => InvokableFactory::class,
                    Filter\JobTitleLists\ImportFilter::class => Filter\JobTitleLists\ImportFilterFactory::class,
                    // Permissions
                    Filter\Permissions\SaveFilter::class => Filter\Permissions\SaveFilterFactory::class,
                    Filter\Permissions\DeleteFilter::class => Filter\Permissions\DeleteFilterFactory::class,
                    // Roles
                    Filter\Roles\SaveFilter::class => Filter\Roles\SaveFilterFactory::class,
                    Filter\Roles\DeleteFilter::class => Filter\Roles\DeleteFilterFactory::class,
                    // Users
                    Filter\Users\SaveFilter::class => Filter\Users\SaveFilterFactory::class,
                    Filter\Users\DeleteFilter::class => Filter\Users\DeleteFilterFactory::class,
                    Filter\Users\PasswordSaveFilter::class => Filter\Users\PasswordSaveFilterFactory::class,
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
                Authentication\JwtAuthentication::class => Authentication\JwtAuthenticationFactory::class,
                Middleware\ClientMiddleware::class => Middleware\ClientMiddlewareFactory::class,
                Middleware\RestrictedModeMiddleware::class => Middleware\RestrictedModeMiddlewareFactory::class,
                Middleware\JwtAuthenticationMiddleware::class => Middleware\JwtAuthenticationMiddlewareFactory::class,
                Listener\LoginListener::class => Listener\LoginListenerFactory::class,
                Utils\SmtpMailer::class => Container\SmtpMailerFactory::class,
                Utils\TokenEncrypt::class => Utils\TokenEncryptFactory::class,
                StorageInterface::class => Container\CacheFactory::class,
                SimpleCacheInterface::class => Container\SimpleCacheFactory::class,   
                PredisInterface::class => Container\PredisFactory::class,
                EventManagerInterface::class => Container\EventManagerFactory::class,

                // Handlers
                //------------------------------------------
                // common
                Handler\Common\Stream\EventsHandler::class => Handler\Common\Stream\EventsHandlerFactory::class,
                Handler\Common\Locales\FindAllHandler::class => Handler\Common\Locales\FindAllHandlerFactory::class,
                Handler\Common\Years\FindAllHandler::class => Handler\Common\Years\FindAllHandlerFactory::class,
                Handler\Common\Months\FindAllHandler::class => Handler\Common\Months\FindAllHandlerFactory::class,
                Handler\Common\Cities\FindAllHandler::class => Handler\Common\Cities\FindAllHandlerFactory::class,
                Handler\Common\Countries\FindAllHandler::class => Handler\Common\Countries\FindAllHandlerFactory::class,
                Handler\Common\Currencies\FindAllHandler::class => Handler\Common\Currencies\FindAllHandlerFactory::class,
                Handler\Common\AreaCodes\FindAllHandler::class => Handler\Common\AreaCodes\FindAllHandlerFactory::class,
                Handler\Common\Files\FindOneByIdHandler::class => Handler\Common\Files\FindOneByIdHandlerFactory::class,
                Handler\Common\Files\ReadOneByIdHandler::class => Handler\Common\Files\ReadOneByIdHandlerFactory::class,

                // auth
                Handler\Auth\TokenHandler::class => Handler\Auth\TokenHandlerFactory::class,
                Handler\Auth\RefreshHandler::class => Handler\Auth\RefreshHandlerFactory::class,
                Handler\Auth\LogoutHandler::class => Handler\Auth\LogoutHandlerFactory::class,
                Handler\Auth\SessionUpdateHandler::class => Handler\Auth\SessionUpdateHandlerFactory::class,
                Handler\Auth\ResetPasswordHandler::class => Handler\Auth\ResetPasswordHandlerFactory::class,
                Handler\Auth\CheckResetCodeHandler::class => Handler\Auth\CheckResetCodeHandlerFactory::class,
                Handler\Auth\ChangePasswordHandler::class => Handler\Auth\ChangePasswordHandlerFactory::class,
                // account
                Handler\Account\FindMeHandler::class => Handler\Account\FindMeHandlerFactory::class,
                Handler\Account\UpdateHandler::class => Handler\Account\UpdateHandlerFactory::class,
                Handler\Account\UpdatePasswordHandler::class => Handler\Account\UpdatePasswordHandlerFactory::class,
                // users
                Handler\Users\CreateHandler::class => Handler\Users\CreateHandlerFactory::class,
                Handler\Users\UpdateHandler::class => Handler\Users\UpdateHandlerFactory::class,
                Handler\Users\DeleteHandler::class => Handler\Users\DeleteHandlerFactory::class,
                Handler\Users\FindOneByIdHandler::class => Handler\Users\FindOneByIdHandlerFactory::class,
                Handler\Users\FindAllHandler::class => Handler\Users\FindAllHandlerFactory::class,
                Handler\Users\FindAllByPagingHandler::class => Handler\Users\FindAllByPagingHandlerFactory::class,
                // roles
                Handler\Roles\CreateHandler::class => Handler\Roles\CreateHandlerFactory::class,
                Handler\Roles\UpdateHandler::class => Handler\Roles\UpdateHandlerFactory::class,
                Handler\Roles\DeleteHandler::class => Handler\Roles\DeleteHandlerFactory::class,
                Handler\Roles\FindOneByIdHandler::class => Handler\Roles\FindOneByIdHandlerFactory::class,
                Handler\Roles\FindAllHandler::class => Handler\Roles\FindAllHandlerFactory::class,
                Handler\Roles\FindAllByPagingHandler::class => Handler\Roles\FindAllByPagingHandlerFactory::class,
                // permissions
                Handler\Permissions\CopyHandler::class => Handler\Permissions\CopyHandlerFactory::class,
                Handler\Permissions\CreateHandler::class => Handler\Permissions\CreateHandlerFactory::class,
                Handler\Permissions\UpdateHandler::class => Handler\Permissions\UpdateHandlerFactory::class,
                Handler\Permissions\DeleteHandler::class => Handler\Permissions\DeleteHandlerFactory::class,
                Handler\Permissions\FindAllHandler::class => Handler\Permissions\FindAllHandlerFactory::class,
                Handler\Permissions\FindAllByPagingHandler::class => Handler\Permissions\FindAllByPagingHandlerFactory::class,
                // employees
                Handler\Employees\CreateHandler::class => Handler\Employees\CreateHandlerFactory::class,
                Handler\Employees\UpdateHandler::class => Handler\Employees\UpdateHandlerFactory::class,
                Handler\Employees\DeleteHandler::class => Handler\Employees\DeleteHandlerFactory::class,
                Handler\Employees\FindOneByIdHandler::class => Handler\Employees\FindOneByIdHandlerFactory::class,
                Handler\Employees\FindAllHandler::class => Handler\Employees\FindAllHandlerFactory::class,
                Handler\Employees\FindAllByPagingHandler::class => Handler\Employees\FindAllByPagingHandlerFactory::class,
                // employee grades
                Handler\EmployeeGrades\CreateHandler::class => Handler\EmployeeGrades\CreateHandlerFactory::class,
                Handler\EmployeeGrades\UpdateHandler::class => Handler\EmployeeGrades\UpdateHandlerFactory::class,
                Handler\EmployeeGrades\DeleteHandler::class => Handler\EmployeeGrades\DeleteHandlerFactory::class,
                Handler\EmployeeGrades\FindAllHandler::class => Handler\EmployeeGrades\FindAllHandlerFactory::class,
                Handler\EmployeeGrades\FindAllByPagingHandler::class => Handler\EmployeeGrades\FindAllByPagingHandlerFactory::class,
                // companies
                Handler\Companies\CreateHandler::class => Handler\Companies\CreateHandlerFactory::class,
                Handler\Companies\UpdateHandler::class => Handler\Companies\UpdateHandlerFactory::class,
                Handler\Companies\DeleteHandler::class => Handler\Companies\DeleteHandlerFactory::class,
                Handler\Companies\FindOneByIdHandler::class => Handler\Companies\FindOneByIdHandlerFactory::class,
                Handler\Companies\FindAllHandler::class => Handler\Companies\FindAllHandlerFactory::class,
                Handler\Companies\FindAllByPagingHandler::class => Handler\Companies\FindAllByPagingHandlerFactory::class,
                // departments
                Handler\Departments\CreateHandler::class => Handler\Departments\CreateHandlerFactory::class,
                Handler\Departments\UpdateHandler::class => Handler\Departments\UpdateHandlerFactory::class,
                Handler\Departments\DeleteHandler::class => Handler\Departments\DeleteHandlerFactory::class,
                Handler\Departments\FindAllHandler::class => Handler\Departments\FindAllHandlerFactory::class,
                Handler\Departments\FindAllByPagingHandler::class => Handler\Departments\FindAllByPagingHandlerFactory::class,
                // job titles
                Handler\JobTitles\CreateHandler::class => Handler\JobTitles\CreateHandlerFactory::class,
                Handler\JobTitles\UpdateHandler::class => Handler\JobTitles\UpdateHandlerFactory::class,
                Handler\JobTitles\DeleteHandler::class => Handler\JobTitles\DeleteHandlerFactory::class,
                Handler\JobTitles\FindAllHandler::class => Handler\JobTitles\FindAllHandlerFactory::class,
                Handler\JobTitles\FindAllByPagingHandler::class => Handler\JobTitles\FindAllByPagingHandlerFactory::class,
                // job title lists
                Handler\JobTitleLists\UploadHandler::class => Handler\JobTitleLists\UploadHandlerFactory::class,
                Handler\JobTitleLists\PreviewHandler::class => Handler\JobTitleLists\PreviewHandlerFactory::class,
                Handler\JobTitleLists\ImportHandler::class => Handler\JobTitleLists\ImportHandlerFactory::class,
                Handler\JobTitleLists\ResetHandler::class => Handler\JobTitleLists\ResetHandlerFactory::class,
                Handler\JobTitleLists\RemoveHandler::class => Handler\JobTitleLists\RemoveHandlerFactory::class,
                Handler\JobTitleLists\UpdateHandler::class => Handler\JobTitleLists\UpdateHandlerFactory::class,
                Handler\JobTitleLists\DeleteHandler::class => Handler\JobTitleLists\DeleteHandlerFactory::class,
                Handler\JobTitleLists\FindAllHandler::class => Handler\JobTitleLists\FindAllHandlerFactory::class,
                Handler\JobTitleLists\FindAllByPagingHandler::class => Handler\JobTitleLists\FindAllByPagingHandlerFactory::class,
                // failed logins
                Handler\FailedLogins\FindAllByPagingHandler::class => Handler\FailedLogins\FindAllByPagingHandlerFactory::class,
                Handler\FailedLogins\FindAllIpAdressesHandler::class => Handler\FailedLogins\FindAllIpAdressesHandlerFactory::class,
                Handler\FailedLogins\FindAllUsernamesHandler::class => Handler\FailedLogins\FindAllUsernamesHandlerFactory::class,
                
                // Models
                //
                Model\AuthModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new Model\AuthModel($dbAdapter);
                },
                Model\CompanyModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $companies = new TableGateway('companies', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $cacheStorage = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Model\CompanyModel($companies, $cacheStorage, $columnFilters);
                },
                Model\CommonModel::class => function ($container) {
                    $config = $container->get('config');
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $cacheStorage = $container->get(StorageInterface::class);
                    return new Model\CommonModel($dbAdapter, $cacheStorage, $config);
                },
                Model\DepartmentModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $departments = new TableGateway('departments', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Model\DepartmentModel($departments, $columnFilters);
                },
                Model\EmployeeModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $employees = new TableGateway('employees', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $employeeChildren = new TableGateway('employeeChildren', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $employeeFiles = new TableGateway('employeeFiles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $files = new TableGateway('files', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Model\EmployeeModel(
                        $employees,
                        $employeeChildren,
                        $employeeFiles,
                        $files,
                        $columnFilters
                    );
                },
                Model\EmployeeGradeModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $employeeGrades = new TableGateway('employeeGrades', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $cacheStorage = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Model\EmployeeGradeModel($employeeGrades, $cacheStorage, $columnFilters);
                },
                Model\FailedLoginModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $simpleCache = $container->get(SimpleCacheInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    $users = new TableGateway('users', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $failedLogins = new TableGateway('failedLogins', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\FailedLoginModel($users, $failedLogins, $simpleCache, $columnFilters);
                },
                Model\FileModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $employeeFiles = new TableGateway('employeeFiles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\FileModel($dbAdapter, $employeeFiles);
                },
                Model\JobTitleModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $jobtitles = new TableGateway('jobTitles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $cacheStorage = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Model\JobTitleModel(
                        $jobtitles,
                        $cacheStorage,
                        $columnFilters
                    );
                },
                Model\JobTitleListModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $jobTitles = new TableGateway('jobTitles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $jobTitleList = new TableGateway('jobTitleList', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Model\JobTitleListModel(
                        $jobTitles,
                        $jobTitleList,
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
                    $tokenEncrypt = $container->get(Utils\TokenEncrypt::class);
                    $cacheStorage = $container->get(StorageInterface::class);
                    $jwtEncoder = $container->get(JwtEncoderInterface::class);
                    $users = new TableGateway('users', $dbAdapter, null);
                    return new Model\TokenModel($container->get('config'), $cacheStorage, $tokenEncrypt, $jwtEncoder, $users);
                },
                Model\UserModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $users = new TableGateway('users', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $userRoles = new TableGateway('userRoles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $userAvatars = new TableGateway('userAvatars', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    $simpleCache = $container->get(SimpleCacheInterface::class);
                    return new Model\UserModel(
                        $users,
                        $userRoles,
                        $userAvatars,
                        $columnFilters,
                        $simpleCache
                    );
                },

            ]
        ];
    }
}