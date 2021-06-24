<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2021 Dadolun (https://github.com/dadolun95)
 * @license     Open Source License
 */

namespace Dadolun\SibContactSync\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use \Dadolun\SibContactSync\Model\SubscriptionManager;
use Magento\Customer\Api\AddressRepositoryInterface as CustomerAddressRepository;
use Magento\Store\Model\StoreManagerInterface;
use \Dadolun\SibCore\Helper\DebugLogger;
use \Dadolun\SibContactSync\Helper\Configuration as ConfigurationHelper;

/**
 * Class NewsletterSubscription
 * @package Dadolun\SibContactSync\Observer
 */
class NewsletterSubscription implements ObserverInterface
{

    /**
     * @var SubscriptionManager
     */
    protected $subscriptionManager;

    /**
     * @var CustomerAddressRepository
     */
    protected $customerAddressRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var DebugLogger
     */
    protected $debugLogger;

    /**
     * @var ConfigurationHelper
     */
    protected $configHelper;

    /**
     * NewsletterSubscription constructor.
     * @param SubscriptionManager $subscriptionManager
     * @param CustomerAddressRepository $customerAddressRepository
     * @param StoreManagerInterface $storeManager
     * @param DebugLogger $debugLogger
     * @param ConfigurationHelper $configHelper
     */
    public function __construct(
        SubscriptionManager $subscriptionManager,
        CustomerAddressRepository $customerAddressRepository,
        StoreManagerInterface $storeManager,
        DebugLogger $debugLogger,
        ConfigurationHelper $configHelper
    )
    {
        $this->subscriptionManager = $subscriptionManager;
        $this->customerAddressRepository = $customerAddressRepository;
        $this->storeManager = $storeManager;
        $this->debugLogger = $debugLogger;
        $this->configHelper = $configHelper;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \SendinBlue\Client\ApiException
     * @throws \Zend_Mail_Exception
     */
    public function execute(Observer $observer)
    {
        try {
            $this->debugLogger->info(__('NewsletterSubscription observer START'));
            $updateDataInSib = [];
            /**
             * @var \Magento\Newsletter\Model\Subscriber $subscriber
             */
            $subscriber = $observer->getEvent()->getSubscriber();
            $email = $subscriber->getSubscriberEmail();
            $subscriberStatus = $subscriber->getSubscriberStatus();
            $subscriptionSyncStatus = $this->configHelper->isSyncEnabled();
            $this->debugLogger->info(__('Try update subscription for user with email: %1', $email));
            if ($subscriptionSyncStatus) {
                if ($subscriber->getCustomerId() && in_array($subscriberStatus, ConfigurationHelper::ALLOWED_SUBSCRIBER_STATUSES)) {
                    $this->debugLogger->info(__('Subscribe user by runtime'));
                    /**
                     * @var \Magento\Customer\Api\Data\CustomerInterface $customer
                     */
                    $customer = $this->subscriptionManager->getCustomer($subscriber->getCustomerId());
                    $billingId = !empty($customer->getDefaultBilling()) ? $customer->getDefaultBilling() : '';
                    $firstName = $customer->getFirstname();
                    $lastName = $customer->getLastname();
                    $storeId = $customer->getStoreId();
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
                    $this->subscriptionManager->subscribe($email, $updateDataInSib, $subscriberStatus);
                } else {
                    $this->debugLogger->info($subscriberStatus);
                    if (in_array($subscriberStatus, ConfigurationHelper::ALLOWED_SUBSCRIBER_STATUSES)) {
                        $this->debugLogger->info(__('Subscribe user by runtime'));
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
                        $this->subscriptionManager->subscribe($email, $updateDataInSib, $subscriberStatus);
                    } else {
                        $this->debugLogger->info(__('Unsubscribe user by runtime'));
                        $this->subscriptionManager->unsubscribe($email);
                    }
                }
            } else {
                $this->debugLogger->info(__('Contact Sync is not enabled'));
            }
            $this->debugLogger->info(__('NewsletterSubscription observer END'));
        } catch (\Exception $e) {
            $this->debugLogger->error(__('Error: ') . $e->getMessage());
        }
    }
}
