<?php

declare(strict_types=1);

namespace App\Handler\Common\Files;

use App\Model\FileModel;
use App\Filter\Files\ReadFileFilter;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class FindOneByIdHandler implements RequestHandlerInterface
{
    public function __construct(
        private Translator $translator,
        private FileModel $fileModel,
        private ReadFileFilter $filter,
        private Error $error
    )
    {
        $this->filter = $filter;
        $this->fileModel = $fileModel;
        $this->translator = $translator;
        $this->error = $error;
    }

    /**
     * @OA\Get(
     *   path="/files/findOneById/{fileId}",
     *   tags={"Common"},
     *   summary="Find ",
     *   operationId="files_findOne",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="fileId",
     *       required=true,
     *       description="File id",
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="tableName",
     *       required=true,
     *       description="File tableName",
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation (File content returns to Base64 string)",
     *   ),
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        $get['fileId'] = $queryParams['id'];
        $get['tableName'] = $queryParams['tableName'];

        $this->filter->setInputData($get);
        if ($this->filter->isValid()) {
            $tableName = $this->filter->getValue('tableName');
            $row = $this->fileModel->findOneById($get['fileId'], $tableName);
            if (empty($row)) {
                return new TextResponse(
                    $this->translator->translate('No document found'),
                    404
                );
            }
            $response = new Response('php://temp', 200);
            $response->getBody()->write($row['data']);
            $response = $response->withHeader('Pragma', 'public');
            $response = $response->withHeader('Expires', 0);
            $response = $response->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
            $response = $response->withHeader('Content-Type', 'application/force-download');
            $response = $response->withHeader('Content-Type', 'application/octet-stream');
            $response = $response->withHeader('Content-Type', 'application/download');
            $response = $response->withHeader('Content-Disposition', 'attachment; filename="'.$row['name'].'"');
            $response = $response->withHeader('Content-Transfer-Encoding', 'binary');
            return $response;
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
    }
}


