<?php

declare(strict_types=1);

use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use App\Middleware\JwtAuthenticationMiddleware;
use App\Middleware\JwtAuthenticationDocumentMiddleware;
use Psr\Container\ContainerInterface;
/**
 * Setup routes with a single request method:
 *
 * $app->get('/', App\Handler\Api\HomePageHandler::class, 'home');
 * $app->post('/album', App\Handler\Api\AlbumCreateHandler::class, 'album.create');
 * $app->put('/album/:id', App\Handler\Api\AlbumUpdateHandler::class, 'album.put');
 * $app->patch('/album/:id', App\Handler\Api\AlbumUpdateHandler::class, 'album.patch');
 * $app->delete('/album/:id', App\Handler\Api\AlbumDeleteHandler::class, 'album.delete');
 *
 * Or with multiple request methods:
 * 
 *
 * $app->route('/contact', App\Handler\Api\ContactHandler::class, ['GET', 'POST', ...], 'contact');
 *
 * Or handling all request methods:
 *
 * $app->route('/contact', App\Handler\Api\ContactHandler::class)->setName('contact');
 *
 * or:
 *
 * $app->route(
 *     '/contact',
 *     App\Handler\Api\ContactHandler::class,
 *     Mezzio\Router\Route::HTTP_METHOD_ANY,
 *     'contact'
 * );
 */
