<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license     Open Source License
 */

namespace Dadolun\SibContactSync\Model\Config\Source;

use Magento\Framework\Message\ManagerInterface;
use \Dadolun\SibCore\Helper\SibClientConnector;
use \Dadolun\SibCore\Model\Config\Backend\ApiKey;
use \Dadolun\SibContactSync\Helper\Configuration;
use Magento\Framework\Option\ArrayInterface;
use \SendinBlue\Client\ApiException;

/**
 * Class SelectedListData
 * @package Dadolun\SibContactSync\Model\Config\Source
 */
class SelectedListData implements ArrayInterface
{

    /**
     * @var SibClientConnector
     */
    protected $sibClientConnector;

    /**
     * @var array
     */
    protected $sibLists = [];

    /**
     * @var Configuration
     */
    protected $configHelper;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * SelectedListData constructor.
     * @param SibClientConnector $sibClientConnector
     * @param Configuration $configHelper
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        SibClientConnector $sibClientConnector,
        Configuration $configHelper,
        ManagerInterface $messageManager
    ) {
       $this->sibClientConnector = $sibClientConnector;
       $this->configHelper = $configHelper;
       $this->messageManager = $messageManager;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getSibLists();
    }

    /**
     * Get options in "key-value" format
     * @return array
     */
    public function toArray()
    {
        $sibListsArray = [];
        if ($this->configHelper->isSyncEnabled()) {
            $apiKey = $this->configHelper->getValue('api_key_v3');
            if (!is_null($apiKey) && $apiKey !== '' && $this->configHelper->getValue('api_key_status')) {
                $sibLists = $this->getSibLists();
                foreach ($sibLists as $list) {
                    $sibListsArray[$list['value']] = $list['label'];
                }
            }
        }
        return $sibListsArray;
    }

    /**
     * @return array
     */
    private function getSibLists() {
        $this->sibLists = [];
        if ($this->configHelper->isSyncEnabled()) {
            try {
                $apiKey = $this->configHelper->getValue('api_key_v3');
                if (!is_null($apiKey) && $apiKey !== '' && $this->configHelper->getValue('api_key_status')) {
                    if (empty($this->sibLists)) {
                        $sibClient = $this->sibClientConnector->createSibClient();
                        $sibClient->setApiKey($this->configHelper->getValue('api_key_v3'));
                        $sibLists = $sibClient->getAllLists()['lists'];
                        foreach ($sibLists as $list) {
                            $this->sibLists[] = [
                                'value' => $list['id'],
                                'label' => $list['name']
                            ];
                        }
                    }
                }
            } catch (ApiException $e) {
                $this->messageManager->addErrorMessage(__('An error occurred retrieving subscription lists from Sendinblue via API, please check your API key.'));
            }
        }
        return $this->sibLists;
    }
}
