<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license     Open Source License
 */

namespace Dadolun\SibContactSync\Model\ResourceModel\SibCountryCode;

use \Dadolun\SibContactSync\Model\ResourceModel\SibCountryCode as SibCountryCodeResource;
use \Dadolun\SibContactSync\Model\SibCountryCode;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Dadolun\SibContactSync\Model\ResourceModel\SibCountryCode
 */
class Collection extends AbstractCollection {

    protected function _construct() {
        $this->_init(
            SibCountryCode::class,
            SibCountryCodeResource::class
        );
    }
}
