<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2021 Dadolun (https://github.com/dadolun95)
 * @license     Open Source License
 */

namespace Dadolun\SibContactSync\Model;

use \Dadolun\SibCore\Helper\SibClientConnector;
use \Dadolun\SibContactSync\Helper\Configuration;
use \Dadolun\SibCore\Model\SibClient;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory as NewsletterSubscriberCollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Email\Model\Template\Config\Reader as EmailTemplateReader;
use \Dadolun\SibContactSync\Model\SibCountryCodeRepository;
use Magento\Newsletter\Model\Subscriber;

/**
 * Class SubscriptionManager
 * @package Dadolun\SibContactSync\Model
 */
class SubscriptionManager {

    const DOUBLE_OPTIN_CODE = 'DOUBLE_OPT-IN';
    const DOUBLE_OPTIN_ACTIVE_VALUE = '2';
    const SUBSCRIPTION_ACTION = 'SUBSCRIBE_BY_PLUGIN';
    const SUBSCRIPTION_ACTION_NAME = 'Magento2';

    /**
     * @var NewsletterSubscriberCollectionFactory
     */
    protected $subscriberCollectionFactory;

    /**
     * @var SibClientConnector
     */
    protected $sibClientConnector;

    /**
     * @var Configuration
     */
    protected $configHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var SibCountryCodeRepository
     */
    protected $sibCountryCodeRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var EmailTemplateReader
     */
    protected $emailTemplateReader;

    /**
     * SubscriptionManager constructor.
     * @param NewsletterSubscriberCollectionFactory $subscriberCollectionFactory
     * @param SibClientConnector $sibClientConnector
     * @param Configuration $configHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Dadolun\SibContactSync\Model\SibCountryCodeRepository $sibCountryCodeRepository
     * @param StoreManagerInterface $storeManager
     * @param EmailTemplateReader $emailTemplateReader
     */
    public function __construct(
        NewsletterSubscriberCollectionFactory $subscriberCollectionFactory,
        SibClientConnector $sibClientConnector,
        Configuration $configHelper,
        CustomerRepositoryInterface $customerRepository,
        SibCountryCodeRepository $sibCountryCodeRepository,
        StoreManagerInterface $storeManager,
        EmailTemplateReader $emailTemplateReader
    ) {
        $this->subscriberCollectionFactory = $subscriberCollectionFactory;
        $this->sibClientConnector = $sibClientConnector;
        $this->configHelper = $configHelper;
        $this->customerRepository = $customerRepository;
        $this->sibCountryCodeRepository = $sibCountryCodeRepository;
        $this->storeManager = $storeManager;
        $this->emailTemplateReader = $emailTemplateReader;
    }

    /**
     * @param $email
     * @param $data
     * @param int $subscriberStatus
     * @return bool
     * @throws \SendinBlue\Client\ApiException
     */
    public function subscribe($email, $data, $subscriberStatus = Subscriber::STATUS_SUBSCRIBED)
    {
        if (!$this->configHelper->isSyncEnabled()) {
            return false;
        }

        $sendinConfirmType = $this->configHelper->getContactValue('confirm_type');
        if ($sendinConfirmType === Configuration::SIB_DUBLE_OPTIN_CONFIRM && ($subscriberStatus === Subscriber::STATUS_UNCONFIRMED || $subscriberStatus === Subscriber::STATUS_NOT_ACTIVE)) {
            $listId = $this->configHelper->getContactValue('optin_list_id');
            $updateDataInSib[self::DOUBLE_OPTIN_CODE] = self::DOUBLE_OPTIN_ACTIVE_VALUE;
        } else {
            $listId = $this->configHelper->getContactValue('selected_list_data');
        }

        /**
         * @var \Dadolun\SibCore\Model\SibClient $sibClient
         */
        $apiKey = $this->configHelper->getValue('api_key_v3');
        $sibClient = $this->sibClientConnector->createSibClient($apiKey);
        $sibClient->setApiKey($apiKey);

        $sibData = array(
            "attributes" => $data,
            "listIds" => array_map('intval', explode('|', $listId ?? ''))
        );

        try {
            $sibUser = $sibClient->getUser($email);
        } catch (\Exception $e) {
            $sibUser = [];
        }

        if (!empty($sibUser)) {
            if ($sendinConfirmType === Configuration::SIB_DUBLE_OPTIN_CONFIRM && $subscriberStatus === Subscriber::STATUS_SUBSCRIBED) {
                $sibData["unlinkListIds"] = array_map('intval', explode('|', $this->configHelper->getContactValue('optin_list_id') ?? ''));
            }
            $sibClient->updateUser($email, $sibData);
        } else {
            $sibData["email"] = $email;
            $sibData["updateEnabled"] = true;
            $sibData["emailBlacklisted"] = false;
            $sibData["internalUserHistory"] = array(
                "action" => self::SUBSCRIPTION_ACTION,
                "id" => 1,
                "name" => self::SUBSCRIPTION_ACTION_NAME
            );
            $sibClient->createUser($sibData);
        }
    }

    /**
     * @param $email
     * @return bool
     * @throws \SendinBlue\Client\ApiException
     */
    public function unsubscribe($email) {
        if (!$this->configHelper->isSyncEnabled()) {
            return false;
        }

        /**
         * @var \Dadolun\SibCore\Model\SibClient $sibClient
         */
        $sibClient = $this->sibClientConnector->createSibClient();
        $apiKey = $this->configHelper->getValue('api_key_v3');
        $sibClient->setApiKey($apiKey);
        $sibClient->updateUser($email, array('emailBlacklisted' => true, "smsBlacklisted" => true));
    }

    /**
     * Get final sms value after add and check country code
     *
     * @param $number
     * @param $callPrefix
     * @return string|string[]|null
     */
    public function checkMobileNumber($number, $callPrefix)
    {
        $number = preg_replace('/\s+/', '', $number);
        $charOne = substr($number, 0, 1);
        $charTwo = substr($number, 0, 2);

        if (preg_match('/^' . $callPrefix . '/', $number)) {
            return '00' . $number;
        } elseif ($charOne == '0' && $charTwo != '00') {
            if (preg_match('/^0' . $callPrefix . '/', $number)) {
                return '00' . substr($number, 1);
            } else {
                return '00' . $callPrefix . substr($number, 1);
            }
        } elseif ($charTwo == '00') {
            if (preg_match('/^00' . $callPrefix . '/', $number)) {
                return $number;
            } else {
                return '00' . $callPrefix . substr($number, 2);
            }
        } elseif ($charOne == '+') {
            if (preg_match('/^\+' . $callPrefix . '/', $number)) {
                return '00' . substr($number, 1);
            } else {
                return '00' . $callPrefix . substr($number, 1);
            }
        } elseif ($charOne != '0') {
            return '00' . $callPrefix . $number;
        }
    }

    /**
     * @param $email
     * @return string|bool
     */
    public function checkSubscriberStatus($email) {
        /**
         * @var \Magento\Newsletter\Model\Subscriber $resultSubscriber
         */
        $subscriber = $this->subscriberCollectionFactory->create()
            ->addFieldToSelect('subscriber_id')
            ->addFieldToSelect('subscriber_status')
            ->addFieldToFilter('subscriber_email', $email)
            ->getFirstItem();

        return ( $subscriber->getSubscriberId() ? $subscriber->getSubscriberStatus() : false);
    }

    /**
     * @param $customerId
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomer($customerId) {
        return $this->customerRepository->getById($customerId);
    }

    /**
     * @param $isoCode
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCountryCode($isoCode)
    {
        $sibCountryCode = $this->sibCountryCodeRepository->getByIsoCode($isoCode);
        return $sibCountryCode->getCountryPrefix();
    }
}
