<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2021 Dadolun (https://github.com/dadolun95)
 * @license     Open Source License
 */

namespace Dadolun\SibContactSync\Controller\Adminhtml\Config;

use Dadolun\SibContactSync\Model\SubscriptionManager;
use Dadolun\SibCore\Helper\DebugLogger;
use Magento\Customer\Api\AddressRepositoryInterface as CustomerAddressRepository;
use Magento\Framework\Controller\Result\JsonFactory;
use \Dadolun\SibContactSync\Helper\Configuration as ConfigurationHelper;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory as SubscriberCollectionFactory;
use Magento\Newsletter\Model\Subscriber;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class SyncContacts
 * @package Dadolun\SibContactSync\Controller\Adminhtml\Config
 */
class SyncContacts extends \Magento\Backend\App\Action
{

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var SubscriberCollectionFactory
     */
    protected $subscriberCollectionFactory;

    /**
     * @var ConfigurationHelper
     */
    protected $configHelper;

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
     * SyncContacts constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param SubscriberCollectionFactory $subscriberCollectionFactory
     * @param ConfigurationHelper $configHelper
     * @param SubscriptionManager $subscriptionManager
     * @param CustomerAddressRepository $customerAddressRepository
     * @param StoreManagerInterface $storeManager
     * @param DebugLogger $debugLogger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonFactory $resultJsonFactory,
        SubscriberCollectionFactory $subscriberCollectionFactory,
        ConfigurationHelper $configHelper,
        SubscriptionManager $subscriptionManager,
        CustomerAddressRepository $customerAddressRepository,
        StoreManagerInterface $storeManager,
        DebugLogger $debugLogger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->subscriberCollectionFactory = $subscriberCollectionFactory;
        $this->configHelper = $configHelper;
        $this->subscriptionManager = $subscriptionManager;
        $this->customerAddressRepository = $customerAddressRepository;
        $this->storeManager = $storeManager;
        $this->debugLogger = $debugLogger;
    }

    /**
     * Check whether vat is valid
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $result = $this->syncContacts();
            $result['valid'] = true;
            $result['message'] = __('Contacts correctly synced');
        } catch (\Exception $e) {
            $result['valid'] = false;
            $result['message'] = __('Something went wrong syncing your contacts, enable the debug logger and check api responses');
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData([
            'valid' => (int)$result['valid'],
            'message' => $result['message'],
        ]);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \SendinBlue\Client\ApiException
     */
    private function syncContacts() {
        /**
         * @var Subscriber[] $subscribers
         */
        $subscribers = $this->subscriberCollectionFactory->create()
            ->addFieldToFilter('subscriber_status', ['in' => ConfigurationHelper::ALLOWED_SUBSCRIBER_STATUSES])
            ->getItems();
        foreach ($subscribers as $subscriber) {
            $subscriberStatus = $subscriber->getSubscriberStatus();
            $email = $subscriber->getEmail();
            if ($subscriber->getCustomerId()) {
                $this->debugLogger->info(__('Subscribe user (admin sync)'));
                try {
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
                } catch (\Exception $e) {
                    $this->debugLogger->error($e->getMessage());
                }
            } else {
                $this->debugLogger->info(__('Subscribe user (admin sync)'));
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
                    $this->subscriptionManager->subscribe($email, $updateDataInSib, $subscriberStatus);
                } catch (\Exception $e) {
                    $this->debugLogger->error($e->getMessage());
                }
            }
        }
    }
}
