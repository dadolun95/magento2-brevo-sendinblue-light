<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2021 Dadolun (https://github.com/dadolun95)
 * @license     Open Source License
 */

namespace Dadolun\SibContactSync\Model\Config\Source;

/**
 * Class ConfirmType
 * @package Dadolun\SibContactSync\Model\Config\Source
 */
class SyncType implements \Magento\Framework\Option\ArrayInterface
{

    const SYNC = 'sync';
    const ASYNC = 'async';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ASYNC, 'label' => __('Async')],
            ['value' => self::SYNC, 'label' => __('Sync (can cause performance degradation)')],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::ASYNC => __('Async'),
            self::SYNC => __('Sync (can cause performance degradation)')
        ];
    }
}
