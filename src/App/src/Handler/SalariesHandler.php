<?php

declare(strict_types=1);

namespace App\Handler\Api;

use function paginatorJsonDecode;

use App\Utils\Error;
use App\Utils\DataManager;
use App\Model\SalaryModel;
use App\Entity\SalariesEntity;
use App\Schema\SalarySave;
use App\Filter\SalarySaveFilter;
use Mezzio\Authentication\UserInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class SalariesHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        InputFilterPluginManager $filter,
        SalaryModel $salaryModel,
        DataManager $dataManager,
        Error $error
    )
    {
        $this->filter = $filter;
        $this->translator = $translator;
        $this->salaryModel = $salaryModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
    }
    
    /**
     * @OA\Get(
     *   path="/salaries/findAllByPaging",
     *   tags={"Salaries"},
     *   summary="Find all salaries by pagination",
     *   operationId="salaries_findAllByPaging",
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
     *     @OA\JsonContent(ref="#/components/schemas/SalaryFindAllByPageResultVM"),
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
        // q=ersin+güvenç&SalarieshortName=test&_sort=taxOffice&_order=desc&_sort=taxNumber&_order=asc

        // https://docs.laminas.dev/tutorials/pagination/
        $paginator = $this->salaryModel->findAllByPaging($get);

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
     *   path="/salaries/create",
     *   tags={"Salaries"},
     *   summary="Create a new salary",
     *   operationId="salaries_create",
     *
     *   @OA\RequestBody(
     *     description="Create new salary",
     *     @OA\JsonContent(ref="#/components/schemas/SalarySave"),
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
        $inputFilter = $this->filter->get(SalarySaveFilter::class);
        $inputFilter->setInputData($post);
        $user = $this->request->getAttribute(UserInterface::class);
        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                SalarySave::class,
                [
                    'salaries' => SalaryEntity::class,
                ]
            );
            $data['salaryId'] = $inputFilter->getValue('id');
            $this->salaryModel->create($data, $user);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response);            
    }

    /**
     * @OA\Put(
     *   path="/salaries/update/{salaryId}",
     *   tags={"Salaries"},
     *   summary="Update salary",
     *   operationId="salaries_update",
     *
     *   @OA\Parameter(
     *       name="salaryId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update salary",
     *     @OA\JsonContent(ref="#/components/schemas/SalarySave"),
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
    public function onPutUpdate(array $post, string $salaryId)
    {   
        $inputFilter = $this->filter->get(SalarySaveFilter::class);
        $post['id'] = $salaryId;
        $inputFilter->setInputData($post);
        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                SalarySave::class,
                [
                    'salaries' => SalariesEntity::class,
                ]
            );
            $data['salaryId'] = $inputFilter->getValue('id');
            $this->salaryModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response); 
    }

    /**
     * @OA\Delete(
     *   path="/salaries/delete/{salaryId}",
     *   tags={"Salaries"},
     *   summary="Delete salary",
     *   operationId="salaries_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="salaryId",
     *       required=true,
     *       description="Salary uuid",
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
    public function onDeleteDelete(string $salaryId)
    {
        $this->salaryModel->delete($salaryId);
        return new JsonResponse([]);
    }   

}