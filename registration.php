<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license     Open Source License
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Dadolun_SibContactSync',
    isset($file) && realpath($file) == __FILE__ ? dirname($file) : __DIR__
);
