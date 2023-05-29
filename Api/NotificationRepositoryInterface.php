<?php

namespace Codilar\NotifyStock\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Codilar\NotifyStock\Api\Data\NotificationInterface;

interface NotificationRepositoryInterface
{
    /**
     * Save notification.
     *
     * @param array $data
     * @return bool
     * @throws CouldNotSaveException
     */
    public function saveNotification(array $data);

    /**
     * Retrieve notifications by status.
     *
     * @param int $status
     * @return \Codilar\NotifyStock\Api\Data\NotificationInterface[]
     */
    public function getListByStatus($status);

    /**
     * Retrieve notifications based on search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Codilar\NotifyStock\Api\Data\NotificationSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Retrieve product by ID.
     *
     * @param int $productId
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     */
    public function getProductById($productId);
}
