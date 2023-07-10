<?php

declare(strict_types=1);

namespace App\Handler\Api;

use function paginatorJsonDecode;

use App\Utils\Error;
use App\Utils\DataManager;
use App\Model\MinWageModel;
use App\Entity\MinWageEntity;
use App\Schema\MinWageSave;
use App\Filter\MinWageSaveFilter;
use Mezzio\Authentication\UserInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class MinumumWagesHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        InputFilterPluginManager $filter,
        MinWageModel $minWageModel,
        DataManager $dataManager,
        Error $error
    )
    {
        $this->filter = $filter;
        $this->translator = $translator;
        $this->minWageModel = $minWageModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
    }
    
    /**
     * @OA\Get(
     *   path="/minumumwages/findAllByPaging",
     *   tags={"MinWages"},
     *   summary="Find all minumum wages by pagination",
     *   operationId="minumumwages_findAllByPaging",
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
     *     @OA\JsonContent(ref="#/components/schemas/MinWageFindAllByPageResultVM"),
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
        // q=ersin+güvenç&MinWageshortName=test&_sort=taxOffice&_order=desc&_sort=taxNumber&_order=asc

        // https://docs.laminas.dev/tutorials/pagination/
        $paginator = $this->minWageModel->findAllByPaging($get);

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
     *   path="/minumumwages/create",
     *   tags={"MinWages"},
     *   summary="Create a new min wage",
     *   operationId="minumumwages_create",
     *
     *   @OA\RequestBody(
     *     description="Create new employee",
     *     @OA\JsonContent(ref="#/components/schemas/MinWageSave"),
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
        $inputFilter = $this->filter->get(MinWageSaveFilter::class);
        $inputFilter->setInputData($post);
        $user = $this->request->getAttribute(UserInterface::class);
        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                MinWageSave::class,
                [
                    'minumumwages' => MinWageEntity::class,
                ]
            );
            $data['wageId'] = $inputFilter->getValue('id');
            $this->minWageModel->create($data, $user);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response);            
    }

    /**
     * @OA\Put(
     *   path="/minumumwages/update/{wageId}",
     *   tags={"MinWages"},
     *   summary="Update min wage",
     *   operationId="minumumwages_update",
     *
     *   @OA\Parameter(
     *       name="wageId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update employee",
     *     @OA\JsonContent(ref="#/components/schemas/MinWageSave"),
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
    public function onPutUpdate(array $post, string $wageId)
    {   
        $inputFilter = $this->filter->get(MinWageSaveFilter::class);
        $post['id'] = $wageId;
        $inputFilter->setInputData($post);
        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                MinWageSave::class,
                [
                    'minumumwages' => MinWageEntity::class,
                ]
            );
            $data['wageId'] = $inputFilter->getValue('id');
            $this->minWageModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response); 
    }

    /**
     * @OA\Delete(
     *   path="/minumumwages/delete/{wageId}",
     *   tags={"MinWages"},
     *   summary="Delete min wage",
     *   operationId="minumumwages_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="wageId",
     *       required=true,
     *       description="MinWage uuid",
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
    public function onDeleteDelete(string $wageId)
    {
        $this->minWageModel->delete($wageId);
        return new JsonResponse([]);
    }
    

}
