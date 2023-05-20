<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license     Open Source License
 */

namespace Dadolun\SibContactSync\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Configuration
 * @package Dadolun\SibContactSync\Helper
 */
class Configuration extends \Dadolun\SibCore\Helper\Configuration
{
    const SIB_CLIENT_FIRSTNAME = 'FIRSTNAME';
    const SIB_CLIENT_LASTNAME = 'LASTNAME';

    const SIB_CLIENT_ATTRIBUTE = 'CLIENT';
    const SIB_SMS_ATTRIBUTE = 'CLIENT';
    const SIB_STORE_ATTRIBUTE = 'STORE_ID';
    const SIB_LANG_ATTRIBUTE = 'MAGENTO_LANG';
    const SIB_COMPANY_ATTRIBUTE = 'COMPANY';
    const SIB_COUNTRY_ATTRIBUTE = 'COUNTRY_ID';
    const SIB_REGION_ATTRIBUTE = 'REGION';
    const SIB_CITY_ATTRIBUTE = 'CITY';
    const SIB_STREET_ATTRIBUTE = 'STREET';
    const SIB_POSTCODE_ATTRIBUTE = 'POSTCODE';

    const SIB_NO_CONFIRM = 'nocon';
    const SIB_SIMPLEMAIL_CONFIRM = 'simplemail';
    const SIB_DUBLE_OPTIN_CONFIRM = 'doubleoptin';

    const CONFIG_GROUP_CONTACT_PATH = 'sendinblue_contact';
    const MODULE_CONTACT_CONFIG_PATH = self::CONFIG_SECTION_PATH . '/' . self::CONFIG_GROUP_CONTACT_PATH;

    const ALLOWED_SUBSCRIBER_STATUSES = [
        Subscriber::STATUS_SUBSCRIBED,
        Subscriber::STATUS_NOT_ACTIVE,
        Subscriber::STATUS_UNCONFIRMED
    ];

    /**
     * @param $val
     * @return mixed
     */
    public function getContactValue($val) {
        return $this->scopeConfig->getValue(self::MODULE_CONTACT_CONFIG_PATH . '/' . $val, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $val
     * @return bool
     */
    public function getContactFlag($val) {
        return $this->scopeConfig->isSetFlag(self::MODULE_CONTACT_CONFIG_PATH . '/' . $val, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Set module config value
     *
     * @param $pathVal
     * @param $val
     * @param string $scope
     * @param int $scopeId
     */
    public function setContactValue($pathVal, $val, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0)
    {
        $this->configWriter->save(self::MODULE_CONTACT_CONFIG_PATH . '/' . $pathVal, $val, $scope, $scopeId);
    }

    /**
     * @param int $scopeId
     * @return mixed
     */
    public function getDefaultSenderFromEmail($scopeId = 0) {
        return $this->scopeConfig->getValue(
            'trans_email/ident_sales/email',
            ScopeInterface::SCOPE_STORE,
            $scopeId
        );
    }

    /**
     * @param int $scopeId
     * @return mixed
     */
    public function getDefaultSenderFromName($scopeId = 0) {
        return $this->scopeConfig->getValue(
            'trans_email/ident_sales/name',
            ScopeInterface::SCOPE_STORE,
            $scopeId
        );
    }

    /**
     * Checks whether the Sendinblue API key and the Sendinblue subscription form is enabled
     * and returns the true|false accordingly.
     *
     * @return bool
     */
    public function isSyncEnabled() {
        $subsStatus = $this->getContactFlag('subscribe_setting');
        if ($this->isServiceActive() && $subsStatus) {
            return true;
        }
        return false;
    }

}
