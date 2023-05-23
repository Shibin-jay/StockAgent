<?php
namespace Codilar\NotifyStock\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Customer\Model\Session;
use Codilar\NotifyStock\Model\ResourceModel\Notification\CollectionFactory;


class SalableQtyStatus extends \Magento\Framework\View\Element\Template
{
    protected $_productRepository;
    protected $_getProductSalableQty;
    protected $customerSession;
    protected $_notificationCollectionFactory;

    protected $userContext;

    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        GetProductSalableQtyInterface $getProductSalableQty,
        Session $customerSession,
        CollectionFactory $notificationCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_productRepository = $productRepository;
        $this->_getProductSalableQty = $getProductSalableQty;
        $this->customerSession = $customerSession;
        $this->_notificationCollectionFactory = $notificationCollectionFactory;
    }

    public function getSalableQty($productId)
    {
        $product = $this->_productRepository->getById($productId);
        $salableQty = $this->_getProductSalableQty->execute($product->getSku(), $product->getStore()->getWebsiteId());
        return $salableQty;
    }

    public function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    public function getCustomerEmail()
    {
        if ($this->isCustomerLoggedIn()) {
            return $this->customerSession->getCustomer()->getEmail();
        }
        return null;
    }

    public function getCustomerId()
    {
        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomer()->getId();
            return $customerId;
        }
        return null;
    }


    public function getNotificationCollection()
    {
        $collection = $this->_notificationCollectionFactory->create();
        if ($this->isCustomerLoggedIn()) {
            $collection->addFieldToFilter('email', ['eq' => $this->getCustomerEmail()]);
        }
        return $collection;
    }
    public function getCustomerIdLO()
    {
      echo $this->customerSession->getCustomer()->getId();
    }
    /**
     * Retrieve stock block
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
