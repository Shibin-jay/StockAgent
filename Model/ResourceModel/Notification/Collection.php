<?php
namespace Codilar\NotifyStock\Model\ResourceModel\Notification;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Codilar\NotifyStock\Model\Notification;
use Codilar\NotifyStock\Model\ResourceModel\Notification as NotificationResourceModel;

/**
 * Class Collection
 *
 * collection of notifications
 */
class Collection extends AbstractCollection
{
    /**
     * Initialize collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Notification::class, NotificationResourceModel::class);
    }
}
