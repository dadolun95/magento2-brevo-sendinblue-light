<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license     Open Source License
 */

namespace Dadolun\SibContactSync\Api;

use Magento\Framework\Model\AbstractModel;

/**
 * Interface SibCountryCodeResourceInterface
 * @package Dadolun\SibContactSync\Api
 */
interface SibCountryCodeResourceInterface
{
    /**
     * @param AbstractModel $object
     * @return mixed
     */
    public function save(AbstractModel $object);

    /**
     * @param AbstractModel $object
     * @param $value
     * @param null $field
     * @return mixed
     */
    public function load(AbstractModel $object, $value, $field = null);

    /**
     * @param AbstractModel $object
     * @return mixed
     */
    public function delete(AbstractModel $object);
}
