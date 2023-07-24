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
 * $app->get('/', App\Handler\HomePageHandler::class, 'home');
 * $app->post('/album', App\Handler\AlbumCreateHandler::class, 'album.create');
 * $app->put('/album/:id', App\Handler\AlbumUpdateHandler::class, 'album.put');
 * $app->patch('/album/:id', App\Handler\AlbumUpdateHandler::class, 'album.patch');
 * $app->delete('/album/:id', App\Handler\AlbumDeleteHandler::class, 'album.delete');
 *
 * Or with multiple request methods:
 * 
 *
 * $app->route('/contact', App\Handler\ContactHandler::class, ['GET', 'POST', ...], 'contact');
 *
 * Or handling all request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class)->setName('contact');
 *
 * or:
 *
 * $app->route(
 *     '/contact',
 *     App\Handler\ContactHandler::class,
 *     Mezzio\Router\Route::HTTP_METHOD_ANY,
 *     'contact'
 * );
 */
return static function (Application $app, MiddlewareFactory $factory, ContainerInterface $container) : void {

    // Auth
    $app->route('/api/auth/token', App\Handler\Auth\TokenHandler::class, ['POST']);
    $app->route('/api/auth/refresh', [App\Handler\Auth\RefreshHandler::class], ['POST']);
    $app->route('/api/auth/logout', [App\Handler\Auth\LogoutHandler::class], ['GET']);
    $app->route('/api/auth/findAllPermissions', [App\Handler\Auth\FindAllPermissionsHandler::class], ['GET']);  
    
    $auth = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
    ];
    // Accunt
    $app->route('/api/account/findMe', [...$auth, ...[App\Handler\Account\FindMeHandler::class]], ['GET']);
    $app->route('/api/account/update', [...$auth, ...[App\Handler\Account\UpdateHandler::class]], ['PUT']);
    $app->route('/api/account/updatePassword', [...$auth, ...[App\Handler\Account\UpdatePasswordHandler::class]], ['PUT']);

    // Roles
    $app->route('/api/roles/create', [...$auth, ...[App\Handler\Roles\CreateHandler::class]], ['POST']);
    $app->route('/api/roles/update/:roleId', [...$auth, ...[App\Handler\Roles\UpdateHandler::class]], ['PUT']);
    $app->route('/api/roles/delete/:roleId', [...$auth, ...[App\Handler\Roles\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/roles/findAll', [App\Handler\Roles\FindAllHandler::class], ['GET']);
    $app->route('/api/roles/findAllByPaging', [...$auth, ...[App\Handler\Roles\FindAllByPagingHandler::class]], ['GET']);
    $app->route('/api/roles/findOneById/:roleId', [...$auth, ...[App\Handler\Roles\FindOneByIdHandler::class]], ['GET']);

    // Users
    $app->route('/api/users/create', [...$auth, [App\Handler\Users\CreateHandler::class]], ['POST']);
    $app->route('/api/users/update/:userId', [...$auth, [App\Handler\Users\UpdateHandler::class]], ['PUT']);
    $app->route('/api/users/delete/:userId', [...$auth, [App\Handler\Users\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/users/updatePassword/:userId', [...$auth, [App\Handler\Users\UpdatePasswordHandler::class]], ['PUT']);
    $app->route('/api/users/findAll', [...$auth, [App\Handler\Users\FindAllHandler::class]], ['GET']);
    $app->route('/api/users/findAllByPaging', [...$auth, [App\Handler\Users\FindAllByPagingHandler::class]], ['GET']);
    $app->route('/api/users/findOneById/:userId', [...$auth, [App\Handler\Users\FindOneByIdHandler::class]], ['GET']);

    // Permissions
    $app->route('/api/permissions/create', [...$auth, [App\Handler\Permissions\CreateHandler::class]], ['POST']);
    $app->route('/api/permissions/copy/:permId', [...$auth, [App\Handler\Permissions\CopyHandler::class]], ['POST']);
    $app->route('/api/permissions/update/:permId', [...$auth, [App\Handler\Permissions\UpdateHandler::class]], ['PUT']);
    $app->route('/api/permissions/delete/:permId', [...$auth, [App\Handler\Permissions\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/permissions/findAll', [JwtAuthenticationMiddleware::class, App\Handler\Permissions\FindAllHandler::class], ['GET']);
    $app->route('/api/permissions/findAllByPaging', [...$auth, [App\Handler\Permissions\FindAllByPagingHandler::class]], ['GET']);
    
    // Employee Grades
    $app->route('/api/employeegrades/create', [...$auth, [App\Handler\EmployeeGrades\CreateHandler::class]], ['POST']);
    $app->route('/api/employeegrades/update/:gradeId', [...$auth, [App\Handler\EmployeeGrades\UpdateHandler::class]], ['PUT']);
    $app->route('/api/employeegrades/delete/:gradeId', [...$auth, [App\Handler\EmployeeGrades\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/employeeGrades/findAll', [JwtAuthenticationMiddleware::class, App\Handler\EmployeeGrades\FindAllHandler::class], ['GET']);
    $app->route('/api/employeegrades/findAllByPaging', [...$auth, [App\Handler\EmployeeGrades\FindAllByPagingHandler::class]], ['GET']);

    // Companies
    $app->route('/api/companies/create', [...$auth, [App\Handler\Companies\CreateHandler::class]], ['POST']);
    $app->route('/api/companies/update/:companyId', [...$auth, [App\Handler\Companies\UpdateHandler::class]], ['PUT']);
    $app->route('/api/companies/delete/:companyId', [...$auth, [App\Handler\Companies\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/companies/findAll', [JwtAuthenticationMiddleware::class, App\Handler\Companies\FindAllHandler::class], ['GET']);
    $app->route('/api/companies/findAllByPaging', [...$auth, [App\Handler\Companies\FindAllByPagingHandler::class]], ['GET']);
    $app->route('/api/companies/findOneById/:companyId', [...$auth, [App\Handler\Companies\FindOneByIdHandler::class]], ['GET']);
    
    // JobTitles
    $app->route('/api/jobtitles/create', [...$auth, [App\Handler\JobTitles\CreateHandler::class]], ['POST']);
    $app->route('/api/jobtitles/update/:jobTitleId', [...$auth, [App\Handler\JobTitles\UpdateHandler::class]], ['PUT']);
    $app->route('/api/jobtitles/delete/:jobTitleId', [...$auth, [App\Handler\JobTitles\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/jobtitles/findAll', [JwtAuthenticationMiddleware::class, App\Handler\JobTitles\FindAllHandler::class], ['GET']);
    $app->route('/api/jobtitles/findAllByPaging', [...$auth, [App\Handler\JobTitles\FindAllByPagingHandler::class]], ['GET']);

    // Common and public resources *
    // 
    $app->route('/api/years/findAll', App\Handler\Common\Years\FindAllHandler::class, ['GET']);
    $app->route('/api/months/findAll', App\Handler\Common\Months\FindAllHandler::class, ['GET']);
    $app->route('/api/cities/findAll', App\Handler\Common\Cities\FindAllHandler::class, ['GET']);
    $app->route('/api/countries/findAll', App\Handler\Common\Countries\FindAllHandler::class, ['GET']);
    $app->route('/api/areaCodes/findAll', App\Handler\Common\AreaCode\FindAllHandler::class, ['GET']);

    $app->route('/api/files/findOne/:fileId', App\Handler\FileHandler::class, ['GET']);
    $app->route('/api/employeeTypes/findAll', App\Handler\EmployeeTypesHandler::class, ['GET']);
    $app->route('/api/employeeGroups/findAll', App\Handler\EmployeeGroupsHandler::class, ['GET']);
    $app->route('/api/employeeLists/findAll', App\Handler\EmployeeListsHandler::class, ['GET']);
    $app->route('/api/salarylists/downloadXls', App\Handler\SalaryListsHandler::class, ['GET']);
    $app->route('/api/paymenttypes/findAll', App\Handler\PaymentTypesHandler::class, ['GET']);
    $app->route('/api/costCenters/findAll', App\Handler\CostCentersHandler::class, ['GET']);
    $app->route('/api/jobTitles/findAll', App\Handler\JobTitlesHandler::class, ['GET']);
    $app->route('/api/disabilities/findAll', App\Handler\DisabilitiesHandler::class, ['GET']);
    $app->route('/api/sqlOrders/findAll', App\Handler\SqlOrderHandler::class, ['GET']);

    $customers = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\CustomerHandler::class
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
        App\Handler\DepartmentHandler::class
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
        App\Handler\WorkplaceHandler::class
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
        App\Handler\EmployeeHandler::class
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
        App\Handler\EmployeeListsHandler::class
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


    $employeeProfiles = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\EmployeeProfilesHandler::class
    ];
    $app->route('/api/employeeprofiles/create', $employeeProfiles, ['POST']);
    $app->route('/api/employeeprofiles/update/:profileId', $employeeProfiles, ['PUT']);
    $app->route('/api/employeeprofiles/delete/:profileId', $employeeProfiles, ['DELETE']);
    $app->route('/api/employeeprofiles/findAll', $employeeProfiles, ['GET']);
    $app->route('/api/employeeprofiles/findAllByPaging', $employeeProfiles, ['GET']);

    $jobTitleLists = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\JobTitleListsHandler::class
    ];
    //
    // xlsx import functions
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
        App\Handler\MinumumWagesHandler::class
    ];
    $app->route('/api/minumumwages/create', $minumumWages, ['POST']);
    $app->route('/api/minumumwages/update/:wageId', $minumumWages, ['PUT']);
    $app->route('/api/minumumwages/delete/:wageId', $minumumWages, ['DELETE']);
    $app->route('/api/minumumwages/findAllByPaging', $minumumWages, ['GET']);

    $disabilities = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\DisabilitiesHandler::class
    ];
    $app->route('/api/disabilities/create', $disabilities, ['POST']);
    $app->route('/api/disabilities/update/:disabilityId', $disabilities, ['PUT']);
    $app->route('/api/disabilities/delete/:disabilityId', $disabilities, ['DELETE']);
    $app->route('/api/disabilities/findAllByPaging', $disabilities, ['GET']);

    $salaryLists = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\SalaryListsHandler::class
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
        App\Handler\SalariesHandler::class
    ];
    $app->route('/api/salaries/create', $salaries, ['POST']);
    $app->route('/api/salaries/update/:salaryId', $salaries, ['PUT']);
    $app->route('/api/salaries/delete/:salaryId', $salaries, ['DELETE']);
    $app->route('/api/salaries/findAllByPaging', $salaries, ['GET']);

    $payrollSchemes = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Handler\PayrollSchemeHandler::class
    ];
    $app->route('/api/payrollschemes/create', $payrollSchemes, ['POST']);
    $app->route('/api/payrollschemes/update/:schemeId', $payrollSchemes, ['PUT']);
    $app->route('/api/payrollschemes/delete/:schemeId', $payrollSchemes, ['DELETE']);
    $app->route('/api/payrollschemes/findAllByPaging', $payrollSchemes, ['GET']);

};
