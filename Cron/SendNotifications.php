<?php

namespace Codilar\NotifyStock\Cron;

use Psr\Log\LoggerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Framework\Exception\LocalizedException;
use Codilar\NotifyStock\Model\StockUpdater;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Codilar\NotifyStock\Api\NotificationRepositoryInterface;
use Codilar\NotifyStock\Api\Data\NotificationInterface;
use Codilar\NotifyStock\Model\ResourceModel\Notification\CollectionFactory as NotificationCollectionFactory;

class SendNotifications
{
    /**
     * @var NotificationCollectionFactory
     */
    private $notificationCollectionFactory;
    private $logger;
    private $stockUpdater;
    private $notificationRepository;
    private $notificationInterface;
    private $transportBuilder;
    protected $getSalableQuantityDataBySku;
    private $inlineTranslation;
    private $scopeConfig;

    public function __construct(
        StockUpdater $stockUpdater,
        NotificationRepositoryInterface $notificationRepository,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        LoggerInterface $logger,
        NotificationCollectionFactory $notificationCollectionFactory,
        NotificationInterface $notificationInterface,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->logger = $logger;
        $this->notificationInterface = $notificationInterface;
        $this->stockUpdater = $stockUpdater;
        $this->notificationRepository = $notificationRepository;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        $this->notificationCollectionFactory = $notificationCollectionFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        $notificationsData = $this->getPendingNotifications();
        foreach ($notificationsData as $data) {
            $productId = $data->getProductId();
            $this->sendEmail($data);
            // $this->updateDatabase();
        }
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

    /**
     * Send Email for the clients.
     *
     * @return NULL
     * @throws Exception
     */
    private function sendEmail($notification)
    {
        $name = $notification->getName();
        $email = $notification->getEmail();
        $product_ids = $this->getProductIdsByCustomer($email,$name);
        $productId = $notification->getProductId();
        $productQty = (int)$this->notificationInterface->getProductQuantityById($productId);
        $productName = $this->notificationInterface->getProductNameById($productId);
        if($productQty !== 0){
            return false;
        }
        try {
            $this->inlineTranslation->suspend();
            $senderName = $this->scopeConfig->getValue(
                'stockAgent_settings/notify_config/sender_name',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $senderEmail = $this->scopeConfig->getValue(
                'stockAgent_settings/notify_config/sender_email',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $sender =[
                'name' => $senderName,
                'email' => $senderEmail
            ];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('stockAgent_settings_notify_config_template')
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ])
                ->setTemplateVars([
                    'customer_name' => $name,
                    'sender_name' => $senderName,
                    'product_ids' => $product_ids,
                ])
                ->setFrom($sender)
                ->addTo($email, $name)
                ->getTransport();
            $transport->sendMessage();

            $this->inlineTranslation->resume();

        } catch (\Exception $e) {
            // Handle email sending exception
            $this->logger->debug($e->getMessage());
        }
    }

    private function updateDatabase($notification)
    {
        $notification->setSendDate(date('Y-m-d H:i:s'));
        $notification->setSendCount($notification->getSendCount() + 1);
        $notification->setStatus(1);

        try {
            $this->notificationRepository->save($notification);
        } catch (\Exception $e) {
            // Handle database update exception
            $this->logger->debug($e->getMessage());
        }
    }
    private function getProductIdsByCustomer($email, $name)
    {
        try {
            $notificationCollection = $this->notificationCollectionFactory->create();
            $notificationCollection->addFieldToFilter('email', ['eq' => $email])
            ->addFieldToFilter('name', ['eq' => $name])
            ->addFieldToFilter('status', ['eq' => 0])
            ->addFieldToFilter('send_date', ['null' => true])
            ->addOrder('product_id', 'ASC')
            ->setPageSize(0);
            return $notificationCollection->getColumnValues('product_id');
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }
}
