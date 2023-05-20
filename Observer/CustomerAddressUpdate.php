<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license    This code is licensed under MIT license (see LICENSE for details)
 */

namespace Dadolun\SibContactSync\Observer;

use \Dadolun\SibContactSync\Helper\Configuration as ConfigurationHelper;
use \Dadolun\SibContactSync\Model\SubscriptionManager;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use \Dadolun\SibCore\Helper\DebugLogger;

/**
 * Class CustomerAddressUpdate
 * @package Dadolun\SibContactSync\Observer
 */
class CustomerAddressUpdate implements ObserverInterface
{
    /**
     * @var SubscriptionManager
     */
    protected $subscriptionManager;

    /**
     * @var DebugLogger
     */
    protected $debugLogger;

    /**
     * @var ConfigurationHelper
     */
    protected $configHelper;

    /**
     * CustomerRegistration constructor.
     * @param SubscriptionManager $subscriptionManager
     * @param DebugLogger $debugLogger
     * @param ConfigurationHelper $configHelper
     */
    public function __construct(
        SubscriptionManager $subscriptionManager,
        DebugLogger $debugLogger,
        ConfigurationHelper $configHelper
    )
    {
        $this->subscriptionManager = $subscriptionManager;
        $this->debugLogger = $debugLogger;
        $this->configHelper = $configHelper;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            $this->debugLogger->info(__('CustomerAddressUpdate observer START'));
            $updateDataInSib = array();
            /**
             * @var Address $address
             */
            $address = $observer->getCustomerAddress();
            /**
             * @var $customer Customer
             */
            $customer = $address->getCustomer();
            $billing = $address->getIsDefaultBilling();
            $shipping = $address->getIsDefaultShipping();
            $status = !empty($billing) ? $billing : $shipping;
            $email = $customer->getEmail();
            $subscriberStatus = $this->subscriptionManager->checkSubscriberStatus($email);
            $subscriptionSyncStatus = $this->configHelper->isSyncEnabled();
            $this->debugLogger->info(__('Try update customer address (for customer with email: %1', $email));
            if ($status == 1 && in_array($subscriberStatus, ConfigurationHelper::ALLOWED_SUBSCRIBER_STATUSES) && $subscriptionSyncStatus) {
                $street = $address->getStreet();
                $streetValue = '';
                foreach ($street as $streetData) {
                    $streetValue .= $streetData . ' ';
                }

                $smsValue = !empty($address->getTelephone()) ? $address->getTelephone() : '';

                $countryId = !empty($address->getCountryId()) ? $address->getCountryId() : '';
                if (!empty($smsValue) && !empty($countryId)) {
                    $countryCode = $this->subscriptionManager->getCountryCode($countryId);
                    if (!empty($countryCode)) {
                        $updateDataInSib['SMS'] = $this->subscriptionManager->checkMobileNumber($smsValue, $countryCode);
                    }
                }

                $updateDataInSib[ConfigurationHelper::SIB_COMPANY_ATTRIBUTE] = !empty($address->getCompany()) ? $address->getCompany() : '';
                $updateDataInSib[ConfigurationHelper::SIB_COUNTRY_ATTRIBUTE] = !empty($address->getCountryId()) ? $address->getCountryId() : '';
                $updateDataInSib[ConfigurationHelper::SIB_STREET_ATTRIBUTE] = !empty($streetValue) ? $streetValue : '';
                $updateDataInSib[ConfigurationHelper::SIB_POSTCODE_ATTRIBUTE] = !empty($address->getPostcode()) ? $address->getPostcode() : '';
                $updateDataInSib[ConfigurationHelper::SIB_REGION_ATTRIBUTE] = !empty($address->getRegion()) ? $address->getRegion() : '';
                $updateDataInSib[ConfigurationHelper::SIB_CITY_ATTRIBUTE] = !empty($address->getCity()) ? $address->getCity() : '';

                $firstName = $customer->getFirstName();
                $lastName = $customer->getLastName();
                $storeView = $customer->getStore()->getName();
                $storeId = $customer->getStoreId();

                if (!empty($firstName)) {
                    $updateDataInSib[ConfigurationHelper::SIB_CLIENT_FIRSTNAME] = $firstName;
                }
                if (!empty($lastName)) {
                    $updateDataInSib[ConfigurationHelper::SIB_CLIENT_LASTNAME] = $lastName;
                }

                $updateDataInSib[ConfigurationHelper::SIB_CLIENT_ATTRIBUTE] = 1;

                if (!empty($storeId)) {
                    $updateDataInSib[ConfigurationHelper::SIB_STORE_ATTRIBUTE] = $storeId;
                }
                if (!empty($storeView)) {
                    $updateDataInSib[ConfigurationHelper::SIB_LANG_ATTRIBUTE] = $storeView;
                }
                $this->subscriptionManager->subscribe($email, $updateDataInSib, $subscriberStatus);
            } else {
                $this->debugLogger->info(__('Contact Sync is not enabled'));
            }
            $this->debugLogger->info(__('CustomerAddressUpdate observer END'));
        } catch (\Exception $e) {
            $this->debugLogger->error(__('Error: ') . $e->getMessage());
        }
    }
}
