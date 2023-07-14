<?php

declare(strict_types=1);

namespace App\Handler\Api;

use function paginatorJsonDecode;

use App\Utils\Error;
use App\Utils\DataManager;
use App\Model\NotificationModel;
use App\Entity\NotificationsEntity;
use App\Schema\NotificationSave;
use App\Filter\NotificationSaveFilter;
use App\ViewModel\NotificationFindOneByIdVM;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class NotificationHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        InputFilterPluginManager $filter,
        notificationModel $notificationModel,
        DataManager $dataManager,
        Error $error
    )
    {
        $this->filter = $filter;
        $this->translator = $translator;
        $this->notificationModel = $notificationModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
    }

    /**
     * @OA\Get(
     *   path="/notifications/findAllByPaging",
     *   tags={"Notifications"},
     *   summary="Find all notifications by pagination",
     *   operationId="notifications_findAllByPaging",
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
     *     @OA\JsonContent(ref="#/components/schemas/NotificationFindAllByPageResultVM"),
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
        $paginator = $this->notificationModel->findAllByPaging($get);

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
     *   path="/notifications/findOneById/{notifyId}",
     *   tags={"Notifications"},
     *   summary="Find item data",
     *   operationId="notifications_findOneById",
     *
     *   @OA\Parameter(
     *       name="notifyId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/NotificationFindOneByIdResultVM"),
     *   ),
     *)
     **/
    public function onGetFindOneById(string $notifyId)
    {
        $row = $this->notificationModel->findOneById($notifyId);
        if ($row) {
            $viewModel = new NotificationFindOneByIdVM($row);
            return new JsonResponse(['data' => $viewModel->getData()]);            
        }
        return new JsonResponse([], 404);
    }
    
    /**
     * @OA\Post(
     *   path="/notifications/create",
     *   tags={"Notifications"},
     *   summary="Create a new notification",
     *   operationId="notifications_create",
     *
     *   @OA\RequestBody(
     *     description="Create new job title",
     *     @OA\JsonContent(ref="#/components/schemas/NotificationSave"),
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
        $inputFilter = $this->filter->get(NotificationSaveFilter::class);
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                NotificationSave::class,
                [
                    'notifications' => NotificationsEntity::class,
                ]
            );
            $data['notifyId'] = $inputFilter->getValue('id');
            $data['users'] = $inputFilter->getValue('users');
            $this->notificationModel->create($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response);            
    }

    /**
     * @OA\Put(
     *   path="/notifications/update/{notifyId}",
     *   tags={"Notifications"},
     *   summary="Update notification",
     *   operationId="notifications_update",
     *
     *   @OA\Parameter(
     *       name="notifyId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update customer",
     *     @OA\JsonContent(ref="#/components/schemas/NotificationSave"),
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
    public function onPutUpdate(array $post, string $notifyId)
    {   
        $inputFilter = $this->filter->get(NotificationSaveFilter::class);
        $post['id'] = $notifyId;
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                NotificationSave::class,
                [
                    'notifications' => NotificationsEntity::class,
                ]
            );
            $data['notifyId'] = $inputFilter->getValue('id');
            $data['users'] = $inputFilter->getValue('users');
            $this->notificationModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response); 
    }

    /**
     * @OA\Delete(
     *   path="/notifications/delete/{notifyId}",
     *   tags={"Notifications"},
     *   summary="Delete notification",
     *   operationId="notifications_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="notifyId",
     *       required=true,
     *       description="Notification uuid",
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
    public function onDeleteDelete(string $notifyId)
    {
        $this->notificationModel->delete($notifyId);
        return new JsonResponse([]);
    }
}
