<?php

declare(strict_types=1);

namespace App\Listener;

use App\Model\UserCampaignModel;
use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;

class AppListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    const onBeforeNewUser = 'onBeforeNewUser';
    const onAfterNewUser = 'onAfterNewUser';
    const onUserLogin = 'onUserLogin';
    const ERROR_MEMBERSHIP_SERVICE_IS_LIMITED = 'Membership service is limited to users in specific campaigns only';

    public function __construct()
    {

    }

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        // user
        // 
        $this->listeners[] = $events->attach(Self::onBeforeNewUser, [$this, Self::onBeforeNewUser]);
        $this->listeners[] = $events->attach(Self::onAfterNewUser, [$this, Self::onAfterNewUser]);
        $this->listeners[] = $events->attach(Self::onUserLogin, [$this, Self::onUserLogin]);
    }

    public function onBeforeNewUser(EventInterface $e)
    {   
        if (empty(CLIENT_IS_LIMITED_NEW_USER)) {  // yeni kullanıcı açma limitli değilse kullanıcı açmaya izin ver
            return true;
        }
        // limitli ise kampanyayı kontrol et listede var ise kullanıcı izin ver
        // 
        $params = $e->getParams();
        $userId = $params['user_id'];
        $username = trim($params['username']);

        // Kişi kayıt olduğunda campaigns_user tablosunda start_date ve end_date e göre geçerli ise kampanya ve
        // bu client a ve $username e göre kullanıcı var ise 
        // coupon tablosundan bir kupon alınıp kullanıcıya assign edilir.
        // kupon assign edilirken campaign password var olmadığından varmış gibi her zaman "1" gönderilir 
        // böylece kuponun kullanıcıya assign edildiğini anlayarak kuponu diğerlerinden ayırt etmiş oluruz.

        if ($this->userCampaignModel->hasValidCampaign($username)) { // kampanya validasyonu
            $campaignId = $this->userCampaignModel->getCampaignId();
            $errorMessage = $this->userCampaignModel->validateCampaign($userId, $campaignId);
            if ($this->userCampaignModel->hasError()) {
                return $errorMessage;
            }
            return true;
        }
        return Self::ERROR_MEMBERSHIP_SERVICE_IS_LIMITED;
    }

    public function onAfterNewUser(EventInterface $e)
    {
        // kullanıcı kayıt başarılı ise
        //
        $params = $e->getParams();
        $userId = $params['user_id'];
        $username = trim($params['username']);

        // ve geçerli kampanya id si boş değilse kampanya doğrulanmış kuponu kullanıcıya atayalım
        // 
        $campaignId = $this->userCampaignModel->getCampaignId();
        if (! empty($campaignId)) { 
            $this->userCampaignModel->assignCoupon($userId, $campaignId, $username);
        }
    }

    public function onUserLogin(EventInterface $e)
    {
        $params = $e->getParams();
        if ($params['account_type_id'] != 'pt') { // eğer hesap hasta hesabı değilse girişe her zaman izin ver
            return true;
        }
        if (empty(CLIENT_IS_LIMITED_LOGIN)) {  // kullanıcı girişi limitli değilse ise kullanıcı girişine izin ver
            return true;
        }
        // limitli ise ve geçerli kampanya varsa izin ver
        // 
        $username = trim($params['username']);
        if ($this->userCampaignModel->hasValidCampaign($username)) {
            return true;
        }
        return false;
    }

}