<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2021 Dadolun (https://github.com/dadolun95)
 * @license     Open Source License
 */

namespace Dadolun\SibContactSync\Model\ResourceModel;

use Dadolun\SibContactSync\Api\SibCountryCodeResourceInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class SibCountryCode
 * @package Dadolun\SibContactSync\Model\ResourceModel
 */
class SibCountryCode extends AbstractDb implements SibCountryCodeResourceInterface {

    protected function _construct() {
        $this->_init('sendinblue_country_codes', 'sendinblue_country_code_id');
    }
}
