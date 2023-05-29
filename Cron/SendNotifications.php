<?php

namespace Codilar\NotifyStock\Cron;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\ProductAlert\Block\Email\Stock;
use Psr\Log\LoggerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Helper\View;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\ProductAlert\Helper\Data;
use Magento\ProductAlert\Model\ProductSalability;
use Magento\Store\Api\Data\WebsiteInterface;

use Codilar\NotifyStock\Model\StockUpdater;
use Codilar\NotifyStock\Api\NotificationRepositoryInterface;
use Codilar\NotifyStock\Api\Data\NotificationInterface;
use Codilar\NotifyStock\Model\ResourceModel\Notification\CollectionFactory as NotificationCollectionFactory;
use function Safe\date;

class SendNotifications
{
    /**
     * Product collection array
     *
     * @var \Magento\Catalog\Model\Product[]
     */
    protected $_products = [];

    /**
     * Product collection which of back in stock
     *
     * @var array
     */
    protected $_stockProducts = [];
    /**
     * @var array
     */
    private $notificationCollectionFactory;
    private $logger;
    private $notificationRepository;
    private $notificationInterface;
    private $transportBuilder;
    private $inlineTranslation;
    private $storeManager;
    private $scopeConfig;
    private $productRepository;
    private $appEmulation;

    /**
     * Stock block
     *
     * @var Stock

     */
    protected $_stockBlock;

    /**
     * @var ProductSalability
     */
    private $productSalability;

