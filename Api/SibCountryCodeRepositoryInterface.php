<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2021 Dadolun (https://github.com/dadolun95)
 * @license     Open Source License
 */

namespace Dadolun\SibContactSync\Api;

/**
 * Interface SibCountryCodeRepositoryInterface
 * @package Dadolun\SibContactSync\Api
 */
interface SibCountryCodeRepositoryInterface
{
    /**
     * @param \Magento\Framework\Model\AbstractModel $menu
     * @return \Dadolun\SibContactSync\Api\Data\SibCountryCodeInterface|null
     */
    public function save(\Magento\Framework\Model\AbstractModel $menu);

    /**
     * @param $countryCodeId
     * @return \Dadolun\SibContactSync\Api\Data\SibCountryCodeInterface|null
     */
    public function getById($countryCodeId);

    /**
     * @param $isoCode
     * @return \Dadolun\SibContactSync\Api\Data\SibCountryCodeInterface|null
     */
    public function getByIsoCode($isoCode);

    /**
     * @param \Magento\Framework\Model\AbstractModel $menu
     * @return void
     */
    public function delete(\Magento\Framework\Model\AbstractModel $menu);
}
