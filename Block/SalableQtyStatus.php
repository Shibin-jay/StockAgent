<?php

namespace Codilar\NotifyStock\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Customer\Model\Session;
use Codilar\NotifyStock\Model\ResourceModel\Notification\CollectionFactory;
use Magento\Customer\Model\Customer;
use Magento\Framework\View\Element\Template;

class SalableQtyStatus extends Template
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var GetProductSalableQtyInterface
     */
    protected $getProductSalableQty;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var CollectionFactory
     */
    protected $notificationCollectionFactory;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * SalableQtyStatus constructor.
     *
     * @param Context                       $context
     * @param ProductRepositoryInterface    $productRepository
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param Session                       $customerSession
     * @param CollectionFactory             $notificationCollectionFactory
     * @param Customer                      $customer
     * @param array                         $data
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        GetProductSalableQtyInterface $getProductSalableQty,
        Session $customerSession,
        CollectionFactory $notificationCollectionFactory,
        Customer $customer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productRepository = $productRepository;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->customerSession = $customerSession;
        $this->notificationCollectionFactory = $notificationCollectionFactory;
        $this->customer = $customer;
    }

    /**
     * Get salable quantity for a product.
     *
     * @param int $productId
     *
     * @return float|int
     */
    public function getSalableQty($productId)
    {
        $product = $this->productRepository->getById($productId);
        $salableQty = $this->getProductSalableQty->execute($product->getSku(), $product->getStore()->getWebsiteId());
        return $salableQty;
    }

    /**
     * Check if the customer is logged in.
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * Get the customer's email.
     *
     * @return string|null
     */
    public function getCustomerEmail()
    {
        if ($this->isCustomerLoggedIn()) {
            return $this->customerSession->getCustomer()->getEmail();
        }
        return null;
    }

    /**
     * Get the customer's ID.
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->customerSession->getCustomer()->getId();
        }
        return null;
    }

    /**
     * Get the notification collection.
     *
     * @return \Codilar\NotifyStock\Model\ResourceModel\Notification\Collection
     */
    public function getNotificationCollection()
    {
        $collection = $this->notificationCollectionFactory->create();
        if ($this->isCustomerLoggedIn()) {
            $collection->addFieldToFilter('email', ['eq' => $this->getCustomerEmail()]);
        }
        return $collection;
    }
    // the phpstan has an error over here 

    /**
     * Retrieve stock block.
     *
     * @return Stock
     * @throws LocalizedException
     */
    protected function _getStockBlock()
    {
        if ($this->_stockBlock === null) {
            $this->_stockBlock = $this->_productAlertData->createBlock(Stock::class);
        }
        return $this->_stockBlock;
    }
}
