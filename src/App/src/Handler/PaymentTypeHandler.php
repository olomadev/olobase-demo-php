<?php

declare(strict_types=1);

namespace App\Handler\Api;

use function paginatorJsonDecode;

use App\Utils\Error;
use App\Utils\DataManager;
use App\ViewModel\PaymentTypeFindOneByIdVM;
use App\Model\PaymentTypeModel;
use App\Entity\PaymentTypesEntity;
use App\Schema\PaymentTypeSave;
use App\Filter\PaymentTypeSaveFilter;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class PaymentTypeHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        InputFilterPluginManager $filter,
        PaymentTypeModel $paymentTypeModel,
        DataManager $dataManager,
        Error $error
    )
    {
        $this->filter = $filter;
        $this->translator = $translator;
        $this->paymentTypeModel = $paymentTypeModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
    }

    /**
     * @OA\Get(
     *   path="/paymenttypes/findAllByPaging",
     *   tags={"Payment Types"},
     *   summary="Find all payment types by pagination",
     *   operationId="paymentTypes_findAllByPaging",
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
     *     @OA\JsonContent(ref="#/components/schemas/PaymentTypeFindAllByPageResultVM"),
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

        // https://docs.laminas.dev/tutorials/pagination/
        $paginator = $this->paymentTypeModel->findAllByPaging($get);

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
     *   path="/paymenttypes/findOneById/{paymentTypeId}",
     *   tags={"Payment Types"},
     *   summary="Find item data",
     *   operationId="paymentTypes_findOneById",
     *
     *   @OA\Parameter(
     *       name="paymentTypeId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/PaymentTypeFindOneByIdResultVM"),
     *   ),
     *)
     **/
    public function onGetFindOneById(string $paymentTypeId)
    {
        $row = $this->paymentTypeModel->findOneById($paymentTypeId);
        if ($row) {
            $viewModel = new PaymentTypeFindOneByIdVM($row);
            return new JsonResponse(['data' => $viewModel->getData()]);            
        }
        return new JsonResponse([], 404);
    }
    
    /**
     * @OA\Post(
     *   path="/paymenttypes/create",
     *   tags={"Payment Types"},
     *   summary="Create a new expense type",
     *   operationId="paymentTypes_create",
     *
     *   @OA\RequestBody(
     *     description="Create new expense type",
     *     @OA\JsonContent(ref="#/components/schemas/PaymentTypeSave"),
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
        $inputFilter = $this->filter->get(PaymentTypeSaveFilter::class);
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                PaymentTypeSave::class,
                [
                    'paymentTypes' => PaymentTypesEntity::class,
                ]
            );
            $data['paymentTypeId'] = $inputFilter->getValue('id');
            $this->paymentTypeModel->create($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response);            
    }

    /**
     * @OA\Put(
     *   path="/paymenttypes/update/{paymentTypeId}",
     *   tags={"Payment Types"},
     *   summary="Update expense type",
     *   operationId="paymentTypes_update",
     *
     *   @OA\Parameter(
     *       name="paymentTypeId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update customer",
     *     @OA\JsonContent(ref="#/components/schemas/PaymentTypeSave"),
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
    public function onPutUpdate(array $post, string $paymentTypeId)
    {   
        $inputFilter = $this->filter->get(PaymentTypeSaveFilter::class);
        $post['id'] = $paymentTypeId;
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                PaymentTypeSave::class,
                [
                    'paymentTypes' => PaymentTypesEntity::class,
                ]
            );
            $data['paymentTypeId'] = $inputFilter->getValue('id');
            $this->paymentTypeModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response); 
    }

    /**
     * @OA\Delete(
     *   path="/paymenttypes/delete/{paymentTypeId}",
     *   tags={"Payment Types"},
     *   summary="Delete expense type",
     *   operationId="paymentTypes_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="paymentTypeId",
     *       required=true,
     *       description="PaymentType uuid",
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
    public function onDeleteDelete(string $paymentTypeId)
    {
        $this->paymentTypeModel->delete($paymentTypeId);
        return new JsonResponse([]);
    }
}
