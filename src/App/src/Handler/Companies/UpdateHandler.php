<?php

declare(strict_types=1);

namespace App\Handler\Companies;

use App\Model\CompanyModel;
use App\Entity\CompaniesEntity;
use App\Schema\Companies\CompanySave;
use App\Filter\Companies\SaveFilter;
use Oloma\Php\DataManagerInterface;
use Oloma\Php\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UpdateHandler implements RequestHandlerInterface
{
    public function __construct(
        private CompanyModel $companyModel,
        private DataManagerInterface $dataManager,
        private SaveFilter $filter,
        private Error $error,
    ) 
    {
        $this->companyModel = $companyModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
        $this->filter = $filter;
    }
    
    /**
     * @OA\Put(
     *   path="/companies/update/{companyId}",
     *   tags={"Companies"},
     *   summary="Update company",
     *   operationId="companies_update",
     *
     *   @OA\Parameter(
     *       name="companyId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update Company",
     *     @OA\JsonContent(ref="#/components/schemas/CompanySave"),
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
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $companyId = $request->getAttribute("companyId");
        $post = $request->getParsedBody();
        $post['id'] = $companyId;

        $this->filter->setInputData($post);
        $data = array();
        $response = array();
        if ($this->filter->isValid()) {
            $this->dataManager->setInputFilter($this->filter);
            $data = $this->dataManager->getEntityData(
                CompanySave::class,
                [
                    'companies' => CompaniesEntity::class,
                ]
            );
            $data['companyId'] = $this->filter->getValue('id');
            $this->companyModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse($response);   
    }
}
