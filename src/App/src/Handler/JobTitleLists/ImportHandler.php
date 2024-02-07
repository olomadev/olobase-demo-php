<?php

declare(strict_types=1);

namespace App\Handler\JobTitleLists;

use App\Filter\JobTitleLists\ImportFilter;
use Mezzio\Authentication\UserInterface;
use Olobase\Mezzio\DataManagerInterface;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Predis\ClientInterface as Predis;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class ImportHandler implements RequestHandlerInterface
{
    public function __construct(
        Translator $translator,
        Predis $predis,
        ImportFilter $filter,
        Error $error
    ) 
    {
        $this->filter = $filter;
        $this->predis = $predis;
        $this->error = $error;
    }
    
    /**
     * @OA\Post(
     *   path="/jobtitlelists/import",
     *   tags={"JobTitle Lists"},
     *   summary="Import a new excel list",
     *   operationId="jobTitleLists_import",
     *
     *   @OA\RequestBody(
     *     description="Import new job title list",
     *     @OA\JsonContent(ref="#/components/schemas/JobTitleListImport"),
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
        $user = $request->getAttribute(UserInterface::class);
        $post = $request->getParsedBody();
        $fileKey = CACHE_TMP_FILE_KEY.$user->getId();
        $hasFile = $this->predis->get($fileKey);

        if (! $hasFile) {
            return new JsonResponse(
                [
                    'data' => 
                    [
                        'error' => $this->translator->translate(
                            'Uploaded file has expired or file does not exists'
                        )
                    ]
                ], 
                400
            );
        }
        $this->filter->setInputData($post);
        if ($this->filter->isValid()) {
            $data = array();
            $data['fileKey'] = $fileKey;
            
            $yearId = $this->filter->getValue('yearId');
            $data['yearId'] = null;
            if (! empty($yearId['id'])) {
                $data['yearId'] = $yearId['id'];    
            }
            $data['listName'] = $this->filter->getValue('listName');

            // send to queue
            // https://www.vultr.com/docs/implement-redis-queue-and-worker-with-php-on-ubuntu-20-04/
            // 
            $this->predis->rpush("jobtitlelist_save", json_encode($data));
            $this->predis->expire("jobtitlelist_save", 300);

            return new JsonResponse([], 200); 
        }
        return new JsonResponse($this->error->getMessages($this->filter), 400);
    }
}
