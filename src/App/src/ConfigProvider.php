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
                    Filter\AuthFilter::class => InvokableFactory::class,
                    Filter\AccountSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\PasswordUpdateFilter::class => ReflectionBasedAbstractFactory::class,
                    Filter\ObjectInputFilter::class => Container\ObjectInputFilterFactory::class,
                    Filter\CollectionInputFilter::class => Container\CollectionInputFilterFactory::class,

                    // Filter\DepartmentSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\CompanySaveFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\DisabilitySaveFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\EmployeeSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\EmployeeListSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\EmployeeGradeSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\EmployeeProfileSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\EmployeeListImportFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\ExpenseTypeSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\PaymentTypeSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\PayrollSchemeSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\JobTitleSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\JobTitleListImportFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\JobTitleListSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\MinWageSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\DisabilityFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\NotificationSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\ExchangeRateSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\PasswordUpdateFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\PasswordSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\PermissionSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\RoleSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\ResetPasswordFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\SalarySaveFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\SalaryListSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\SendResetPasswordFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\UserSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\WorkplaceSaveFilter::class => ReflectionBasedAbstractFactory::class,
                    // Filter\FileUploadFilter::class => ReflectionBasedAbstractFactory::class,
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
                Authentication\JwtAuthentication::class => Container\JwtAuthenticationFactory::class,
                Middleware\ClientMiddleware::class => ReflectionBasedAbstractFactory::class,
                Middleware\JwtAuthenticationMiddleware::class => Container\JwtAuthenticationMiddlewareFactory::class,
                StorageInterface::class => Container\CacheFactory::class,
                SimpleCacheInterface::class => Container\SimpleCacheFactory::class,   
                             
                Mailer::class => Container\MailerFactory::class,
                ErrorMailer::class => Container\ErrorMailerFactory::class,
                ClientInterface::class => Container\PredisFactory::class,

                // Handlers
                //
                Handler\Auth\TokenHandler::class => Handler\Auth\TokenHandlerFactory::class,
                Handler\Auth\RefreshHandler::class => Handler\Auth\RefreshHandlerFactory::class,
                Handler\Auth\LogoutHandler::class => Handler\Auth\LogoutHandlerFactory::class,
                Handler\Auth\FindAllPermissionsHandler::class => Handler\Auth\FindAllPermissionsHandlerFactory::class,
                Handler\Account\FindMeHandler::class => Handler\Account\FindMeHandlerFactory::class,
                Handler\Account\UpdateHandler::class => Handler\Account\UpdateHandlerFactory::class,
                Handler\Account\UpdatePasswordHandler::class => Handler\Account\UpdatePasswordHandlerFactory::class,
                // Handler\AccountHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\AgreementTypesHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\AuthHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\AreaCodesHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\CustomerHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\DepartmentHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\SubDepartmentHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\CountriesHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\CitiesHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\CommonFunctionsHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\CompanyHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\DisabilitiesHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\FileHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\EmployeeHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\EmployeeListsHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\EmployeeTypesHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\EmployeeGroupsHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\EmployeeGradesHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\EmployeeProfilesHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\CostCentersHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\JobTitlesHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\JobTitleListsHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\MinumumWagesHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\NotificationHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\NotifyModulesHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\NotifyDatesHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\PaymentTypeHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\PaymentTypesHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\PayrollSchemeHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\MonthsHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\PermissionHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\RoleHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\RolesHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\RoleKeysHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\SalariesHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\SalaryListsHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\ExchangeRatesHandler::class => ReflectionBasedAbstractFactory::class,                
                // Handler\SqlOrderHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\UserHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\YearsHandler::class => ReflectionBasedAbstractFactory::class,
                // Handler\WorkplaceHandler::class => ReflectionBasedAbstractFactory::class,

                // Models
                //
                Model\AllowanceModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $employeeCostAllowance = new TableGateway('employeeCostAllowanceSheetLabel', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $employeeCostAllowances = new TableGateway('employeeCostAllowances', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
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
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
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
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Model\DepartmentModel($departments, $columnFilters);
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
                Model\DisabilityModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    $disabilities = new TableGateway('disabilities', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\DisabilityModel($disabilities, $columnFilters);
                },
                Model\EmployeeModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $employees = new TableGateway('employees', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $employeeGroups = new TableGateway('employeeGroups', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $cacheStorage = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
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
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Model\EmployeeListModel(
                        $employeeList,
                        $cacheStorage,
                        $columnFilters
                    );
                },
                Model\EmployeeGradeModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $employeeGrades = new TableGateway('employeeGrades', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Model\EmployeeGradeModel($employeeGrades, $columnFilters);
                },
                Model\EmployeeProfileModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $employeeProfiles = new TableGateway('employeeProfiles', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Model\EmployeeProfileModel($employeeProfiles, $columnFilters);
                },
                Model\ExchangeRatesModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $exchangeRates = new TableGateway('exchangeRates', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Model\ExchangeRatesModel($exchangeRates, $columnFilters);
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
                Model\PaymentTypeModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $paymentTypes = new TableGateway('paymentTypes', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Model\PaymentTypeModel(
                        $paymentTypes,
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
                Model\PayrollSchemeModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    $payrollScheme = new TableGateway('payrollScheme', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\PayrollSchemeModel($payrollScheme, $columnFilters);
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
                Model\SalaryListModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $salaryList = new TableGateway('salaryList', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $cacheStorage = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Model\SalaryListModel(
                        $salaryList,
                        $cacheStorage,
                        $columnFilters
                    );
                },
                Model\SalaryModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    $salaries = new TableGateway('salaries', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\SalaryModel($salaries, $columnFilters);
                },
                Model\MinWageModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    $minWage = new TableGateway('minWage', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\MinWageModel($minWage, $columnFilters);
                },
                Model\NotificationModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    $notifications = new TableGateway('notifications', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $notificationUsers = new TableGateway('notificationUsers', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    return new Model\NotificationModel($notifications, $notificationUsers, $columnFilters);
                },
                Model\PermissionModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $cacheStorage = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
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
                Model\WorkplaceModel::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $workplaces = new TableGateway('workplaces', $dbAdapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
                    $cacheStorage = $container->get(StorageInterface::class);
                    $columnFilters = $container->get(ColumnFiltersInterface::class);
                    return new Model\WorkplaceModel($workplaces, $columnFilters, $cacheStorage);
                },

            ]
        ];
    }
}