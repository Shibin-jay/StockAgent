<?php
namespace Codilar\NotifyStock\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Notification
 *
 * Resource model for notification
 */
class Notification extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('product_alert_stock', 'alert_stock_id');
    }
}
