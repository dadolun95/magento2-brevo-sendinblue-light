<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license    This code is licensed under MIT license (see LICENSE for details)
 */

namespace Dadolun\SibContactSync\Block\Adminhtml\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Class SyncContacts
 * @package Dadolun\SibContactSync\Block\Adminhtml\Config
 */
class SyncContacts extends Field
{

    /**
     * SyncContacts constructor.
     * @param Context $context
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context $context,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    )
    {
        parent::__construct($context, $data, $secureRenderer);
    }

    /**
     * Set template to itself
     *
     * @return $this|Field
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
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = !empty($originalData['button_label']) ? $originalData['button_label'] : __('Sync contacts');
        $storeParams = "";
        if ($this->_request->getParam("store")) {
            $storeParams = 'store/' . $this->_request->getParam("store");
        }

        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('sibcontactsync/config/synccontacts') . $storeParams,
            ]
        );

        return $this->_toHtml();
    }
}
