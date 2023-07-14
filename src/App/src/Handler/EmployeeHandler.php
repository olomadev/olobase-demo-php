<?php

declare(strict_types=1);

namespace App\Handler\Api;

use function paginatorJsonDecode;

use App\Utils\Error;
use App\Utils\DataManager;
use App\ViewModel\EmployeeFindOneByIdVM;
use App\Model\EmployeeModel;
use App\Entity\EmployeesEntity;
use App\Entity\EmployeeEducationEntity;
use App\Entity\EmployeePersonalEntity;
use App\Schema\EmployeeSave;
use App\Filter\EmployeeSaveFilter;
use Mezzio\Authentication\UserInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class EmployeeHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        InputFilterPluginManager $filter,
        EmployeeModel $employeeModel,
        DataManager $dataManager,
        Error $error
    )
    {
        $this->filter = $filter;
        $this->translator = $translator;
        $this->employeeModel = $employeeModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
    }

    /**
     * @OA\Get(
     *   path="/employees/findAll",
     *   tags={"Employees"},
     *   summary="Find all employees",
     *   operationId="employees_findAll",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/CommonFindAllResultVM"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function onGetFindAll(array $get)
    {
        $data = $this->employeeModel->findOptionsById($get);
        if ($data === false) {
            return new JsonResponse(
                [
                    'data' => [],
                    'error' => $this->translator->translate('Please first choose at least one employee list'),
                ],
            );    
        }
        return new JsonResponse([
            'data' => $data,
        ]);
    }

    /**
     * @OA\Get(
     *   path="/employees/findAllBySearch",
     *   tags={"Employees"},
     *   summary="Search for all employees",
     *   operationId="employees_findAllBySearch",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/EmployeeFindAllBySearchResultVM"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function onGetFindAllBySearch(array $get)
    {
        $data = $this->employeeModel->findAllBySearch($get);
        return new JsonResponse([
            'data' => $data,
        ]);
    }

    /**
     * @OA\Get(
     *   path="/employees/findAllByPaging",
     *   tags={"Employees"},
     *   summary="Find all employees by pagination",
     *   operationId="employees_findAllByPaging",
     *
     *   @OA\Parameter(
     *       name="q",
     *       in="query",
     *       required=false,
     *       description="Search string",
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Parameter(
     *       name="_page",
     *       in="query",
     *       required=false,
     *       description="Page number",
     *       @OA\Schema(
     *           type="integer",
     *       ),
     *   ),
     *   @OA\Parameter(
     *       name="_perPage",
     *       in="query",
     *       required=false,
     *       description="Per page",
     *       @OA\Schema(
     *           type="integer",
     *       ),
     *   ),
     *   @OA\Parameter(
     *       name="_sort",
     *       in="query",
     *       required=false,
     *       description="Order items",
     *       @OA\Schema(
     *           type="array",
     *           @OA\Items()
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/EmployeeFindAllByPageResultVM"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function onGetFindAllByPaging(array $get)
    {
        $page = empty($get['_page']) ? 1 : (int)$get['_page'];
        $perPage = empty($get['_perPage']) ? 5 : (int)$get['_perPage'];

        // queries:
        // q=ersin+güvenç&EmployeeshortName=test&_sort=taxOffice&_order=desc&_sort=taxNumber&_order=asc

        // https://docs.laminas.dev/tutorials/pagination/
        $paginator = $this->employeeModel->findAllByPaging($get);

        $page = ($page < 1) ? 1 : $page;
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);

        return new JsonResponse([
            'page' => $paginator->getCurrentPageNumber(),
            'perPage' => $paginator->getItemCountPerPage(),
            'totalPages' => $paginator->count(),
            'totalItems' => $paginator->getTotalItemCount(),
            'data' => paginatorJsonDecode($paginator->getCurrentItems()),
        ]);
    }

    /**
     * @OA\Get(
     *   path="/employees/findOneById/{employeeId}",
     *   tags={"Employees"},
     *   summary="Find employee data",
     *   operationId="employees_findOneById",
     *
     *   @OA\Parameter(
     *       name="employeeId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/EmployeeFindOneByIdResultVM"),
     *   ),
     *)
     **/
    public function onGetFindOneById(string $employeeId)
    {
        $row = $this->employeeModel->findOneById($employeeId);
        if ($row) {
            $viewModel = new EmployeeFindOneByIdVM($row);
            return new JsonResponse(['data' => $viewModel->getData()]);            
        }
        return new JsonResponse([], 404);
    }
    
    /**
     * @OA\Post(
     *   path="/employees/create",
     *   tags={"Employees"},
     *   summary="Create a new employee",
     *   operationId="employees_create",
     *
     *   @OA\RequestBody(
     *     description="Create new employee",
     *     @OA\JsonContent(ref="#/components/schemas/EmployeeSave"),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad request, returns to validation errors"
     *   )
     *)
     **/
    public function onPostCreate(array $post)
    {
        $inputFilter = $this->filter->get(EmployeeSaveFilter::class);
        $inputFilter->setInputData($post);
        $user = $this->request->getAttribute(UserInterface::class);
        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                EmployeeSave::class,
                [
                    'employees' => EmployeesEntity::class,
                ]
            );
            $data['employeeId'] = $inputFilter->getValue('id');
            $data['employeeGroups'] = $inputFilter->getValue('groups');
            $this->employeeModel->create($data, $user);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response);            
    }

    /**
     * @OA\Put(
     *   path="/employees/update/{employeeId}",
     *   tags={"Employees"},
     *   summary="Update employee",
     *   operationId="employees_update",
     *
     *   @OA\Parameter(
     *       name="employeeId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update employee",
     *     @OA\JsonContent(ref="#/components/schemas/EmployeeSave"),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad request, returns to validation errors"
     *   )
     *)
     **/
    public function onPutUpdate(array $post, string $employeeId)
    {   
        $inputFilter = $this->filter->get(EmployeeSaveFilter::class);
        $post['id'] = $employeeId;
        $inputFilter->setInputData($post);
        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                EmployeeSave::class,
                [
                    'employees' => EmployeesEntity::class,
                ]
            );
            $data['employeeId'] = $inputFilter->getValue('id');
            $data['employeeGroups'] = $inputFilter->getValue('groups');
            $this->employeeModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response); 
    }

    /**
     * @OA\Delete(
     *   path="/employees/delete/{employeeId}",
     *   tags={"Employees"},
     *   summary="Delete employee",
     *   operationId="employees_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="employeeId",
     *       required=true,
     *       description="Employee uuid",
     *       @OA\Schema(
     *           type="string",
     *           format="uuid",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *   )
     *)
     **/
    public function onDeleteDelete(string $employeeId)
    {
        $this->employeeModel->delete($employeeId);
        return new JsonResponse([]);
    }
    

}
