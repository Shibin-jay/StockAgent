<?php
namespace Codilar\NotifyStock\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Notification extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('product_alert_stock', 'alert_stock_id');
    }
}