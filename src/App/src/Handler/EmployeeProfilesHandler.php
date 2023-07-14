<?php

declare(strict_types=1);

namespace App\Handler\Api;

use App\Utils\Error;
use App\Utils\DataManager;
use App\Model\CommonModel;
use App\Model\EmployeeProfileModel;
use App\Schema\EmployeeProfileSave;
use App\Entity\EmployeeProfilesEntity;
use App\Filter\EmployeeProfileSaveFilter;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class EmployeeProfilesHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        InputFilterPluginManager $filter,
        EmployeeProfileModel $employeeProfileModel,
        CommonModel $commonModel,
        DataManager $dataManager,
        Error $error
    )
    {
        $this->filter = $filter;
        $this->translator = $translator;
        $this->commonModel = $commonModel;
        $this->dataManager = $dataManager;
        $this->employeeProfileModel = $employeeProfileModel;
        $this->error = $error;
    }

    /**
     * @OA\Get(
     *   path="/employeeprofiles/findAll",
     *   tags={"Common"},
     *   summary="Find all employee profiles",
     *   operationId="employeeProfiles_findAll",
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
        $data = $this->commonModel->findEmployeeProfiles();
        return new JsonResponse([
            'data' => $data
        ]);
    }

    /**
     * @OA\Get(
     *   path="/employeeprofiles/findAllByPaging",
     *   tags={"Employee Profiles"},
     *   summary="Find all employee profiles by pagination",
     *   operationId="employeeProfiles_findAllByPaging",
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
     *     @OA\JsonContent(ref="#/components/schemas/EmployeeProfilesFindAllByPageResultVM"),
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
        // q=ersin+güvenç&companieshortName=test&_sort=taxOffice&_order=desc&_sort=taxNumber&_order=asc

        // https://docs.laminas.dev/tutorials/pagination/
        $paginator = $this->employeeProfileModel->findAllByPaging($get);

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
     * @OA\Post(
     *   path="/employeeprofiles/create",
     *   tags={"Employee Profiles"},
     *   summary="Create a new employee profile",
     *   operationId="employeeProfiles_create",
     *
     *   @OA\RequestBody(
     *     description="Create new Company",
     *     @OA\JsonContent(ref="#/components/schemas/EmployeeProfileSave"),
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
        $inputFilter = $this->filter->get(EmployeeProfileSaveFilter::class);
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                EmployeeProfileSave::class,
                [
                    'employeeProfiles' => EmployeeProfilesEntity::class,
                ]
            );
            $data['profileId'] = $inputFilter->getValue('id');
            // $user = $this->request->getAttribute(UserInterface::class);
            $this->employeeProfileModel->create($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response);            
    }

    /**
     * @OA\Put(
     *   path="/employeeprofiles/update/{profileId}",
     *   tags={"Employee Profiles"},
     *   summary="Update employee profile",
     *   operationId="employeeProfiles_update",
     *
     *   @OA\Parameter(
     *       name="profileId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update Employee Profile",
     *     @OA\JsonContent(ref="#/components/schemas/EmployeeProfileSave"),
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
    public function onPutUpdate(array $post, string $profileId)
    {   
        $inputFilter = $this->filter->get(EmployeeProfileSaveFilter::class);
        $post['id'] = $profileId;
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                EmployeeProfileSave::class,
                [
                    'employeeProfiles' => EmployeeProfilesEntity::class,
                ]
            );
            $data['profileId'] = $inputFilter->getValue('id');
            $this->employeeProfileModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response); 
    }

    /**
     * @OA\Delete(
     *   path="/employeeprofiles/delete/{profileId}",
     *   tags={"Employee Profiles"},
     *   summary="Delete employee profile",
     *   operationId="employeeProfiles_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="profileId",
     *       required=true,
     *       description="Grade uuid",
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
    public function onDeleteDelete(string $profileId)
    {
        $this->employeeProfileModel->delete($profileId);
        return new JsonResponse([]);
    }

}
