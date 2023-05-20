<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license     Open Source License
 */

namespace Dadolun\SibContactSync\Model\Config\Backend;

use \Dadolun\SibCore\Helper\SibClientConnector;
use \Dadolun\SibCore\Model\SibClient;
use \Dadolun\SibContactSync\Helper\Configuration;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Newsletter\Model\Subscriber;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class ApiKey
 * @package Dadolun\SibCore\Model\Config\Backend
 */
class ConfirmType extends Value
{

    const OPTIN_LIST_NAME = 'Temp - DOUBLE OPTIN';
    const OPTIN_FORM_NAME = 'Magento Optin Form';

    const NORMAL_TYPE_CONTACT_ATTRIBUTES_KEY = "normal";

    /**
     * @var SibClientConnector
     */
    protected $sibClientConnector;

    /**
     * @var Configuration
     */
    protected $configHelper;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var array
     */
    protected $contactNormalAttributes;

    /**
     * ConfirmType constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param SibClientConnector $sibClientConnector
     * @param Configuration $configHelper
     * @param ManagerInterface $messageManager
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $contactNormalAttributes
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        SibClientConnector $sibClientConnector,
        Configuration $configHelper,
        ManagerInterface $messageManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $contactNormalAttributes = [],
        array $data = []
    ) {
        $this->sibClientConnector = $sibClientConnector;
        $this->configHelper = $configHelper;
        $this->messageManager = $messageManager;
        $this->contactNormalAttributes = $contactNormalAttributes;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return Value|void
     */
    public function beforeSave()
    {
        $this->_dataSaveAllowed = false;
        $value = (string)$this->getValue();
        $apiKey = $this->configHelper->getValue('api_key_v3');
        if (!is_null($apiKey) && $apiKey !== '') {

            try {
                /**
                 * @var SibClient $sibClient
                 */
                $sibClient = $this->sibClientConnector->createSibClient($apiKey);
                $sibClient->setApiKey($apiKey);
                $sibClient->getAccount();
            } catch (\SendinBlue\Client\ApiException $e) {
                $this->messageManager->addErrorMessage(__('An error occurred retrieving Sendinblue Account. Please check your API key.'));
                $this->_dataSaveAllowed = false;
                return;
            }

            if (SibClient::RESPONSE_CODE_OK == $sibClient->getLastResponseCode()) {
                if ($value === Configuration::SIB_DUBLE_OPTIN_CONFIRM) {
                    try {
                        $resOptin = $this->checkFolderListDoubleoptin($sibClient);
                        $this->configHelper->setPathValue(Subscriber::XML_PATH_CONFIRMATION_FLAG, 1);
                    } catch (\Exception $e) {
                        $this->_dataSaveAllowed = false;
                        return;
                    }
                    if (isset($resOptin['optin_id'])) {
                        $this->configHelper->setContactValue('optin_list_id', $resOptin['optin_id']);
                        $this->_dataSaveAllowed = true;
                    }
                } else {
                    try {
                        $this->configHelper->setPathValue(Subscriber::XML_PATH_CONFIRMATION_FLAG, $value);
                        $this->_dataSaveAllowed = true;
                    } catch (\Exception $e) {
                        $this->_dataSaveAllowed = false;
                        return;
                    }
                }

                try {
                    $contactAttributes =  $sibClient->getAttributes()['attributes'];
                    $contactAttributesList = array();
                    foreach($contactAttributes as $attribute) {
                        $contactAttributesList[] = $attribute['name'];
                    }
                    foreach ($this->contactNormalAttributes as $contactAttribute => $type) {
                        if (!in_array($contactAttribute, $contactAttributesList)) {
                            $sibClient->createAttribute(self::NORMAL_TYPE_CONTACT_ATTRIBUTES_KEY, $contactAttribute, array("type" => $type));
                        }
                    }
                    $this->_dataSaveAllowed = true;
                } catch (\Exception $e) {
                    $this->_dataSaveAllowed = false;
                    return;
                }
            }
        } else {
            $this->_dataSaveAllowed = false;
            return;
        }
        $this->setValue($value);
    }

    /**
     * @param $sibClient
     * @return array|bool
     */
    private function checkFolderListDoubleoptin($sibClient)
    {
        $dataApi = array(
            "offset" => 0,
            "limit" => 50
        );
        $folderResp = $sibClient->getFolders($dataApi);
        $sibOptinListArray = array();
        $folders = false;
        if (!empty($folderResp['folders'])) {
            foreach ($folderResp['folders'] as $value) {
                if ($value['name'] === self::OPTIN_FORM_NAME) {
                    $listResp = $sibClient->getAllLists($value['id']);
                    if (!empty($listResp['lists']) && $listResp['count']) {
                        foreach ($listResp['lists'] as $val) {
                            if ($val['name'] === self::OPTIN_LIST_NAME) {
                                $sibOptinListArray['optin_id'] = $val['id'];
                            }
                        }
                    }
                }
            }
            if (count($sibOptinListArray) > 0) {
                $folders = $sibOptinListArray;
            } else {
                $folders = false;
            }
        }
        if ($folders === false || !isset($folders['optin_id'])) {
            $data = ['name' => self::OPTIN_FORM_NAME];
            $folderRes = $sibClient->createFolder($data);
            $folderId = $folderRes['id'];

            $data = [
                'name' => self::OPTIN_LIST_NAME,
                'folderId' => $folderId
            ];
            $listResult = $sibClient->createList($data);
            $listId = $listResult['id'];
            $folders['optin_id'] = $listId;
        }
        return $folders;
    }
}
