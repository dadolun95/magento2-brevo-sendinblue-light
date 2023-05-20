<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license     Open Source License
 */

namespace Dadolun\SibContactSync\Model\Config\Source;

/**
 * Class ConfirmType
 * @package Dadolun\SibContactSync\Model\Config\Source
 */
class ConfirmType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'nocon', 'label' => __('No Confirmation')],
            ['value' => 'simplemail', 'label' => __('Simple Confirmation')],
            ['value' => 'doubleoptin', 'label' => __('Double opt-in confirmation')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return ['nocon' => __('No Confirmation'), 'simplemail' => __('Simple Confirmation'), 'doubleoptin' => __('Double opt-in confirmation')];
    }
}
