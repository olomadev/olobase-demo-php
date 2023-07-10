<?php

declare(strict_types=1);

namespace App\Listener;

use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use App\Model\MessagesModel;
use App\Utils\Sms;
use App\Utils\Mailer;
use App\Utils\PaymentRefund;

class NotificationListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    const onUserCreateNew = 'onUserCreateNew';
    const onUserResendActivationCode = 'onUserResendActivationCode';
    const onUserResetPassword = 'onUserResetPassword';
    const onUserCreateNewAppointment = 'onUserCreateNewAppointment';
    const onUserCancelAppointment = 'onUserCancelAppointment';
    const onUserConfirmAppointment = 'onUserConfirmAppointment';

    const onAdminAppointmentCreateNew = 'onAdminAppointmentCreateNew';
    const onAdminAppointmentDateChange = 'onAdminAppointmentDateChange';
    const onAdminAppointmentConsultantChange = 'onAdminAppointmentConsultantChange';

    private $mailer;
    private $messagesModel;

    public function __construct(MessagesModel $messagesModel, Mailer $mailer, Sms $sms)
    {
        $this->messagesModel = $messagesModel;
        $this->mailer = $mailer;
        $this->sms = $sms;
    }

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        // user
        // 
        $this->listeners[] = $events->attach(Self::onUserCreateNew, [$this, Self::onUserCreateNew]);
        $this->listeners[] = $events->attach(Self::onUserResendActivationCode, [$this, Self::onUserResendActivationCode]);
        $this->listeners[] = $events->attach(Self::onUserResetPassword, [$this, Self::onUserResetPassword]);
        $this->listeners[] = $events->attach(Self::onUserCreateNewAppointment, [$this, Self::onUserCreateNewAppointment]);
        $this->listeners[] = $events->attach(Self::onUserCancelAppointment, [$this, Self::onUserCancelAppointment]);
        $this->listeners[] = $events->attach(Self::onUserConfirmAppointment, [$this, Self::onUserConfirmAppointment]);
        // admin
        // 
        $this->listeners[] = $events->attach(Self::onAdminAppointmentCreateNew, [$this, Self::onAdminAppointmentCreateNew]);
        $this->listeners[] = $events->attach(Self::onAdminAppointmentDateChange, [$this, Self::onAdminAppointmentDateChange]);
        $this->listeners[] = $events->attach(Self::onAdminAppointmentConsultantChange, [$this, Self::onAdminAppointmentConsultantChange]);
    }

    /**
     * User create new membership
     */
    public function onUserCreateNew(EventInterface $e)
    {    
        $params = $e->getParams();
        $object = $e->getTarget();
        $link = PROJECT_HOST.'/authorization/activateEmail?code='.$params['activationCode'].'&locale='.$object->translator->getLocale();
        $variables = [
            '{EMAIL}' => strip_tags($params['email']),
            '{ACTIVATION_LINK}' => urlencode($link),
        ];
        $this->mailer->isHtml(true);
        $this->mailer->setVariable('logoUrl', CLIENT_HOST.CLIENT_LOGO_BASE_PATH.'logo-menu.png');
        $this->mailer->setVariable('primaryColor', CLIENT_PRIMARY_COLOR);
        $body = $this->mailer->getTemplate('mail_user_activation', $variables);
        $this->mailer->to($params['email']);
        $this->mailer->subject($object->translator->translate('Anında Doktor E-Mail Activation', 'message_templates'));
        $this->mailer->body($body);
        $this->mailer->send();
    }

    /**
     * User resend activation code
     */
    public function onUserResendActivationCode(EventInterface $e)
    {
        $params = $e->getParams();
        $object = $e->getTarget();
        $link = PROJECT_HOST.'/authorization/activateEmail?code='.$params['activationCode'].'&locale='.$object->translator->getLocale();
        $variables = [
            '{EMAIL}' => strip_tags($params['email']),
            '{ACTIVATION_LINK}' => urlencode($link),
        ];
        $this->mailer->isHtml(true);
        $this->mailer->setVariable('logoUrl', CLIENT_HOST.CLIENT_LOGO_BASE_PATH.'logo-menu.png');
        $this->mailer->setVariable('primaryColor', CLIENT_PRIMARY_COLOR);
        $body = $this->mailer->getTemplate('mail_user_activation', $variables);
        $this->mailer->to($params['email']);
        $this->mailer->subject($this->translator->translate('Anında Doktor E-Mail Activation', 'message_templates'));
        $this->mailer->body($body);
        $this->mailer->send();
    }

    /**
     * User reset password
     */
    public function onUserResetPassword(EventInterface $e)
    {
        $params = $e->getParams();
        $object = $e->getTarget();
        $username = strip_tags($params['username']);
        $row = $this->messagesModel->findUserByUsername($username);

        $link = PROJECT_HOST.'/passwordResetChangePassword?code='.$params['resetCode'].'&locale='.$object->translator->getLocale();
        $variables = [
            '{EMAIL}' => strip_tags($row['email']),
            '{RESET_PASSWORD_LINK}' => urlencode($link),
        ];
        $this->mailer->isHtml(true);
        $this->mailer->setVariable('logoUrl', CLIENT_HOST.CLIENT_LOGO_BASE_PATH.'logo-menu.png');
        $this->mailer->setVariable('primaryColor', CLIENT_PRIMARY_COLOR);
        $body = $this->mailer->getTemplate('mail_user_forgot_password', $variables);
        $this->mailer->to($row['email']);
        $this->mailer->subject($object->translator->translate('Anında Doktor Forgotten Password', 'message_templates'));
        $this->mailer->body($body);
        $this->mailer->send();
    }

    /**
     * User create new appointment
     */
    public function onUserCreateNewAppointment(EventInterface $e)
    {    
        $params = $e->getParams();
        $object = $e->getTarget();
        $totalAmount = $params['totalAmount'];
        $appointmentId = $params['appointmentId'];

        $row = $this->messagesModel->findOneAppointmentById($appointmentId);
        $link = PROJECT_HOST.'/patient/consultation/'.$row['appointment_id'].'?locale='.$object->translator->getLocale();

        if (! empty($row)) { // her şey yolunda ise maili gönder
            $variables = [
                '{PATIENT_NAME}' => $row['patient_name'],
                '{CONSULTANT_NAME}' => $row['title_name'].' '.$row['consultant_name'],
                '{APPOINTMENT_DATE}' => date('d-m-Y', strtotime($row['appointment_date'])),
                '{APPOINTMENT_TIME}' => substr($row['appointment_time'], 0, -3),
                '{SPECIFICATION_NAME}' => $row['specification_name'],
                '{CALL_LINK}' => urlencode($link),
                '{LINK}' => 'https://'.$row['client_key'].'.'.PROJECT_DOMAIN,
                '{EMAIL}' => $row['patient_email'],
                '{CLIENT_RESERVED_APPOINTMENT_REMOVAL_MINUTE}' => ceil(CLIENT_RESERVED_APPOINTMENT_REMOVAL_MINUTE / 60),
            ];
            $this->mailer->isHtml(true);
            $this->mailer->setVariable('logoUrl', CLIENT_HOST.CLIENT_LOGO_BASE_PATH.'logo-menu.png');
            $this->mailer->setVariable('primaryColor', CLIENT_PRIMARY_COLOR);
            $this->mailer->setVariable('totalAmount', $totalAmount);
            $body = $this->mailer->getTemplate('mail_user_create_appointment', $variables);
            $this->mailer->to($row['patient_email']);
            $this->mailer->subject($object->translator->translate('Appointment Created', 'message_templates'));
            $this->mailer->body($body);
            $this->mailer->send();

            if ($row['status_id'] == 'confirmed') {

                // hastaya sms gönder
                // 
                $this->sms->clear();
                $this->sms->setLocale($object->translator->getLocale());
                $this->sms->to($row['patient_area_code'].$row['patient_phone']);
                $message = $this->sms->getTemplate('sms_patient_confirmed', $variables);
                $this->sms->message($message);
                $this->sms->send();

                // doktora sms gönder
                // 
                $smsNotification1 = $row['c_sms_notification1'];
                $smsNotification2 = $row['c_sms_notification2'];
                if ($row['use_specification_settings']) {
                    $smsNotification1 = $row['s_sms_notification1'];
                    $smsNotification2 = $row['s_sms_notification2'];
                }
                if ($smsNotification1 && $smsNotification2) {
                    $this->sms->clear();
                    $this->sms->setLocale($object->translator->getLocale());
                    $this->sms->to($row['consultant_area_code'].$row['consultant_phone']);
                    $message = $this->sms->getTemplate('sms_consultant_confirmed', $variables);
                    $this->sms->message($message);
                    $this->sms->send();
                }
                // doktora bildirim gönder "x" tarihinde "x" danışanınız ile olan randevunuz onaylandı
                // 
                if (! empty($row['consultant_email'])) {
                    $link = PROJECT_HOST.'/doctor/consultation/'.$row['appointment_id'].'?locale='.$object->translator->getLocale();
                    $variables['{CALL_LINK}'] = urlencode($link);
                    $variables['{EMAIL}'] = $row['consultant_email'];
                    $this->mailer->clear();
                    $this->mailer->isHtml(true);
                    $this->mailer->setVariable('section', 'user');
                    $this->mailer->setVariable('role', 'consultant');
                    $this->mailer->setVariable('logoUrl', CLIENT_HOST.CLIENT_LOGO_BASE_PATH.'logo-menu.png');
                    $this->mailer->setVariable('primaryColor', CLIENT_PRIMARY_COLOR);
                    $body = $this->mailer->getTemplate('mail_confirmed_appointment', $variables);
                    $this->mailer->to($row['consultant_email']);
                    if (! empty($row['consultant_cc_emails'])) {
                        $ccEmails = explode(',', $row['consultant_cc_emails']);
                        foreach ($ccEmails as $ccEmail) {
                            $this->mailer->cc($ccEmail);
                        }
                    }
                    $this->mailer->subject($object->translator->translate('Appointment Confirmed', 'message_templates'));
                    $this->mailer->body($body);
                    $this->mailer->send();
                }
            }
        }
    }

    /**
     * User cancel appointment
     */
    public function onUserCancelAppointment(EventInterface $e)
    {
        $params = $e->getParams();
        $object = $e->getTarget();
        $appointmentId = $params['appointmentId'];

        $row = $this->messagesModel->findOneAppointmentById($appointmentId);
        if (! empty($row)) {

            // hastaya mail gönder
            // 
            $variables = [
                '{PATIENT_NAME}' => $row['patient_name'],
                '{CONSULTANT_NAME}' => $row['title_name'].' '.$row['consultant_name'],
                '{APPOINTMENT_DATE}' => date('d-m-Y', strtotime($row['appointment_date'])),
                '{APPOINTMENT_TIME}' => substr($row['appointment_time'], 0, -3),
                '{SPECIFICATION_NAME}' => $row['specification_name'],
                '{EMAIL}' => $row['patient_email'],
                '{LINK}' => 'https://'.$row['client_key'].'.'.PROJECT_DOMAIN,
            ];
            $this->mailer->clear(true);
            $this->mailer->isHtml(true);
            $this->mailer->setVariable('role', 'user');
            $this->mailer->setVariable('logoUrl', CLIENT_HOST.CLIENT_LOGO_BASE_PATH.'logo-menu.png');
            $this->mailer->setVariable('primaryColor', CLIENT_PRIMARY_COLOR);
            $body = $this->mailer->getTemplate('mail_canceled_appointment', $variables);
            $this->mailer->to($row['patient_email']);
            $this->mailer->subject($object->translator->translate('Appointment Canceled', 'message_templates'));
            $this->mailer->body($body);
            $this->mailer->send();

            // hastaya sms gönder
            // 
            $this->sms->clear();
            $this->sms->setLocale($object->translator->getLocale());
            $this->sms->to($row['patient_area_code'].$row['patient_phone']);
            $message = $this->sms->getTemplate('sms_patient_canceled', $variables);
            $this->sms->message($message);
            $this->sms->send();

            // doktora sms gönder
            // 
            $smsNotification1 = $row['c_sms_notification1'];
            if ($row['use_specification_settings']) {
                $smsNotification1 = $row['s_sms_notification1'];
            }
            if ($smsNotification1) {
                $this->sms->clear();
                $this->sms->setLocale($object->translator->getLocale());
                $this->sms->to($row['consultant_area_code'].$row['consultant_phone']);
                $message = $this->sms->getTemplate('sms_consultant_canceled', $variables);
                $this->sms->message($message);
                $this->sms->send();
            }
            // doktora mail gönder
            // 
            if (! empty($row['consultant_email'])) {
                $variables['{EMAIL}'] = $row['consultant_email'];
                $this->mailer->clear();
                $this->mailer->isHtml(true);
                $this->mailer->setVariable('role', 'consultant');
                $this->mailer->setVariable('logoUrl', CLIENT_HOST.CLIENT_LOGO_BASE_PATH.'logo-menu.png');
                $this->mailer->setVariable('primaryColor', CLIENT_PRIMARY_COLOR);
                $body = $this->mailer->getTemplate('mail_canceled_appointment', $variables);
                $this->mailer->to($row['consultant_email']);
                if (! empty($row['consultant_cc_emails'])) {
                    $ccEmails = explode(',', $row['consultant_cc_emails']);
                    foreach ($ccEmails as $ccEmail) {
                        $this->mailer->cc($ccEmail);
                    }
                }
                $this->mailer->subject($object->translator->translate('Appointment Canceled', 'message_templates'));
                $this->mailer->body($body);
                $this->mailer->send();
            }
            // adminlere mail gönder ödemeyi iptal etsinler diye
            //
            $adminEmails = $this->messagesModel->findAdminEmails();
            if (! empty($row['payment_id']) && ! empty($adminEmails) && $row['total_amount'] > 0) {
                $refundAmount = number_format((float)$row['total_amount'], 2, '.', '');
                $variables['{EMAIL}'] = $adminEmails[0];
                $variables['{CURRENCY_ID}'] = $row['currency_id'];
                $variables['{REFUND_LINK}'] = 'https://www.paytr.com/magaza/kullanici-girisi';
                $variables['{REFUND_AMOUNT}'] = $refundAmount;
                $this->mailer->clear();
                $this->mailer->isHtml(true);
                $this->mailer->setVariable('logoUrl', CLIENT_HOST.CLIENT_LOGO_BASE_PATH.'logo-menu.png');
                $this->mailer->setVariable('primaryColor', CLIENT_PRIMARY_COLOR);
                $body = $this->mailer->getTemplate('mail_cancel_payment_request', $variables);
                $this->mailer->to($adminEmails[0]);
                unset($adminEmails[0]);
                foreach ($adminEmails as $cc) {
                    $this->mailer->cc($cc);
                }
                $this->mailer->subject($object->translator->translate('Payment Cancel Request', 'message_templates'));
                $this->mailer->body($body);
                $this->mailer->send();
                //
                // otomatik iptal (admin ve kullanıcı tarafı için ortak)
                // 
                $refund = new PaymentRefund;
                $refund->setOrderId($row['order_id']);
                $refund->setRefundAmount($refundAmount);
                $refund->send();
            }
        }
    }

    /**
     * Randevunuz onaylandı
     */
    public function onUserConfirmAppointment(EventInterface $e)
    {
        $params = $e->getParams();
        $object = $e->getTarget();
        $appointmentId = $params['appointmentId'];

        $row = $this->messagesModel->findOneAppointmentById($appointmentId);
        if (! empty($row)) {

            // kullanıcıya bildirim gönder "x" danışman ile olan "x" tarihindeki randevunuz onaylandı
            // 
            $link = PROJECT_HOST.'/patient/consultation/'.$row['appointment_id'].'?locale='.$object->translator->getLocale();
            $variables = [
                '{PATIENT_NAME}' => $row['patient_name'],
                '{CONSULTANT_NAME}' => $row['title_name'].' '.$row['consultant_name'],
                '{APPOINTMENT_DATE}' => date('d-m-Y', strtotime($row['appointment_date'])),
                '{APPOINTMENT_TIME}' => substr($row['appointment_time'], 0, -3),
                '{SPECIFICATION_NAME}' => $row['specification_name'],
                '{CALL_LINK}' => urlencode($link),
                '{EMAIL}' => $row['patient_email'],
                '{LINK}' => 'https://'.$row['client_key'].'.'.PROJECT_DOMAIN,
            ];
            $this->mailer->clear();
            $this->mailer->isHtml(true);
            $this->mailer->setVariable('section', 'user');
            $this->mailer->setVariable('role', 'user');
            $this->mailer->setVariable('logoUrl', CLIENT_HOST.CLIENT_LOGO_BASE_PATH.'logo-menu.png');
            $this->mailer->setVariable('primaryColor', CLIENT_PRIMARY_COLOR);
            $body = $this->mailer->getTemplate('mail_confirmed_appointment', $variables);
            $this->mailer->to($row['patient_email']);
            $this->mailer->subject($object->translator->translate('Appointment Confirmed', 'message_templates'));
            $this->mailer->body($body);
            $this->mailer->send();

            // hastaya sms gönder
            // 
            $this->sms->clear();
            $this->sms->setLocale($object->translator->getLocale());
            $this->sms->to($row['patient_area_code'].$row['patient_phone']);
            $message = $this->sms->getTemplate('sms_patient_confirmed', $variables);
            $this->sms->message($message);
            $this->sms->send();

            // doktora sms gönder
            // 
            $smsNotification1 = $row['c_sms_notification1'];
            $smsNotification2 = $row['c_sms_notification2'];
            if ($row['use_specification_settings']) {
                $smsNotification1 = $row['s_sms_notification1'];
                $smsNotification2 = $row['s_sms_notification2'];
            }
            if ($smsNotification1 && $smsNotification2) {
                $this->sms->clear();
                $this->sms->setLocale($object->translator->getLocale());
                $this->sms->to($row['consultant_area_code'].$row['consultant_phone']);
                $message = $this->sms->getTemplate('sms_consultant_confirmed', $variables);
                $this->sms->message($message);
                $this->sms->send();
            }
            // doktora bildirim gönder "x" tarihinde "x" danışanınız ile olan randevunuz onaylandı
            // 
            if (! empty($row['consultant_email'])) {
                $link = PROJECT_HOST.'/doctor/consultation/'.$row['appointment_id'].'?locale='.$object->translator->getLocale();
                $variables['{CALL_LINK}'] = urlencode($link);
                $variables['{EMAIL}'] = $row['consultant_email'];
                $this->mailer->clear();
                $this->mailer->isHtml(true);
                $this->mailer->setVariable('section', 'user');
                $this->mailer->setVariable('role', 'consultant');
                $this->mailer->setVariable('logoUrl', CLIENT_HOST.CLIENT_LOGO_BASE_PATH.'logo-menu.png');
                $this->mailer->setVariable('primaryColor', CLIENT_PRIMARY_COLOR);
                $body = $this->mailer->getTemplate('mail_confirmed_appointment', $variables);
                $this->mailer->to($row['consultant_email']);
                if (! empty($row['consultant_cc_emails'])) {
                    $ccEmails = explode(',', $row['consultant_cc_emails']);
                    foreach ($ccEmails as $ccEmail) {
                        $this->mailer->cc($ccEmail);
                    }
                }
                $this->mailer->subject($object->translator->translate('Appointment Confirmed', 'message_templates'));
                $this->mailer->body($body);
                $this->mailer->send();
            }
        }
    }

    /**
     * Randevunuz oluşturuldu (onaylı ise)
     */    
    public function onAdminAppointmentCreateNew(EventInterface $e)
    {
        $params = $e->getParams();
        $object = $e->getTarget();
        $appointmentId = $params['appointmentId'];

        $row = $this->messagesModel->findOneAppointmentById($appointmentId);
        if (! empty($row)) {

            // kullanıcıya bildirim gönder "x" danışman ile olan "x" tarihindeki randevunuz oluşturuldu
            // 
            $link = PROJECT_HOST.'/patient/consultation/'.$row['appointment_id'].'?locale='.$object->translator->getLocale();
            $variables = [
                '{PATIENT_NAME}' => $row['patient_name'],
                '{CONSULTANT_NAME}' => $row['title_name'].' '.$row['consultant_name'],
                '{APPOINTMENT_DATE}' => date('d-m-Y', strtotime($row['appointment_date'])),
                '{APPOINTMENT_TIME}' => substr($row['appointment_time'], 0, -3),
                '{SPECIFICATION_NAME}' => $row['specification_name'],
                '{CALL_LINK}' => urlencode($link),
                '{EMAIL}' => $row['patient_email'],
            ];
            $this->mailer->clear();
            $this->mailer->isHtml(true);
            $this->mailer->setVariable('section', 'admin');
            $this->mailer->setVariable('role', 'user');
            $this->mailer->setVariable('logoUrl', CLIENT_HOST.CLIENT_LOGO_BASE_PATH.'logo-menu.png');
            $this->mailer->setVariable('primaryColor', CLIENT_PRIMARY_COLOR);
            $body = $this->mailer->getTemplate('mail_confirmed_appointment', $variables);
            $this->mailer->to($row['patient_email']);
            $this->mailer->subject($object->translator->translate('Appointment Created', 'message_templates'));
            $this->mailer->body($body);
            $this->mailer->send();

            // hastaya sms gönder
            // 
            $this->sms->clear();
            $this->sms->setLocale($object->translator->getLocale());
            $this->sms->to($row['patient_area_code'].$row['patient_phone']);
            $message = $this->sms->getTemplate('sms_patient_confirmed', $variables);
            $this->sms->message($message);
            $this->sms->send();

            // doktora sms gönder
            // 
            $smsNotification1 = $row['c_sms_notification1'];
            $smsNotification2 = $row['c_sms_notification2'];
            if ($row['use_specification_settings']) {
                $smsNotification1 = $row['s_sms_notification1'];
                $smsNotification2 = $row['s_sms_notification2'];
            }
            if ($smsNotification1 && $smsNotification2) {
                $this->sms->clear();
                $this->sms->setLocale($object->translator->getLocale());
                $this->sms->to($row['consultant_area_code'].$row['consultant_phone']);
                $message = $this->sms->getTemplate('sms_consultant_confirmed', $variables);
                $this->sms->message($message);
                $this->sms->send();
            }
            // doktora bildirim gönder "x" tarihinde "x" danışanınız ile olan randevunuz oluşturuldu
            // 
            if (! empty($row['consultant_email'])) {
                $link = PROJECT_HOST.'/doctor/consultation/'.$row['appointment_id'].'?locale='.$object->translator->getLocale();
                $variables['{CALL_LINK}'] = urlencode($link);
                $variables['{EMAIL}'] = $row['consultant_email'];
                $this->mailer->clear();
                $this->mailer->isHtml(true);
                $this->mailer->setVariable('section', 'admin');
                $this->mailer->setVariable('role', 'consultant');
                $this->mailer->setVariable('logoUrl', CLIENT_HOST.CLIENT_LOGO_BASE_PATH.'logo-menu.png');
                $this->mailer->setVariable('primaryColor', CLIENT_PRIMARY_COLOR);
                $body = $this->mailer->getTemplate('mail_confirmed_appointment', $variables);
                $this->mailer->to($row['consultant_email']);
                if (! empty($row['consultant_cc_emails'])) {
                    $ccEmails = explode(',', $row['consultant_cc_emails']);
                    foreach ($ccEmails as $ccEmail) {
                        $this->mailer->cc($ccEmail);
                    }
                }
                $this->mailer->subject($object->translator->translate('Appointment Created', 'message_templates'));
                $this->mailer->body($body);
                $this->mailer->send();
            }
        }
    }
    
    /**
     * Randevunuzun tarih / saati güncellendi
     */    
    public function onAdminAppointmentDateChange(EventInterface $e)
    {
        $params = $e->getParams();
        $object = $e->getTarget();
        $appointmentId = $params['appointmentId'];

        $row = $this->messagesModel->findOneAppointmentById($appointmentId);
        if (! empty($row)) {

            // kullanıcıya bildirim gönder
            // Randevu Değişikliği: "x" danışmanı için randevu tarihi/saati değiştirilmiştir. Yeni randevu bilgileriniz aşağıdaki gibidir.
            // 
            $link = PROJECT_HOST.'/patient/consultation/'.$row['appointment_id'].'?locale='.$object->translator->getLocale();
            $variables = [
                '{PATIENT_NAME}' => $row['patient_name'],
                '{CONSULTANT_NAME}' => $row['title_name'].' '.$row['consultant_name'],
                '{APPOINTMENT_DATE}' => date('d-m-Y', strtotime($row['appointment_date'])),
                '{APPOINTMENT_TIME}' => substr($row['appointment_time'], 0, -3),
                '{SPECIFICATION_NAME}' => $row['specification_name'],
                '{CALL_LINK}' => urlencode($link),
                '{EMAIL}' => $row['patient_email'],
            ];
            $this->mailer->clear();
            $this->mailer->isHtml(true);
            $this->mailer->setVariable('section', 'admin');
            $this->mailer->setVariable('role', 'user');
            $this->mailer->setVariable('changed', 'date');
            $this->mailer->setVariable('logoUrl', CLIENT_HOST.CLIENT_LOGO_BASE_PATH.'logo-menu.png');
            $this->mailer->setVariable('primaryColor', CLIENT_PRIMARY_COLOR);
            $body = $this->mailer->getTemplate('mail_changed_appointment', $variables);
            $this->mailer->to($row['patient_email']);
            $this->mailer->subject($object->translator->translate('Appointment Changed', 'message_templates'));
            $this->mailer->body($body);
            $this->mailer->send();

            // hastaya sms gönder
            // 
            $this->sms->clear();
            $this->sms->setLocale($object->translator->getLocale());
            $this->sms->to($row['patient_area_code'].$row['patient_phone']);
            $message = $this->sms->getTemplate('sms_patient_changed', $variables);
            $this->sms->message($message);
            $this->sms->send();

            // doktora sms gönder
            // 
            $smsNotification1 = $row['c_sms_notification1'];
            if ($row['use_specification_settings']) {
                $smsNotification1 = $row['s_sms_notification1'];
            }
            if ($smsNotification1) {
                $this->sms->clear();
                $this->sms->setLocale($object->translator->getLocale());
                $this->sms->to($row['consultant_area_code'].$row['consultant_phone']);
                $message = $this->sms->getTemplate('sms_consultant_changed', $variables);
                $this->sms->message($message);
                $this->sms->send();
            }
            // Randevu Değişikliği: "x" ile olan randevu tarihi/saati değiştirilmiştir. Yeni randevu bilgileriniz aşağıdaki gibidir.
            // 
            if (! empty($row['consultant_email'])) {
                $link = PROJECT_HOST.'/doctor/consultation/'.$row['appointment_id'].'?locale='.$object->translator->getLocale();
                $variables['{CALL_LINK}'] = urlencode($link);
                $variables['{EMAIL}'] = $row['consultant_email'];
                $this->mailer->clear();
                $this->mailer->isHtml(true);
                $this->mailer->setVariable('section', 'admin');
                $this->mailer->setVariable('role', 'consultant');
                $this->mailer->setVariable('changed', 'date');
                $this->mailer->setVariable('logoUrl', CLIENT_HOST.CLIENT_LOGO_BASE_PATH.'logo-menu.png');
                $this->mailer->setVariable('primaryColor', CLIENT_PRIMARY_COLOR);
                $body = $this->mailer->getTemplate('mail_changed_appointment', $variables);
                $this->mailer->to($row['consultant_email']);
                if (! empty($row['consultant_cc_emails'])) {
                    $ccEmails = explode(',', $row['consultant_cc_emails']);
                    foreach ($ccEmails as $ccEmail) {
                        $this->mailer->cc($ccEmail);
                    }
                }
                $this->mailer->subject($object->translator->translate('Appointment Changed', 'message_templates'));
                $this->mailer->body($body);
                $this->mailer->send();
            }
        }
    }

    /**
     * Randevunuz bir başka danışman ile yeniden oluşturuldu
     */
    public function onAdminAppointmentConsultantChange(EventInterface $e)
    {
        // user confirm appointment ile aynı bildirim
        // 
        $params = $e->getParams();
        $object = $e->getTarget();
        $appointmentId = $params['appointmentId'];

        $row = $this->messagesModel->findOneAppointmentById($appointmentId);
        if (! empty($row)) {

            // kullanıcıya bildirim gönder "x" danışman ile randevunuz "y" danışmanı olarak güncellendi
            // 
            $link = PROJECT_HOST.'/patient/consultation/'.$row['appointment_id'].'?locale='.$object->translator->getLocale();
            $variables = [
                '{PATIENT_NAME}' => $row['patient_name'],
                '{CONSULTANT_NAME}' => $row['title_name'].' '.$row['consultant_name'],
                '{APPOINTMENT_DATE}' => date('d-m-Y', strtotime($row['appointment_date'])),
                '{APPOINTMENT_TIME}' => substr($row['appointment_time'], 0, -3),
                '{SPECIFICATION_NAME}' => $row['specification_name'],
                '{CALL_LINK}' => urlencode($link),
                '{EMAIL}' => $row['patient_email'],
            ];
            $this->mailer->clear();
            $this->mailer->isHtml(true);
            $this->mailer->setVariable('section', 'user');
            $this->mailer->setVariable('role', 'user');
            $this->mailer->setVariable('changed', 'consultant');
            $this->mailer->setVariable('logoUrl', CLIENT_HOST.CLIENT_LOGO_BASE_PATH.'logo-menu.png');
            $this->mailer->setVariable('primaryColor', CLIENT_PRIMARY_COLOR);
            $body = $this->mailer->getTemplate('mail_changed_appointment', $variables);
            $this->mailer->to($row['patient_email']);
            $this->mailer->subject($object->translator->translate('Appointment Changed', 'message_templates'));
            $this->mailer->body($body);
            $this->mailer->send();

            // hastaya sms gönder
            // 
            $this->sms->clear();
            $this->sms->setLocale($object->translator->getLocale());
            $this->sms->to($row['patient_area_code'].$row['patient_phone']);
            $message = $this->sms->getTemplate('sms_patient_consultant_changed', $variables);
            $this->sms->message($message);
            $this->sms->send();

            // doktora sms gönder
            // 
            $smsNotification1 = $row['c_sms_notification1'];
            if ($row['use_specification_settings']) {
                $smsNotification1 = $row['s_sms_notification1'];
            }
            if ($smsNotification1) {
                $this->sms->clear();
                $this->sms->setLocale($object->translator->getLocale());
                $this->sms->to($row['consultant_area_code'].$row['consultant_phone']);
                $message = $this->sms->getTemplate('sms_consultant_confirmed', $variables);
                $this->sms->message($message);
                $this->sms->send();
            }
            // doktora bildirim gönder "x" tarihinde "x" danışanınız ile olan randevunuz oluşturuldu
            // 
            if (! empty($row['consultant_email'])) {
                $link = PROJECT_HOST.'/doctor/consultation/'.$row['appointment_id'].'?locale='.$object->translator->getLocale();
                $variables['{CALL_LINK}'] = urlencode($link);
                $variables['{EMAIL}'] = $row['consultant_email'];
                $this->mailer->clear();
                $this->mailer->isHtml(true);
                $this->mailer->setVariable('section', 'admin');
                $this->mailer->setVariable('role', 'consultant');
                $this->mailer->setVariable('logoUrl', CLIENT_HOST.CLIENT_LOGO_BASE_PATH.'logo-menu.png');
                $this->mailer->setVariable('primaryColor', CLIENT_PRIMARY_COLOR);
                $body = $this->mailer->getTemplate('mail_confirmed_appointment', $variables);
                $this->mailer->to($row['consultant_email']);
                if (! empty($row['consultant_cc_emails'])) {
                    $ccEmails = explode(',', $row['consultant_cc_emails']);
                    foreach ($ccEmails as $ccEmail) {
                        $this->mailer->cc($ccEmail);
                    }
                }
                $this->mailer->subject($object->translator->translate('Appointment Created', 'message_templates'));
                $this->mailer->body($body);
                $this->mailer->send();
            }
        }
    }

}