<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license    This code is licensed under MIT license (see LICENSE for details)
 */

namespace Dadolun\SibContactSync\Api\Data;

/**
 * Interface SibCountryCodeInterface
 * @package Dadolun\SibContactSync\Api\Data
 */
interface SibCountryCodeInterface
{
    const ID = 'sendinblue_country_code_id';

    const ISO_CODE = 'iso_code';

    const COUNTRY_PREFIX = 'country_prefix';

    const CREATED_TIME = 'created_time';

    const UPDATED_TIME = 'created_time';

    /**
     * @return integer
     */
    public function getId();

    /**
     * @return string
     */
    public function getIsoCode();

    /**
     * @return string
     */
    public function getCountryPrefix();

    /**
     * @return string
     */
    public function getCreatedTime();

    /**
     * @return string
     */
    public function getUpdatedTime();

    /**
     * @param string $isoCode
     * @return SibCountryCodeInterface
     */
    public function setIsoCode($isoCode);

    /**
     * @param string $countryPrefix
     * @return SibCountryCodeInterface
     */
    public function setCountryPrefix($countryPrefix);

    /**
     * @param $date
     * @return SibCountryCodeInterface
     */
    public function setUpdatedTime($date);
}
