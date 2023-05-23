<?php

namespace Codilar\NotifyStock\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Codilar\NotifyStock\Api\NotificationRepositoryInterface;
use Codilar\NotifyStock\Api\Data\NotificationInterface;
use Codilar\NotifyStock\Model\ResourceModel\Notification as NotificationResource;

use Codilar\NotifyStock\Model\ResourceModel\Notification\CollectionFactory as NotificationCollectionFactory;

class NotificationRepository implements NotificationRepositoryInterface
{
    /**
     * @var NotificationFactory
     */
    private $notificationFactory;

    /**
     * @var NotificationCollectionFactory
     */
    private $notificationCollectionFactory;
    /**
     * @var NotificationResource
     */
    private $notificationResource;

    public function __construct(
        NotificationFactory $notificationFactory,
        NotificationCollectionFactory $notificationCollectionFactory,
        NotificationResource $notificationResource
    ) {
        $this->notificationResource = $notificationResource;
        $this->notificationFactory = $notificationFactory;
        $this->notificationCollectionFactory = $notificationCollectionFactory;
    }

    /**
     * Save notification
     *
     * @param array $data
     * @return bool
     * @throws CouldNotSaveException
     */
    public function saveNotification(array $data)
    {
        try {
            $notification = $this->notificationFactory->create();
            $notification->setData($data);
            $this->notificationResource->save($notification);

            return true;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
    }

    /**
     * Retrieve notifications by status
     *
     * @param int $status
     * @return \Codilar\NotifyStock\Api\Data\NotificationInterface[]
     */
    public function getListByStatus($status)
    {
        $collection = $this->notificationCollectionFactory->create();
        $collection->addFieldToFilter('status', $status);

        return $collection->getItems();
    }

    /**
     * Retrieve notifications based on search criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Codilar\NotifyStock\Api\Data\NotificationInterface[]
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->notificationCollectionFactory->create();

        $searchResults = $this->notificationCollectionFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
