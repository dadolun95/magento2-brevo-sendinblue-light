<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license    This code is licensed under MIT license (see LICENSE for details)
 */

namespace Dadolun\SibContactSync\Model;

use \Dadolun\SibCore\Helper\SibClientConnector;
use \Dadolun\SibContactSync\Helper\Configuration;
use \Dadolun\SibCore\Model\SibClient;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory as NewsletterSubscriberCollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Email\Model\Template\Config\Reader as EmailTemplateReader;
use \Dadolun\SibContactSync\Model\SibCountryCodeRepository;
use Magento\Newsletter\Model\Subscriber;
use Magento\Framework\Message\ManagerInterface;

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
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * SubscriptionManager constructor.
     * @param NewsletterSubscriberCollectionFactory $subscriberCollectionFactory
     * @param SibClientConnector $sibClientConnector
     * @param Configuration $configHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param SibCountryCodeRepository $sibCountryCodeRepository
     * @param StoreManagerInterface $storeManager
     * @param EmailTemplateReader $emailTemplateReader
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        NewsletterSubscriberCollectionFactory $subscriberCollectionFactory,
        SibClientConnector $sibClientConnector,
        Configuration $configHelper,
        CustomerRepositoryInterface $customerRepository,
        SibCountryCodeRepository $sibCountryCodeRepository,
        StoreManagerInterface $storeManager,
        EmailTemplateReader $emailTemplateReader,
        ManagerInterface $messageManager
    ) {
        $this->subscriberCollectionFactory = $subscriberCollectionFactory;
        $this->sibClientConnector = $sibClientConnector;
        $this->configHelper = $configHelper;
        $this->customerRepository = $customerRepository;
        $this->sibCountryCodeRepository = $sibCountryCodeRepository;
        $this->storeManager = $storeManager;
        $this->emailTemplateReader = $emailTemplateReader;
        $this->messageManager = $messageManager;
    }

    /**
     * @param $email
     * @param $data
     * @param int $subscriberStatus
     * @return bool|void
     */
    public function subscribe($email, $data, $subscriberStatus = Subscriber::STATUS_SUBSCRIBED)
    {
        if (!$this->configHelper->isSyncEnabled()) {
            return false;
        }

        if ($subscriberStatus == Subscriber::STATUS_UNCONFIRMED || $subscriberStatus == Subscriber::STATUS_NOT_ACTIVE) {
            $listId = $this->configHelper->getContactValue('optin_list_id');
        } else {
            $listId = $this->configHelper->getContactValue('selected_list_data');
        }

        /**
         * @var SibClient $sibClient
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
        } catch (\SendinBlue\Client\ApiException $e) {
            $sibUser = [];
        }

        try {
            if (!empty($sibUser)) {
                if ($subscriberStatus === Subscriber::STATUS_SUBSCRIBED && in_array(intval($this->configHelper->getContactValue('optin_list_id')), $sibUser->getListIds())) {
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
        } catch (\SendinBlue\Client\ApiException $e) {
            $this->messageManager->addErrorMessage(__('An error occurred synchronizing user on Sendinblue.'));
        }
    }

    /**
     * @param $email
     * @return bool|void
     */
    public function unsubscribe($email) {
        if (!$this->configHelper->isSyncEnabled()) {
            return false;
        }

        try {
            /**
             * @var SibClient $sibClient
             */
            $sibClient = $this->sibClientConnector->createSibClient();
            $apiKey = $this->configHelper->getValue('api_key_v3');
            $sibClient->setApiKey($apiKey);
            $sibClient->updateUser($email, array('emailBlacklisted' => true, "smsBlacklisted" => true));
        } catch (\SendinBlue\Client\ApiException $e) {
            $this->messageManager->addErrorMessage(__('An error occurred synchronizing user on Sendinblue.'));
        }
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
     * @return CustomerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCustomer($customerId) {
        return $this->customerRepository->getById($customerId);
    }

    /**
     * @param $isoCode
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCountryCode($isoCode)
    {
        $sibCountryCode = $this->sibCountryCodeRepository->getByIsoCode($isoCode);
        return $sibCountryCode->getCountryPrefix();
    }
}
