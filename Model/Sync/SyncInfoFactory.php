<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license    This code is licensed under MIT license (see LICENSE for details)
 */

namespace Dadolun\SibContactSync\Model\Sync;

use Dadolun\SibContactSync\Api\Data\SyncInfoInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class SyncInfoFactory
 * @package Dadolun\SibContactSync\Model\Sync
 */
class SyncInfoFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * SyncInfoFactory constructor.
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    )
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param $storeId
     * @param $type
     * @param $data
     * @return SyncInfoInterface
     */
    public function create($storeId, $type, $email)
    {
        /**
         * @var SyncInfoInterface $syncInfo
         */
        $syncInfo = $this->objectManager->create(SyncInfoInterface::class);
        $syncInfo->setStoreId($storeId);
        $syncInfo->setType($type);
        $syncInfo->setEmail($email);
        return $syncInfo;
    }
}
