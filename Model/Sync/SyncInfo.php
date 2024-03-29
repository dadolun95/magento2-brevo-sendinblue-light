<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license    This code is licensed under MIT license (see LICENSE for details)
 */

namespace Dadolun\SibContactSync\Model\Sync;

use Dadolun\SibContactSync\Api\Data\SyncInfoInterface;

/**
 * Class SyncInfo
 * @package Dadolun\SibContactSync\Model\Sync
 */
class SyncInfo implements SyncInfoInterface
{
    private $store;
    private $type;
    private $email;

    /**
     * @return int|null
     */
    public function getStoreId() {
        return $this->store;
    }

    /**
     * Set sib store sync
     *
     * @param int $store
     * @return void
     */
    public function setStoreId($store) {
        $this->store = $store;
    }

    /**
     * @return string|null
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email) {
        $this->email = $email;
    }
}
