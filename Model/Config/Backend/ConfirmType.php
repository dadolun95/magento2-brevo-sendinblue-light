<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2021 Dadolun (https://github.com/dadolun95)
 * @license     Open Source License
 */

namespace Dadolun\SibContactSync\Model\Config\Backend;

use \Dadolun\SibCore\Helper\SibClientConnector;
use \Dadolun\SibCore\Model\SibClient;
use \Dadolun\SibContactSync\Helper\Configuration;
use Magento\Newsletter\Model\Subscriber;

/**
 * Class ApiKey
 * @package Dadolun\SibCore\Model\Config\Backend
 */
class ConfirmType extends \Magento\Framework\App\Config\Value
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
     * @var array
     */
    protected $contactNormalAttributes;

    /**
     * ConfirmType constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param SibClientConnector $sibClientConnector
     * @param Configuration $configHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $contactNormalAttributes
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        SibClientConnector $sibClientConnector,
        Configuration $configHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $contactNormalAttributes = [],
        array $data = []
    ) {
        $this->sibClientConnector = $sibClientConnector;
        $this->configHelper = $configHelper;
        $this->contactNormalAttributes = $contactNormalAttributes;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return \Magento\Framework\App\Config\Value|void
     * @throws \SendinBlue\Client\ApiException
     */
    public function beforeSave()
    {
        $this->_dataSaveAllowed = false;
        $value = (string)$this->getValue();
        $apiKey = $this->configHelper->getValue('api_key_v3');
        if (!is_null($apiKey) && $apiKey !== '') {
            /**
             * @var \Dadolun\SibCore\Model\SibClient $sibClient
             */
            $sibClient = $this->sibClientConnector->createSibClient($apiKey);
            $sibClient->setApiKey($apiKey);
            $sibClient->getAccount();

            if (SibClient::RESPONSE_CODE_OK == $sibClient->getLastResponseCode()) {
                if ($value === Configuration::SIB_DUBLE_OPTIN_CONFIRM) {
                    try {
                        $resOptin = $this->checkFolderListDoubleoptin($sibClient);
                        $this->configHelper->setPathValue(Subscriber::XML_PATH_CONFIRMATION_FLAG, 1);
                    } catch (\Exception $e) {
                        $this->_dataSaveAllowed = false;
                    }
                    if (isset($resOptin['optin_id'])) {
                        $this->configHelper->setContactValue('optin_list_id', $resOptin['optin_id']);
                        $this->_dataSaveAllowed = true;
                    }
                } else {
                    try {
                        $this->configHelper->setPathValue(Subscriber::XML_PATH_CONFIRMATION_FLAG, 0);
                        $this->_dataSaveAllowed = true;
                    } catch (\Exception $e) {
                        $this->_dataSaveAllowed = false;
                    }
                }

                try {
                    foreach ($this->contactNormalAttributes as $contactAttribute => $type) {
                        $sibClient->createAttribute(self::NORMAL_TYPE_CONTACT_ATTRIBUTES_KEY, $contactAttribute, array("type" => $type));
                    }
                    $this->_dataSaveAllowed = true;
                } catch (\Exception $e) {
                    $this->_dataSaveAllowed = false;
                }
            }
        } else {
            $this->_dataSaveAllowed = false;
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
                            var_dump($val['name']);
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
