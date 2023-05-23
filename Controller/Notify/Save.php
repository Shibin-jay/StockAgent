<?php

namespace Codilar\NotifyStock\Controller\Notify;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Model\Session as CustomerSession;

use Codilar\NotifyStock\Api\NotificationRepositoryInterface;

class Save extends Action
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var NotificationRepositoryInterface
     */
    private $notificationRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        NotificationRepositoryInterface $notificationRepository,
        CustomerSession $customerSession
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->notificationRepository = $notificationRepository;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $params = $this->getRequest()->getParams();
        $data = [
            'email' => isset($params['email']) ? $params['email'] : null,
            'product_id' => isset($params['product_id']) ? $params['product_id'] : null,
            'name' => isset($params['name']) ? $params['name'] : null,
            'customer_id' => isset($params['customer_id']) ? $params['customer_id'] : null
        ];
        if (!$data['email'] || !$data['product_id']) {
            return $resultJson->setData(['success' => false, 'message' => __('Please enter a valid email address')]);
        }

        if (!$data['customer_id'] && $data['name'] && $data['email']) {
            $data['customer_id'] = null;
        }
        $data['customer_id'] = null;

        try {
            $this->notificationRepository->saveNotification($data);
        } catch (LocalizedException $e) {
            return $resultJson->setData(['success' => false, 'message' => $e->getMessage()]);
        }

        return $resultJson->setData(['success' => true, 'message' => __('You will be notified when the product is back in stock')]);
    }
}
