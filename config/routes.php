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

    // Auth (public)
    $app->route('/api/auth/token', App\Handler\Auth\TokenHandler::class, ['POST']);
    $app->route('/api/auth/refresh', [App\Handler\Auth\RefreshHandler::class], ['POST']);
    $app->route('/api/auth/logout', [App\Handler\Auth\LogoutHandler::class], ['GET']);
    $app->route('/api/auth/session', [JwtAuthenticationMiddleware::class, App\Handler\Auth\SessionUpdateHandler::class], ['POST']);
    $app->route('/api/auth/resetPassword', [App\Handler\Auth\ResetPasswordHandler::class], ['POST']);
    $app->route('/api/auth/checkResetCode', [App\Handler\Auth\CheckResetCodeHandler::class], ['GET']);
    $app->route('/api/auth/changePassword', [App\Handler\Auth\ChangePasswordHandler::class], ['POST']);
    
    $auth = [
        JwtAuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
    ];
    // Account (private)
    $app->route('/api/account/findMe', [...$auth, ...[App\Handler\Account\FindMeHandler::class]], ['GET']);
    $app->route('/api/account/update', [...$auth, ...[App\Handler\Account\UpdateHandler::class]], ['PUT']);
    $app->route('/api/account/updatePassword', [...$auth, ...[App\Handler\Account\UpdatePasswordHandler::class]], ['PUT']);

    // Roles (private)
    $app->route('/api/roles/create', [...$auth, ...[App\Handler\Roles\CreateHandler::class]], ['POST']);
    $app->route('/api/roles/update/:roleId', [...$auth, ...[App\Handler\Roles\UpdateHandler::class]], ['PUT']);
    $app->route('/api/roles/delete/:roleId', [...$auth, ...[App\Handler\Roles\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/roles/findAll', [App\Handler\Roles\FindAllHandler::class], ['GET']);
    $app->route('/api/roles/findAllByPaging', [...$auth, ...[App\Handler\Roles\FindAllByPagingHandler::class]], ['GET']);
    $app->route('/api/roles/findOneById/:roleId', [...$auth, ...[App\Handler\Roles\FindOneByIdHandler::class]], ['GET']);

    // Users (private)
    $app->route('/api/users/create', [...$auth, [App\Handler\Users\CreateHandler::class]], ['POST']);
    $app->route('/api/users/update/:userId', [...$auth, [App\Handler\Users\UpdateHandler::class]], ['PUT']);
    $app->route('/api/users/delete/:userId', [...$auth, [App\Handler\Users\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/users/updatePassword/:userId', [...$auth, [App\Handler\Users\UpdatePasswordHandler::class]], ['PUT']);
    $app->route('/api/users/findAll', [...$auth, [App\Handler\Users\FindAllHandler::class]], ['GET']);
    $app->route('/api/users/findAllByPaging', [...$auth, [App\Handler\Users\FindAllByPagingHandler::class]], ['GET']);
    $app->route('/api/users/findOneById/:userId', [...$auth, [App\Handler\Users\FindOneByIdHandler::class]], ['GET']);

    // Avatars (private)
    $app->route('/api/avatars/findOneById/:userId', [...$auth, [App\Handler\Users\FindOneByIdHandler::class]], ['GET']);
    $app->route('/api/avatars/update/:userId', [...$auth, [App\Handler\Users\UpdateHandler::class]], ['PUT']);

    // Permissions (private)
    $app->route('/api/permissions/create', [...$auth, [App\Handler\Permissions\CreateHandler::class]], ['POST']);
    $app->route('/api/permissions/copy/:permId', [...$auth, [App\Handler\Permissions\CopyHandler::class]], ['POST']);
    $app->route('/api/permissions/update/:permId', [...$auth, [App\Handler\Permissions\UpdateHandler::class]], ['PUT']);
    $app->route('/api/permissions/delete/:permId', [...$auth, [App\Handler\Permissions\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/permissions/findAll', [JwtAuthenticationMiddleware::class, App\Handler\Permissions\FindAllHandler::class], ['GET']);
    $app->route('/api/permissions/findAllByPaging', [...$auth, [App\Handler\Permissions\FindAllByPagingHandler::class]], ['GET']);

    // Companies (private)
    $app->route('/api/companies/create', [...$auth, [App\Handler\Companies\CreateHandler::class]], ['POST']);
    $app->route('/api/companies/update/:companyId', [...$auth, [App\Handler\Companies\UpdateHandler::class]], ['PUT']);
    $app->route('/api/companies/delete/:companyId', [...$auth, [App\Handler\Companies\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/companies/findAll', [JwtAuthenticationMiddleware::class, App\Handler\Companies\FindAllHandler::class], ['GET']);
    $app->route('/api/companies/findAllByPaging', [...$auth, [App\Handler\Companies\FindAllByPagingHandler::class]], ['GET']);
    $app->route('/api/companies/findOneById/:companyId', [...$auth, [App\Handler\Companies\FindOneByIdHandler::class]], ['GET']);
    
    // Employees (private)
    $app->route('/api/employees/create', [...$auth, [App\Handler\Employees\CreateHandler::class]], ['POST']);
    $app->route('/api/employees/update/:employeeId', [...$auth, [App\Handler\Employees\UpdateHandler::class]], ['PUT']);
    $app->route('/api/employees/delete/:employeeId', [...$auth, [App\Handler\Employees\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/employees/findAll', [...$auth, [App\Handler\Employees\FindAllHandler::class]], ['GET']);
    $app->route('/api/employees/findAllByPaging', [...$auth, [App\Handler\Employees\FindAllByPagingHandler::class]], ['GET']);
    $app->route('/api/employees/findOneById/:employeeId', [...$auth, [App\Handler\Employees\FindOneByIdHandler::class]], ['GET']);

    // Employee Grades (private)
    $app->route('/api/employee-grades/create', [...$auth, [App\Handler\EmployeeGrades\CreateHandler::class]], ['POST']);
    $app->route('/api/employee-grades/update/:gradeId', [...$auth, [App\Handler\EmployeeGrades\UpdateHandler::class]], ['PUT']);
    $app->route('/api/employee-grades/delete/:gradeId', [...$auth, [App\Handler\EmployeeGrades\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/employee-grades/findAll', [JwtAuthenticationMiddleware::class, App\Handler\EmployeeGrades\FindAllHandler::class], ['GET']);
    $app->route('/api/employee-grades/findAllByPaging', [...$auth, [App\Handler\EmployeeGrades\FindAllByPagingHandler::class]], ['GET']);
    
    // Departments (private)
    $app->route('/api/departments/create', [...$auth, [App\Handler\Departments\CreateHandler::class]], ['POST']);
    $app->route('/api/departments/update/:departmentId', [...$auth, [App\Handler\Departments\UpdateHandler::class]], ['PUT']);
    $app->route('/api/departments/delete/:departmentId', [...$auth, [App\Handler\Departments\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/departments/findAll', [JwtAuthenticationMiddleware::class, App\Handler\Departments\FindAllHandler::class], ['GET']);
    $app->route('/api/departments/findAllByPaging', [...$auth, [App\Handler\Departments\FindAllByPagingHandler::class]], ['GET']);

    // JobTitles (private)
    $app->route('/api/jobtitles/create', [...$auth, [App\Handler\JobTitles\CreateHandler::class]], ['POST']);
    $app->route('/api/jobtitles/update/:jobTitleId', [...$auth, [App\Handler\JobTitles\UpdateHandler::class]], ['PUT']);
    $app->route('/api/jobtitles/delete/:jobTitleId', [...$auth, [App\Handler\JobTitles\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/jobtitles/findAll', [JwtAuthenticationMiddleware::class, App\Handler\JobTitles\FindAllHandler::class], ['GET']);
    $app->route('/api/jobtitles/findAllByPaging', [...$auth, [App\Handler\JobTitles\FindAllByPagingHandler::class]], ['GET']);

    // JobTitleLists - (xlsx import functions) (private)
    // 
    $app->route('/api/jobtitlelists/upload', [...$auth, [App\Handler\JobTitleLists\UploadHandler::class]], ['POST']);
    $app->route('/api/jobtitlelists/preview', [...$auth, [App\Handler\JobTitleLists\PreviewHandler::class]], ['GET']);
    $app->route('/api/jobtitlelists/import', [...$auth, [App\Handler\JobTitleLists\ImportHandler::class]], ['POST']);
    $app->route('/api/jobtitlelists/reset', [...$auth, [App\Handler\JobTitleLists\ResetHandler::class]], ['DELETE']);
    $app->route('/api/jobtitlelists/remove', [...$auth, [App\Handler\JobTitleLists\RemoveHandler::class]], ['DELETE']);
    //
    // JobTitleLists - (standart api functions) (private)
    //
    $app->route('/api/jobtitlelists/update/:listId', [...$auth, [App\Handler\JobTitleLists\UpdateHandler::class]], ['PUT']);
    $app->route('/api/jobtitlelists/delete/:listId', [...$auth, [App\Handler\JobTitleLists\DeleteHandler::class]], ['DELETE']);
    $app->route('/api/jobtitlelists/findAll', [...$auth, [App\Handler\JobTitleLists\FindAllHandler::class]], ['GET']);
    $app->route('/api/jobtitlelists/findAllByPaging', [...$auth, [App\Handler\JobTitleLists\FindAllByPagingHandler::class]], ['GET']);

    // FailedLogins (private)
    $app->route('/api/failedlogins/findAllByPaging', [...$auth, [App\Handler\FailedLogins\FindAllByPagingHandler::class]], ['GET']);
    $app->route('/api/failedloginips/findAll', [...$auth, [App\Handler\FailedLogins\FindAllIpAdressesHandler::class]], ['GET']);
    $app->route('/api/failedloginusernames/findAll', [...$auth, [App\Handler\FailedLogins\FindAllUsernamesHandler::class]], ['GET']);

    // Common (public)
    // 
    $app->route('/api/stream/events', App\Handler\Common\Stream\EventsHandler::class, ['GET']);
    $app->route('/api/locales/findAll', App\Handler\Common\Locales\FindAllHandler::class, ['GET']);
    $app->route('/api/years/findAll', App\Handler\Common\Years\FindAllHandler::class, ['GET']);
    $app->route('/api/months/findAll', App\Handler\Common\Months\FindAllHandler::class, ['GET']);
    $app->route('/api/cities/findAll', App\Handler\Common\Cities\FindAllHandler::class, ['GET']);
    $app->route('/api/countries/findAll', App\Handler\Common\Countries\FindAllHandler::class, ['GET']);
    $app->route('/api/currencies/findAll', App\Handler\Common\Currencies\FindAllHandler::class, ['GET']);
    $app->route('/api/areacodes/findAll', App\Handler\Common\AreaCodes\FindAllHandler::class, ['GET']);
    $app->route('/api/files/findOneById/:fileId', App\Handler\Common\Files\FindOneByIdHandler::class, ['GET']);
    $app->route('/api/files/readOneById/:fileId', App\Handler\Common\Files\ReadOneByIdHandler::class, ['GET']);

};
