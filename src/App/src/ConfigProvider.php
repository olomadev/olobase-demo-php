<?php

declare(strict_types=1);

namespace App;

use Predis\ClientInterface;
use App\Utils\Error;
use App\Utils\Mailer;
use App\Utils\ErrorMailer;
use App\Utils\DataManager;
use App\Utils\CacheFlush;
use App\Utils\EventManager;
use App\Utils\ColumnFilters;
use Laminas\Cache\Storage\StorageInterface;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\EventManager\EventManagerInterface;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;

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
        // print_r($this->getDependencies()['factories']);
        //die;

        return [
            'dependencies' => $this->getDependencies(),
            'input_filters' => [
                'factories' => [
                    Filter\AuthFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\CollectionInputFilter::class => Container\CollectionInputFilterFactory::class,
                    Filter\ObjectInputFilter::class => Container\ObjectInputFilterFactory::class,
                    Filter\AccountSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\DepartmentSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\CompanySaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\DisabilitySaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\EmployeeSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\EmployeeListSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\EmployeeGradeSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\EmployeeProfileSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\EmployeeListImportFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\ExpenseTypeSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\PaymentTypeSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\PayrollSchemeSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\JobTitleSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\JobTitleListImportFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\JobTitleListSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\MinWageSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\DisabilityFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\NotificationSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\ExchangeRateSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\PasswordUpdateFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\PasswordSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\PermissionSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\RoleSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\ResetPasswordFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\SalarySaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\SalaryListSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\SendResetPasswordFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\UserSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\WorkplaceSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\FileUploadFilter::class => ReflectionBasedAbstractFactory::class,
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
            ],
            'factories' => [

                // Classes
                //
                Authentication\JwtEncoder::class => Container\JwtEncoderFactory::class,
                Authentication\JwtAuthentication::class => Container\JwtAuthenticationFactory::class,
                Middleware\JwtAuthenticationMiddleware::class => Container\JwtAuthenticationMiddlewareFactory::class,
                Middleware\JwtAuthenticationDocumentMiddleware::class => Container\JwtAuthenticationDocumentMiddlewareFactory::class,
                Middleware\ClientMiddleware::class => ReflectionBasedAbstractFactory::class,
                \Mezzio\Authentication\UserInterface::class => Container\DefaultUserFactory::class,
                \Mezzio\Authorization\AuthorizationInterface::class => Container\AuthorizationFactory::class,
                StorageInterface::class => Container\CacheFactory::class,
                SimpleCacheInterface::class => Container\SimpleCacheFactory::class,
                CacheFlush::class => Container\CacheFlushFactory::class,
                ColumnFilters::class => Container\ColumnFiltersFactory::class,
                DataManager::class => Container\DataManagerFactory::class,
                EventManager::class => Container\EventManagerFactory::class,
                Mailer::class => Container\MailerFactory::class,
                ErrorMailer::class => Container\ErrorMailerFactory::class,
                Error::class => Container\ErrorFactory::class,
                EventManagerInterface::class => Container\LaminasEventManagerFactory::class,
                ClientInterface::class => Container\PredisFactory::class,
                // AppListener::class => ReflectionBasedAbstractFactory::class,
                // NotificationListener::class => ReflectionBasedAbstractFactory::class,

                // Handlers
                //
                Handler\Api\AccountHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\AgreementTypesHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\AuthHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\AreaCodesHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\CustomerHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\DepartmentHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\SubDepartmentHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\CountriesHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\CitiesHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\CommonFunctionsHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\CompanyHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\DisabilitiesHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\FileHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\EmployeeHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\EmployeeListsHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\EmployeeTypesHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\EmployeeGroupsHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\EmployeeGradesHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\EmployeeProfilesHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\CostCentersHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\JobTitlesHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\JobTitleListsHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\MinumumWagesHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\NotificationHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\NotifyModulesHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\NotifyDatesHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\PaymentTypeHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\PaymentTypesHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\PayrollSchemeHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\MonthsHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\PermissionHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\RoleHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\RolesHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\RoleKeysHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\SalariesHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\SalaryListsHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\ExchangeRatesHandler::class => ReflectionBasedAbstractFactory::class,                
                Handler\Api\SqlOrderHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\UserHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\YearsHandler::class => ReflectionBasedAbstractFactory::class,
                Handler\Api\WorkplaceHandler::class => ReflectionBasedAbstractFactory::class,

                // Models
                //
                Model\AllowanceModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $employeeCostAllowance = new TableGateway('employeeCostAllowanceSheetLabel', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $employeeCostAllowances = new TableGateway('employeeCostAllowances', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFilters::class);
                    return new Model\AllowanceModel(
                        $employeeCostAllowance,
                        $employeeCostAllowances,
                        $columnFilters
                    );
                },
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
                Model\CustomerModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $customers = new TableGateway('customers', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $customerJobTitles = new TableGateway('customerJobTitles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $customerAllowances = new TableGateway('employeeCostAllowances', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $customerExpenseTypes = new TableGateway('customerExpenseTypes', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $customerDepartments = new TableGateway('customerDepartments', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFilters::class);
                    return new Model\CustomerModel(
                        $customers,
                        $customerJobTitles,
                        $customerAllowances,
                        $customerExpenseTypes,
                        $customerDepartments,
                        $columnFilters
                    );
                },
                Model\DepartmentModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $departments = new TableGateway('departments', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFilters::class);
                    return new Model\DepartmentModel($departments, $columnFilters);
                },
                Model\CompanyModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $companies = new TableGateway('companies', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFilters::class);
                    return new Model\CompanyModel($companies, $columnFilters);
                },
                Model\CommonModel::class => function ($container) {
                    $config = $container->get('config');
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $cacheStorage = $container->get(StorageInterface::class);
                    return new Model\CommonModel($dbAdapter, $cacheStorage, $config);
                },
                Model\DisabilityModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $columnFilters = $container->get(ColumnFilters::class);
                    $disabilities = new TableGateway('disabilities', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\DisabilityModel($disabilities, $columnFilters);
                },
                Model\EmployeeModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $employees = new TableGateway('employees', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $employeeGroups = new TableGateway('employeeGroups', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $cacheStorage = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFilters::class);
                    return new Model\EmployeeModel(
                        $employees,
                        $employeeGroups,
                        $cacheStorage,
                        $columnFilters
                    );
                },
                Model\EmployeeListModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $employeeList = new TableGateway('employeeList', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $cacheStorage = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFilters::class);
                    return new Model\EmployeeListModel(
                        $employeeList,
                        $cacheStorage,
                        $columnFilters
                    );
                },
                Model\EmployeeGradeModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $employeeGrades = new TableGateway('employeeGrades', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFilters::class);
                    return new Model\EmployeeGradeModel($employeeGrades, $columnFilters);
                },
                Model\EmployeeProfileModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $employeeProfiles = new TableGateway('employeeProfiles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFilters::class);
                    return new Model\EmployeeProfileModel($employeeProfiles, $columnFilters);
                },
                Model\ExchangeRatesModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $exchangeRates = new TableGateway('exchangeRates', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFilters::class);
                    return new Model\ExchangeRatesModel($exchangeRates, $columnFilters);
                },
                Model\JobTitleModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $jobtitles = new TableGateway('jobTitles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFilters::class);
                    return new Model\JobTitleModel(
                        $jobtitles,
                        $columnFilters
                    );
                },
                Model\JobTitleListModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $jobTitleList = new TableGateway('jobTitleList', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $cacheStorage = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFilters::class);
                    return new Model\JobTitleListModel(
                        $jobTitleList,
                        $cacheStorage,
                        $columnFilters
                    );
                },
                Model\PaymentTypeModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $paymentTypes = new TableGateway('paymentTypes', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFilters::class);
                    return new Model\PaymentTypeModel(
                        $paymentTypes,
                        $columnFilters
                    );
                },
                Model\PermissionModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $cacheStorage = $container->get(StorageInterface::class);
                    $tableGateway = new TableGateway('permissions', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\PermissionModel($tableGateway, $cacheStorage);
                },
                Model\PayrollSchemeModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $columnFilters = $container->get(ColumnFilters::class);
                    $payrollScheme = new TableGateway('payrollScheme', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\PayrollSchemeModel($payrollScheme, $columnFilters);
                },
                Model\RoleModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $cacheStorage = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFilters::class);
                    $roles = new TableGateway('roles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $rolePermissions = new TableGateway('rolePermissions', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\RoleModel(
                        $roles,
                        $rolePermissions,
                        $cacheStorage,
                        $columnFilters
                    );
                },
                Model\SalaryListModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $salaryList = new TableGateway('salaryList', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $cacheStorage = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFilters::class);
                    return new Model\SalaryListModel(
                        $salaryList,
                        $cacheStorage,
                        $columnFilters
                    );
                },
                Model\SalaryModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $columnFilters = $container->get(ColumnFilters::class);
                    $salaries = new TableGateway('salaries', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\SalaryModel($salaries, $columnFilters);
                },
                Model\MinWageModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $columnFilters = $container->get(ColumnFilters::class);
                    $minWage = new TableGateway('minWage', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\MinWageModel($minWage, $columnFilters);
                },
                Model\NotificationModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $columnFilters = $container->get(ColumnFilters::class);
                    $notifications = new TableGateway('notifications', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $notificationUsers = new TableGateway('notificationUsers', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\NotificationModel($notifications, $notificationUsers, $columnFilters);
                },
                Model\PermissionModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $cacheStorage = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFilters::class);
                    $permissions = new TableGateway('permissions', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\PermissionModel($permissions, $cacheStorage, $columnFilters);
                },
                Model\SettingsModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $exchangeRates = new TableGateway('exchangeRates', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\SettingsModel($exchangeRates);
                },
                Model\TokenModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $jwtEncoder = $container->get(Authentication\JwtEncoder::class);
                    $users = new TableGateway('users', $dbAdapter, null);
                    $refreshToken = new TableGateway('refreshTokens', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\TokenModel($container->get('config'), $jwtEncoder, $users, $refreshToken);
                },
                Model\UserModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $users = new TableGateway('users', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $userRoles = new TableGateway('userRoles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $userAvatars = new TableGateway('userAvatars', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFilters::class);
                    $cacheStorage = $container->get(StorageInterface::class);
                    return new Model\UserModel(
                        $users,
                        $userRoles,
                        $userAvatars,
                        $columnFilters,
                        $cacheStorage
                    );
                },
                Model\WorkplaceModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $workplaces = new TableGateway('workplaces', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $cacheStorage = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFilters::class);
                    return new Model\WorkplaceModel($workplaces, $columnFilters, $cacheStorage);
                },

            ]
        ];
    }
}