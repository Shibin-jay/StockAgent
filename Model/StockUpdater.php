<?php

namespace Codilar\NotifyStock\Model;

use Codilar\NotifyStock\Model\NotificationRepository;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\Exception\LocalizedException;

class StockUpdater
{
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var NotificationRepository
     */
    private $notificationRepository;

    /**
     * StockUpdater constructor.
     * @param StockRegistryInterface $stockRegistry
     * @param NotificationRepository $notificationRepository
     */
    public function __construct(
        StockRegistryInterface $stockRegistry,
        NotificationRepository $notificationRepository
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * Update stock and create notifications for out-of-stock products
     */
    public function execute()
    {
        try {
            $outOfStockProducts = $this->getOutOfStockProducts();

            foreach ($outOfStockProducts as $product) {
                $this->updateStock($product);
                $this->createNotification($product);
            }
        } catch (LocalizedException $e) {
            // Handle exception
        }
    }

    /**
     * Retrieve out-of-stock products
     *
     * @return array
     * @throws LocalizedException
     */
    private function getOutOfStockProducts()
    {
        $outOfStockProducts = [];

        $stockItems = $this->stockRegistry->getList();
        foreach ($stockItems as $stockItem) {
            if ($this->isOutOfStock($stockItem)) {
                $outOfStockProducts[] = $stockItem->getProduct();
            }
        }

        return $outOfStockProducts;
    }

    /**
     * Check if the stock item is out of stock
     *
     * @param StockItemInterface $stockItem
     * @return bool
     */
    private function isOutOfStock(StockItemInterface $stockItem)
    {
        return $stockItem->getIsInStock() == false;
    }

    /**
     * Update stock status to in-stock
     *
     * @param \Magento\Catalog\Model\Product $product
     * @throws LocalizedException
     */
    private function updateStock($product)
    {
        $productId = $product->getId();
        $stockItem = $this->stockRegistry->getStockItem($productId);

        $stockItem->setIsInStock(true);
        $stockItem->setQty(1); // Set the desired quantity
        $stockItem->setIsQtyDecimal(false); // Set if the quantity is decimal or not

        $this->stockRegistry->updateStockItemBySku($product->getSku(), $stockItem);
    }

    /**
     * Create a notification entry for the out-of-stock product
     *
     * @param \Magento\Catalog\Model\Product $product
     */
    private function createNotification($product)
    {
        $notificationData = [
            'name' => $product->getName(),
            'email' => $product->getEmail(),
            'product_id' => $product->getId(),
            'customer_id' => $product->getCustomerId(), // Set the customer ID
        ];

        $this->notificationRepository->saveNotification($notificationData);
    }
}
