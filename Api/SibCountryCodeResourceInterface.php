<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2021 Dadolun (https://github.com/dadolun95)
 * @license     Open Source License
 */

namespace Dadolun\SibContactSync\Api;

/**
 * Interface SibCountryCodeResourceInterface
 * @package Dadolun\SibContactSync\Api
 */
interface SibCountryCodeResourceInterface
{
    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return mixed
     */
    public function save(\Magento\Framework\Model\AbstractModel $object);

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param $value
     * @param null $field
     * @return mixed
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null);

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return mixed
     */
    public function delete(\Magento\Framework\Model\AbstractModel $object);
}