    public function __construct(
        StockUpdater $stockUpdater,
        NotificationRepositoryInterface $notificationRepository,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductRepositoryInterface $productRepository,
        ProductCollectionFactory $productCollectionFactory,
        Data $productAlertData,
        LoggerInterface $logger,
        Emulation $appEmulation,
        View $customerHelper,
        NotificationCollectionFactory $notificationCollectionFactory,
        NotificationInterface $notificationInterface,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        ProductSalability $productSalability,
    ) {
        $this->productSalability = $productSalability;
        $this->_productAlertData = $productAlertData;
        $this->stockUpdater = $stockUpdater;
        $this->notificationRepository = $notificationRepository;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->appEmulation = $appEmulation;
        $this->customerHelper = $customerHelper;
        $this->notificationCollectionFactory = $notificationCollectionFactory;
        $this->notificationInterface = $notificationInterface;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    public function execute()
    {
        $notificationsData = $this->getPendingNotifications();
        // $website = $this->storeManager->getWebsite($websiteId);
        // $defaultStoreId = $website->getDefaultStore()->getId();
        foreach ($notificationsData as $notification) {
            $product = $this->productRepository->getById($notification->getProductId());
            // $this->sendEmail($notification);
            $this->updateDatabase($notification, $product);
        }
    }
    public function setStore($store)
    {
        if ($store instanceof \Magento\Store\Model\Website) {
            $store = $store->getDefaultStore();
        }
        if (!$store instanceof \Magento\Store\Model\Store) {
            $store = $this->storeManager->getStore($store);
        }

        $this->_store = $store;

        return $this;
    }
    /**
     * Retrieve the store for the email
     *
     * @param int $storeId
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    private function getStore(int $storeId): StoreInterface
    {
        return $this->storeManager->getStore($storeId);

    }
    public function getPendingNotifications()
    {
        try {
            $notificationCollection = $this->notificationCollectionFactory->create();
            $notificationCollection->addFieldToFilter('status', ['eq' => 0])
                ->addFieldToFilter('send_date', ['null' => true]);

            return $notificationCollection;
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    public function reset()
    {
        $this->_products = [];
    }
    private function sendEmail($notification)
    {
        $products = $this->getProducts();
        $customerName = $notification->getName();
        $email = $notification->getEmail();
        $productId = $notification->getProductId();
        $productQty = (int)$this->notificationInterface->getProductQuantityById($productId);
        $productName = $this->notificationInterface->getProductNameById($productId);
        // make it opposite for production ...as of now its for testing purposes
        if ($productQty !== 0) {
            return false;
        }
        try {
            $this->inlineTranslation->suspend();
            $storeId = (int)$this->getStoreId();
            $store = $this->getStore($storeId);
            $senderName = $this->scopeConfig->getValue('stockAgent_settings/notify_config/sender_name', ScopeInterface::SCOPE_STORE);
            $senderEmail = $this->scopeConfig->getValue('stockAgent_settings/notify_config/sender_email', ScopeInterface::SCOPE_STORE);

            $sender = [
                'name' => $senderName,
                'email' => $senderEmail,
            ];
            $this->appEmulation->startEnvironmentEmulation($storeId);

            $block = $this->_getStockBlock();
            $this->setStore($store)->reset();

            // Add products to the block
            foreach ($products as $product) {
                $product->setCustomerGroupId($this->_customer->getGroupId());
                $block->addProduct($product);
            }
            $alertGrid = $this->_appState->startEnvironmentEmulation(
                $storeId,
                \Magento\Framework\App\Area::AREA_FRONTEND,
                true);
            $this->appEmulation->stopEnvironmentEmulation();
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('stockAgent_settings_notify_config_template')
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ])
                ->setTemplateVars([
                    'customerName' => $customerName,
                    'alertGrid' => $alertGrid,
                ])
                ->addTo($email, $customerName)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();

            return true;
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }
    private function getStockProducts(): array
    {
        return $this->_stockProducts;
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
    private function getCustomerStoreId()
    {
        if ($this->_stockBlock === null) {
            $this->_stockBlock = $this->_productAlertData->createBlock(Stock::class);
        }
        return $this->_stockBlock;
    }
   private function getStoreId()
   {
       return $this->storeManager->getStore()->getId();
   }
    /**
     * Add product (back in stock) to collection
     *
     * @param Product $product
     *
     * @return $this
     */
   public function addStockProduct(ProductInterface $product)
   {
    $this->_stockProducts=[];
    $this->_stockProducts[$product->getId()] = $product;
    return $this;
   }

   private function updateDatabase($notification,ProductInterface $product)
    {
//        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
//        $zendLogger = new \Zend_Log();
//        $zendLogger->addWriter($writer);
//        $zendLogger->info(" Message Log " . print_r($notification, true));
//        die();
         $notification->setSendDate(date('Y-m-d H:i:s'));
         $notification->setSendCount($notification->getSendCount() + 1);
         $notification->setStatus(1);

        try {
            $this->notificationRepository->saveNotification($notification);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    private function isProductBackInStock(ProductInterface $product)
    {
//        die("here 2");
        $sku = $product -> getSku();
        $salable = $this->getSalableQuantityDataBySku->execute($sku);
//        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
//        $zendLogger = new \Zend_Log();
//        $zendLogger->addWriter($writer);
//        $zendLogger->info(" Message Log " .$salable[0]['qty']);
    }
//   public function  saveStockAlert( $notification,ProductInterface $product){
//    if ($this->getSalableQuantityDataBySku($product->getSku()) < 1);
//       {
//           return false;
//       }
////       $notification->setSendDate(date(DateTime::DATETIME_PHP_FORMAT));
//       $notification->setSendDate(date(DateTime::ATOM));
//       $notification->setSendCount($alert->getSendCount +1);
//       $notification->setStatus(1);
//       $this->stockAlert->save($alert);
//       return  true;
//   }

    /**
     * Get products matching specific criteria
     *
     * @return ProductInterface[]
     * @throws NoSuchEntityException
     */
    public function getProducts():array
    {
        return $this->_stockProducts;
    }
    /**
     * Clean data
     *
     * @return $this
     */
    public function clean()
    {
        $this->_customer = null;
        $this->_priceProducts = [];
        $this->_stockProducts = [];

        return $this;
    }

}
