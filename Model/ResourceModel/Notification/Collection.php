<?php
namespace Codilar\NotifyStock\Model\ResourceModel\Notification;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Codilar\NotifyStock\Model\Notification',
            'Codilar\NotifyStock\Model\ResourceModel\Notification'
        );
    }
}
