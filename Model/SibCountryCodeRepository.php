<?php
/**
 * @package     Dadolun_SibContactSync
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license    This code is licensed under MIT license (see LICENSE for details)
 */

namespace Dadolun\SibContactSync\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use \Dadolun\SibContactSync\Api\Data\SibCountryCodeInterface;
use \Dadolun\SibContactSync\Api\Data\SibCountryCodeInterfaceFactory;
use \Dadolun\SibContactSync\Api\SibCountryCodeRepositoryInterface;
use \Dadolun\SibContactSync\Api\SibCountryCodeResourceInterface;
use \Dadolun\SibContactSync\Model\ResourceModel\SibCountryCode\CollectionFactory as SibCountryCodeCollectionFactory;
use \Dadolun\SibContactSync\Model\ResourceModel\SibCountryCode\Collection;
use Magento\Framework\Model\AbstractModel;

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
     * @return AbstractModel|SibCountryCodeInterface|Collection|null
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
     * @return AbstractModel|SibCountryCodeInterface|null
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
     * @param AbstractModel $menu
     * @return AbstractModel|SibCountryCodeInterface|null
     * @throws CouldNotSaveException
     */
    public function save(AbstractModel $menu)
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
     * @param AbstractModel $menu
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(AbstractModel $menu)
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
