<?php

declare(strict_types=1);

namespace App\Handler\Api;

use App\Model\RoleModel;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class RoleKeysHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        RoleModel $roleModel
    )
    {
        $this->translator = $translator;
        $this->roleModel = $roleModel;
    }

    /**
     * @OA\Get(
     *   path="/roleKeys/findAll",
     *   tags={"Common"},
     *   summary="Find all role keys",
     *   operationId="roleKeys_findAll",
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
    public function onGetFindAll()
    {
        $data = $this->roleModel->findAllKeys();
        return new JsonResponse([
            'data' => $data
        ]);
    }

}
