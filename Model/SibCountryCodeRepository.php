<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2021 Dadolun (https://github.com/dadolun95)
 * @license     Open Source License
 */

namespace Dadolun\SibContactSync\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use \Dadolun\SibContactSync\Api\Data\SibCountryCodeInterface;
use \Dadolun\SibContactSync\Api\Data\SibCountryCodeInterfaceFactory;
use \Dadolun\SibContactSync\Api\SibCountryCodeRepositoryInterface;
use \Dadolun\SibContactSync\Api\SibCountryCodeResourceInterface;
use \Dadolun\SibContactSync\Model\ResourceModel\SibCountryCode\CollectionFactory as SibCountryCodeCollectionFactory;

/**
 * Class SibCountryCodeRepository
 * @package Dadolun\SibContactSync\Model
 */
class SibCountryCodeRepository implements SibCountryCodeRepositoryInterface {

    /**
     * @var SibCountryCodeInterfaceFactory
     */
    protected $sibCountryCodeFactory;

    /**
     * @var SibCountryCodeResourceInterface
     */
    protected $sibCountryCodeResource;

    /**
     * @var SibCountryCodeCollectionFactory
     */
    protected $sibCountryCodeCollectionFactory;

    /**
     * SibCountryCodeRepository constructor.
     * @param SibCountryCodeInterfaceFactory $sibCountryCodeFactory
     * @param SibCountryCodeResourceInterface $sibCountryCodeResource
     * @param SibCountryCodeCollectionFactory $sibCountryCodeCollectionFactory
     */
    public function __construct(
        SibCountryCodeInterfaceFactory $sibCountryCodeFactory,
        SibCountryCodeResourceInterface $sibCountryCodeResource,
        SibCountryCodeCollectionFactory $sibCountryCodeCollectionFactory
    ){
        $this->sibCountryCodeFactory = $sibCountryCodeFactory;
        $this->sibCountryCodeResource = $sibCountryCodeResource;
        $this->sibCountryCodeCollectionFactory = $sibCountryCodeCollectionFactory;
    }

    /**
     * @param $sibCountryCodeId
     * @return \Dadolun\SibContactSync\Api\Data\SibCountryCodeInterface|ResourceModel\SibCountryCode\Collection|null
     * @throws NoSuchEntityException
     */
    public function getById($sibCountryCodeId)
    {
        $sibCountryCode = $this->sibCountryCodeFactory->create();
        $sibCountryCode->load($sibCountryCodeId);
        if (!$sibCountryCode->getId()) {
            throw new NoSuchEntityException(
                __("The country code that was requested doesn't exist. Verify the id and try again.")
            );
        }
        return $sibCountryCode;
    }

    /**
     * @param $isoCode
     * @return \Dadolun\SibContactSync\Api\Data\SibCountryCodeInterface|ResourceModel\SibCountryCode\Collection|null
     * @throws NoSuchEntityException
     */
    public function getByIsoCode($isoCode)
    {
        $sibCountryCode = $this->sibCountryCodeCollectionFactory->create()
            ->addFieldToFilter(SibCountryCodeInterface::ISO_CODE, $isoCode)
            ->getFirstItem();

        if (!$sibCountryCode->getId()) {
            throw new NoSuchEntityException(
                __("The country code that was requested doesn't exist. Verify the id and try again.")
            );
        }
        return $sibCountryCode;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $menu
     * @return \Magento\Framework\Model\AbstractModel|SibCountryCodeInterface|null
     * @throws CouldNotSaveException
     */
    public function save(\Magento\Framework\Model\AbstractModel $menu)
    {
        try {
            $this->sibCountryCodeResource->save($menu);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __($e->getMessage()),
                $e
            );
        }
        return $menu;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $menu
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(\Magento\Framework\Model\AbstractModel $menu)
    {
        try {
            $this->sibCountryCodeResource->delete($menu);
        }  catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('The "%1" country code couldn\'t be removed.', $menu->getId()),
                $e
            );
        }
    }
}
