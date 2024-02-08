<?php

declare(strict_types=1);

namespace App\Handler\Auth;

use App\Model\UserModel;
use App\Utils\SmtpMailer;
use App\Filter\Auth\ResetPasswordFilter;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class ResetPasswordHandler implements RequestHandlerInterface
{
    public function __construct(
        private Translator $translator,
        private UserModel $userModel,
        private ResetPasswordFilter $filter,
        private SmtpMailer $mailer,
        private Error $error
    ) {
        $this->translator = $translator;
        $this->userModel = $userModel;
        $this->filter = $filter;
        $this->mailer = $mailer;
        $this->error = $error;
    }

    /**
     * @OA\Post(
     *   path="/auth/resetPassword",
     *   tags={"Auth"},
     *   summary="Send reset password code to user",
     *   operationId="auth_resetPassword",
     *
     *   @OA\RequestBody(
     *     description="Send reset password request",
     *     @OA\JsonContent(ref="#/components/schemas/ResetPassword"),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->filter->setInputData($request->getParsedBody());
        if ($this->filter->isValid()) {
            $username = $this->filter->getValue('email');
            $resetCode = $this->userModel->generateResetPassword($username);
            $userRow = $this->userModel->findOneByUsername($username);
            //
            // Send reset password email to user
            //
            $link = 'https://demo.oloma.dev/resetPassword?resetCode='.$resetCode;
            $this->mailer->isHtml(true);
            $data = [
                'email' => $username,
                'resetPasswordLink' => urlencode($link),
                'themeColor' => $userRow['themeColor'] ?: "#039BE5",
            ];
            $body = $this->mailer->getTemplate('forgotPassword', $data);
            $this->mailer->to($username);
            $this->mailer->subject($this->translator->translate('Forgotten Password', 'templates'));
            $this->mailer->body($body);
            // $this->mailer->debugOutput();
            $this->mailer->send();
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse([]);
    }

}
