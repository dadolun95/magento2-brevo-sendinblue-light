<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2021 Dadolun (https://github.com/dadolun95)
 * @license     Open Source License
 */

namespace Dadolun\SibContactSync\Model\Config\Source;

use \Dadolun\SibCore\Helper\SibClientConnector;
use \Dadolun\SibCore\Model\Config\Backend\ApiKey;
use \Dadolun\SibCore\Helper\Configuration;

/**
 * Class SelectedListData
 * @package Dadolun\SibContactSync\Model\Config\Source
 */
class SelectedListData implements \Magento\Framework\Option\ArrayInterface
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
     * SelectedListData constructor.
     * @param SibClientConnector $sibClientConnector
     * @param Configuration $configHelper
     */
    public function __construct(
        SibClientConnector $sibClientConnector,
        Configuration $configHelper
    ) {
       $this->sibClientConnector = $sibClientConnector;
       $this->configHelper = $configHelper;
    }

    /**
     * @return array
     * @throws \SendinBlue\Client\ApiException
     */
    public function toOptionArray()
    {
        return $this->getSibLists();
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $sibListsArray = [];
        $apiKey = $this->configHelper->getValue('api_key_v3');
        if (!is_null($apiKey) && $apiKey !== '' && $this->configHelper->getValue('api_key_status')) {
            $sibLists = $this->getSibLists();
            foreach ($sibLists as $list) {
                $sibListsArray[$list['value']] = $list['label'];
            }
        }
        return $sibListsArray;
    }

    /**
     * @return array
     * @throws \SendinBlue\Client\ApiException
     */
    private function getSibLists() {
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
        } else {
            $this->sibLists = [];
        }
        return $this->sibLists;
    }
}
