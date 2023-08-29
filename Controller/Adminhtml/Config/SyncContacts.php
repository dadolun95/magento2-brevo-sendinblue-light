<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license    This code is licensed under MIT license (see LICENSE for details)
 */

namespace Dadolun\SibContactSync\Controller\Adminhtml\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Store\Model\StoreManagerInterface;
use \Dadolun\SibContactSync\Api\Data\SyncInfoInterface;
use \Dadolun\SibContactSync\Model\Sync\SyncInfoFactory;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Class SyncContacts
 * @package Dadolun\SibContactSync\Controller\Adminhtml\Config
 */
class SyncContacts extends Action
{

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var SyncInfoFactory
     */
    protected $syncInfoFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var PublisherInterface
     */
    protected $messagePublisher;

    /**
     * SyncContacts constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param SyncInfoFactory $syncInfoFactory
     * @param PublisherInterface $messagePublisher
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        SyncInfoFactory $syncInfoFactory,
        PublisherInterface $messagePublisher,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->syncInfoFactory = $syncInfoFactory;
        $this->messagePublisher = $messagePublisher;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        try {

            /** @var SyncInfoInterface $dataObject */
            $dataObject = $this->syncInfoFactory->create(
                $this->getRequest()->getParam("store") ? $this->getRequest()->getParam("store") : 0,
                SyncInfoInterface::TOTAL_SYNC_TYPE,
                ''
            );

            $this->messagePublisher->publish('sibSync.contact', $dataObject);
            $result['valid'] = true;
            $result['message'] = __('Subscribers sync is correctly added to queue. Please wait some time or check the logs (if enabled), data will\'be synchronized soon!');
        } catch (\Exception $e) {
            $result['valid'] = false;
            $result['message'] = __('Something went wrong syncing your contacts, enable the debug logger and check api responses');
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData([
            'valid' => (int)$result['valid'],
            'message' => $result['message'],
        ]);
    }
}
