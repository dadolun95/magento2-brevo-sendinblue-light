<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license    This code is licensed under MIT license (see LICENSE for details)
 */

namespace Dadolun\SibContactSync\Api;

use \Dadolun\SibContactSync\Api\Data\SibCountryCodeInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Interface SibCountryCodeRepositoryInterface
 * @package Dadolun\SibContactSync\Api
 */
interface SibCountryCodeRepositoryInterface
{
    /**
     * @param AbstractModel $menu
     * @return SibCountryCodeInterface|null
     */
    public function save(AbstractModel $menu);

    /**
     * @param $countryCodeId
     * @return SibCountryCodeInterface|null
     */
    public function getById($countryCodeId);

    /**
     * @param $isoCode
     * @return SibCountryCodeInterface|null
     */
    public function getByIsoCode($isoCode);

    /**
     * @param AbstractModel $menu
     * @return void
     */
    public function delete(AbstractModel $menu);
}
