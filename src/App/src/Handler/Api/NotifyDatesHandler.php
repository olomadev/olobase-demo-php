<?php

declare(strict_types=1);

namespace App\Handler\Api;

use App\Utils\Error;
use App\Model\CommonModel;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class NotifyDatesHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        CommonModel $commonModel,
        Error $error
    )
    {
        $this->translator = $translator;
        $this->commonModel = $commonModel;
        $this->error = $error;
    }

    /**
     * @OA\Get(
     *   path="/notifyDates/findAll",
     *   tags={"Common"},
     *   summary="Find all notification dates",
     *   operationId="notifyDates_findAll",
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
        $results = $this->commonModel->findNotificationDates($get['moduleId']);
        $data = array();
        foreach ($results as $key => $val) {
            $data[$key] = [
                'id' => $val['id'],
                'name' => $this->translator->translate($val['id'], 'labels')
            ];
        }
        return new JsonResponse([
            'data' => $data
        ]);
    }
}