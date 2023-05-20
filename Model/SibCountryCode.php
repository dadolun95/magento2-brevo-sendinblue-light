<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license     Open Source License
 */

namespace Dadolun\SibContactSync\Model;

use Magento\Framework\Model\AbstractModel;
use \Dadolun\SibContactSync\Api\SibCountryCodeResourceInterface;
use \Dadolun\SibContactSync\Api\Data\SibCountryCodeInterface;

/**
 * Class SibCountryCode
 * @package Dadolun\SibContactSync\Model
 */
class SibCountryCode extends AbstractModel implements SibCountryCodeInterface {

    const ENTITY = 'sendinblue_country_codes';

    /**
     * @var array
     */
    protected $interfaceAttributes = [
        SibCountryCodeInterface::ID,
        SibCountryCodeInterface::ISO_CODE,
        SibCountryCodeInterface::COUNTRY_PREFIX,
        SibCountryCodeInterface::CREATED_TIME,
        SibCountryCodeInterface::UPDATED_TIME
    ];

    protected function _construct(){
        $this->_init(SibCountryCodeResourceInterface::class);
    }

    /**
     * @return int|null
     */
    public function getId() {
        return $this->getData(self::ID);
    }

    /**
     * @return string|null
     */
    public function getIsoCode() {
        return $this->getData(self::ISO_CODE);
    }

    /**
     * @return string|null
     */
    public function getCountryPrefix() {
        return $this->getData(self::COUNTRY_PREFIX);
    }

    /**
     * @return string
     */
    public function getCreatedTime() {
        return $this->getData(self::CREATED_TIME);
    }

    /**
     * @return integer
     */
    public function getUpdatedTime() {
        return $this->getData(self::UPDATED_TIME);
    }

    /**
     * @param string $isoCode
     * @return SibCountryCodeInterface|SibCountryCode
     */
    public function setIsoCode($isoCode) {
        return $this->setData(self::ISO_CODE, $isoCode);
    }

    /**
     * @param string $countryPrefix
     * @return SibCountryCodeInterface|SibCountryCode
     */
    public function setCountryPrefix($countryPrefix) {
        return $this->setData(self::COUNTRY_PREFIX, $countryPrefix);
    }

    /**
     * @param $date
     * @return SibCountryCodeInterface|SibCountryCode
     */
    public function setUpdatedTime($date) {
        return $this->setData(self::UPDATED_TIME, $date);
    }
}
