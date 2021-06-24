<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2021 Dadolun (https://github.com/dadolun95)
 * @license     Open Source License
 */

namespace Dadolun\SibContactSync\Block\Adminhtml\Config;

/**
 * Class SyncContacts
 * @package Dadolun\SibContactSync\Block\Adminhtml\Config
 */
class SyncContacts extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * Set template to itself
     *
     * @return $this|\Magento\Config\Block\System\Config\Form\Field
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('Dadolun_SibContactSync::config/sync-contacts.phtml');
        }
        return $this;
    }

    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = !empty($originalData['button_label']) ? $originalData['button_label'] : __('Sync contacts');
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('sibcontactsync/config/synccontacts'),
            ]
        );

        return $this->_toHtml();
    }
}