return static function (Application $app, MiddlewareFactory $factory, ContainerInterface $container) : void {

	// public
    $app->route('/api/auth/findAllPermissions', [App\Handler\Api\AuthHandler::class], ['GET']);  
    $app->route('/api/auth/findResources', [App\Handler\Api\AuthHandler::class], ['GET']);  
	$app->route('/api/auth/token', App\Handler\Api\AuthHandler::class, ['POST']);
    $app->route('/api/auth/sendResetPassword', App\Handler\Api\AuthHandler::class, ['POST']);
    $app->route('/api/auth/resetPassword', App\Handler\Api\AuthHandler::class, ['POST']);
	$app->route('/api/auth/refresh', [App\Handler\Api\AuthHandler::class], ['POST']);
	$app->route('/api/auth/changePassword', [JwtAuthenticationMiddleware::class, App\Handler\Api\AuthHandler::class], ['PUT']);
	$app->route('/api/auth/logout', [App\Handler\Api\AuthHandler::class], ['GET']);

    $account = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\Api\AccountHandler::class
    ];
    $app->route('/api/account/findMe', $account, ['GET']);
    $app->route('/api/account/update', $account, ['PUT']);
    $app->route('/api/account/updatePassword', $account, ['PUT']);

    $roles = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\Api\RoleHandler::class
    ];
    $app->route('/api/roles/create', $roles, ['POST']);
    $app->route('/api/roles/update/:roleId', $roles, ['PUT']);
    $app->route('/api/roles/delete/:roleId', $roles, ['DELETE']);
    $app->route('/api/roles/findAllByPaging', $roles, ['GET']);
    $app->route('/api/roles/findOneById/:roleId', $roles, ['GET']);

    $users = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\Api\UserHandler::class
    ];
    $app->route('/api/users/create', $users, ['POST']);
    $app->route('/api/users/update/:userId', $users, ['PUT']);
    $app->route('/api/users/delete/:userId', $users, ['DELETE']);
    $app->route('/api/users/updatePassword/:userId', $users, ['PUT']);
    $app->route('/api/users/setAvatar', $users, ['POST']);
    $app->route('/api/users/findAll', $users, ['GET']);
    $app->route('/api/users/findAllByPaging', $users, ['GET']);
    $app->route('/api/users/findOneById/:userId', $users, ['GET']);

    $permissions = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\Api\PermissionHandler::class
    ];
    $app->route('/api/permissions/create', $permissions, ['POST']);
    $app->route('/api/permissions/create/:permId', $permissions, ['POST']);
    $app->route('/api/permissions/update/:permId', $permissions, ['PUT']);
    $app->route('/api/permissions/delete/:permId', $permissions, ['DELETE']);
    $app->route('/api/permissions/findAllByPaging', $permissions, ['GET']);
    $app->route('/api/permissions/findOneById', $permissions, ['GET']);
    $permissions = [
        JwtAuthenticationMiddleware::class,
        App\Handler\Api\PermissionHandler::class
    ];
    $app->route('/api/permissions/findAll', $permissions, ['GET']);

    $companies = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\Api\CompanyHandler::class
    ];
    $app->route('/api/companies/create', $companies, ['POST']);
    $app->route('/api/companies/update/:companyId', $companies, ['PUT']);
    $app->route('/api/companies/delete/:companyId', $companies, ['DELETE']);
    $app->route('/api/companies/findAll', $companies, ['GET']);
    $app->route('/api/companies/findAllByPaging',$companies, ['GET']);
    $app->route('/api/companies/findOneById/:companyId', $companies, ['GET']);

    $exchangeRates = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\Api\ExchangeRatesHandler::class
    ];
    $app->route('/api/exchangeRates/findOne', $exchangeRates, ['GET']);
    $app->route('/api/exchangeRates/findAllByPaging', $exchangeRates, ['GET']);
    $app->route('/api/exchangeRates/findWeeklyChart', $exchangeRates, ['GET']);
    $app->route('/api/exchangeRates/create', $exchangeRates, ['POST']);

    // Common and public resources *
    // 
    $app->route('/api/years/findAll', App\Handler\Api\YearsHandler::class, ['GET']);
    $app->route('/api/notifyDates/findAll', App\Handler\Api\NotifyDatesHandler::class, ['GET']);
    $app->route('/api/notifyModules/findAll', App\Handler\Api\NotifyModulesHandler::class, ['GET']);
    $app->route('/api/files/findOne/:fileId', App\Handler\Api\FileHandler::class, ['GET']);
    $app->route('/api/areaCodes/findAll', App\Handler\Api\AreaCodesHandler::class, ['GET']);
    $app->route('/api/roles/findAll', App\Handler\Api\RolesHandler::class, ['GET']);
    $app->route('/api/months/findAll', App\Handler\Api\MonthsHandler::class, ['GET']);
    $app->route('/api/countries/findAll', App\Handler\Api\CountriesHandler::class, ['GET']);
    $app->route('/api/cities/findAll', App\Handler\Api\CitiesHandler::class, ['GET']);
    $app->route('/api/employeeTypes/findAll', App\Handler\Api\EmployeeTypesHandler::class, ['GET']);
    $app->route('/api/employeeGrades/findAll', App\Handler\Api\EmployeeGradesHandler::class, ['GET']);
    $app->route('/api/employeeGroups/findAll', App\Handler\Api\EmployeeGroupsHandler::class, ['GET']);
    $app->route('/api/employeeLists/findAll', App\Handler\Api\EmployeeListsHandler::class, ['GET']);
    $app->route('/api/salarylists/downloadXls', App\Handler\Api\SalaryListsHandler::class, ['GET']);
    $app->route('/api/paymenttypes/findAll', App\Handler\Api\PaymentTypesHandler::class, ['GET']);
    $app->route('/api/costCenters/findAll', App\Handler\Api\CostCentersHandler::class, ['GET']);
    $app->route('/api/jobTitles/findAll', App\Handler\Api\JobTitlesHandler::class, ['GET']);
    $app->route('/api/disabilities/findAll', App\Handler\Api\DisabilitiesHandler::class, ['GET']);
    $app->route('/api/sqlOrders/findAll', App\Handler\Api\SqlOrderHandler::class, ['GET']);

    $jobTitles = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\Api\JobTitlesHandler::class
    ];
    $app->route('/api/jobtitles/create', $jobTitles, ['POST']);
    $app->route('/api/jobtitles/update/:jobTitleId', $jobTitles, ['PUT']);
    $app->route('/api/jobtitles/delete/:jobTitleId', $jobTitles, ['DELETE']);
    $app->route('/api/jobtitles/findAllByPaging', $jobTitles, ['GET']);
    $app->route('/api/jobtitles/findOneById/:jobTitleId', $jobTitles, ['GET']);

    $paymentTypes = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\Api\PaymentTypeHandler::class
    ];
    $app->route('/api/paymenttypes/create', $paymentTypes, ['POST']);
    $app->route('/api/paymenttypes/update/:paymentTypeId', $paymentTypes, ['PUT']);
    $app->route('/api/paymenttypes/delete/:paymentTypeId', $paymentTypes, ['DELETE']);
    $app->route('/api/paymenttypes/findAllByPaging', $paymentTypes, ['GET']);
    $app->route('/api/paymenttypes/findOneById/:paymentTypeId', $paymentTypes, ['GET']);

    $customers = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\Api\CustomerHandler::class
    ];
    $app->route('/api/customers/create', $customers, ['POST']);
    $app->route('/api/customers/update/:customerId', $customers, ['PUT']);
    $app->route('/api/customers/delete/:customerId', $customers, ['DELETE']);
    $app->route('/api/customers/findAll', $customers, ['GET']);
    $app->route('/api/customers/findAllByPaging', $customers, ['GET']);
    $app->route('/api/customers/findOneById/:customerId', $customers, ['GET']);

    $departments = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\Api\DepartmentHandler::class
    ];
    $app->route('/api/departments/create', $departments, ['POST']);
    $app->route('/api/departments/update/:departmentId', $departments, ['PUT']);
    $app->route('/api/departments/delete/:departmentId', $departments, ['DELETE']);
    $app->route('/api/departments/findAll', $departments, ['GET']);
    $app->route('/api/departments/findAllByPaging', $departments, ['GET']);
    $app->route('/api/departments/findOneById/:departmentId', $departments, ['GET']);

    $workplaces = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\Api\WorkplaceHandler::class
    ];
    $app->route('/api/workplaces/create', $workplaces, ['POST']);
    $app->route('/api/workplaces/update/:workplaceId', $workplaces, ['PUT']);
    $app->route('/api/workplaces/delete/:workplaceId', $workplaces, ['DELETE']);
    $app->route('/api/workplaces/findAll', $workplaces, ['GET']);
    $app->route('/api/workplaces/findAllByPaging', $workplaces, ['GET']);
    $app->route('/api/workplaces/findOneById/:workplaceId', $workplaces, ['GET']);

    $employees = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\Api\EmployeeHandler::class
    ];
    $app->route('/api/employees/create', $employees, ['POST']);
    $app->route('/api/employees/update/:employeeId', $employees, ['PUT']);
    $app->route('/api/employees/delete/:employeeId', $employees, ['DELETE']);
    $app->route('/api/employees/findAll', $employees, ['GET']);
    $app->route('/api/employees/findAllBySearch', $employees, ['GET']);
    $app->route('/api/employees/findAllByPaging', $employees, ['GET']);
    $app->route('/api/employees/findOneById/:employeeId', $employees, ['GET']);

    $employeeLists = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\Api\EmployeeListsHandler::class
    ];
    //
    // xls import functions
    // 
    $app->route('/api/employeelists/upload', $employeeLists, ['POST']);
    $app->route('/api/employeelists/previewResults', $employeeLists, ['GET']);
    $app->route('/api/employeelists/import', $employeeLists, ['POST']);
    $app->route('/api/employeelists/importStatus', $employeeLists, ['GET']);
    $app->route('/api/employeelists/reset', $employeeLists, ['DELETE']);
    $app->route('/api/employeelists/remove', $employeeLists, ['DELETE']);
    //
    // standart api functions
    //
    $app->route('/api/employeelists/update/:listId', $employeeLists, ['PUT']);
    $app->route('/api/employeelists/delete/:listId', $employeeLists, ['DELETE']);
    $app->route('/api/employeelists/findAll', $employeeLists, ['GET']);
    $app->route('/api/employeelists/findAllByPaging', $employeeLists, ['GET']);

    $employeeGrades = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\Api\EmployeeGradesHandler::class
    ];
    $app->route('/api/employeegrades/create', $employeeGrades, ['POST']);
    $app->route('/api/employeegrades/update/:gradeId', $employeeGrades, ['PUT']);
    $app->route('/api/employeegrades/delete/:gradeId', $employeeGrades, ['DELETE']);
    $app->route('/api/employeegrades/findAll', $employeeGrades, ['GET']);
    $app->route('/api/employeegrades/findAllByPaging', $employeeGrades, ['GET']);

    $employeeProfiles = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\Api\EmployeeProfilesHandler::class
    ];
    $app->route('/api/employeeprofiles/create', $employeeProfiles, ['POST']);
    $app->route('/api/employeeprofiles/update/:profileId', $employeeProfiles, ['PUT']);
    $app->route('/api/employeeprofiles/delete/:profileId', $employeeProfiles, ['DELETE']);
    $app->route('/api/employeeprofiles/findAll', $employeeProfiles, ['GET']);
    $app->route('/api/employeeprofiles/findAllByPaging', $employeeProfiles, ['GET']);

    $jobTitleLists = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\Api\JobTitleListsHandler::class
    ];
    //
    // xls import functions
    // 
    $app->route('/api/jobtitlelists/upload', $jobTitleLists, ['POST']);
    $app->route('/api/jobtitlelists/previewResults', $jobTitleLists, ['GET']);
    $app->route('/api/jobtitlelists/import', $jobTitleLists, ['POST']);
    $app->route('/api/jobtitlelists/importStatus', $jobTitleLists, ['GET']);
    $app->route('/api/jobtitlelists/reset', $jobTitleLists, ['DELETE']);
    $app->route('/api/jobtitlelists/remove', $jobTitleLists, ['DELETE']);
    //
    // standart api functions
    //
    $app->route('/api/jobtitlelists/update/:listId', $jobTitleLists, ['PUT']);
    $app->route('/api/jobtitlelists/delete/:listId', $jobTitleLists, ['DELETE']);
    $app->route('/api/jobtitlelists/findAll', $jobTitleLists, ['GET']);
    $app->route('/api/jobtitlelists/findAllByPaging', $jobTitleLists, ['GET']);

    $minumumWages = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\Api\MinumumWagesHandler::class
    ];
    $app->route('/api/minumumwages/create', $minumumWages, ['POST']);
    $app->route('/api/minumumwages/update/:wageId', $minumumWages, ['PUT']);
    $app->route('/api/minumumwages/delete/:wageId', $minumumWages, ['DELETE']);
    $app->route('/api/minumumwages/findAllByPaging', $minumumWages, ['GET']);

    $disabilities = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\Api\DisabilitiesHandler::class
    ];
    $app->route('/api/disabilities/create', $disabilities, ['POST']);
    $app->route('/api/disabilities/update/:disabilityId', $disabilities, ['PUT']);
    $app->route('/api/disabilities/delete/:disabilityId', $disabilities, ['DELETE']);
    $app->route('/api/disabilities/findAllByPaging', $disabilities, ['GET']);

    $salaryLists = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\Api\SalaryListsHandler::class
    ];
    //
    // xls import functions
    // 
    $app->route('/api/salarylists/upload', $salaryLists, ['POST']);
    $app->route('/api/salarylists/previewResults', $salaryLists, ['GET']);
    $app->route('/api/salarylists/import', $salaryLists, ['POST']);
    $app->route('/api/salarylists/importStatus', $salaryLists, ['GET']);
    $app->route('/api/salarylists/reset', $salaryLists, ['DELETE']);
    $app->route('/api/salarylists/remove', $salaryLists, ['DELETE']);
    //
    // standart api functions
    //
    $app->route('/api/salarylists/update/:listId', $salaryLists, ['PUT']);
    $app->route('/api/salarylists/delete/:listId', $salaryLists, ['DELETE']);
    $app->route('/api/salarylists/findAll', $salaryLists, ['GET']);
    $app->route('/api/salarylists/findAllByPaging', $salaryLists, ['GET']);
    
    $salaries = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\Api\SalariesHandler::class
    ];
    $app->route('/api/salaries/create', $salaries, ['POST']);
    $app->route('/api/salaries/update/:salaryId', $salaries, ['PUT']);
    $app->route('/api/salaries/delete/:salaryId', $salaries, ['DELETE']);
    $app->route('/api/salaries/findAllByPaging', $salaries, ['GET']);

    $payrollSchemes = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\Api\PayrollSchemeHandler::class
    ];
    $app->route('/api/payrollschemes/create', $payrollSchemes, ['POST']);
    $app->route('/api/payrollschemes/update/:schemeId', $payrollSchemes, ['PUT']);
    $app->route('/api/payrollschemes/delete/:schemeId', $payrollSchemes, ['DELETE']);
    $app->route('/api/payrollschemes/findAllByPaging', $payrollSchemes, ['GET']);

};
