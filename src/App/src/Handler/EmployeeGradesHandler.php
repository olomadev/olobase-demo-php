<?php

declare(strict_types=1);

namespace App\Handler\Api;

use App\Utils\Error;
use App\Utils\DataManager;
use App\Model\CommonModel;
use App\Model\EmployeeGradeModel;
use App\Schema\EmployeeGradeSave;
use App\Entity\EmployeeGradesEntity;
use App\Filter\EmployeeGradeSaveFilter;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class EmployeeGradesHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        InputFilterPluginManager $filter,
        EmployeeGradeModel $employeeGradeModel,
        CommonModel $commonModel,
        DataManager $dataManager,
        Error $error
    )
    {
        $this->filter = $filter;
        $this->translator = $translator;
        $this->commonModel = $commonModel;
        $this->dataManager = $dataManager;
        $this->employeeGradeModel = $employeeGradeModel;
        $this->error = $error;
    }

    /**
     * @OA\Get(
     *   path="/employeegrades/findAll",
     *   tags={"Common"},
     *   summary="Find all employee grades",
     *   operationId="employeeGrades_findAll",
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
        $data = $this->commonModel->findEmployeeGrades();
        return new JsonResponse([
            'data' => $data
        ]);
    }

    /**
     * @OA\Get(
     *   path="/employeegrades/findAllByPaging",
     *   tags={"Employee Grades"},
     *   summary="Find all employee grades by pagination",
     *   operationId="employeeGrades_findAllByPaging",
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
     *     @OA\JsonContent(ref="#/components/schemas/EmployeeGradesFindAllByPageResultVM"),
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
        $paginator = $this->employeeGradeModel->findAllByPaging($get);

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
     *   path="/employeegrades/create",
     *   tags={"Employee Grades"},
     *   summary="Create a new employee grade",
     *   operationId="employeeGrades_create",
     *
     *   @OA\RequestBody(
     *     description="Create new Company",
     *     @OA\JsonContent(ref="#/components/schemas/EmployeeGradeSave"),
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
        $inputFilter = $this->filter->get(EmployeeGradeSaveFilter::class);
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                EmployeeGradeSave::class,
                [
                    'employeeGrades' => EmployeeGradesEntity::class,
                ]
            );
            $data['gradeId'] = $inputFilter->getValue('id');
            // $user = $this->request->getAttribute(UserInterface::class);
            $this->employeeGradeModel->create($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response);            
    }

    /**
     * @OA\Put(
     *   path="/employeegrades/update/{gradeId}",
     *   tags={"Employee Grades"},
     *   summary="Update employee grade",
     *   operationId="employeeGrades_update",
     *
     *   @OA\Parameter(
     *       name="gradeId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update Company",
     *     @OA\JsonContent(ref="#/components/schemas/EmployeeGradeSave"),
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
    public function onPutUpdate(array $post, string $gradeId)
    {   
        $inputFilter = $this->filter->get(EmployeeGradeSaveFilter::class);
        $post['id'] = $gradeId;
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                EmployeeGradeSave::class,
                [
                    'employeeGrades' => EmployeeGradesEntity::class,
                ]
            );
            $data['gradeId'] = $inputFilter->getValue('id');
            $this->employeeGradeModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response); 
    }

    /**
     * @OA\Delete(
     *   path="/employeegrades/delete/{gradeId}",
     *   tags={"Employee Grades"},
     *   summary="Delete employee grade",
     *   operationId="employeeGrades_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="gradeId",
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
    public function onDeleteDelete(string $gradeId)
    {
        $this->employeeGradeModel->delete($gradeId);
        return new JsonResponse([]);
    }

}
