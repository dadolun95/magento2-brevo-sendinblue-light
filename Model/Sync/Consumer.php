<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license    This code is licensed under MIT license (see LICENSE for details)
 */

namespace Dadolun\SibContactSync\Model\Sync;

use \Dadolun\SibContactSync\Api\Data\SyncInfoInterface;
use \Dadolun\SibContactSync\Model\SubscriptionManager;
use \Dadolun\SibCore\Helper\DebugLogger;
use Magento\Store\Model\StoreManagerInterface;
use \Dadolun\SibContactSync\Helper\Configuration as ConfigurationHelper;
use Magento\Customer\Api\AddressRepositoryInterface as CustomerAddressRepository;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory as SubscriberCollectionFactory;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Class Consumer
 * @package Dadolun\SibContactSync\Model\Sync
 */
class Consumer
{
    /**
     * @var SubscriberCollectionFactory
     */
    private $subscriberCollectionFactory;

    /**
     * @var ConfigurationHelper
     */
    private $configHelper;

    /**
     * @var SubscriptionManager
     */
    private $subscriptionManager;

    /**
     * @var CustomerAddressRepository
     */
    private $customerAddressRepository;

    /**
     * @var DebugLogger
     */
    private $debugLogger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SyncInfoFactory
     */
    private $syncInfoFactory;

    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * Consumer constructor.
     * @param SubscriberCollectionFactory $subscriberCollectionFactory
     * @param ConfigurationHelper $configHelper
     * @param SubscriptionManager $subscriptionManager
     * @param CustomerAddressRepository $customerAddressRepository
     * @param StoreManagerInterface $storeManager
     * @param DebugLogger $debugLogger
     * @param SyncInfoFactory $syncInfoFactory
     * @param SubscriberFactory $subscriberFactory
     */
    public function __construct(
        SubscriberCollectionFactory $subscriberCollectionFactory,
        ConfigurationHelper $configHelper,
        SubscriptionManager $subscriptionManager,
        CustomerAddressRepository $customerAddressRepository,
        StoreManagerInterface $storeManager,
        DebugLogger $debugLogger,
        SyncInfoFactory $syncInfoFactory,
        SubscriberFactory $subscriberFactory
    ) {
        $this->subscriberCollectionFactory = $subscriberCollectionFactory;
        $this->configHelper = $configHelper;
        $this->subscriptionManager = $subscriptionManager;
        $this->customerAddressRepository = $customerAddressRepository;
        $this->debugLogger = $debugLogger;
        $this->storeManager = $storeManager;
        $this->syncInfoFactory = $syncInfoFactory;
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * Consumer logic.
     *
     * @param SyncInfoInterface $syncData
     * @return void
     */
    public function process(SyncInfoInterface $syncData)
    {
        $syncType = $syncData->getType();
        if ($syncType === SyncInfoInterface::TOTAL_SYNC_TYPE) {
            $this->syncContacts($syncData->getStoreId());
        } else {
            $email = $syncData->getEmail();
            $subscriber = $this->subscriberFactory->create()->loadBySubscriberEmail($email, $syncData->getStoreId());
            $this->syncContact($subscriber);
        }
    }

    /**
     * @param $subscriber
     */
    private function syncContact($subscriber) {
        $updateDataInSib = [];
        $subscriberStatus = $subscriber->getSubscriberStatus();
        $email = $subscriber->getEmail();
        $this->debugLogger->info(__('Start async saving for subscriber with email %1', $email));
        if ($subscriber->getCustomerId()) {
            try {
                /**
                 * @var CustomerInterface $customer
                 */
                $customer = $this->subscriptionManager->getCustomer($subscriber->getCustomerId());
                $billingId = !empty($customer->getDefaultBilling()) ? $customer->getDefaultBilling() : '';
                $firstName = $customer->getFirstname();
                $lastName = $customer->getLastname();
                $storeId = $subscriber->getStoreId();
                $storeView = $this->storeManager->getStore($storeId)->getName();

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

                if (!empty($billingId)) {
                    $address = $this->customerAddressRepository->getById($billingId);
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
                            $updateDataInSib[ConfigurationHelper::SIB_SMS_ATTRIBUTE] = $this->subscriptionManager->checkMobileNumber($smsValue, $countryCode);
                        }
                    }

                    $updateDataInSib[ConfigurationHelper::SIB_COMPANY_ATTRIBUTE] = !empty($address->getCompany()) ? $address->getCompany() : '';
                    $updateDataInSib[ConfigurationHelper::SIB_COUNTRY_ATTRIBUTE] = !empty($address->getCountryId()) ? $address->getCountryId() : '';
                    $updateDataInSib[ConfigurationHelper::SIB_STREET_ATTRIBUTE] = !empty($streetValue) ? $streetValue : '';
                    $updateDataInSib[ConfigurationHelper::SIB_POSTCODE_ATTRIBUTE] = !empty($address->getPostcode()) ? $address->getPostcode() : '';
                    $updateDataInSib[ConfigurationHelper::SIB_REGION_ATTRIBUTE] = !empty($address->getRegion()) ? $address->getRegion()->getRegionCode() : '';
                    $updateDataInSib[ConfigurationHelper::SIB_CITY_ATTRIBUTE] = !empty($address->getCity()) ? $address->getCity() : '';
                }
                $this->debugLogger->info(__('Subscription will synced with status %1', $subscriberStatus));
                $this->subscriptionManager->subscribe($email, $updateDataInSib, $subscriberStatus, $storeId);
                $this->debugLogger->info(__('Subscription synced by queue runner for email %1', $email));
            } catch (\Exception $e) {
                $this->debugLogger->error($e->getMessage());
            }
        } else {
            try {
                $updateDataInSib[ConfigurationHelper::SIB_CLIENT_ATTRIBUTE] = 0;
                $storeId = $subscriber->getStoreId();
                if ($storeId) {
                    $updateDataInSib[ConfigurationHelper::SIB_STORE_ATTRIBUTE] = $storeId;
                }
                $stores = $this->storeManager->getStores(true, false);
                foreach ($stores as $store) {
                    if ($store->getId() == $storeId) {
                        $storeView = $store->getName();
                    }
                }
                if (!empty($storeView)) {
                    $updateDataInSib[ConfigurationHelper::SIB_LANG_ATTRIBUTE] = $storeView;
                }
                $this->debugLogger->info(__('Subscription will synced with status %1', $subscriberStatus));
                $this->subscriptionManager->subscribe($email, $updateDataInSib, $subscriberStatus, $storeId);
                $this->debugLogger->info(__('Subscription synced by queue runner for email %1', $email));
            } catch (\Exception $e) {
                $this->debugLogger->error($e->getMessage());
            }
        }
    }

    /**
     * @param $storeId
     */
    private function syncContacts($storeId) {

        if ($storeId !== 0) {
            /**
             * @var Subscriber[] $subscribers
             */
            $subscribers = $this->subscriberCollectionFactory->create()
                ->addFieldToFilter('store_id', $storeId)
                ->addFieldToFilter('subscriber_status', ['in' => ConfigurationHelper::ALLOWED_SUBSCRIBER_STATUSES])
                ->getItems();
        } else {
            /**
             * @var Subscriber[] $subscribers
             */
            $subscribers = $this->subscriberCollectionFactory->create()
                ->addFieldToFilter('subscriber_status', ['in' => ConfigurationHelper::ALLOWED_SUBSCRIBER_STATUSES])
                ->getItems();
        }

        foreach ($subscribers as $subscriber) {
            $this->syncContact($subscriber);
        }
    }
}
