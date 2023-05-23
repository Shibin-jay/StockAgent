<?php
namespace Codilar\NotifyStock\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Codilar\NotifyStock\Api\Data\NotificationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class Notification extends AbstractModel implements NotificationInterface
{
//    const NAME = 'name';
//    const EMAIL = 'email';
//    const PRODUCT_ID = 'product_id';
//    const CUSTOMER_ID = 'customer_id';

     /**
     * @var GetSalableQuantityDataBySku
     */
    private $getSalableQuantityDataBySku;

    /**
     * @var stockRegistryInterface
     */
    private $stockRegistry;


    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Codilar\NotifyStock\Model\ResourceModel\Notification $resource,
        \Codilar\NotifyStock\Model\ResourceModel\Notification\Collection $resourceCollection,
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        // $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Codilar\NotifyStock\Model\ResourceModel\Notification');
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * @return int|null
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Retrieve notification by id
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
     * Get product name by ID
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
     * Retrieve the quantity of a product by its product ID
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
//            die($stockItem->getQty());
        } catch (\Exception $e) {
            return null;
        }
    }

}
