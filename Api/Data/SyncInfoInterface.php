<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license    This code is licensed under MIT license (see LICENSE for details)
 */

namespace Dadolun\SibContactSync\Api\Data;

/**
 * Interface SyncInfoInterface
 * @package Dadolun\SibContactSync\Api\Data
 */
interface SyncInfoInterface
{

    const TOTAL_SYNC_TYPE = 'total';
    const PARTIAL_SYNC_TYPE = 'partial';

    /**
     * @return int|null
     */
    public function getStoreId();

    /**
     * Set sib store sync
     *
     * @param int $store
     * @return void
     */
    public function setStoreId($store);

    /**
     * @return string|null
     */
    public function getType();

    /**
     * Set sib sync type
     *
     * @param string $type
     * @return void
     */
    public function setType($type);

    /**
     * @return string|null
     */
    public function getEmail();

    /**
     * Set sib subscriber email
     *
     * @param string $email
     * @return void
     */
    public function setEmail($email);
}
