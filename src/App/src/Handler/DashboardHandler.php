<?php

declare(strict_types=1);

namespace App\Handler\Api;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class DashboardHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator
    )
    {
        $this->translator = $translator;
    }
}
