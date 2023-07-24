<?php

declare(strict_types=1);

namespace App\Handler\Api;

use function paginatorJsonDecode;

use App\Utils\Error;
use App\Model\CommonModel;
use App\Utils\DataManager;
use App\ViewModel\JobTitleFindOneByIdVM;
use App\Model\JobTitleModel;
use App\Entity\JobTitlesEntity;
use App\Schema\JobTitleSave;
use App\Filter\JobTitleSaveFilter;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class JobTitlesHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        InputFilterPluginManager $filter,
        JobTitleModel $jobTitleModel,
        CommonModel $commonModel,
        DataManager $dataManager,
        Error $error
    )
    {
        $this->filter = $filter;
        $this->translator = $translator;
        $this->jobTitleModel = $jobTitleModel;
        $this->commonModel = $commonModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
    }



}
