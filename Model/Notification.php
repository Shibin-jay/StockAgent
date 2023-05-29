<?php
namespace Codilar\NotifyStock\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Codilar\NotifyStock\Api\Data\NotificationInterface;

class Notification extends AbstractModel implements NotificationInterface
{
    /**
     * @var GetSalableQuantityDataBySku
     */
    private $getSalableQuantityDataBySku;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Notification constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Codilar\NotifyStock\Model\ResourceModel\Notification $resource
     * @param \Codilar\NotifyStock\Model\ResourceModel\Notification\Collection $resourceCollection
     * @param ProductRepositoryInterface $productRepository
     * @param StockRegistryInterface $stockRegistry
     * @param GetSalableQuantityDataBySku $getSalableQuantityDataBySku
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Codilar\NotifyStock\Model\ResourceModel\Notification $resource,
        \Codilar\NotifyStock\Model\ResourceModel\Notification\Collection $resourceCollection,
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize model
     */
    protected function _construct()
    {
        $this->_init(\Codilar\NotifyStock\Model\ResourceModel\Notification::class);
    }

    /**
     * Get the name attribute value
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Set the name attribute value
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get the email attribute value
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * Set the email attribute value
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Get the product ID attribute value
     *
     * @return int|null
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * Set the product ID attribute value
     *
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Get the customer ID attribute value
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Set the customer ID attribute value
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Retrieve notification by ID
     *
     * @param int $notificationId
     * @return $this|null
     */
    public function getById($notificationId)
    {
        $notification = $this->load($notificationId);
        if (!$notification->getId()) {
            return null;
        }
        return $notification;
    }

    /**
     * Get the product name by ID
     *
     * @param int $productId
     * @return string|null
     */
    public function getProductNameById($productId)
    {
        try {
            $product = $this->productRepository->getById($productId);
            return $product->getName();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get the product quantity by ID
     *
     * @param int $productId
     * @return float|null
     */
    public function getProductQuantityById($productId)
    {
        try {
            $product = $this->productRepository->getById($productId);
            $stockItem = $this->stockRegistry->getStockItem($product->getId());
            return $stockItem->getQty();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if the product is salable
     *
     * @param ProductInterface $product
     * @param WebsiteInterface $website
     * @return bool
     */
    public function isSalable(ProductInterface $product, WebsiteInterface $website): bool
    {
        return $product->isSalable();
    }
}
