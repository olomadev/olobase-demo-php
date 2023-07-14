<?php

declare(strict_types=1);

namespace App\Handler\Api;

use App\Utils\Error;
use App\Model\CommonModel;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class PaymentTypesHandler extends AbstractHandler
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
     *   path="/paymentTypes/findAll",
     *   tags={"Common"},
     *   summary="Find all payment types",
     *   operationId="paymentTypes_findAll",
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
        $data = $this->commonModel->findPaymentTypes($get);
        return new JsonResponse([
            'data' => $data
        ]);
    }
}
